<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Jobs\LeverClose;
use App\UserReal;
use App\Jobs\LeverPushTrade;

class TransactionOrder extends Model
{

    const BUY = 1; //买入
    const SELL = 2; //卖出

    const ENTRUST = 0; //挂单中
    const TRANSACTION = 1; //交易中
    const CLOSING = 2; //平仓中
    const CLOSED = 3; //已平仓
    const CANCEL = 4; //已撤单
    protected $table = 'lever_transaction';
    public $timestamps = false;

    protected $appends = [
        'user_name',
        'agent_level',
        'phone',
        'parent_agent_name',
        'currency_name',//tian add
        'userreal_name',//tian add
        'profits',//tian add
        'symbol',
    ];

    public function getSymbolAttribute()
    {
        $currency_id = $this->getAttribute('currency');
        $legal_id = $this->getAttribute('legal');
        $currency_match = CurrencyMatch::where('currency_id', $currency_id)
            ->where('legal_id', $legal_id)
            ->first();
        return $currency_match ? $currency_match->symbol : '';
    }


    /**
     * 取每单盈利
     *
     * @return void
     */
    public function getProfitsAttribute()
    {
        $profits = 0;
        $type = $this->getAttribute('type');
        $number = $this->getAttribute('number');
        $status = $this->getAttribute('status');
        if ($status == self::ENTRUST || $status == self::CANCEL) {
            return 0.00;
        }
        //$multiple = $this->getAttribute('multiple');
        //$multiple_number = bc_mul($number, $multiple);
        $update_price = $this->getAttribute('update_price');
        $price = $this->getAttribute('price');
        $diff = $type == self::BUY ? bc_sub($update_price, $price) : bc_sub($price, $update_price);
        $profits = bc_mul($diff, $number);
        return $profits;
    }

    public function getCurrencyNameAttribute() {
        $value = $this->attributes['currency'];
        return DB::table('currency')->where('id' , $value)->first();

    }
    public function getUserrealNameAttribute() {
        $value = $this->attributes['user_id'];
        $is_real_name=DB::table('user_real')->where('user_id' , $value)->first();
        if(!empty($is_real_name))
        {
            return $is_real_name->name;
        }
        else
        {
            return " ";
        }

    }


    public function getCreateTimeAttribute() {
        $value = $this->attributes['create_time'];

        return date('Y-m-d H:i:s' , $value);
    }


    public function getUpdateTimeAttribute() {
        $value = $this->attributes['update_time'];
        if ($value == 0){
            return '-';
        }else{

            return date('Y-m-d H:i:s' , $value);
        }
    }


    public function getHandleTimeAttribute() {
        $value = $this->attributes['handle_time'];
        if ($value == 0){
            return '-';
        }else{

            return date('Y-m-d H:i:s' , $value);
        }
    }
    public function getCompleteTimeAttribute() {
        $value = $this->attributes['complete_time'];
        if ($value == 0){
            return '-';
        }else{

            return date('Y-m-d H:i:s' , $value);
        }
    }
    public function getPhoneAttribute() {
        $user = $this->user()->getResults();
        return $user->phone ?? '';
    }
    public function getUserNameAttribute() {
        $user = $this->user()->getResults();
        return $user->account_number ?? '';
    }

    public function getAgentLevelAttribute() {
        $user = $this->user()->getResults();

        if ($user) {
            
            if ($user->agent_id == 0){
                return '普通用户';
            }else{
                
                $agent = DB::table('agent')->where('id' , $user->agent_id)->first();
    
                $agent_name = '';
               
                if(!empty($agent) && $agent->level==0 ){
                    $agent_name = '超管';
                }else if(!empty($agent) && $agent->level > 0){
                    $agent_name =$agent->level.'级代理商';
                }
               
                return $agent_name;
            }
        } else {
            return '无用户';
        }
        
    }

    public function getParentAgentNameAttribute() {
        $user = $this->user()->getResults();
        if ($user) {
            if ($user->agent_note_id == 0){
                return '无';
            }else{
                $agent = DB::table('agent')->where('id' , $user->agent_note_id)->first();
    
                return $agent->username;
            }
        } else {
            return '';
        } 
    }

    public static function get_user($uid){
        return DB::table('users')->where('id' , $uid)->first();
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }


}