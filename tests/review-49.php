<?php
require __DIR__.'/review-bootstrap.php';
class DiscoveryDatabase extends iPSKManagerDatabase {
    function get_migrationReplacements($extra=[]) { return []; }
    function set_dbSchemaVersion($version) { $this->dbSchemaVersion=$version; return true; }
}
$dir=sys_get_temp_dir().'/ipsk-migrations-'.bin2hex(random_bytes(8));mkdir($dir);
try {
    foreach (['missing','empty','gap','complete','current'] as $case) {
        foreach(glob($dir.'/*') as $f) unlink($f);
        $db=new DiscoveryDatabase();$db->dbSchemaVersion=$case==='current'?7:6;
        (new ReflectionProperty(iPSKManagerDatabase::class,'migrationPath'))->setValue($db,$case==='missing'?$dir.'/missing':$dir);
        $c=new class { function query($sql) {return true;} function more_results(){return false;} };
        (new ReflectionProperty(iPSKManagerDatabase::class,'dbConnection'))->setValue($db,$c);
        if($case==='gap') file_put_contents($dir.'/v8__gap.sql','SELECT 1;');
        if($case==='complete') file_put_contents($dir.'/v7__test.sql','SELECT 1;');
        $r=$db->runSchemaMigrations(7);
        verify($r['success']===in_array($case,['complete','current'],true),'Wrong discovery outcome: '.$case);
        verify($r['currentVersion']===($r['success']?7:6) && $r['targetVersion']===7,'Wrong achieved version');
    }
} finally {foreach(glob($dir.'/*') as $f) unlink($f);rmdir($dir);}
echo "PASS review 49: missing/empty/gapped migration paths fail, complete/current upgrades report achieved version\n";
