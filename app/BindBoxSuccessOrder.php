<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Currency;
use App\BindBox;
    
class BindBoxSuccessOrder extends Model
{
    protected $table = 'bind_box_success_order';
    public $timestamps = false;
    
    protected $appends = [
        'currency_name',
        'image',
        'end_time',
        'name',
        'price',
    ];
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    
    public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
    }
    
    public function getPriceAttribute()
    {
        $BindBox = BindBox::where('code',$this->attributes['code'])->first();
        if (!empty($BindBox)) {
            return $BindBox->price;
        }
        return '';
    }
    
    public function getImageAttribute()
    {
        $BindBox = BindBox::where('code',$this->attributes['code'])->first();
        if (!empty($BindBox)) {
            return $BindBox->image;
        }
        return '';
    }
    
    public function getEndTimeAttribute()
    {
        $BindBox = BindBox::where('code',$this->attributes['code'])->first();
        if (!empty($BindBox)) {
            return $BindBox->end_time;
        }
        return '';
    }
    
    public function getNameAttribute()
    {
        $BindBox = BindBox::where('code',$this->attributes['code'])->first();
        if (!empty($BindBox)) {
            return $BindBox->name;
        }
        return '';
    }
    

}
