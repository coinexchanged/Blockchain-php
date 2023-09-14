<?php
/**
 * Created by PhpStorm.
 * User: 杨圣新
 * Date: 2018/10/26
 * Time: 16:45
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class RobotPlan extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'robot_plan';

    protected $appends = [
        'create_date',
        'created_date',
        'currency_info',
        'legal_info',
    ];

    public function getCreateDateAttribute()
    {
        $value = $this->attributes['itime'];
        return date('Y-m-d H:i:s', $value);
    }
    public function getCreatedDateAttribute()
    {
        $value = $this->attributes['etime'];
        return date('Y-m-d H:i:s', $value);
    }

    public function getCurrencyInfoAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'target')->value('name') ?? '';
    }

    public function getLegalInfoAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'base')->value('name') ?? '';
    }

}
