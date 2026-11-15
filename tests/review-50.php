<?php
require __DIR__.'/review-bootstrap.php';
$_SESSION=validSession()+['adminAuthorized'=>true];
$path=__DIR__.'/../supportfiles/adminportals/modules/sysconfig/general.inc.php';$source=file_get_contents($path);
preg_match_all('/\$(adminPortalSettings|ldapSettings|samlSettings)\[\x27([^\x27]+)\x27\]/',$source,$matches,PREG_SET_ORDER);
foreach($matches as $match)${$match[1]}[$match[2]]='';
$ldapListing=false;$adminPortalSettings['complexity-similar-value']='16';$adminPortalSettings['complexity-similar']=' checked';
$ipskISEDB=new class {public $writes=[];function getLdapDirectoryCount(){return 0;}function setGlobalSetting(...$args){$this->writes[]=$args;return true;}};
ob_start();include $path;$html=ob_get_clean();$doc=new DOMDocument();@$doc->loadHTML($html);$xp=new DOMXPath($doc);$field=$xp->query('//input[@id="complexSimilar"]')->item(0);verify($field->getAttribute('value')==='16','Checkbox malformed');
foreach([16,false,null,17] as $value){$ipskISEDB->writes=[];$sanitizedInput=['module-action'=>'complexity','complexLowercase'=>1,'complexUppercase'=>2,'complexNumbers'=>4,'complexSpecial'=>0,'complexSimilar'=>$value];ob_start();include __DIR__.'/../supportfiles/adminportals/modules/sysconfig/update.inc.php';$out=ob_get_clean();verify($value===16?$ipskISEDB->writes===[['advanced-settings','password-complexity',23]]:($ipskISEDB->writes===[] && str_contains($out,'Invalid')),'Invalid mask saved or bit lost');}
echo "PASS review 50: rendered similar-character value saves intact; malformed masks rejected without writes\n";
