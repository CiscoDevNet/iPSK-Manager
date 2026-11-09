<?php
require __DIR__.'/review-bootstrap.php';
$payload='</textarea></pre><img src=x onerror=alert(1)><script>alert(1)</script>"';
$row=array_fill_keys(['sessionID','fileName','functionName','className','classMethodName','lineNumber','message'],$payload);
$row+=['id'=>1,'dateCreated'=>'2026-11-08 12:00:00','logDataPayload'=>json_encode(['debug'=>$payload])];
$ipskISEDB = new class($row) {
    private $row;
    function __construct($row) { $this->row=$row; }
    function getLogging() { return new class($this->row) {
        private $row; function __construct($row) {$this->row=$row;}
        function fetch_assoc() {$r=$this->row;$this->row=null;return $r;}
    }; }
    function getLoggingById($id) { return $this->row; }
};
$_SESSION=validSession()+['adminAuthorized'=>true]; $_POST=['id'=>1]; $sanitizedInput=[]; $globalDateOutputFormat='c';
foreach (['logging','view'] as $view) {
    ob_start(); include __DIR__.'/../supportfiles/adminportals/modules/logging/'.$view.'.inc.php'; $html=ob_get_clean();
    verify(!str_contains($html,$payload) && str_contains($html,'&lt;img'), 'Log payload not escaped');
    $doc=new DOMDocument(); @$doc->loadHTML($html);
    verify($doc->getElementsByTagName('img')->length===0,'Payload created an HTML element');
}
echo "PASS review 05: stored log messages and debug payloads render as inert text\n";
