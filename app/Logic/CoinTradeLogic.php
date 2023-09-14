<?php


namespace App\Logic;


use App\AccountLog;
use App\CoinTrade;
use App\CurrencyQuotation;
use App\Setting;
use App\UsersWallet;
use Illuminate\Support\Facades\DB;

class CoinTradeLogic
{
    public static function userSellCoin($userId,$sellCurrencyId,$wantCurrencyId,$amount,$price){
        //第一步  找出钱包
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $sellCurrencyId)
            ->lockForUpdate()
            ->first();

        //锁钱包该币种数量
        //获取当前价格
        $qut = CurrencyQuotation::getInstance($wantCurrencyId,$sellCurrencyId);
        DB::beginTransaction();
        try{
//            $price = bc_mul($amount,$price,8);
            $result = change_wallet_balance($wallet,2, -$amount, AccountLog::COIN_TRADE_FROZEN, '币币交易下单，资金锁定');
            if ($result !== true) {
                throw new \Exception($result);
            }

            change_wallet_balance(
                $wallet,
                2,
                $amount,
                AccountLog::COIN_TRADE_FROZEN,
                '币币交易下单，锁定资金增加',
                true,
                0,
                0,
                serialize([])
            );
            //生成
            CoinTrade::newTrade($userId,CoinTrade::TRADE_TYPE_SELL,$sellCurrencyId,$wantCurrencyId,$amount,$qut->now_price,$price);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }


    }

    public static function userBuyCoint($userId,$buyCurrencyId,$payCurrencyId,$amount,$price){
        //第一步  找出钱包
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $payCurrencyId)
            ->lockForUpdate()
            ->first();
        //锁钱包该币种数量
        $qut = CurrencyQuotation::getInstance($payCurrencyId,$buyCurrencyId);
        $costPrice = bc_mul($price,$amount);
        DB::beginTransaction();
        try{
//            $price = bc_mul($amount,$price,8);
            $result = change_wallet_balance($wallet,2, -$costPrice, AccountLog::COIN_TRADE_FROZEN, '币币交易下单，资金锁定');
            if ($result !== true) {
                throw new \Exception($result);
            }

            change_wallet_balance(
                $wallet,
                2,
                $costPrice,
                AccountLog::COIN_TRADE_FROZEN,
                '币币交易下单，锁定资金增加',
                true,
                0,
                0,
                serialize([])
            );
            //生成
            CoinTrade::newTrade($userId,CoinTrade::TRADE_TYPE_BUY,$buyCurrencyId,$payCurrencyId,$amount,$qut->now_price,$price);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
        // 生成订单
    }

    public static function matchSellTrade($currencyId,$legalId,$nowPrice){
        $tradeList = CoinTrade::where([
            'currency_id' => $currencyId,
            'legal_id' => $legalId,
            'status' => 1,
            'type' => 2
        ])->where('target_price','<=',$nowPrice)->limit(1)->get();

        foreach($tradeList as $trade){
            DB::beginTransaction();
            try{

                $wallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->legal_id)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }

                $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                change_wallet_balance(
                    $targetWallet,
                    2,
                    -$trade->trade_amount,
                    AccountLog::COIN_TRADE_FROZEN,
                    '币币交易锁定减少',
                    true,
                    0,
                    0,
                    serialize([])
                );
                $chargeFee = $trade->charge_fee;
                $chargeFee = bc_sub(1,$chargeFee,8);
                //手续费
                $costPrice = bc_mul($costPrice,$chargeFee,8);
                change_wallet_balance($wallet,
                    2,
                    $costPrice,
                    AccountLog::COIN_TRADE,
                    '币币交易成功');
                $trade->status = 2;
                $trade->save();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }

    public static function matchBuyTrade($currencyId,$legalId,$nowPrice){
        $tradeList = CoinTrade::where([
            'currency_id' => $currencyId,
            'legal_id' => $legalId,
            'status' => 1,
            'type' => 1
        ])->where('target_price','>=',$nowPrice)->limit(1)->get();


        foreach($tradeList as $trade){

            DB::beginTransaction();
            try{
                //1 扣掉锁定资金
                $wallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->legal_id)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }
                $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                change_wallet_balance(
                    $wallet,
                    2,
                    -$costPrice,
                    AccountLog::COIN_TRADE_FROZEN,
                    '币币交易成功，锁定资金减少',
                    true,
                    0,
                    0,
                    serialize([])
                );

                //手续费
                $chargeFee = $trade->charge_fee;
                $chargeFee = bc_sub(1,$chargeFee,8);
                $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                change_wallet_balance($targetWallet,
                    2,
                    $amount,
                    AccountLog::COIN_TRADE,
                    '币币交易成功');
                $trade->status = 2;
                $trade->save();
                
                //解冻操作
                if($currencyId == 29 && $trade->trade_amount > 50){
                    // echo 'int33333333333';
                    $price = bc_mul($trade->trade_amount,0.25,8);
                    // var_dump([$trade->user_id,$currencyId,$price]);
                    //获取账号锁定金额
                    $lh_account = DB::table('lh_bank_account')->where('uid',$trade->u_id)->first();
                    if($lh_account && $lh_account->coin_lock_balance > 0){
                        if($lh_account->coin_lock_balance > $price){
                            $coin_lock_balance = bc_sub($lh_account->coin_lock_balance,$price,8);
                            $amount = $price;
                        }else{
                            $coin_lock_balance = 0;
                            $amount = $lh_account->coin_lock_balance;
                        }
                        DB::table('lh_bank_account')->where('uid',$trade->u_id)->update([
                                'coin_lock_balance' => $coin_lock_balance
                            ]);
                        DB::table('wallet_unlock_order')->insert([
                                'user_id' => $trade->u_id,
                                'wallet_id' => $targetWallet->id,
                                'currency_id' => $currencyId,
                                'amount' => $amount,
                                'created_at' => date("Y-m-d H:i:s"),
                                'handle_time' => date('Y-m-d H:i:s',strtotime('+1 day'))
                            ]);
                       
                    }
                    // $res = DB::table('wallet_unlock_order')->where('user_id',$trade->u_id)->where('currency_id',$currencyId)->where('status',1)->where('remain_amount','>',0)->orderBy('amount','desc')->first();
                    // if($res){
                    //     DB::table('wallet_unlock_order')->where('id',$res->id)->update([
                    //         'status' => 2,
                    //         'handle_time'=>date("Y-m-d H:i:s",strtotime('+1 day'))
                    //         ]);
                    //     // $res->status = 2;
                    //     // $res->handle_time = ;
                    //     // $res->save();
                    // }
                }
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage().'333333333333333333'.PHP_EOL;
                continue;
            }
        }
    }


    public static function forceMatchTrade($tradeId){
        $trade = CoinTrade::find($tradeId);
        if($trade->status != 1){
            throw new \Exception('状态异常');
        }
        DB::beginTransaction();
        try{
            switch ($trade->type){
                case 1:
                    //1 扣掉锁定资金
                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->legal_id)
                        ->lockForUpdate()
                        ->first();

                    $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    if(!$wallet || !$targetWallet){
                        throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                    }
                    $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                    change_wallet_balance(
                        $wallet,
                        2,
                        -$costPrice,
                        AccountLog::COIN_TRADE_FROZEN,
                        '币币交易成功，锁定资金减少',
                        true,
                        0,
                        0,
                        serialize([])
                    );

                    //手续费
                    $chargeFee = $trade->charge_fee;
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                    change_wallet_balance($targetWallet,
                        2,
                        $amount,
                        AccountLog::COIN_TRADE,
                        '币币交易成功');
                    $trade->status = 2;
                    $trade->save();
                    break;
                case  2:

                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->legal_id)
                        ->lockForUpdate()
                        ->first();

                    $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    if(!$wallet || !$targetWallet){
                        throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                    }

                    $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                    change_wallet_balance(
                        $targetWallet,
                        2,
                        -$trade->trade_amount,
                        AccountLog::COIN_TRADE_FROZEN,
                        '币币交易锁定减少',
                        true,
                        0,
                        0,
                        serialize([])
                    );
                    $chargeFee = $trade->charge_fee;
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    //手续费
                    $costPrice = bc_mul($costPrice,$chargeFee,8);
                    change_wallet_balance($wallet,
                        2,
                        $costPrice,
                        AccountLog::COIN_TRADE,
                        '币币交易成功');
                    $trade->status = 2;
                    $trade->save();
                    break;
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    }

    public static function cancelTrade($id){
        $trade = CoinTrade::find($id);
        if(!$trade)
            throw new \Exception('找不到订单');
        if($trade->status != 1)
            throw new \Exception('订单状态异常,请刷新列表。');
        switch ($trade->type){
            case 1:
                    DB::beginTransaction();
                    try{
                        //解除锁定  还钱
                        $wallet = UsersWallet::where("user_id", $trade->u_id)
                            ->where("currency", $trade->legal_id)
                            ->lockForUpdate()
                            ->first();
                        $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);
                        $result = change_wallet_balance($wallet,
                            2,
                            $costPrice,
                            AccountLog::COIN_TRADE_FROZEN,
                            '取消币币交易，资金返还');
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                        $result = change_wallet_balance(
                            $wallet,
                            2,
                            -$costPrice,
                            AccountLog::COIN_TRADE_FROZEN,
                            '取消币币交易，退换锁定资金',
                            true,
                            0,
                            0,
                            serialize([])
                        );
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                        $trade ->status = 3;
                        $trade->save();
                        DB::commit();
                    }catch (\Exception $e){
                        DB::rollBack();
                        throw $e;
                    }
                break;
            case 2:
                DB::beginTransaction();
                try{
                    //解除锁定  还钱
                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    $result = change_wallet_balance($wallet,2, $trade->trade_amount, AccountLog::COIN_TRADE_FROZEN, '取消币币交易');
                    if ($result !== true) {
                        throw new \Exception($result);
                    }

                    $result = change_wallet_balance(
                        $wallet,
                        2,
                        -$trade->trade_amount,
                        AccountLog::COIN_TRADE_FROZEN,
                        '取消币币交易,退换锁定资金',
                        true,
                        0,
                        0,
                        serialize([])
                    );
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                    $trade ->status = 3;
                    $trade->save();
                    DB::commit();
                }catch (\Exception $e){
                    DB::rollBack();
                    throw $e;
                }
                break;
            default:
                throw new \Exception('类型有误');
        }
        return true;
    }
}
