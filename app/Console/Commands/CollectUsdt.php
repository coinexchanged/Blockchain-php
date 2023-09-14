<?php

namespace App\Console\Commands;

use App\Utils\ZtPay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectUsdt extends Command
{
    protected $gas_address;//手续费地址
    protected $center_address;//归集地址

    protected $name = 'usdt:collect';
    protected $description = '归集usdt.';
    protected $fee = 0;

    public function handle()
    {
        //获取所有的待归集的账号
        $list = Db::table('users_wallet')->where('currency', 3)->where('id', '>=', 656)->get()->toArray();
//        var_dump($list);
        //归集转账
        $gas_price = $this->getGasPrice();
        $gas = $this->getGas();
        if ($gas_price < 0.0000000001) {
            exit('获取矿工费失败');
        }
        echo "获取到矿工价格：{$gas_price}\r\n\r\n";

        foreach ($list as $wallet) {
            //获取线上余额
            echo "开始归集：{$wallet->address}\r\n";
            $balance = $this->getBalance($wallet->address);
            $usdt = $balance['usdt'];
            $eth = $balance['eth'];
            $min = $this->getMinumberAmount();

            if ($usdt >= $min) {
                $this->fee = ($gas * $gas_price) / 1000000000;

                if ($this->fee < 0.0000000001) {
                    return die('手续费异常');
                }
                echo "手续费价格：{$this->fee}\r\n";
                if ($this->fee > $eth) {
                    //手续费大于当前钱包eth价格
                    $is_ok = $this->transEth($wallet->address);
                    if (!$is_ok) {
                        echo "手续费转账失败，执行下一条归集\r\n";
                        continue;
                    }
                }

                $is_transferd = $this->transfer($wallet->address, $usdt);
                if ($is_transferd) {
                    echo "归集完成：{$usdt}\r\n";
                } else {
                    echo "归集失败";
                }
            } else {
                echo "金额过低暂不提现{$usdt}小于{$min}：\r\n";
            }
        }
    }

    private function transfer($address, $usdt)
    {
        $data = [
            'appid' => trim(env('ZTPAY_APPID')),
            'method' => "transfer",
            'name' => 'USDT_ERC20',
            'from' => $address,
            'to' => env('ZTPAY_CENTER'),
            'amount' => $usdt,
            'gas' => $this->getGas(),
            'gasPrice' => $this->getGasPrice()
        ];
        $data['sign'] = ZtPay::getSign($data);
        $res = ZtPay::http_request($data);
        if ($res['code'] == 0) {
            echo $res['message'] . "金额{$res['data']['amount']},'手续费'{$res['data']['fee_amount']}}\r\n";
            return true;
        } else {
            return false;
        }
    }

    private function transEth($address)
    {
        $from_address = env('ZTPAY_GASCENTER');
        $balance = $this->getBalance($from_address);
        if ($balance['eth'] < ($this->fee * 2)) {
            throwException(new \Exception('手续费矿场余额不足'));
        }

        $data = [
            'appid' => trim(env('ZTPAY_APPID')),
            'method' => "transfer",
            'name' => 'ETH',
            'from' => $from_address,
            'to' => $address,
            'amount' => $this->fee,
            'gas' => $this->getGas(),
            'gasPrice' => $this->getGasPrice()
        ];
        $data['sign'] = ZtPay::getSign($data);
        $res = ZtPay::http_request($data);
        if ($res['code'] == 0) {
            echo $res['message'] . "金额{$res['data']['amount']},'手续费'{$res['data']['fee_amount']}}\r\n";
            return true;
        } else {
            return false;
        }
    }

    private function getBalance($address)
    {
        $data = array(
            'appid' => trim(env('ZTPAY_APPID')),
            'method' => "get_balance",
            'name' => trim('USDT_ERC20'),
            'address' => trim($address),
        );
        $data['sign'] = \App\Utils\ZtPay::getSign($data);
        $res = \App\Utils\ZtPay::http_request($data);
        if ($res) {
            if ($res['code'] == 0) {
                $amount = [
                    'usdt' => $res['data']['USDT'] ?? 0,
                    'eth' => $res['data']['ETH'] ?? 0
                ];
                return $amount;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    protected function getMinumberAmount()
    {
        return 1;
    }

    protected function getGas()
    {
        return 50000;
    }


    protected function getGasPrice()
    {
        $data = [
            'appid' => env('ZTPAY_APPID'),
            'method' => 'get_eth_gasprice'
        ];
        $data['sign'] = ZtPay::getSign($data);
        $res = ZtPay::http_request($data);
        if ($res['code'] == 0) {
            return $res['data']['standard'];
        } else {
            return 0;
        }

    }
}
