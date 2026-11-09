<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/../supportfiles/include/iPSKInstaller.php';
class mysqli_sql_exception extends RuntimeException {}
class mysqli {
    static $exists=false,$users=[],$fail='', $throw=true,$failed=false,$queries=[];
    function __construct(...$args){}
    function real_escape_string($s){return addslashes($s);}
    function select_db($name){if($name==='mysql')return true;if(!self::$exists)throw new mysqli_sql_exception('Unknown database');return true;}
    function query($q){self::$queries[]=$q;
        if(self::$fail!=='' && !self::$failed && str_contains($q,self::$fail)){self::$failed=true;if(self::$throw)throw new mysqli_sql_exception('Injected installation failure');return false;}
        if(str_starts_with($q,'CREATE DATABASE'))self::$exists=true;
        if(str_starts_with($q,'DROP DATABASE'))self::$exists=false;
        if(preg_match("/^CREATE USER '([^']+)'/",$q,$m))self::$users[$m[1]]=true;
        if(preg_match("/^DROP USER '([^']+)'/",$q,$m))unset(self::$users[$m[1]]);
        return true;
    }
}
function generatePassword($length){return str_repeat('a',$length);}
function generateEncryptionKey(){return base64_encode(str_repeat('k',32));}
$source=file_get_contents(__DIR__.'/../adminportal/installer.php');$start=strpos($source,"elseif(\$_POST['step'] == 6){")+strlen("elseif(\$_POST['step'] == 6){");$end=strpos($source,"}elseif(\$_POST['step'] == 7){",$start);$code=substr($source,$start,$end-$start);
$code=str_replace('__DIR__',var_export(realpath(__DIR__.'/../adminportal'),true),$code);
$path=sys_get_temp_dir().'/ipsk-config-'.bin2hex(random_bytes(8));
$code=str_replace("ipskWriteInstallerConfig(".var_export(realpath(__DIR__.'/../adminportal'),true).'."/../supportfiles/include/config.php"', 'ipskWriteInstallerConfig('.var_export($path,true),$code);
function installFixture($code){$passwordLength=20;$dbCreateFlag=$managerUserCreateFlag=$iseUserCreateFlag=$inContainer=false;eval($code);return $installProgress;}
try {
foreach([true,false] as $throw)foreach(['CREATE TABLE','CREATE DEFINER','TRIGGER `','ALTER TABLE','INSERT INTO',"CREATE USER 'fixture-app'","CREATE USER 'fixture-ise'",'GRANT ALL','GRANT SELECT'] as $failure){
    $_SESSION=['adminpassword'=>'synthetic','dbhostname'=>'fixture','rootusername'=>'root','rootpassword'=>'synthetic','databasename'=>'fixture-db','dbusername'=>'fixture-app','iseusername'=>'fixture-ise'];
    mysqli::$exists=false;mysqli::$users=[];mysqli::$fail=$failure;mysqli::$failed=false;mysqli::$throw=$throw;
    $progress=installFixture($code);verify(!$_SESSION['installSuccess'] && !mysqli::$exists && mysqli::$users===[] && !file_exists($path),'Failure bypassed cleanup: '.$failure);
    mysqli::$fail='';installFixture($code);verify($_SESSION['installSuccess'] && file_exists($path),'Retry blocked after '.$failure);unlink($path);
}
file_put_contents($path,'existing');verify(!ipskWriteInstallerConfig($path,'replacement') && file_get_contents($path)==='existing','Existing config overwritten');
} finally {if(file_exists($path))unlink($path);}
echo "PASS review 51: false/exception failures across DDL/users/grants clean up and permit retry; config publication is atomic\n";
