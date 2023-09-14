<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MicroNumber extends Model
{
    protected $appends=['currency_name'];

    public function getCurrencyNameAttribute()
    {
        return  $this->currency()->value('name');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
