<?php
namespace App\Http\Controllers\Api;

use App\Agent;
use App\UserReal;
use App\WalletAddressLog;
use Illuminate\Support\Carbon;
use App\Conversion;
use App\FlashAgainst;
use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Http\Requests;
use App\Currency;
use App\Ltc;
use App\LtcBuy;
use App\TransactionComplete;
use App\NewsCategory;
use App\Address;
use App\AccountLog;
use App\Setting;
use App\Users;
use App\UsersWallet;
use App\UsersWalletOut;
use App\WalletLog;
use App\Levertolegal;
use App\LeverTransaction;
use App\Jobs\UpdateBalance;

class OutLog extends Controller
{
    public function out_log(Request $request)
    {
        $uid = $request->get('uid', 0);
        $type = $request->get('type', 0);
        $num = $request->get('num', 0);
        $currency = $request->get('currency', 0);
        $msg = $request->get('msg', '');
        if($type == 1){
            $wallet = UsersWallet::where('user_id', $uid)->where('currency', $currency)->lockForUpdate()->first();
            $result = change_wallet_balance($wallet, 2, $num, AccountLog::WALLET_CURRENCY_IN, $msg);
        }else if($type == 2){
            $wallet = UsersWallet::where('user_id', $uid)->where('currency', $currency)->lockForUpdate()->first();
            $result = change_wallet_balance($wallet, 2, -$num, AccountLog::WALLETOUTDONE, $msg);
        }
    }
}