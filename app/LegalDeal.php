<?php

namespace App;

use App\Http\Controllers\Api\ChatController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Seller;

class LegalDeal extends Model
{
    protected $table = 'legal_deal';
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
        'bank_address',
        'unread_msg'
    ];


    public function getUnreadMsgAttribute()
    {
        return ChatController::unreadMsg($this->user_id, $this->id);
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
        return $this->belongsTo('App\Seller', 'seller_id', 'id');
    }

    public function legalDealSend()
    {
        return $this->hasOne('App\LegalDealSend', 'id', 'legal_deal_send_id');
    }

    public function getWayNameAttribute()
    {
        return LegalDealSend::find($this->attributes['legal_deal_send_id'])->way;
    }


    public function getCreateTimeAttribute()
    {
        return date('H:i:s m/d', $this->attributes['create_time']);
    }
    public function getFormatCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }

    public function getDealMoneyAttribute()
    {
        $legal = LegalDealSend::find($this->attributes['legal_deal_send_id']);
        if (!empty($legal)) {
            return bcmul($this->attributes['number'], $legal->price, 6);
        }
        return 0;
    }


    public function getCurrencyNameAttribute()
    {
        $legal = LegalDealSend::find($this->attributes['legal_deal_send_id']);
        if (!empty($legal)) {
            return $legal->currency_name;
        }
        return '';
    }

    public function getSellerPhoneAttribute(){
        $seller = Seller::find($this->attributes['seller_id']);
        if (empty($seller)){
            return '';
        }else{
            return $seller->mobile;
        }
    }

    public function getTypeAttribute()
    {
        return $this->hasOne('App\LegalDealSend', 'id', 'legal_deal_send_id')->value('type');
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
        return $this->hasOne('App\Seller', 'id', 'seller_id')->value('name');
    }

    public function getBankAddressAttribute()
    {
        return $this->hasOne('App\Seller', 'id', 'seller_id')->value('bank_address');
    }

    public function getPriceAttribute()
    {
        return $this->hasOne('App\LegalDealSend', 'id', 'legal_deal_send_id')->value('price');
    }

    public function getHesAccountAttribute()
    {
        $legal_send = LegalDealSend::find($this->attributes['legal_deal_send_id']);
        if (!empty($legal_send)) {
            $seller = Seller::find($legal_send->seller_id);
            if (!empty($seller)) {
                if ($legal_send->way == 'bank') {
                    return '银行账号：'.$seller->bank_account .',开户银行：' . $seller->bank_name . ',开户支行:' . $seller->bank_address;
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
        $seller = Seller::find($this->attributes['seller_id']);
        if (!empty($seller)) {
            $real = UserReal::where('user_id', $seller->user_id)->where('review_status', 2)->first();
            if (!empty($real)) {
                return $real->name;
            }
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

    // 是否卖方
    public function getIsSellerAttribute()
    {
        $user_id = Users::getUserId();
        $legal_send = LegalDealSend::find($this->attributes['legal_deal_send_id']);
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

    public static function cancelLegalDealById($legal_deal_id,$type = AccountLog::LEGAL_DEAL_USER_SELL_CANCEL){
//        DB::beginTransaction();
        try{
            $legal_deal = LegalDeal::lockForUpdate()->find($legal_deal_id);
            if(!$legal_deal){
                throw new \Exception('未知交易信息');
            }
            
            if ($legal_deal->is_sure != 0) {
                throw new \Exception('当前状态不能取消');
            }
            $legal_deal_send = LegalDealSend::lockForUpdate()->find($legal_deal->legal_deal_send_id);
            $users_wallet = UsersWallet::where('user_id', $legal_deal->user_id)
                ->lockForUpdate()->where('currency', $legal_deal_send->currency_id)->first();
            if ($legal_deal_send->is_done != 0) {
                    throw new \Exception('商家发布已撤销或已完成,无法取消该交易');
                }
            if ($legal_deal_send->type == 'buy') { //求购
               
                $legal_deal_send->surplus_number = bc_add($legal_deal_send->surplus_number,$legal_deal->number,5);//
                if ($legal_deal_send->surplus_number > 0) {
                    $legal_deal_send->is_done = 0;
                    $legal_deal_send->is_shelves = 1; // 这里只是恢复发布的广告上架
                }

                $res2=change_wallet_balance($users_wallet,1,$legal_deal->number, $type, '取消出售给商家法币',false);
                if ($res2 !== true) {
                    throw new \Exception('取消失败:撤回冻结到余额失败');
                }
                $res1=change_wallet_balance($users_wallet,1,-$legal_deal->number, $type, '取消出售给商家法币',true);
                if ($res1 !== true) {
                    throw new \Exception('取消失败:减少冻结资金失败');
                }

            } elseif ($legal_deal_send->type == 'sell') { //出售
//                $legal_deal_send->surplus_number += $legal_deal->number;
                $legal_deal_send->surplus_number = bc_add($legal_deal_send->surplus_number,$legal_deal->number,5);
                if ($legal_deal_send->surplus_number > 0) {
                    $legal_deal_send->is_done = 0;
                    $legal_deal_send->is_shelves = 1; // 这里只是恢复发布的广告上架
                }
                
            }
            $legal_deal_send->save();

            $legal_deal->is_sure = 2;
           
            $legal_deal->update_time = time();
            $legal_deal->save();
//            DB::commit();
            return true;

        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
            return false;
        }

    }


}
