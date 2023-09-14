<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersInsurance extends Model
{
    //
    protected $table = 'users_insurances';
    protected $guarded = [];
    protected $appends = [
        'insurance_rules_arr',
        'mobile',
        'user_name',
        'insurance_type_type',
        'insurance_type_name',
        'status_str',
        'claim_status_str',
        'rescinded_type_str'
    ];

    public function getStatusStrAttribute()
    {
        $status = $this->getAttribute('status');
        if($status == 1){
            return '生效中';
        }else{
            return '已失效';
        }
    }

    public function getClaimStatusStrAttribute()
    {
        $claim_status = $this->getAttribute('claim_status');
        if($claim_status == 1){
            return '理赔中';
        }else{
            return '--';
        }
    }

    public function getRescindedTypeStrAttribute()
    {
        $rescinded_type = $this->getAttribute('rescinded_type');
        $status = $this->getAttribute('status');
        if($rescinded_type == 1){
            return '手动解约';
        }else{
            if($status == 0){
                return '自动解约';
            }else{
                return '--';
            }

        }
    }

    public function getMobileAttribute()
    {
        $mobile = $this->user()->value('account_number');
        return $mobile;
    }

    public function getUserNameAttribute()
    {
        $user = $this->user()->first();
        if ($user) {
            return $user->userreal_name;
        } else {
            return '--';
        }
    }

    public function getInsuranceTypeNameAttribute()
    {
        $insurance_type = $this->insurance_type()->first();
        return $insurance_type->type_name;
    }
    public function getInsuranceTypeTypeAttribute()
    {
        $insurance_type = $this->insurance_type()->first();
        return $insurance_type->type;
    }
    public function insurance_type()
    {
        return $this->belongsTo(InsuranceType::class, 'insurance_type_id');
    }

    public function getInsuranceRulesArrAttribute()
    {
        return $this->insurance_rules()->orderBy('amount', 'desc')->get();
    }

    public function insurance_rules()
    {
        return $this->hasMany(InsuranceRule::class, 'insurance_type_id', 'insurance_type_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
