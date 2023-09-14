<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class C2cDealSend extends Model
{
    protected $table = 'c2c_deal_send';
    public $timestamps = false;
    protected $appends = ['seller_name','currency_name','limitation','way_name','currency_logo'];

    public function getCreateTimeAttribute(){
        return date('Y-m-d H:i:s',$this->attributes['create_time']);
    }

    public function getSellerNameAttribute(){
        return $this->hasOne('App\Users','id','seller_id')->value('account_number');
    }
    public function user(){
        return $this->hasOne('App\Users','id','seller_id');
    }

    public function getCurrencyNameAttribute(){
        return $this->hasOne('App\Currency','id','currency_id')->value('name');
    }

    public function getCurrencyLogoAttribute(){
        return $this->hasOne('App\Currency','id','currency_id')->value('logo');
    }

    public function getLimitationAttribute(){
        return array('min'=>bc_mul($this->attributes['min_number'] , $this->attributes['price'],5),
                     'max'=>bc_mul($this->attributes['surplus_number'] , $this->attributes['price'],5)
        );
    }

    public function getWayNameAttribute(){
        if ($this->attributes['way'] == 'ali_pay'){
            return '支付宝';
        }elseif ($this->attributes['way'] == 'we_chat'){
            return '微信';
        }elseif ($this->attributes['way'] == 'bank'){
            //return Seller::find($this->attributes['seller_id'])->bank_name;
            return UserCashInfo::where('user_id',$this->attributes['seller_id'])->first()->bank_name;
        }
    }

    //该发布信息下是否有未完成的订单
    public static function isHasIncompleteness($id){
        $is_deal = C2cDeal::where('legal_deal_send_id', $id)->pluck('is_sure')->toArray();
//        var_dump($is_deal);die;
        if (in_array(0,$is_deal)) {
            return true;
        }else{
            return false;
        }
    }

    //撤回发布
    public static function sendBack($id){
        //找到撤回的交易记录
        $send = self::find($id);
        try{
            $results = C2cDeal::where('legal_deal_send_id',$id)
                ->where('is_sure',0)->get();
            if (!empty($results)){
                foreach ($results as $result){

                    C2cDeal::cancelLegalDealById($result->id,AccountLog::C2C_DEAL_AUTO_CANCEL);

                }
                $send->is_done = 1;
                $send->save();
            }
            return true;
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
            return false;
        }

    }

    public function legalDeal(){
        return $this->hasOne('App\C2cDeal','legal_deal_send_id','id');
    }
}
