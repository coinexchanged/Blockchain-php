<?php
namespace App\DAO;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Users;
use App\Level;
use App\UserUpgradeLog;
use App\PrizePool;
use App\LeverTransaction;
use App\UsersWallet;
USE App\UsersWalletcopy;
use App\Setting;
use App\AccountLog;


class FactprofitsDAO
{
    /**
     * 会员历史总盈亏释放
     * @param project $user_id 要释放用户的id
     */
    public static function Profit_loss_release($user_id)
    {
        $profit_loss_release=Setting::getValueByKey('profit_loss_release','')/1000;
        $sum=LeverTransaction::where("user_id","=",$user_id)->where("status","=",3)->sum("fact_profits");
//        var_dump($profit_loss_release);
//        var_dump($sum);die;
        if($sum<0)
        {
            $aaaa=UsersWalletcopy::leftjoin("currency","currency.id","=","users_wallet.currency")->where("currency.name","=","USDT")->where("users_wallet.user_id","=",$user_id)->select("users_wallet.id","users_wallet.lever_balance","users_wallet.user_id","currency.id as currency_id")->first();
            $user_walllet=UsersWalletcopy::where("user_id","=",$aaaa->user_id)->where("currency","=",$aaaa->currency_id)->first();
            $number=-bc_mul($sum,$profit_loss_release,8);
            $user_walllet->lever_balance=$user_walllet->lever_balance+$number;
            $user_walllet->save();
            try {
                //增加杠杆币日志记录
                $result = change_wallet_balance(
                    $user_walllet,
                    3,
                    +$number,
                    AccountLog::PROFIT_LOSS_RELEASE,
                    '历史盈亏释放,增加杠杆币'.$number,
                    false,
                    $user_id,
                    0
                );
                if ($result !== true) {
                    throw new \Exception('历史盈亏释放,增加杠杆币:' . $result);
                }
                DB::commit();
                return true;
            } catch (\Exception $ex) {
                DB::rollBack();
                return false;
            }

        }



    }


}
