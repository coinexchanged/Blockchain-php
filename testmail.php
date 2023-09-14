<?php


echo strtotime('+1 month');
die;
echo strtotime('+1 min');
die;
require __DIR__ . '/smtp.php';

$smtp = new Smtp('103.98.112.66', 8080, true, 'service@pcmcoinb.com', 'NxFpJyA5ipEvJjEK7ecoAw');//这里面的一个true是表示使用身份验证,否则不使用身份验证.
$smtp->debug = true;//是否显示发送的调试信息
$state = $smtp->sendmail('dab1117@163.com', 'service@pcmcoinb.com', '测试邮件', '这是一封测试邮件', 'HTML');
