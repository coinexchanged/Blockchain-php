<?php
namespace App\DAO\PrizePool;

use Illuminate\Support\Facades\DB;
use App\{Users, AccountLog, UsersWallet, PrizePool};

class CurrencySender implements PrizeSender
{
    public function send(\App\PrizePool &$prize) : bool
    {
        try {
            $prize->refresh();
            if ($prize->status != 0) {
                throw new \Exception('奖励发放异常');
            }
            if ($prize->reward_type != PrizePool::REWARD_CURRENCY) {
                throw new \Exception('奖励发放类型不匹配');
            }
            if (!in_array($prize->currency_type, [1, 2, 3])) {
                throw new \Exception('币种类型不正确');
            }
            DB::transaction(function () use (&$prize) {
                $user_wallet = UsersWallet::where('user_id', $prize->to_user_id)
                    ->where('currency', $prize->reward_currency)
                    ->lockForUpdate()
                    ->first();
                if (!$user_wallet) {
                    throw new \Exception('用户钱包不存在');
                }
                $change_result = change_wallet_balance($user_wallet, $prize->currency_type, $prize->reward_qty, AccountLog::REWARD_CURRENCY, $prize->memo, false, $prize->from_user_id, $prize->sign, $extra_data);
                if ($change_result !== true) {
                    throw new \Exception($change_result);
                }
                $prize->receive_time = time();
                $prize->status = 1;
                $result = $prize->save();
                if (!$result) {
                    throw new \Exception('奖励通证发放失败');
                }
            });
            return true;
        } catch (\Exception $e) {
            $error_info = serialize([
                'time' => time(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ]);
            $prize->error_info = $error_info;
            $prize->save();
            return false;
        }
    }
}
