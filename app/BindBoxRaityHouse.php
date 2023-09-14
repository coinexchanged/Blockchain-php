<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Currency;
    
class BindBoxRaityHouse extends Model
{
    protected $table = 'bind_box_raity_house';
    public $timestamps = false;
    
    // protected $appends = [
    //     'currency_name',
    // ];
    
    // public function currency()
    // {
    //     return $this->belongsTo(Currency::class);
    // }
    
    //  public function getCurrencyNameAttribute()
    // {
    //     return $this->currency()->value('name');
    // }

}
