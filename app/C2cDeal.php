<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class C2cDeal extends Model
{
    protected $table = 'c2c_deal';
    public $timestamps = false;
    protected $appends = [
        'deal_money',
        'currency_name',
        'type',
        'account_number',
        'phone',
        'seller_name',
        'price',
        'hes_account',
        'hes_realname',
        'way_name',
        'format_create_time',
        'is_seller',
        'user_cash_info',
        'seller_phone',
        'user_realname',
        'format_update_time',
        'sell_cash_info',
    ];
    public function getSellCashInfoAttribute(){
        return UserCashInfo::where('user_id',$this->attributes['seller_id'])->first();
    }
    public function getUserCashInfoAttribute()
    {
        $user = $this->user()->getResults();
        if (!$user) {
            return [];
        }
        $cashinfo = $user->cashinfo;
        if (!$cashinfo) {
            return [];
        }
        return $cashinfo;
    }

    public function user()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }
    public function seller()
    {
        return $this->belongsTo('App\Users', 'seller_id', 'id');
    }

    public function legalDealSend()
    {
        return $this->hasOne('App\C2cDealSend', 'id', 'legal_deal_send_id');
    }

    public function getCreateTimeAttribute()
    {
        return date('H:i m/d', $this->attributes['create_time']);
    }
    public function getFormatCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
    
    public function getFormatUpdateTimeAttribute()
    {
        $time=isset($this->attributes['update_time'])?$this->attributes['update_time']:0;
        return $time?date('Y-m-d H:i:s',$time):'';
        
    }

    public function getDealMoneyAttribute()
    {
        $legal = C2cDealSend::find($this->attributes['legal_deal_send_id']);
        if (!empty($legal)) {
            return bcmul($this->attributes['number'], $legal->price, 6);
        }
        return 0;
    }


    public function getCurrencyNameAttribute()
    {
        $legal = C2cDealSend::find($this->attributes['legal_deal_send_id']);
        if (!empty($legal)) {
            return $legal->currency_name;
        }
        return '';
    }

    public function getSellerPhoneAttribute(){
        $seller = Users::find($this->attributes['seller_id']);
        if (empty($seller)) return null;
        // $user = Users::find($seller->user_id);
        if (!empty($seller)){
            return $seller->phone;
        }else{
            return null;
        }
    }

    public function getTypeAttribute()
    {
        return $this->hasOne('App\C2cDealSend', 'id', 'legal_deal_send_id')->value('type');
    }

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getPhoneAttribute()
    {
        return  $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getSellerNameAttribute()
    {
        //return $this->hasOne('App\Seller', 'id', 'seller_id')->value('name');
        return $this->hasOne('App\Users', 'id', 'seller_id')->value('account_number');
    }

    public function getPriceAttribute()
    {
        return $this->hasOne('App\C2cDealSend', 'id', 'legal_deal_send_id')->value('price');
    }

    public function getHesAccountAttribute()
    {
        $legal_send = C2cDealSend::find($this->attributes['legal_deal_send_id']);
        if (!empty($legal_send)) {
            $seller = Users::find($legal_send->seller_id)->cashinfo;
            if (!empty($seller)) {
               
                if ($legal_send->way == 'bank') {
                    return $seller->bank_account;
                } elseif ($legal_send->way == 'we_chat') {
                    return $seller->wechat_account;
                } elseif ($legal_send->way == 'ali_pay') {
                    return $seller->ali_account;
                }
            }
        }
        return '';
    }

    public function getHesRealnameAttribute()
    {
    
        $seller_id=$this->attributes['seller_id'];
        
        $real = UserReal::where('user_id', $seller_id)->where('review_status', 2)->first();
        if (!empty($real)) {
            return $real->name;
        }
        
        return '';
    }

    public function getUserRealnameAttribute()
    {
        $user_real = UserReal::where('user_id', $this->attributes['user_id'])->first();
        if (empty($user_real)) {
            return '';
        }
        return $user_real->name;
    }

    public function getWayNameAttribute()
    {
        return C2cDealSend::find($this->attributes['legal_deal_send_id'])->way_name;
    }

    // 是否卖方
    public function getIsSellerAttribute()
    {
        $user_id = Users::getUserId();
        $legal_send = C2cDealSend::find($this->attributes['legal_deal_send_id']);
        $seller = Seller::find($this->attributes['seller_id']);
        if ($legal_send == null || $seller == null) {
            return 0;
        }
        if (($this->attributes['user_id'] == $user_id) && ($legal_send->type == 'buy')) {
            return 1;
        } elseif (($legal_send->type == 'sell') && ($user_id == $seller->user_id)) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function cancelLegalDealById($legal_deal_id,$type = AccountLog::C2C_DEAL_AUTO_CANCEL){
//        DB::beginTransaction();
        try{
            $legal_deal = C2cDeal::find($legal_deal_id);
            $legal_deal_send = C2cDealSend::lockForUpdate()->find($legal_deal->legal_deal_send_id);
            $users_wallet = UsersWallet::where('user_id', $legal_deal->user_id)->lockForUpdate()->where('currency', $legal_deal_send->currency_id)->first();
            if ($legal_deal_send->type == 'buy') { //求购
                // do something
//                if ($users_wallet->legal_balance < $number){
//                    DB::rollback();
//                    return $this->error('您的法币余额不足');
//                }
//                $legal_deal_send->surplus_number += $legal_deal->number;//
                // $legal_deal_send->surplus_number = bc_add($legal_deal_send->surplus_number,$legal_deal->number,5);//
                // if ($legal_deal_send->surplus_number > 0) {
                //     $legal_deal_send->is_done = 0;
                // }
                $legal_deal_send->is_done = 0;
                $data_wallet1 = [
                    'balance_type' => 2,
                    'wallet_id' => $users_wallet->id,
                    'lock_type' => 0,
                    'create_time' => time(),
                    'before' => $users_wallet->legal_balance,
                    'change' => $legal_deal->number,
                    'after' => bc_add($users_wallet->legal_balance, $legal_deal->number, 5),
                ];
                $data_wallet2 = [
                    'balance_type' => 2,
                    'wallet_id' => $users_wallet->id,
                    'lock_type' => 1,
                    'create_time' => time(),
                    'before' => $users_wallet->lock_legal_balance,
                    'change' => -$legal_deal->number,
                    'after' => bc_sub($users_wallet->lock_legal_balance, $legal_deal->number, 5),
                ];
//                $users_wallet->legal_balance += $legal_deal->number;
                $users_wallet->legal_balance = bc_add($users_wallet->legal_balance,$legal_deal->number,5);
//                $users_wallet->lock_legal_balance -= $legal_deal->number;
                $users_wallet->lock_legal_balance = bc_sub($users_wallet->lock_legal_balance,$legal_deal->number,5);
                $users_wallet->save();
                $legal_deal_send->save();

                AccountLog::insertLog(
                    [
                        'user_id' => $legal_deal->user_id,
                        'value' => $legal_deal->number,
                        'info' => '取消出售给商家法币,余额增加',
                        'type' => $type,
                        'currency' => $legal_deal_send->currency_id
                    ],
                    $data_wallet1
                );
                AccountLog::insertLog(
                    [
                        'user_id' => $legal_deal->user_id,
                        'value' => -$legal_deal->number,
                        'info' => '取消出售给商家法币,锁定余额减少',
                        'type' => $type,
                        'currency' => $legal_deal_send->currency_id
                    ],
                    $data_wallet2
                );
            } elseif ($legal_deal_send->type == 'sell') { //出售
//                $legal_deal_send->surplus_number += $legal_deal->number;
                // $legal_deal_send->surplus_number = bc_add($legal_deal_send->surplus_number,$legal_deal->number,5);
                // if ($legal_deal_send->surplus_number > 0) {
                //     $legal_deal_send->is_done = 0;
                // }
                $legal_deal_send->is_done = 0;
                $legal_deal_send->save();
            }
            $legal_deal->is_sure = 2;
            $legal_deal->save();
            $legal_deal->update_time = time();
//            DB::commit();
            return true;

        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
            return false;
        }

    }


}
