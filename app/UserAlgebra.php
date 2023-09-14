<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Seller;

class UserAlgebra extends Model
{
    protected $table = 'user_algebra';
//    public $timestamps = false;
    protected $appends=[
        'mobile',
        'touch_mobile',
    ];

    public function getMobileAttribute()
    {

        $res=$this->belongsTo('App\Users', 'user_id', 'id')->value('phone');
        if(empty($res)){
            $res=$this->belongsTo('App\Users', 'user_id', 'id')->value('email');
        }
        return $res;

    }

    public function getTouchMobileAttribute()
    {

        $res=$this->belongsTo('App\Users', 'touch_user_id', 'id')->value('phone');
        if(empty($res)){
            $res=$this->belongsTo('App\Users', 'touch_user_id', 'id')->value('email');
        }
        return $res;

    }

}
