<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Currency;
use App\Users;
use App\BindBox;

class BindBoxQuotationLog extends Model
{
    protected $table = 'bind_box_quotation_log';
    public $timestamps = false;

    protected $appends = [
        'head_portrait',
        'currency_name',
        'nick_name',
        'nft_name',
        'nft_image',
    ];
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    
     public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
    }
    
    public function getHeadPortraitAttribute()
    {
        $user = Users::find($this->attributes['buyer_id']);
        if (!empty($user)) {
            return $user->head_portrait;
        }
        return '';
    }
    
    public function getNftImageAttribute()
    {
        
        $BindBox = BindBox::where('code',$this->attributes['code'])->first();
        if (!empty($BindBox)) {
            return $BindBox->image;
        }
        return '';
        
    }
    public function getNftNameAttribute()
    {
        
        $BindBox = BindBox::where('code',$this->attributes['code'])->first();
        if (!empty($BindBox)) {
            return $BindBox->name;
        }
        return '';
        
    }
    public function getNickNameAttribute()
    {
        
        $user = Users::find($this->attributes['buyer_id']);
        if (!empty($user)) {
            return $user->nickname;
        }
        return '';
        
    }
    
}