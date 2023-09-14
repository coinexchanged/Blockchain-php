<?php

/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use App\AccountLog;
use App\Currency;
use App\Level;
use App\Users;
use App\UsersWallet;
use App\Setting;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateFund extends Command
{
    protected $signature = 'update_user_fund';
    protected $description = '更新资产';


    public function handle()
    {

        $this->comment("start");
            $user=Users::all();
        if (count($user)!=0) {
            foreach ($user as $v){
                $fund=$this->fund($v->id);
                $v->fund=$fund;
                $v->save();
            }
        }
        

        $this->comment("end");
    }


    public function fund($user_id)
    {
        $currency=Currency::where('is_micro', 1)->get();
        if (empty($currency)) {
            return 0;
        }
        $price=0;
        foreach ($currency as $v){
            $user_wallet=UsersWallet::where('user_id', $user_id)->where('currency', $v->id)->first();
            if (!empty($user_wallet)) {
                $fund=$user_wallet->micro_balance*$v->price;
                $price+=$fund;
            }
        }
        return $price;
    }
}
