<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Users;                            
class FeedBack extends Model
{
    protected $table = 'feedback';
    public $timestamps = false;
    protected $appends = ['account_number'];

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
    public function getReplyTimeAttribute()
    {
        if($this->attributes['reply_time']){
            return date('Y-m-d H:i:s', $this->attributes['reply_time']);
        }
        
    }
    public static function getNameById($currency_id)
    {
        $currency = self::find($currency_id);
        return $currency->name;
    }
    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }
    // public function getImgAttribute($value)
    // {
    //     return unserialize($value);
    // }

    // public function setImgAttribute($value)
    // {
    //     if (empty($value)) {
    //         $this->attributes['discuss_img'] = "0";
    //     } else {
    //         $this->attributes['discuss_img'] = serialize($value);
    //     }

    // }
    public function user()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }



}
