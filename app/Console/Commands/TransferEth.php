<?php
/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use App\AccountLog;
use App\TransferEths;
use App\UsersWallet;
use App\Setting;
use App\Currency;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferEth extends Command
{
    protected $signature = 'transfer_eth{currency_id : id}';
    protected $description = '批量转eth';

    protected $contract_address = "";   //合约地址
    protected $total_account_address = "";   //总账号地址
    protected $total_account_key = "";   //总账号私钥
    protected $currency_type = "";   //币种类型

    protected $eth_transfer_value = 0.001;   //总账号私钥

    public function handle()
    {

        $currency_id = $this->argument('currency_id');
        // AccountLog::insertLog(array("user_id"=>$currency_id,
        // "value"=>$currency_id,
        // "type"=>1,
        // "info"=>"测试"));
        // exit();
        $currency = Currency::find($currency_id);
        $contract_address     =  $currency->contract_address;
        $total_account_address     = $currency->total_account;
        $total_account_key     = $currency->key;
        $currency_type     = $currency->type;
        if (empty($contract_address) || empty($total_account_address) || empty($total_account_key)) {
            $this->comment("后台账号设置错误");
            exit();
        }
        
        $this->contract_address = $contract_address;
        $this->total_account_address = $total_account_address;
        $this->total_account_key = $total_account_key;
        $this->currency_type = $currency_type;

        $datas = UsersWallet::where('currency',$currency_id)->get();
        $this->comment("start");
        foreach ($datas as $d){
            $this->transferEth($d);
        }
        $this->comment("end");
    }
    public function transferEth($data){
        if($this->currency_type!='btc'){
            if (!empty($data->address)){
                $address = $data->address;
                    
                if ($this->currency_type != 'erc20') {
                   return false;
                }
                
                $url = "https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=" . $this->contract_address . "&address=" . $address . "&tag=latest&apikey=579R8XPDUY1SHZNEZP9GA4FEF1URNC3X45" . rand(1, 1000);

                $content = RPC::apihttp($url);
                if ($content){
                    DB::beginTransaction();
                    try {
                        $content = json_decode($content,true);
                        var_dump($content);
                        if (isset($content["message"]) && $content["message"] == "OK"){
                            $content["result"] = $content["result"] / 1000000000000000000;
                            if ($content["result"] > 0){
                                $transfer_url = "http://47.92.171.137:8999/web3/transfer?from_address=".$this->total_account_address."&toaddress=".$address."&transfer_value=".$this->eth_transfer_value."&privates=".$this->total_account_key;
                                $transfer_content = RPC::apihttp($transfer_url);
                                $transfer_content = @json_decode($transfer_content,true);
                                if ($transfer_content["error"] == "0"){
                                    AccountLog::insertLog(array("user_id"=>9999999,"value"=>0.001,"type"=>AccountLog::ETH_EXCHANGE,"info"=>$data->user_id."打入ETH成功",'currency'=>$data->currency));
                                    $this->comment($transfer_content["content"]);
                                    $this->comment($data->user_id."充值成功");
                                    $wallet = UsersWallet::find($data->id);
                                    $wallet->old_balance = $wallet->old_balance+0.001;
                                    $wallet->save();
                                }else{
                                    $this->comment("请重试");
                                }
                            }else{
                                $this->comment($data->user_id."没有代币");
                            }
                        }
                        DB::commit();
                    } catch (\Exception $ex) {
                        DB::rollback();
                        $this->comment($ex->getMessage());
                    }
                }

            }
        }
    }

}
