<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\LeverTransaction;
use App\UsersWallet;
use App\AccountLog;

class OvernightFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lever:overnight';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '杠杆交易隔夜费';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = strtotime(date('Y-m-d'));
        $count = LeverTransaction::where('create_time', '<', $today)
            ->where('status', LeverTransaction::TRANSACTION)
            ->count();
        if ($count <= 0) {
            $this->info(date('Y-m-d H:i:s') . ' 没有要执行的任务');
            return ;
        }
        $this->info(date('Y-m-d H:i:s') . ' 共' . $count . '个任务');
        $current = 0;
        LeverTransaction::where('create_time', '<', $today)
            ->where('status', LeverTransaction::TRANSACTION)
            ->chunk(100, function($lever_transactions) use (&$current) {
                try {
                    foreach ($lever_transactions as $key => $trade) {
                        $this->info('正在执行第'. ++$current . '个任务');
                        DB::transaction(function() use ($trade) {
                            $trade->refresh(); //获取最新数据
                            if ($trade->status != LeverTransaction::TRANSACTION) {
                                throw newx \Exception('交易状态异常');
                            }
                            $profit = $trade->profits; //盈利
                            $overnight_rate = bc_div($trade->overnight, 100); //取当时的隔夜费率
                            $trade_money = bc_mul($trade->price, $trade->number); //交易市值
                            $overnight_fee = bc_mul($trade_money, $overnight_rate); //隔夜费
                            $caution_money = $trade->caution_money; //当前可用保证金
                            $wallet_money = 0;
                            $user_wallet = UsersWallet::where('user_id', $trade->user_id)
                                ->where('currency', $trade->legal)
                                ->lockForUpdate()
                                ->first();
                            $user_wallet && $wallet_money = $user_wallet->lever_balance;
                            $subtotal = bc_add($wallet_money, $caution_money);
                            //如果余额+保证金不够扣
                            if (bc_comp($subtotal, $overnight_fee) < 0) {
                                //判断下盈利是否够扣
                                if (bc_comp($profit, $overnight_fee) < 0) {
                                    //盈利都不够扣了，怎么破？个人认为应该平仓这笔交易
                                }
                            }
                            //优先从保证金扣除
                            if (bc_comp($trade->caution_money, $overnight_fee) >= 0) {
                                //可用保证金扣为0不影响风险率的计算，风险率改为取原始保证金计算
                                $caution_should_deduct = $overnight_fee;
                                $balance_should_deduct = 0;
                            } else {
                                //不够扣除先扣保证金，再扣余额
                                $caution_should_deduct = $caution_money;
                                $balance_should_deduct = bc_sub($overnight_fee, $caution_should_deduct);
                            }
                            $extra_data = serialize([
                                'trade_id' => $trade->id,
                                'trade_money' => $trade_money,
                                'overnight_rate' => $overnight_rate,
                                'overnight_fee' => $overnight_fee,
                                'caution_deduct' => $caution_should_deduct,
                                'balance_deduct' => $balance_should_deduct,
                            ]);
                            $result = change_wallet_balance(
                                $user_wallet,
                                3,
                                -$balance_should_deduct,
                                AccountLog::LEVER_TRANSACTION_OVERNIGHT,
                                '杠杆交易id:' . $trade->id. ',收取隔夜费:' . $overnight_fee . '(从保证金扣除:' . $caution_should_deduct . ',从余额扣除:' . $balance_should_deduct . ')',
                                false,
                                0,
                                0,
                                $extra_data,
                                true, //为0时继续执行，目的是为了写入扣取隔夜费的日志
                                true //不够扣允许扣成负数
                            );
                            if ($result !== true) {
                                throw new \Exception($result);
                            }
                            $trade->caution_money = bc_sub($caution_money, $caution_should_deduct);
                            $trade->overnight_money = bc_add($trade->overnight_money, $overnight_fee);
                            $result = $trade->save();
                            if (!$result) {
                                throw new \Exception('从交易保证金扣除隔夜费失败');
                            }                           
                        });
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            });
        $this->info(date('Y-m-d H:i:s') . ' 全部执行完成');
    }
}
