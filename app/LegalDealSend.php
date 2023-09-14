<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UsersWallet;
use App\Currency;

class LegalDealSend extends Model
{
    protected $table = 'legal_deal_send';
    public $timestamps = false;
    protected $appends = [
        'seller_name',
        'currency_name',
        'limitation',
        'way_name',
        'currency_logo',
    ];

    public function seller()
    {
        return $this->belongsTo('App\Seller', 'seller_id', 'id');
    }
    
    public function getCreateTimeAttribute(){
        return date('Y-m-d H:i:s',$this->attributes['create_time']);
    }

    public function getSellerNameAttribute(){
        return $this->hasOne('App\Seller','id','seller_id')->value('name');
    }

    public function getCurrencyNameAttribute(){
        return $this->hasOne('App\Currency','id','currency_id')->value('name');
    }

    public function getCurrencyLogoAttribute(){
        return $this->hasOne('App\Currency','id','currency_id')->value('logo');
    }

    public function getLimitationAttribute(){
        return array('min'=>bc_mul($this->attributes['min_number'] , $this->attributes['price'],5),
                     'max'=>bc_mul($this->attributes['max_number'] , $this->attributes['price'],5)
        );
    }

    public function getWayNameAttribute(){
        if ($this->attributes['way'] == 'ali_pay'){
            return '支付宝';
        }elseif ($this->attributes['way'] == 'we_chat'){
            return '微信';
        }elseif ($this->attributes['way'] == 'bank'){
            $seller = Seller::find($this->attributes['seller_id']);
            if($seller){
                $bank_name = $seller->bank_name;
            }else{
                $bank_name = '--';
            }
            return $bank_name;
        }
    }

    //该发布信息下是否有未完成的订单
    public static function isHasIncompleteness($id){
       
        return LegalDeal::where('legal_deal_send_id', $id)
            ->whereNotIn('is_sure', [1, 2])
            ->exists();
    }

    //撤回发布
    public static function sendBack($id){

        //找到撤回的交易记录
        $send = self::find($id);
        try{
            $results = LegalDeal::where('legal_deal_send_id',$id)
                ->where('is_sure',3)->first();
            if (!empty($results)){
                throw new \Exception('该发布信息下有已付款的订单，请确认再撤回');
            }
            $results = LegalDeal::where('legal_deal_send_id',$id)
                ->where('is_sure',0)->get();
            if (!empty($results)){
                foreach ($results as $result){

                    LegalDeal::cancelLegalDealById($result->id,AccountLog::LEGAL_DEAL_AUTO_CANCEL);

                }
                // $send->is_done = 1;
                // $send->save();
            }

            $legal_send = self::lockForUpdate()->find($id);
            
            if (!empty($legal_send)){
                if($legal_send->type == 'sell'){
                    if($legal_send->surplus_number > 0){
                        $seller = Seller::lockForUpdate()->find($legal_send->seller_id);
                        if (!empty($seller)){
                            $user_id = $seller->user_id;
                            
                            $user_wallet=UsersWallet::lockForUpdate()->where("user_id",$user_id)->where("currency", $seller->currency_id)->first();
                            $res1=change_wallet_balance($user_wallet,1,-$legal_send->surplus_number, AccountLog::SELLER_BACK_SEND, '商家撤回',true);
                            if ($res1 !== true) {
                                throw new \Exception('撤回失败:减少冻结资金失败');
                            }
                            $res2=change_wallet_balance($user_wallet,1,$legal_send->surplus_number, AccountLog::SELLER_BACK_SEND, '商家撤回');
                            if ($res2 !== true) {
                                throw new \Exception('撤回失败:增加余额失败');
                            }

                            $seller->lock_seller_balance = bc_sub($seller->lock_seller_balance,$legal_send->surplus_number,5);

                            $seller->save();
                            
                        }

                    }
                    
                   
                }
               
            }

            $send->refresh()->syncOriginal();
           // $send->surplus_number = 0;
            $send->is_done = 2;
            $send->is_shelves = 2;
            $send->is_sendback=2;
            $send->save();

            return true;
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
            return false;
        }

    }
}
