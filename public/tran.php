<?php
if (Redis::get($enkey)) {
    $alog->info_en = Redis::get($enkey);
    Redis::setex($enkey, 60 * 5, $alog->info_en);
} else {
    $alog->info_en = mtranslate($alog->info, 'en', 'zh');
    sleep(1);
    Redis::setex($enkey, 60 * 5, $alog->info_en);
}
if (Redis::get($jpkey)) {
    $alog->info_jp = Redis::get($jpkey);
    Redis::setex($jpkey, 60 * 5, $alog->info_en);
} else {
    $alog->info_jp = mtranslate($alog->info, 'jp', 'zh');
    sleep(1);
    Redis::setex($jpkey, 60 * 5, $alog->info_jp);
}

if (Redis::get($hkkey)) {
    $alog->info_hk = Redis::get($hkkey);
    Redis::setex($hkkey, 60 * 5, $alog->info_hk);
} else {
    $alog->info_hk = mtranslate($alog->info, 'cht', 'zh');
    sleep(1);
    Redis::setex($hkkey, 60 * 5, $alog->info_hk);
}

if (Redis::get($spakey)) {
    $alog->info_spa = Redis::get($spakey);
    Redis::setex($spakey, 60 * 5, $alog->info_spa);
} else {
    $alog->info_spa = mtranslate($alog->info, 'spa', 'zh');
    sleep(1);
    Redis::setex($spakey, 60 * 5, $alog->info_spa);
}

if (Redis::get($krkey)) {
    $alog->info_kr = Redis::get($krkey);
    Redis::setex($krkey, 60 * 5, $alog->info_kr);
} else {
    $alog->info_kr = mtranslate($alog->info, 'kor', 'zh');
    sleep(1);
    Redis::setex($krkey, 60 * 5, $alog->info_kr);
}

$alog->transfered = 1;
$alog->save();

$this->comment($alog->info_en);
$this->comment($alog->info_jp);
$this->comment($alog->info_kr);
$this->comment($alog->info_hk);
$this->comment($alog->info_spa);
$this->comment("\r\n");
