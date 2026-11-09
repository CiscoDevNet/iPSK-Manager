<?php
require __DIR__.'/review-bootstrap.php';
class FailingConnection {
    public $insert_id=99,$field_count=0,$rollbacks=0,$commits=0,$mode='false';
    function begin_transaction(){return true;}function rollback(){$this->rollbacks++;return true;}function commit(){$this->commits++;return $this->mode!=='commit';}
    function prepare($sql){return new class($this){public $field_count=0;function __construct(private $c){}function bind_param($t,&...$v){return true;}function execute(){if($this->c->mode==='throw')throw new RuntimeException('SQL failed');return $this->c->mode==='commit';}function close(){}};}
}
foreach(['false','throw'] as $mode){$c=new FailingConnection();$c->mode=$mode;$db=connectionFor($c);foreach([
    fn()=>$db->updateEndpoint(1,'Name','','','actor'),fn()=>$db->updateEndpointPsk(1,'psk=validkey'),fn()=>$db->suspendEndpointAssociationbyId(1),fn()=>$db->activateEndpointAssociationbyId(1),fn()=>$db->deleteEndpointAssociationbyId(1),fn()=>$db->addWirelessNetwork('SSID','','actor'),fn()=>$db->addSponsorGroup('Group','',0,[],0,0,'actor'),fn()=>$db->updateAuthorizationTemplate(1,'Policy','','validkey',0,8,'','','actor'),fn()=>$db->addInternalUser('User','Name','','','hash','actor')
] as $call)verify($call()===false,'Failed mutation reported success');}
$path=tempnam(sys_get_temp_dir(),'ipsk-handler');file_put_contents($path,'<?php $ipskISEDB->updateEndpoint(1,"Name","","","actor");print "SUCCESS_RESPONSE";');
try{foreach(['false','throw','commit'] as $mode){http_response_code(200);$c=new FailingConnection();$c->mode=$mode;$db=connectionFor($c);ob_start();$ok=ipskRenderDatabaseOperation($db,$path,['ipskISEDB'=>$db]);$out=ob_get_clean();verify(!$ok && !str_contains($out,'SUCCESS_RESPONSE') && $c->rollbacks===1 && http_response_code()===500,'Failed query/commit leaked success or missed rollback');}}finally{unlink($path);}
echo "PASS review 20: false/exception mutations return false; query/commit failures roll back and suppress success responses\n";
