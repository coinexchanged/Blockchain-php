<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\LeverTransaction;
use App\UsersWallet;
use App\AccountLog;
use App\Setting;

class Insurancemoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insurance_money';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '持币生币';

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
        $count = UsersWallet::where('lock_insurance_balance','>',0)
            ->count();
        if ($count <= 0) {
            $this->info(date('Y-m-d H:i:s') . ' 没有要执行的任务');
            return ;
        }
        $this->info(date('Y-m-d H:i:s') . ' 共' . $count . '个任务');
        $insurance_money_rate = Setting::getValueByKey('insurance_money_rate',1);

        foreach (UsersWallet::where('lock_insurance_balance','>',0)->cursor() as $w) {

            try {
                DB::beginTransaction();
                $lock=$w->lock_insurance_balance;
                $return=bc_mul($lock,bc_div($insurance_money_rate,100));

                $res=change_wallet_balance($w,5,$return,AccountLog::INSURANCE_MONEY, "用户持险生币", false); 
                if ($res !== true) {
                    throw new \Exception($res);
                }

                DB::commit();
                $this->info( $w->id . '：执行成功');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->comment( $w->id . '失败:' . $e->getMessage());
            }
        }
       
        
        $this->info(date('Y-m-d H:i:s') . ' 全部执行完成');
    }
}
