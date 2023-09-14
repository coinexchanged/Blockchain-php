<?php

namespace App\DAO\PrizePool;

use App\PrizePool as PrizePoolModel;

class CurrencyCalculator implements PrizeCalculator
{
    protected $reward_type = PrizePoolModel::REWARD_CURRENCY;
    protected $reward_currency;
    protected $currency_type;
    
    public function __construct($reward_currency, $currency_type)
    {
        $this->reward_currency = $reward_currency;
        $this->currency_type = $currency_type;
    }

    public function calculate($scene, $reward_qty, $to_user, $from_user = null, $memo = '', $attach_data = [])
    {
        $fasten_data = [
            'reward_type' => $this->reward_type,
            'reward_currency' => $this->reward_currency,
            'create_time' => time(), //发奖时间
        ];
        try {
            PrizePoolModel::unguard();
            $data = [
                'scene' => $scene,
                'reward_qty' => $reward_qty,
                'to_user_id' => $to_user->id,
                'from_user_id' => $from_user ? $from_user->id : $to_user->id,
                'memo' => $memo,
            ];
            $data = array_merge($data, $attach_data, $fasten_data);
            $prize_pool = PrizePoolModel::create($data);
        } catch (\Exception $e) {
            return null;
        } finally {
            PrizePoolModel::reguard();
        }
        return isset($prize_pool->id) ? $prize_pool : null;
    }
}
