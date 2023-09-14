<?php
return [
    'region_id'         => env('ALIYUN_SMS_REGION_ID', 'cn-hangzhou'), // regionid
    'access_key'        => env('ALIYUN_SMS_AK'), // accessKey
    'access_secret'     => env('ALIYUN_SMS_AS'), // accessSecret
    'sign_name'         => env('ALIYUN_SMS_SIGN_NAME'), // 签名
];