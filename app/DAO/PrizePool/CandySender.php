<?php
namespace App\DAO\PrizePool;

use Illuminate\Support\Facades\DB;
use App\PrizePool;
use App\{Users, AccountLog};

class CandySender implements PrizeSender
{
    public function send(\App\PrizePool &$prize) : bool
    {
        try {
            $prize->refresh();
            if ($prize->status != 0) {
                throw new \Exception('奖励发放异常');
            }
            if ($prize->reward_type != PrizePool::REWARD_CANDY) {
                throw new \Exception('奖励发放类型不匹配');
            }
            DB::transaction(function () use (&$prize) {
                $user = Users::lockForUpdate()->find($prize->to_user_id);
                $change_result = change_user_candy($user, $prize->reward_qty, AccountLog::REWARD_CANDY, $prize->memo);
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
