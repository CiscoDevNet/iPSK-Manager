<?php
require __DIR__.'/review-bootstrap.php';
class MigrationRoutineStub {
    public $queries=[], $error='fixture failure', $mode='', $exists=true;
    function query($sql) {
        $this->queries[]=$sql;
        if (str_starts_with($sql,'SHOW CREATE')) return new class { function fetch_assoc(){ return ['Create Procedure'=>'CREATE PROCEDURE `iPSK_AttributeFetch` () SELECT 1']; } };
        if (str_contains($sql,'CREATE DEFINER') && str_contains($sql,'ipsk_preflight_') && $this->mode==='privilege') return false;
        if (preg_match('/DROP PROCEDURE IF EXISTS `iPSK_AttributeFetch`/',$sql)) $this->exists=false;
        if (str_contains($sql,'CREATE DEFINER') && !str_contains($sql,'ipsk_preflight_') && $this->mode==='replace') throw new RuntimeException('replace failed');
        if (str_starts_with($sql,'CREATE PROCEDURE `iPSK_AttributeFetch`')) $this->exists=true;
        return true;
    }
    function more_results(){return false;}
}
$path=__DIR__.'/../supportfiles/db/migrations';
foreach (['missing','privilege','replace','success'] as $mode) {
    $c=new MigrationRoutineStub();$c->mode=$mode;
    $replacements=['{{DB_NAME}}'=>'fixture'];
    if ($mode!=='missing') $replacements['{{ISE_DB_DEFINER}}']='`fixture`@`%`';
    $runner=new iPSKManagerMigration($c,'fixture',$path,$replacements);
    $result=$runner->executeMigration($runner->getMigrations()[0]);
    verify($result['success']===($mode==='success'),'Wrong migration outcome');
    if ($mode==='missing') verify(!$c->queries,'Missing replacement executed SQL');
    if ($mode==='privilege' || $mode==='replace') verify($c->exists,'Working procedure lost');
}
echo "PASS review 11: preflight replacements/definer privileges and restore failed routine replacement\n";
