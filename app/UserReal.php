<?php
/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;


use Illuminate\Database\Eloquent\Model;
use Session;
class UserReal extends Model
{
    protected $table = 'user_real';
    public $timestamps = false;
    protected $hidden = [];
    protected $appends = ['account'];

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    public function getAccountAttribute()
    {
    
        $res=$this->belongsTo('App\Users', 'user_id', 'id')->value('phone');
        if(empty($res)){
             $res=$this->belongsTo('App\Users', 'user_id', 'id')->value('email');
        }
        return $res;
        
    }
    public function user()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }
}