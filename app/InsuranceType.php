<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
{
    //
    protected $table = 'insurance_types';
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = [
        'currency_name',
        'type_name',
    ];

    public function getCurrencyNameAttribute()
    {
        $currency_id = $this->getAttribute('currency_id');
        $currency_name = Currency::where('id', $currency_id)->first()->name;

        return $currency_name;

    }

    public function getTypeNameAttribute()
    {
        $type = $this->getAttribute('type');
        if ($type == 1) {
            $type_name = '正向险';
        } elseif ($type == 2) {
            $type_name = '反向险';
        } else {
            $type_name = '未知';
        }
        return $type_name;
    }

}
