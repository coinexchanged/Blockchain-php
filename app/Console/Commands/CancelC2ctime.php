<?php

namespace App\Console\Commands;

use App\C2cDeal;
use App\Users;
use Illuminate\Console\Command;
//use App\DAO\FactprofitsDAO;
use Illuminate\Support\Facades\DB;
use App\C2cDealSend;
use App\Setting;
use App\LegalDeal;


class CancelC2ctime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancel:c2cdeal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'c2c取消订单倒计时';

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
        $now = Carbon::now();
        $this->comment('开始执行自动取消C2C交易脚本-'.$now->toDateTimeString());
        $userLegalDealCancel_time=Setting::getValueByKey("userLegalDealCancel_time")*60;
        $result=LegalDeal::where("is_sure",0)->get();//0未确认 1已确认 2已取消 3已付款
        foreach($result as $key=>$value)
        {
            $time=time();
            $create_time=strtotime($value->create_time);
//            var_dump($create_time+$userLegalDealCancel_time); var_dump($time);die;
            if(($create_time+$userLegalDealCancel_time)<=$time)
            {
                $id =$value->id;

                if($value->is_sure==0)//判断只有为0时才取消订单
                {
                    LegalDeal::cancelLegalDealById($id);
                    //取消订单数加一
                    $aaaa=Users::find($value->user_id);
                    $aaaa->today_LegalDealCancel_num=$aaaa->today_LegalDealCancel_num+1;
                    $aaaa->LegalDealCancel_num__update_time=time();
                    $aaaa->save();
                }
                else{
                    return $this->error('该订单状态不能取消');
                }
            }
        }
        $this->comment('执行成功');
    }
}
