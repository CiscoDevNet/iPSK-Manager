<?php
require __DIR__.'/review-bootstrap.php';

class DefinerRows {
    public $num_rows;
    function __construct(private $rows) { $this->num_rows=count($rows); }
    function fetch_assoc() { return array_shift($this->rows); }
}
class DefinerConnection {
    public $queries=[], $routines=[], $hidden=[], $errno=0, $error='fixture error';
    public $strict=true, $unreadable=false, $denyCreate=false, $metadataError=false;
    function prepare($sql) {
        if ($this->metadataError) throw new RuntimeException('Metadata denied');
        return new class($this, $sql) {
            public $field_count=1, $values=[];
            function __construct(private $connection, private $sql) {}
            function bind_param($types, ...$values) { $this->values=$values; return true; }
            function execute() { return true; }
            function get_result() {
                $names=array_slice($this->values,1); $rows=[];
                foreach ($this->connection->routines as $name=>$owner) {
                    if (in_array($name,$names,true) && !in_array($name,$this->connection->hidden,true)) $rows[]=['DEFINER'=>$owner];
                }
                if (str_contains($this->sql,'DISTINCT')) $rows=array_values(array_unique($rows,SORT_REGULAR));
                return new DefinerRows($rows);
            }
            function close() {}
        };
    }
    function query($sql) {
        $this->queries[]=$sql; $this->errno=0;
        if (preg_match('/SHOW CREATE PROCEDURE `([^`]+)`/', $sql, $m)) {
            if (!isset($this->routines[$m[1]]) || in_array($m[1],$this->hidden,true)) {
                if ($this->strict) throw new RuntimeException('Routine does not exist',1305);
                $this->errno=1305; return false;
            }
            $owner=$this->routines[$m[1]];
            $at=strrpos($owner,'@');
            $quoted='`'.str_replace('`','``',substr($owner,0,$at)).'`@`'.str_replace('`','``',substr($owner,$at+1)).'`';
            return new DefinerRows([['Create Procedure'=>$this->unreadable ? null : 'CREATE DEFINER='.$quoted.' PROCEDURE `'.$m[1].'`() SELECT 1']]);
        }
        if (preg_match('/CREATE DEFINER=(`(?:``|[^`])+`@`(?:``|[^`])+`) PROCEDURE `([^`]+)`/', $sql, $m)) {
            if ($this->denyCreate) throw new RuntimeException('CREATE denied',1227);
            if (isset($this->routines[$m[2]])) throw new RuntimeException('Routine already exists',1304);
            $this->routines[$m[2]]=$m[1];
        }
        if (preg_match('/DROP PROCEDURE(?: IF EXISTS)? `([^`]+)`/', $sql, $m)) unset($this->routines[$m[1]]);
        return true;
    }
    function more_results() { return false; }
    function rollback() {}
}
class DefinerDatabase extends iPSKManagerDatabase {
    function set_dbSchemaVersion($version) { $this->dbSchemaVersion=$version; return true; }
}
function definerDatabase($connection, $version=6) {
    $db=new DefinerDatabase();
    $db->set_dbDatabase('fixture'); $db->set_dbUsername('migration-admin'); $db->dbSchemaVersion=$version;
    (new ReflectionProperty(iPSKManagerDatabase::class,'dbConnection'))->setValue($db,$connection);
    return $db;
}
$old=$_SERVER['ISE_DB_USERNAME']??null; unset($_SERVER['ISE_DB_USERNAME']);
try {
    foreach ([true,false] as $strict) {
        $c=new DefinerConnection(); $c->strict=$strict;
        $c->routines=['iPSK_AuthMACPlain'=>"ise`owner@domain@10.%", 'iPSK_FetchGroups'=>"ise`owner@domain@10.%", 'iPSK_MigrationTestProc'=>'admin@localhost'];
        $db=definerDatabase($c); $r=$db->runSchemaMigrations(7);
        verify($r['success'] && $r['currentVersion']===7, 'Missing attribute routine not repaired: '.$r['error']);
        verify($c->routines['iPSK_AttributeFetch']==='`ise``owner@domain`@`10.%`', 'Recovery changed ISE owner/host or selected unrelated owner');
        verify(!preg_grep('/DROP PROCEDURE IF EXISTS `iPSK_AttributeFetch`/', $c->queries), 'Recovery issued an unprotected DROP');
    }
    $c=new DefinerConnection(); $c->routines=['iPSK_AttributeFetch'=>'specific@localhost', 'iPSK_AuthMACPlain'=>'other@%'];
    $db=definerDatabase($c); verify($db->runSchemaMigrations(7)['success'], 'Existing target owner must take precedence over siblings');
    verify($c->routines['iPSK_AttributeFetch']==='`specific`@`localhost`', 'Existing definer changed');
    foreach (['ambiguous','unavailable','metadata-error','unreadable','hidden','create-denied'] as $mode) {
        $c=new DefinerConnection(); $c->routines=['iPSK_AuthMACPlain'=>'ise@%'];
        if ($mode==='ambiguous') $c->routines['iPSK_FetchGroups']='other@localhost';
        if ($mode==='unavailable') $c->routines=['unrelated'=>'admin@%'];
        if ($mode==='metadata-error') $c->metadataError=true;
        if (in_array($mode,['unreadable','hidden'],true)) $c->routines['iPSK_AttributeFetch']='original@localhost';
        if ($mode==='unreadable') $c->unreadable=true;
        if ($mode==='hidden') $c->hidden=['iPSK_AttributeFetch'];
        if ($mode==='create-denied') $c->denyCreate=true;
        $db=definerDatabase($c); $r=$db->runSchemaMigrations(7);
        verify(!$r['success'] && $r['currentVersion']===6 && !$r['applied'], 'Unsafe failure advanced version: '.$mode);
        verify(!preg_grep('/DROP PROCEDURE IF EXISTS `iPSK_AttributeFetch`/', $c->queries), 'Failure dropped target: '.$mode);
        if (isset($c->routines['iPSK_AttributeFetch'])) verify($c->routines['iPSK_AttributeFetch']==='original@localhost', 'Hidden existing procedure overwritten');
        if (in_array($mode,['ambiguous','unavailable','metadata-error','unreadable'],true)) verify(str_contains($r['error'],'alternate DB credentials'), 'Error lacks recovery instructions: '.$mode);
    }
    $c=new DefinerConnection(); $c->routines=['iPSK_AttributeFetch'=>'ise@localhost'];
    $db=definerDatabase($c,9); $r=$db->runSchemaMigrations(10);
    verify($r['success'] && $c->routines['iPSK_AuthMACPlain']==='`ise`@`localhost`', 'Missing plain-MAC routine not repaired');
    $_SERVER['ISE_DB_USERNAME']="explicit`ise";
    $r=$db->get_migrationReplacements(['{{PLAIN_MAC_DEFINER}}'=>'`override`@`host`']);
    verify($r['{{ISE_DB_DEFINER}}']==='`explicit``ise`@`%`' && $r['{{PLAIN_MAC_DEFINER}}']==='`override`@`host`', 'Explicit configuration precedence changed');
} finally {
    if ($old===null) unset($_SERVER['ISE_DB_USERNAME']); else $_SERVER['ISE_DB_USERNAME']=$old;
}
echo "PASS migration definers: missing-routine recovery, original owner/host preservation, ambiguous owners, hidden/unreadable routines and denied creation\n";
