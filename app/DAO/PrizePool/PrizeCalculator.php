<?php

namespace App\DAO\PrizePool;

interface PrizeCalculator
{
    /**
     * 计算奖励
     *
     * @param integer $scene 奖励场景
     * @param float $reward_qty 奖励数量
     * @param \App\Users $to_user 被奖励者
     * @param \App\Users $from_user 触发用户
     * @param string $memo 备注
     * @param array $attach_data 附加数据
     * @return \App\PrizePool 返回奖池记录
     */
    public function calculate($scene, $reward_qty, $to_user, $from_user = null, $memo = '', $attach_data = []);
}
