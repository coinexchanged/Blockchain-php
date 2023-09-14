<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\Setting;
use App\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BonusAlgorithm extends Command
{
    protected $signature = 'bonus_algorithm';
    protected $description = '奖金算法';

    protected $user_bonus = array();   //日均收益
    protected $ecology_bonus = array();   //推广奖励

    public function handle()
    {
        $user_bonus = Setting::getValueByKey("user_bonus");
        $ecology_bonus = Setting::getValueByKey("ecology_bonus");
        if (empty($user_bonus) || empty($ecology_bonus)){
            $this->comment("后台奖金设置错误");
            exit();
        }
        $this->user_bonus = @json_decode($user_bonus, true);
        $this->ecology_bonus = @json_decode($ecology_bonus, true);

        $users = Users::get();
        $this->comment("奖金算法start");
        foreach ($users as $u){
            if ($u->balance > 0){
                $this->setUserBonus($u);
            }
        }
        foreach ($users as $s){
            if ($s->level == Users::USER_LEVEL_ORDINARY) {
                $this->setEcologyBonus($s);
            }
        }
        foreach ($users as $a){
            if ($a->level > Users::USER_LEVEL_ORDINARY){
                $this->setAgentReward($a);
            }
        }
        $this->comment("奖金算法end");
    }
    //代理商管理奖励
    public function setAgentReward($user){
        if (empty($user)) return false;

        DB::beginTransaction();
        try {
            $son = Users::where("parent_id",$user->id)->get();
            $money = 0;

            if (!empty($son)){
                foreach ($son as $s){
                    $money_g = $this->getSonBonus($s) * $this->getProportion($user->level,$s->level);
                    $money = $money + $money_g;
                }
            }

            if (!empty($money)){
                $user->sub_balance = $user->sub_balance + $money;
                $user->save();
                AccountLog::insertLog(
                    array(
                    "user_id"=>$user->id,
                    "value"=>$money,
                    "type"=>AccountLog::AGENT_REWARD,
                    "info"=>"代理商管理奖励"
                    )
                );
                $this->comment($user->id."：代理商管理奖励".$money);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }
    public function getProportion($self_level,$level){
        if (empty($self_level) || empty($level)){
            return 0.05;
        }
        $difference = $self_level - $level;
        if (($difference <= 0) || ($difference > 5)){
            return 0.05;
        }else{
            return $difference * 0.05;
        }

//        if ($level == Users::USER_LEVEL_ORDINARY){
//            return 0.05;
//        }else if ($level == Users::USER_LEVEL_GROUP){
//            return 0.1;
//        }else if ($level == Users::USER_LEVEL_COUNTY_AGENT){
//            return 0.15;
//        }else if ($level == Users::USER_LEVEL_CITY_AGENT){
//            return 0.2;
//        }else if ($level == Users::USER_LEVEL_PROVINCIAL_AGENT){
//            return 0.25;
//        }else{
//            return 0;
//        }
    }

    public function getSonBonus($user){
        if (empty($user)) return 0;
        $son = Users::getSon($user->id);
        $time = time();
        $start_time = $time - 3600 * 1;

        $user_arr = array();
        array_push($user_arr,$user->id);

        if (!empty($son)){
            foreach ($son as $s){
                array_push($user_arr,$s["user_id"]);
            }
        }
        
        $user_bonus = AccountLog::where("type",AccountLog::USER_BONUS)
            ->whereIn("user_id",$user_arr)
            ->where("created_time",">",$start_time)
            ->sum("value");
        $ecology_bonus = AccountLog::where("type",AccountLog::ECOLOGY_BONUS)
            ->whereIn("user_id",$user_arr)
            ->where("created_time",">",$start_time)
            ->sum("value");
        return $user_bonus + $ecology_bonus;
    }
    //更新生态推广奖励
    public function setEcologyBonus($user){
        if (empty($user)) return false;
        DB::beginTransaction();

        $son = Users::getSonId($user->id);
        $time = time();
        $start_time = $time - 3600 * 1;
        $money = 0;
        try {
            if (!empty($son)){
                foreach ($son as $s){
                    $log = AccountLog::where("type",AccountLog::USER_BONUS)->where("user_id",$s["user_id"])->where("created_time",">",$start_time)->first();
                    if (!empty($log)){
                        foreach ($this->ecology_bonus as $eb){
                            if (!empty($eb["one"]) && !empty($eb["two"]) && !empty($eb["three"]) && !empty($eb["four"])){
                                if ($eb["one"] <= $user->balance && $user->balance < $eb["two"]) {
                                    $interest_rate = ($s["level"] == 1) ? $eb["three"] : $eb["four"];
                                    $interest_rate = $interest_rate / 100;
                                    $money = $money + $log->value * $interest_rate;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if ($money > 0 ){
                $user->sub_balance = $user->sub_balance + $money;
                $user->save();
                AccountLog::insertLog(
                    array(
                        "user_id"=>$user->id,
                        "value"=>$money,
                        "type"=>AccountLog::ECOLOGY_BONUS,
                        "info"=>"生态推广奖励增加"
                    )
                );
                $this->comment($user->id."：生态推广奖励增加".$money);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }

    //更新日均收益
    public function setUserBonus($user){
        if (empty($user)) return false;
        DB::beginTransaction();
        try {
            foreach ($this->user_bonus as $ub){
                if (!empty($ub["one"]) && !empty($ub["two"]) && !empty($ub["three"])){
                    if ($ub["one"] <= $user->balance && $user->balance < $ub["two"]){
                        $money = $user->balance * ($ub["three"]/100);
                        $user->sub_balance = $user->sub_balance + $money;
                        $user->save();
                        AccountLog::insertLog(
                            array(
                                "user_id"=>$user->id,
                                "value"=>$money,
                                "type"=>AccountLog::USER_BONUS,
                                "info"=>"日均收益增加"
                            )
                        );
                        $this->comment($user->id."：日均收益增加".$money);
                        break;
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }

}
