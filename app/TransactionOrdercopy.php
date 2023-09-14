<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Jobs\LeverClose;

class TransactionOrdercopy extends Model
{

    protected $table = 'lever_transaction';
    public $timestamps = false;
//
//    protected $appends = [
//        'user_name',
//        'agent_level',
//        'phone',
//        'parent_agent_name'
//    ];
//



//    public function getCreateTimeAttribute() {
//        $value = $this->attributes['create_time'];
//
//        return date('Y-m-d H:i:s' , $value);
//    }
//
//
//    public function getUpdateTimeAttribute() {
//        $value = $this->attributes['update_time'];
//        if ($value == 0){
//            return '-';
//        }else{
//
//            return date('Y-m-d H:i:s' , $value);
//        }
//    }
//
//
//    public function getHandleTimeAttribute() {
//        $value = $this->attributes['handle_time'];
//        if ($value == 0){
//            return '-';
//        }else{
//
//            return date('Y-m-d H:i:s' , $value);
//        }
//    }
//    public function getCompleteTimeAttribute() {
//        $value = $this->attributes['complete_time'];
//        if ($value == 0){
//            return '-';
//        }else{
//
//            return date('Y-m-d H:i:s' , $value);
//        }
//    }
//    public function getPhoneAttribute() {
//        $value = $this->attributes['user_id'];
//
//        $user = self::get_user($value);
//
//        return $user->phone;
//    }
//    public function getUserNameAttribute() {
////        $value = $this->attributes['user_id'];
////
////        $user = self::get_user($value);
////
////        return $user->account_number;
//    }
//
//    public function getAgentLevelAttribute() {
//        $value = $this->attributes['user_id'];
//
//        $user = self::get_user($value);
//        if ($user->agent_id == 0){
//            return '普通用户';
//        }else{
//            $agent = DB::table('agent')->where('id' , $user->agent_id)->first();
//
//            $agent_name = '';
//            switch ($agent->level){
//                case 0:
//                    $agent_name = '超级管理员';
//                    break;
//                case 1:
//                    $agent_name = '一级代理商';
//                    break;
//                case 2:
//                    $agent_name = '二级代理商';
//                    break;
//                case 3:
//                    $agent_name = '三级代理商';
//                    break;
//                case 4:
//                    $agent_name = '四级代理商';
//                    break;
//                default:
//                    $agent_name = '普通用户';
//            }
//            return $agent_name;
//        }
//    }
//
//    public function getParentAgentNameAttribute() {
//        $value = $this->attributes['user_id'];
//
//        $user = self::get_user($value);
//        if ($user->agent_note_id == 0){
//            return '无';
//        }else{
//            $agent = DB::table('agent')->where('id' , $user->agent_note_id)->first();
//
//            return $agent->username;
//        }
//    }
//
//    public static function get_user($uid){
//        return DB::table('users')->where('id' , $uid)->first();
//    }


}