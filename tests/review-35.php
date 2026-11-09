<?php
require __DIR__.'/review-bootstrap.php';
class MembershipStub {
    public $writes=[];
    function updateInternalUserGroupMapping($groups,$id,$sid) {$this->writes[]=$groups;return true;}
}
foreach ([['id'=>2,'membershipSubmitted'=>1],['id'=>2,'memberof'=>[]],['id'=>2],['id'=>2,'memberof'=>'bad']] as $post) {
    $_SESSION=validSession()+['adminAuthorized'=>true,'logonSID'=>'admin'];$_POST=$post;$ipskISEDB=new MembershipStub();
    ob_start();include __DIR__.'/../supportfiles/adminportals/modules/internalusers/updategroups.inc.php';$out=ob_get_clean();
    $valid=isset($post['membershipSubmitted']) || (isset($post['memberof']) && is_array($post['memberof']));
    verify(count($ipskISEDB->writes)===($valid?1:0),'Empty/malformed membership handling');
}
echo "PASS review 35: explicit empty memberships accepted, malformed/missing submissions rejected\n";
