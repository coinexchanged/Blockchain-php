<?php

namespace App;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
class AgentMoneylog extends Model
{
    protected $table = 'agent_money_log';
    public $timestamps = false;
    protected $appends = [
        'user_name',
        'agent_level',
        'jie_agent_name',
        'jie_agent_level',
        'legal_name'
    ];

    public function getCreatedTimeAttribute($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getUserNameAttribute()
    {
     
        // $user=$this->user()->getResults();
        
        // return $user?$user->account_number:'';

        $user=$this->user()->value('account_number');
        
        return $user??'';
        
    }

    public function getJieAgentNameAttribute()
    {   
        $agent=$this->agent()->getResults();
       
        return $agent?$agent->username:'';
    }

    public function getJieAgentLevelAttribute()
    {   
        $agent=$this->agent()->getResults();

        return $agent->is_admin==1?'超级代理商':$agent->level.'级代理商';
    }

    public function getLegalNameAttribute()
    {
        $value = $this->attributes['legal_id'];
        $legal=Currency::find($value);

        return $legal?$legal->name:'';
    }


    public function getAgentLevelAttribute() {
        // $value = $this->attributes['son_user_id'];

        // $user = self::get_user($value);
        $user=$this->user()->getResults();
        if ($user->agent_id == 0){
            return '普通用户';
        }else{
            $agent = DB::table('agent')->where('id' , $user->agent_id)->first();

            if($agent && $agent->level == 0){
                $agent_name = '超级代理商';
            }else if($agent && $agent->level > 0){
                $agent_name = "{$agent->level}级代理商";
            }else{
                $agent_name = '普通用户';
            }
            return $agent_name;
        }
    }




    public static function get_user($uid){
        return DB::table('users')->where('id' , $uid)->first();
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'son_user_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }
}
