<?php
/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use App\AccountLog;
use App\Setting;
use App\Users;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorEthLog extends Command
{
    protected $signature = 'monitor_eth_log';
    protected $description = '监听以太坊账号';

    protected $rate_exchange = 0;   //兑换率
    protected $last_hash = "";   //最后一次交易hash
    protected $company_eth_address = "";   //公司以太坊地址

    public function handle()
    {
        $this->rate_exchange = Setting::getValueByKey("rate_exchange",10);
        $this->last_hash = Setting::getValueByKey("last_hash");
        $this->company_eth_address = Setting::getValueByKey("company_eth_address");

        if (empty($this->company_eth_address)){
            $this->comment("公司以太坊地址为空");
            exit();
        }
        $this->comment("start");
        $this->getEthLog();
        $this->comment("end");
    }
    public function getEthLog($page = 1){
        $parameter = array(
            "module"=>"account",
            "action"=>"txlist",
            "address"=>$this->company_eth_address,
            "startblock"=>"0",
            "endblock"=>"99999999",
            "page"=>$page,
            "offset"=>"10",
            "sort"=>"desc",
            "apikey"=>"579R8XPDUY1SHZNEZP9GA4FEF1URNC3X45"
        );
        $data = RPC::http_post("https://api.etherscan.io/api",$parameter);
        $jsonInfo = @json_decode($data, true);

        if (!empty($jsonInfo)){
            if ($jsonInfo["status"] == "1" && $jsonInfo["message"] == "OK"){
                $continue = false;//是否递归执行该方法

                if(count($jsonInfo["result"]) > 0){
                    if (count($jsonInfo["result"]) == 10){
                        $continue = true;
                    }

                    foreach ($jsonInfo["result"] as $j){
                        if ($j["hash"] == $this->last_hash){
                            $continue = false;
                            break ;
                        }else{
                            //别人转给自己的
                            if (($j["to"] == $this->company_eth_address) && ($j["value"] > 0)){
                                $user = Users::where('eth_address',$j["from"])->first();
                                if (!empty($user)){
                                    $this->updateUserBalance($user,$j);
                                }
                            }
                        }
                    }
                }
                if ($continue){
                    $this->getEthLog($page + 1);
                }
            }else{
                $this->comment($jsonInfo["message"]);
                $this->comment($jsonInfo["result"]);
            }
        }
    }
    public function updateUserBalance($user,$info = array()){
        if (empty($user) || empty($info)){
            return false;
        }
        DB::beginTransaction();
        try {
            $balance = $info["value"] / 10000000000000 * $this->rate_exchange;
            $user->balance = $user->balance + $balance;

            //更新自己级别
            if ($user->balance >= 5000000 && $user->level != Users::USER_LEVEL_LARGEAREA_AGENT){
                $user->level = Users::USER_LEVEL_LARGEAREA_AGENT;
            }else if($user->balance >= 3000000 && $user->level < Users::USER_LEVEL_PROVINCIAL_AGENT){
                $user->level = Users::USER_LEVEL_PROVINCIAL_AGENT;
            }else if($user->balance >= 2000000 && $user->level < Users::USER_LEVEL_CITY_AGENT){
                $user->level = Users::USER_LEVEL_CITY_AGENT;
            }else if($user->balance >= 1000000 && $user->level < Users::USER_LEVEL_COUNTY_AGENT){
                $user->level = Users::USER_LEVEL_COUNTY_AGENT;
            }
            $user->save();

            $this->updateParentLevel($user);

            AccountLog::insertLog(array("user_id"=>$user->id,"value"=>$balance,"type"=>AccountLog::ETH_EXCHANGE,"info"=>$info["hash"]));
            Setting::updateValueByKey("last_hash",$info["hash"]);
            $this->comment($user->id."：增加有效可用余额".$balance);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }

    //更新父级等级
    public function updateParentLevel($user){
        if (empty($user) || empty($user->parent_id)) return false;
        $parent = Users::find($user->parent_id);
        if (empty($parent)) return false;
        if ($parent->level < Users::USER_LEVEL_LARGEAREA_AGENT){
            $min_count = 0;
            $min_money = 0;

            if ($parent->level == Users::USER_LEVEL_PROVINCIAL_AGENT){
                $min_count = 3;
                $min_money = 100000;
            }else if ($parent->level == Users::USER_LEVEL_CITY_AGENT){
                $min_count = 4;
                $min_money = 10000;
            }else if ($parent->level == Users::USER_LEVEL_COUNTY_AGENT){
                $min_count = 5;
                $min_money = 10000;
            }else if ($parent->level == Users::USER_LEVEL_GROUP){
                $min_count = 6;
                $min_money = 10000;
            }else if ($parent->level == Users::USER_LEVEL_ORDINARY){
                $min_count = 8;
                $min_money = 5000;
            }

            $count = Users::where("parent_id",$parent->id)->where("level",$parent->level)->where("balance",">=",$min_money)->count();

            if ($count >= $min_count){
                $parent->level = $parent->level  +1;
                $parent->save();
            }
        }
    }

}
