<?php
http_response_code((int)($_GET['status']??200));
echo json_encode(['method'=>$_SERVER['REQUEST_METHOD'],'body'=>file_get_contents('php://input')]);
