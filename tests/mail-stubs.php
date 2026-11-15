<?php
namespace ReviewMail;
function mail(...$args){$GLOBALS['mailArgs']=$args;return $GLOBALS['mailResult']??true;}
class Log {public $entries=[];function addLogEntry($message,...$args){$this->entries[]=$message;}}
function loadFunctions($path,$start) {$source=str_replace("\r\n","\n",file_get_contents($path));$source=substr($source,strpos($source,$start));$source=preg_replace('/\?>\s*$/','',$source);eval('namespace ReviewMail;'.$source);}
