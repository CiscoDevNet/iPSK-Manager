<?php
if(isset($_GET['body'])) {header('Content-Length: 1000');echo 'partial';flush();}
usleep(1500000);echo 'done';
