<?php
namespace ReviewRandom {
function random_int($min,$max){$GLOBALS['ranges'][]=[$min,$max];return $GLOBALS['end']==='min'?$min:$max;}
}
namespace {
require __DIR__.'/review-bootstrap.php';
$source=file_get_contents(__DIR__.'/../supportfiles/include/iPSKManagerDatabase.php');eval('namespace ReviewRandom; use \InvalidArgumentException;'.preg_replace('/\?>\s*$/','',substr($source,strpos($source,'class iPSKManagerDatabase'))));
$source=file_get_contents(__DIR__.'/../adminportal/installer.php');$start=strpos($source,'function generatePassword(');$end=strpos($source,'function generateEncryptionKey(',$start);eval('namespace ReviewRandom; use \InvalidArgumentException;'.substr($source,$start,$end-$start));
$db=new ReviewRandom\iPSKManagerDatabase();
foreach([fn()=> $db->generateRandomPassword(8,16), fn()=>ReviewRandom\generatePassword(8,16)] as $generate){foreach(['min'=>'l','max'=>'0'] as $end=>$char){$GLOBALS['end']=$end;$GLOBALS['ranges']=[];verify($generate()===str_repeat($char,8),'Alphabet endpoint unreachable');verify($GLOBALS['ranges']===array_fill(0,8,[0,3]),'Sampler has biased bounds');}}
foreach([fn()=> $db->generateRandomPassword(8,32),fn()=>ReviewRandom\generatePassword(8,32)] as $generate){try{$generate();throw new Exception('Empty alphabet accepted');}catch(InvalidArgumentException|ReviewRandom\InvalidArgumentException $e){}}
echo "PASS review 23: both generators sample each alphabet index exactly once and reject empty alphabets\n";
}
