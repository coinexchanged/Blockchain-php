<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Users;
//use App\CandyTransfer;

class CandyTransfer extends Model
{
    protected $table = 'candy_transfer';
    public $timestamps = false;
    const CREATED_AT = 'create_time';

    protected $appends = [
        'to_user_phone',
        'from_user_phone',
    ];
    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


    public function toUser()
    {
        return $this->belongsTo('App\Users', 'to_user_id', 'id')->withDefault();
    }

    public function fromUser()
    {
        return $this->belongsTo('App\Users', 'from_user_id', 'id')->withDefault();
    }
    public function getToUserPhoneAttribute()
    {
        return $this->toUser()->value('account_number');
    }

    public function getFromUserPhoneAttribute()
    {
        return $this->fromUser()->value('account_number');
    }

}
