<?php
require __DIR__.'/review-bootstrap.php';
class ScopeConnection {
    public $rows=[];
    function prepare($sql) {
        return new class($this) {
            private $db;
            public $field_count=1;
            function __construct($db) { $this->db=$db; }
            function bind_param($types, &...$values) { return true; }
            function execute() { return true; }
            function get_result() { return new class($this->db->rows) {
                public $num_rows; private $rows;
                function __construct($rows) { $this->rows=$rows; $this->num_rows=count($rows); }
                function fetch_assoc() { return array_shift($this->rows); }
            }; }
            function close() {}
        };
    }
}
$c=new ScopeConnection(); $db=connectionFor($c); $_SESSION=['logonSID'=>'owner'];
foreach ([16,32,64,128,256,1024] as $action) {
    foreach (['owner','other'] as $owner) {
        $c->rows=[['id'=>7,'endpointId'=>42,'macAddress'=>'AA:BB:CC:DD:EE:FF','createdBy'=>$owner,'groupPermissions'=>1|$action]];
        $permissions=$db->getEndPointAssociationPermissions(7,['count'=>1,['groupDn'=>'fixture']],3);
        verify(($permissions!==false) === ($owner==='owner'), 'Own-only action escaped ownership scope');
        $c->rows[0]['groupPermissions']=2|$action;
        verify($db->getEndPointAssociationPermissions(7,['count'=>1,['groupDn'=>'fixture']],3)!==false, 'Group action rejected');
    }
}
// A foreign own-only grant must not contribute actions to an unrelated group-view grant.
$c->rows=[['id'=>7,'endpointId'=>42,'macAddress'=>'AA:BB:CC:DD:EE:FF','createdBy'=>'other','groupPermissions'=>65],
    ['id'=>7,'endpointId'=>42,'macAddress'=>'AA:BB:CC:DD:EE:FF','createdBy'=>'other','groupPermissions'=>2]];
$p=$db->getEndPointAssociationPermissions(7,['count'=>1,['groupDn'=>'fixture']],3);
verify(($p[0]['advancedPermissions'] & 64)===0, 'Merging grants widened own-only deletion');
echo "PASS review 26: owner scope on all action permissions and overlapping-grant control\n";
