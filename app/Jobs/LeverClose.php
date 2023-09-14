<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\AccountLog;
use App\LeverTransaction;
use App\UsersWallet;

class LeverClose implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task_list;
    protected $deduct_balance;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task_list, $deduct_balance = true)
    {
        $this->task_list = $task_list;
        $this->deduct_balance = $deduct_balance;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lever_transactions = LeverTransaction::whereIn('id', $this->task_list)
            ->where('status', LeverTransaction::CLOSING)
            ->get();
        foreach ($lever_transactions as $key => $trade) {
            try {
                DB::transaction(function () use ($trade) {
                    try {
                        if ($this->deduct_balance) {
                            if(!$this->deductBalance($trade)) {
                                throw new \Exception('平仓扣除资金失败');
                            }
                        }
                        //更新状态和计算最终盈利
                        $trade->status = LeverTransaction::CLOSED;
                        $trade->fact_profits = $trade->profits;
                        $trade->complete_time = microtime(true);
                        $result = $trade->save();
     
                        if (!$result) {
                            throw new \Exception('平仓失败:更新平仓状态失败');
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                });
            } catch (\Exception $e) {
                echo 'File:' . $e->getFile() . PHP_EOL;
                echo 'Line:' . $e->getLine() . PHP_EOL;
                echo 'Message:' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function deductBalance($trade)
    {
        try {
            DB::transaction(function () use ($trade) {
                $legal_wallet = UsersWallet::where('user_id', $trade->user_id)
                    ->where('currency', $trade->legal)
                    ->lockForUpdate()
                    ->first();
                //计算盈亏
                $change = bc_add($trade->caution_money, $trade->profits);
                //从钱包处理资金
                $pre_result = bc_add($legal_wallet->lever_balance, $change);
                $diff = 0;
                //是否余额不够扣除
                // if (bc_comp($pre_result, 0) < 0) {
                //     $change = -$legal_wallet->lever_balance;
                //     $diff = $pre_result;
                // }
                $extra_data = [
                    'trade_id' => $trade->id,
                    'caution_money' => $trade->caution_money,
                    'profit' => $trade->profits,
                    'diff' => $diff,
                ];
                $result = change_wallet_balance(
                    $legal_wallet,
                    3,
                    $change,
                    AccountLog::LEVER_TRANSACTION_ADD,
                    '平仓资金处理',
                    false,
                    0,
                    $diff == 0 ? 0 : 1, //1代表有差额
                    serialize($extra_data),
                    true,//余额为0仍然要平仓
                    true //允许资金扣为负数
                );
                if ($result !== true) {
                    throw new \Exception($result);
                }
            });
            return true;
        } catch (\Exception $e) {
            echo 'File:' . $e->getFile() . PHP_EOL;
            echo 'Line:' . $e->getLine() . PHP_EOL;
            echo 'Message:' . $e->getMessage() . PHP_EOL;
            return false;
        }
    }
}
