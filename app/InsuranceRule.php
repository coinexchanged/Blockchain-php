<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsuranceRule extends Model
{
    //
    protected $table = 'insurance_rules';
    protected $appends=['insurance_name'];
    public $timestamps=false;

    public function getInsuranceNameAttribute()
    {

        $res=$this->belongsTo('App\InsuranceType', 'insurance_type_id', 'id')->value('name');
        if(empty($res)) {
            $res="";
        }
        return $res;

    }
    public function insuranceType()
    {
        return $this->belongsTo('App\InsuranceType', 'insurance_type_id', 'id');
    }
}
