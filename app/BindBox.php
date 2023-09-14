<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\{
    Address,
    Currency,
    UsersInsurance,
    InsuranceType,
    InsuranceClaimApply,
    Users,
    UserCashInfo,
    UserReal,
    UsersWallet,
    BindBoxOrder,
    BindBoxQuotationLog,
    BindBoxCollect
};

class BindBox extends Model
{
    protected $table = 'bind_box';
    public $timestamps = false;
    
    protected $appends = [
        'head_portrait',
        'author_name',
        'currency_name',
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
        $user = Users::find($this->attributes['author']);
        if (!empty($user)) {
            return $user->head_portrait;
        }
        return '';
    }
    
    public function getAuthorNameAttribute()
    {
        $user = Users::find($this->attributes['author']);
        if (!empty($user)) {
            return $user->nickname;
        }
        return '';
    }

}
