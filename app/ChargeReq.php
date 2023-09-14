<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChargeReq extends Model
{
    //
    protected $table = 'charge_req';
    protected $appends = [
        'currency_name',//币种
        'account_number',
        'account'
    ];

    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency_id')->value('name');
    }
    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'uid')->value('account_number');
    }

    public function getAccountAttribute()
    {
        $value = $this->hasOne('App\Users', 'id', 'uid')->value('phone');
        if (empty($value)) {
            $value = $this->hasOne('App\Users', 'id', 'uid')->value('email');
        }
        return $value;
    }
    public function user()
    {
        return $this->belongsTo('App\Users', 'uid', 'id');
    }
}
