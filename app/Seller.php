<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $table = 'seller';
    public $timestamps = false;
    protected $appends = ['bank_name','account_number','currency_name','prove_email','prove_mobile','prove_real','prove_level','is_myseller','legal_balance','lock_legal_balance'];

    public function getBankNameAttribute(){
        return $this->hasOne('App\Bank','id','bank_id')->value('name');
    }

    public function getAccountNumberAttribute(){
        return $this->hasOne('App\Users','id','user_id')->value('account_number');
    }

    public function getCurrencyNameAttribute(){
        return $this->hasOne('App\Currency','id','currency_id')->value('name');
    }
    public function UsersWallet(){
        return $this->hasOne('App\UsersWallet','user_id','user_id');
    }
    public function getLegalBalanceAttribute(){
        $value=$this->attributes['currency_id'];
        $result = UsersWallet::where('user_id',$this->attributes['user_id'])->where('currency',$value)->first();
        if($result)
        {
            return $result->legal_balance;
        }else{
            return 0;
        }

    }
    public function getLockLegalBalanceAttribute(){
        $value=$this->attributes['currency_id'];
        $result = UsersWallet::where('user_id',$this->attributes['user_id'])->where('currency',$value)->first()->lock_legal_balance;
        if($result)
        {
            return $result;
        }else{
            return 0;
        }

    }

    public function setWechatNicknameAttribute($value){
        $this->attributes['wechat_nickname'] = base64_encode($value);
    }

    public function getWechatNicknameAttribute(){
        if (!empty($this->attributes['wechat_nickname'])){
            return base64_decode($this->attributes['wechat_nickname']);
        }else{
            return '';
        }

    }

    public function getCreateTimeAttribute(){
        return date('Y-m-d H:i:s',$this->attributes['create_time']);
    }

    public function legalDeal(){
        return $this->hasOne('App\LegalDeal','seller_id','id');
    }

    public function getProveEmailAttribute(){
        $result = $this->hasOne('App\Users','id','user_id')->value('email');
        if (empty($result)){
            return 0;
        } else{
            return 1;
        }
    }

    public function getProveMobileAttribute(){
        $result = $this->hasOne('App\Users','id','user_id')->value('phone');
        if (empty($result)){
            return 0;
        } else{
            return 1;
        }
    }


    public function getProveRealAttribute(){
        $result = UserReal::where('user_id',$this->attributes['user_id'])->where('review_status',2)->first();
        if (empty($result)){
            return 0;
        } else{
            return 1;
        }
    }

    public function getProveLevelAttribute(){
        $result = UserReal::where('user_id',$this->attributes['user_id'])->where('review_status',2)->first();
        if (!empty($result) && !empty($result->front_pic)){
            return 1;
        }
        return 0;
    }

    public function getIsMysellerAttribute(){
        if ($this->attributes['user_id'] == Users::getUserId()){
            return 1;
        }
        return 0;
    }

}
