<?php
/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use App\AccountLog;
use App\Setting;
use App\Users;
use App\UsersWallet;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Locking extends Command
{
    protected $signature = 'locking';
    protected $description = '锁定任务';

    protected $lock_daily_return = "";   //日均收益

    public function handle()
    {
        $lock_daily_return = Setting::getValueByKey("lock_daily_return");
        if (empty($lock_daily_return) ){
            $this->comment("后台设置错误");
            exit();
        }
        $this->lock_daily_return = $lock_daily_return;

        $datas = UsersWallet::where("remain_lock_balance",">",0)->where("lock_balance",">",0)->get();
        $this->comment("start");
        foreach ($datas as $d){
            $this->lockingMethod($d);
        }
        $this->comment("end");
    }
    public function lockingMethod($data){
        if (empty($data)) return false;
        $user = Users::find($data->user_id);
        if (empty($user)) return false;

        $money = $data->lock_balance * $this->lock_daily_return / 100;
        if ($money >= $data->remain_lock_balance){
            $money = $data->remain_lock_balance;
            $data->remain_lock_balance = 0;
        }else{
            $data->remain_lock_balance = $data->remain_lock_balance - $money;
        }
        if ($money == 0){
            return false;
        }
        DB::beginTransaction();
        try {
            $data->balance = $data->balance + $money;
            $data->save();
            AccountLog::insertLog(array("user_id"=>$data->user_id,"value"=>$money,"type"=>AccountLog::LOCK_BALANCE,"info"=>"释放余额增加"));
            AccountLog::insertLog(array("user_id"=>$data->user_id,"value"=>-$money,"type"=>AccountLog::LOCK_REMAIN_BALANCE,"info"=>"锁仓减少"));

            $this->comment("锁仓改变：".$data->user_id);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }

}
