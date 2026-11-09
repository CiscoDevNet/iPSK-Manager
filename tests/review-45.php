<?php
require __DIR__.'/review-bootstrap.php';
$secret='secret"&psk=literal';
class PskDisplayDatabase {
    public $bits=257;
    function getEndPointAssociationPermissions(...$args) {return [['groupPermissions'=>$this->bits]];}
    function getEndpointByAssociationId($id) {return ['id'=>$id,'endpointId'=>42,'epGroupId'=>7,'createdBy'=>'owner','pskValue'=>'psk='.$GLOBALS['secret'],'macAddress'=>'AA:BB:CC:DD:EE:FF','fullName'=>'Name','description'=>'','emailAddress'=>''];}
}
foreach ([false,true] as $allow) {
    $_SESSION=validSession()+['portalSettings'=>['id'=>3],'authorizationGroups'=>[],'logonSID'=>'owner','portalAuthorization'=>['create'=>false],
        'authorizedEPGroups'=>['count'=>2,['endpointGroupId'=>7,'groupPermissions'=>257],['endpointGroupId'=>99,'groupPermissions'=>1032]]];
    $ipskISEDB=new PskDisplayDatabase();$ipskISEDB->bits=$allow?265:257;
    $sanitizedInput=['id'=>9,'confirmaction'=>0];$portalId='fixture';
    ob_start();include __DIR__.'/../supportfiles/portals/sponsorportal/default/edit.inc.php';$html=ob_get_clean();
    $doc=new DOMDocument();@$doc->loadHTML($html);$xpath=new DOMXPath($doc);
    $input=$xpath->query('//input[@id="ciscoAVPairPSK"]')->item(0);
    verify($input && $input->getAttribute('value')===($allow?$secret:''),'Edit form leaked key or altered authorized display');
}
echo "PASS review 45: edit-only/unrelated grants reveal no key; authorized display retains literal bytes\n";
