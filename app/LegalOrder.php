<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LegalOrder extends Model
{
    //
    protected $table = 'legal_order';
    protected $appends = ['account_number','user_info','cash_info'];

    public  function  setCreateAtAttribute($value)
    {
        $this->attributes['create_at'] = strtotime($value);
    }

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getUserInfoAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->first();
    }

    public function getCashInfoAttribute()
    {
        return $this->hasOne('App\UserCashInfo','user_id','user_id')->first();
    }

    public  function  getsetCreateAtAttribute($value)
    {
        return date('Y-m-d H:i:s',$this->attributes['create_at']);
    }

    public function getQueueableRelations()
    {
        // TODO: Implement getQueueableRelations() method.
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }

    public function users()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }
}
