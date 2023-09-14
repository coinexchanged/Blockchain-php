<?php
namespace App\DAO\PrizePool;

use App\PrizePool as PrizePoolModel;
use App\Setting;

class CandyCalculator implements PrizeCalculator
{
    protected $reward_type = PrizePoolModel::REWARD_CANDY;
    protected $reward_currency = PrizePoolModel::CURRENCY_NONE;
    protected $currency_type = PrizePoolModel::CURRENCY_NONE;
    
    public function __construct()
    {
    }

    public function calculate($scene, $reward_qty, $to_user, $from_user = null, $memo = '', $attach_data = [])
    {
        $fasten_data = [
            'reward_type' => $this->reward_type,
            'reward_currency' => $this->reward_currency,
            'create_time' => time(), //发奖时间
        ];
        $origin_reward_qty = $reward_qty; //原始奖励数量
        $candy_tousdt = Setting::getValueByKey('candy_tousdt', 100);
        $candy_tousdt = bc_div($candy_tousdt, 100);
        $reward_qty = bc_div($reward_qty, $candy_tousdt, 4);
        $extra_data = $attach_data['extra_data'];
        if (is_string($extra_data) && !empty($extra_data)) {
            $extra_data = unserialize($extra_data);
        }
        $extra_data['origin_reward_qty '] = $origin_reward_qty;
        $extra_data['candy_tousdt'] = $candy_tousdt;
        $attach_data['extra_data'] = serialize($extra_data);
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
