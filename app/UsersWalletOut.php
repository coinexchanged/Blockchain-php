<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UsersWalletOut extends Model
{
    protected $table = 'users_wallet_out';
    public $timestamps = false;
    protected $appends = ['currency_name', 'account_number', 'currency_type','user_info'];
    //节点等级
    const TO_BE_AUDITED = 1;
    const ToO_EXAMINE_ADOPT = 2;
    const ToO_EXAMINE_FAIL = 3;

    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('name');
    }

    public function getUserInfoAttribute()
    {
        return $this->hasOne('App\UserCashInfo','user_id','user_id')->first();
    }

    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('type');
    }

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    // public function getRealNumberAttribute()
    // {
    //     // return $this->attributes['number']*(1-$this->attributes['rate']);
    //     return bcmul($this->attributes['number'], (1 - $this->attributes['rate'] / 100), 8);
    // }

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }
}
