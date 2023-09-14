<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlashAgainst extends Model
{
    protected $table = 'flash_against';
    public $timestamps = false;
    protected $appends = ['status_name','mobile','l_currency','r_currency'];

    public function getMobileAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }
    public function getLCurrencyAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'left_currency_id')->value('name');
    }
    public function getRCurrencyAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'right_currency_id')->value('name');
    }

    protected function getStatusNameAttribute()
    {
        $value=$this->attributes['status'];
        if ($value==0){
            $str='闪兑中';
        }elseif ($value==1) {
            $str='已通过';
        }elseif ($value==2){
            $str='已驳回';
        }else{
            $str="";
        }
        return $str;
    }


    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
    public function getReviewTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }


}
