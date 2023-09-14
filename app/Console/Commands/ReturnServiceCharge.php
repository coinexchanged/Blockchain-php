<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\MicroOrder;
use App\UsersInsurance;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReturnServiceCharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'return_service_charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '返还交易手续费用';

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
        //
        $this->comment('========='.date('Y-m-d H:i:s')."开始执行返还保险交易手续费=========");
        $yesterday = Carbon::today()->toDateString();
        MicroOrder::whereDate('created_at', $yesterday)->where('is_insurance','>',0)
            ->where('return_at',null)->chunk(1000,function ($orders){
            foreach ($orders as $order){
                $user_id = $order->user_id;
                $currency_id = $order->currency_id;
                $service_charge = $order->fee;//手续费
                $user_insurance = UsersInsurance::where('user_id', $user_id)
                    ->whereHas('insurance_type', function ($query) use ($currency_id) {
                        $query->where('currency_id', $currency_id);
                    })
                    ->where('status', 1)
                    ->where('claim_status',0)
                    ->first();
                if(!$user_insurance){
                    $this->error("user_id:{$user_id},未找到生效保险");
                    continue;//没有该币种的保险，不返手续费
                }
                $user_wallet = UsersWallet::where('user_id',$user_id)->where('currency', $currency_id)->first();
                if(!$user_wallet){
                    $this->error("user_id:{$user_id},{$currency_id}钱包不存在。");
                    continue;
                }
                try {
                    DB::beginTransaction();
                    change_wallet_balance($user_wallet, 5, $service_charge, AccountLog::RETURN_INSURANCE_TRADE_FEE,
                        '返还保险交易手续费', false);
                    $order->return_at = Carbon::now();
                    $order->save();
                    DB::commit();
                    $this->info("user_id:{$user_id},返还保险交易手续费成功");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("user_id:{$user_id},返还保险交易手续费失败：".$e->getMessage());
                }

            }

        });

        $this->comment('========='.date('Y-m-d H:i:s')."执行返还保险交易手续费成功！=========");
    }
}
