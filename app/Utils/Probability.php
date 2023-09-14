<?php

namespace App\Utils;

class Probability
{
    //chance为抽到概率，数值越小，概率越小
    /*
    $arr = [
        ['name'=>'1000元手机','chance'=>'1'],
        ['name'=>'100元代金券','chance'=>'10'],
        ['name'=>'10元代金券','chance'=>'100'],
        ['name'=>'谢谢参与','chance'=>'700'],
    ];
    */

    /**
     * 抽奖概率算法
     * @param array $prizeArr 奖品数组
     * @param string $index 概率键名
     * @return mixed 抽到的奖品
     */
    public static function lotteryRaffle($prizeArr, $index = 'chance')
    {
        $result = null;
        //数组设为集合
        $prize = collect($prizeArr);
        //概率数组的总概率精度
        $proSum = $prize->sum($index);
        //概率数组循环
        $prize->each(function ($item, $key) use (&$proSum, $index, &$result) {
            $randNum = mt_rand(1, $proSum);
            $current_chance = $item[$index];
            if ($randNum <= $current_chance) {
                $result = $item;
                return false;
            }
            $proSum -= $current_chance;
        });
        return $result;
    }
}
