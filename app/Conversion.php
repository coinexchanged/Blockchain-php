<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    protected $table = 'conversion';
    public $timestamps = false;
    protected $appends = ['mobile','form_currency','to_currency'];

    public function getMobileAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }
    public function getFormCurrencyAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'form_currency_id')->value('name');
    }
    public function getToCurrencyAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'to_currency_id')->value('name');
    }



    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }



}
