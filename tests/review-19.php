<?php
require __DIR__.'/review-bootstrap.php';
class BoundConnection {
    public $calls = [];
    public $insert_id = 42;
    public $affected_rows = 1;
    function prepare($sql) {
        return new class($this, $sql) {
            public $field_count;
            private $db, $sql, $values = [];
            function __construct($db, $sql) { $this->db=$db; $this->sql=$sql; $this->field_count=str_starts_with($sql, 'SELECT') ? 1 : 0; }
            function bind_param($types, &...$values) { $this->values=$values; return true; }
            function execute() { $this->db->calls[]=[$this->sql,$this->values]; return true; }
            function get_result() { return new class { public $num_rows=0; function fetch_assoc() {return ['id'=>42];} }; }
            function close() {}
        };
    }
}
$c = new BoundConnection(); $db = connectionFor($c);
foreach (["x' OR 1=1 -- ", '', [], '00000000-0000-0000-0000-000000000000x'] as $bad) verify($db->getPortalByGuid($bad) === false, 'Invalid GUID accepted');
verify(!$c->calls, 'Invalid GUID reached database');
$guid='00112233-4455-6677-8899-aabbccddeeff';
$db->getPortalByGuid($guid);
verify($c->calls[0][1] === [$guid] && !str_contains($c->calls[0][0],$guid), 'GUID not bound');
$secret="psk=quote'&\\secret";
$db->addEndpoint('AA:BB:CC:DD:EE:FF', "O'Connor", '', '', $secret, "v'lan", "d'acl", 0, "user'sid");
$insert=end($c->calls);
verify(in_array($secret,$insert[1],true) && !str_contains($insert[0],$secret), 'Secret not preserved as bound data');
$db->addEndpointAssociation(42,'AA:BB:CC:DD:EE:FF',1,"user'sid");
verify(end($c->calls)[1] === [1,42,'AA:BB:CC:DD:EE:FF'], 'Association values not bound');
$db->addBulkEndpoints(['AA:BB:CC:DD:EE:01','AA:BB:CC:DD:EE:02'], 'Name', '', '', [$secret,$secret], '', '', 0, 'sid');
foreach ($c->calls as [$sql,$values]) verify(substr_count($sql,'?') === count($values), 'Placeholder count mismatch');
echo "PASS review 19: reject malformed GUIDs and bind portal, endpoint and association values\n";
