<?php
/**
 * swl
 *
 * 20180705
 */

namespace App\Console\Commands;

use App\AccountLog;
use App\UsersWallet;
use App\Setting;
use App\Utils\RPC;
use App\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectToken extends Command
{
    protected $signature = 'collect_token{currency_id : id}';
    protected $description = '收集代币';

    protected $contract_address = "";   //合约地址
    protected $total_account_address = "";   //总账号地址
    protected $total_account_key = "";   //总账号私钥
    protected $currency_type = "";   //币种类型
    protected $decimal_scale = 18; //币种小数位

    public function handle()
    {
        $currency_id = $this->argument('currency_id');
        // AccountLog::insertLog(array("user_id"=>$currency_id,
        // "value"=>$currency_id,
        // "type"=>1,
        // "info"=>"测试"));
        // exit();
        $currency              = Currency::find($currency_id);
        $contract_address      = $currency->contract_address;
        $total_account_address = $currency->total_account;
        $total_account_key     = $currency->key;
        $currency_type         = $currency->type;
        $this->decimal_scale = $currency->decimal_scale;


        if (empty($contract_address) || empty($total_account_address) || empty($total_account_key)) {
            $this->comment("后台账号设置错误");
            exit();
        }
        $this->contract_address      = $contract_address;

        if($currency_type=='erc20'){
            $this->total_account_address = substr($total_account_address, 2);
        }else{
            $this->total_account_address = $total_account_address;
        }
        $this->total_account_key     = $total_account_key;
        $this->currency_type         = $currency_type;


        $datas = UsersWallet::where('currency', $currency_id)->get();

        $this->comment("start");
        foreach ($datas as $d) {
            $this->collectToken($d);
        }
        $this->comment("end");
    }

    public function collectToken($data)
    {
        if ($this->currency_type == 'btc') {
            return false;
        }

        if (empty($data->address)) {
            return false;
        }

        $address = $data->address;

        if ($this->currency_type == 'eth') {
            $url = "https://api.etherscan.io/api?module=account&action=balance&address=" . $address . "&tag=latest&apikey=579R8XPDUY1SHZNEZP9GA4FEF1URNC3X45" . rand(1, 10000);
        } else {
            $url = "https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=" . $this->contract_address . "&address=" . $address . "&tag=latest&apikey=579R8XPDUY1SHZNEZP9GA4FEF1URNC3X45" . rand(1, 1000);
            // $eth_address = substr($eth_address, 2);
            // $address_url = "http://47.92.171.137:8999/web3/transfer/oec?toaddress=".$total_account_address."&transfer_value=".$wallet_out->real_number."&contract_address=".$contract_address."&fromeaddress=".$total_account."&privates=".$key;
        }

        $content = RPC::apihttp($url);

        if (!$content) {
            return false;
        }

        DB::beginTransaction();
        try {
            $content = json_decode($content, true);
            if (isset($content["message"]) && $content["message"] == "OK") {
                $lessen = bc_pow(10, $this->decimal_scale);
                $chain_balance = bc_div($content["result"], $lessen);
                if ($chain_balance > 0) {
                    if ($this->currency_type == 'eth') {
                        $content["result"] = $content["result"] / 1000000000000000000;
                        $content['result']-=0.001;
                        $address_url = 'http://47.92.171.137:8999/web3/transfer?toaddress=' . $this->total_account_address . '&from_address=' . $address . '&transfer_value=' . $content["result"] . '&privates=' . decrypt($data->private);
                    } else {
                        $address_url = "http://47.92.171.137:8999/web3/transfer/oec?is_new=1&toaddress=" . $this->total_account_address . "&transfer_value=" . $content["result"] . "&contract_address=" . $this->contract_address . "&fromeaddress=" . $address . "&privates=" . decrypt($data->private) . '&decimal_scale=' . $this->decimal_scale;
                    }

                    $lian = RPC::apihttp($address_url);
                    $lian = @json_decode($lian, true);

                    if ($lian["error"] == "0") {
                        $data->old_balance = 0;
                        $data->save();
                        AccountLog::insertLog(array(
                            "user_id"=>99999,
                            "value"=>$content["result"],
                            "type"=>AccountLog::ETH_EXCHANGE,
                            "info"=>$data->user_id."归拢",'currency'=>$data->currency
                        ));
                        $this->comment($this->total_account_address . "user_id:" . $lian["content"]);
                    } else {
                        $this->comment('请求地址：');
                        dump($address_url);
                        $this->comment('请求响应：');
                        dump($lian);
                        $this->comment("请重试".$lian["error"]);
                    }
                }else{
                    $this->comment($content["result"]);
                }
            }else{
                // $this->comment($content);
                $this->comment($content);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }

    }

}
