<?php

namespace App\Utils;

use App\Setting;
use Illuminate\Support\Facades\Config;

class ZtPay
{
    public static function http_request($param)
    {
        $url = env('ZTPAY_URL');

        if (empty($url) || empty($param)) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data, true);
    }

    public static function getSign($data)
    {
        $appsecret = Setting::getValueByKey('ztpay_sk','');//trim(env('ZTPAY_APPSK'));
//        var_dump($appsecret);
        $signPars = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ("sign" != $k && "" != $v && $v != "0") {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $appsecret;
        return strtoupper(md5($signPars));

    }
}