<?php
namespace App\DAO\PrizePool;

interface PrizeSender
{
    /**
     * 发放奖励
     *
     * @param \App\PrizePool $prize
     * @return boolean
     */
    public function send(\App\PrizePool &$prize) : bool;
}
