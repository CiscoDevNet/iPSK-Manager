<?php
function runUpload($portal,$csv,$metadata=[]) {
    $file=tempnam(sys_get_temp_dir(),'ipskcsv');file_put_contents($file,$csv);
    $_FILES=['csvFile'=>array_replace(['name'=>'fixture.csv','tmp_name'=>$file,'error'=>UPLOAD_ERR_OK],$metadata)];
    $sanitizedInput=['uploadkey'=>''];
    ob_start();try{
        if($portal==='sponsor')include __DIR__.'/../supportfiles/portals/sponsorportal/default/fileupload.inc.php';
        else {$source=file_get_contents(__DIR__.'/../adminportal/ajax/fileupload.php');$start=strpos($source,'//Define Initial Variables');$end=strpos($source,'print json_encode($returnResult);',$start)+strlen('print json_encode($returnResult);');eval(substr($source,$start,$end-$start));}
        $out=ob_get_contents();
    }finally{ob_end_clean();unlink($file);}
    return json_decode($out,true,512,JSON_THROW_ON_ERROR);
}
