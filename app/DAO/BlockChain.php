<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use App\AccountLog;
use App\Currency;
use App\ChainHash;
use App\LbxHash;
use App\UsersWallet;
use App\Jobs\UpdateBalance;
class BlockChain
{

    public static function getChainBalance($wallet, $chain_currency = '')
    {
        try {
            throw_unless($wallet, new \Exception('钱包不存在'));
            throw_if(empty($wallet->address), new \Exception('钱包地址不存在'));
            $currency = Currency::find($wallet->currency);
            throw_unless($currency, new \Exception('币种不存在'));
            $address = $wallet->address;
            $method = 'GET';
            $chain_currency == '' && $chain_currency = $currency->type;
            switch ($chain_currency) {
                case 'eth':
                    $uri = '/wallet/eth/balance';
                    $params = [
                        'query' => [
                            'address' => $address,
                        ]
                    ];
                    break;
                case 'erc20':
                    $uri = '/wallet/eth/tokenbalance';
                    $params = [
                        'query' => [
                            'address' => $address,
                            'tokenaddress' => $currency->contract_address,
                        ]
                    ];
                    break;
                case 'btc':
                    $uri = '/wallet/btc/balance';
                    $params = [
                        'query' => [
                            'address' => $address,
                        ]
                    ];
                    break;
                case 'usdt':
                    $uri = '/wallet/usdt/balance';
                    $params = [
                        'query' => [
                            'address' => $address,
                        ]
                    ];
                    break;
                default:
                    throw new \Exception('不支持的数字货币');
                    break;
            }
            $http_client = app('LbxChainServer');
            $response = $http_client->request($method, $uri, $params);
            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);
            //echo $uri;
            //var_dump($params);
            //var_dump($result);
            if (!isset($result['code']) || !isset($result['data'])) {
                throw new \Exception('请求接口发生错误');
            }
            if ($result['code'] != 0) {
                throw new \Exception($result['msg'] ?? $result['errorinfo']);
            }
            $balance_data = $result['data'];
            $chain_balance = $balance_data['balance'];
            $lessen = bc_pow(10, $currency->decimal_scale);
            $fact_chain_balance = bc_div($chain_balance, $lessen);
            return $fact_chain_balance;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function updateWalletBalance($wallet, $no_balance_continue = false)
    {
        try {
            $fact_chain_balance = self::getChainBalance($wallet);
            try {
                DB::beginTransaction();
                
                $wallet->refresh();
                //比较现链上余额是否比原链上余额要大
                $compare_result = bc_comp($fact_chain_balance, $wallet->old_balance);
                if ($compare_result  > 0) {
                    $diff_balance = bc_sub($fact_chain_balance, $wallet->old_balance);
                    $wallet->old_balance = $fact_chain_balance; //更新链上余额
                    $save_result = $wallet->save();
                    if (!$save_result) {
                        throw new \Exception('更新链上余额失败');
                    }
                    $change_result = change_wallet_balance($wallet, 2, $diff_balance, AccountLog::ETH_EXCHANGE, '链上充币增加');
                    if ($change_result !== true) {
                        throw new \Exception($change_result);
                    }
                } elseif ($compare_result == 0) {
                    // throw new \Exception(
                    //     '用户id:' . $wallet->user_id . ',币种:' . $wallet->currencyCoin->name
                    //     . '(' . $wallet->currencyCoin->type . '):链上余额无增加'
                    // );
                    if ($no_balance_continue) {
                        UpdateBalance::dispatch($wallet, false)
                            ->onQueue('update:block:balance')
                            ->delay(Carbon::now()->addMinutes(5));
                    }
                } else {

                    if ($no_balance_continue) {
                        UpdateBalance::dispatch($wallet, false)
                            ->onQueue('update:block:balance')
                            ->delay(Carbon::now()->addMinutes(5));
                    }
                    throw new \Exception(
                        '用户id:' . $wallet->user_id . ',币种:' . $wallet->currencyCoin->name
                        . '(' . $wallet->currencyCoin->type . '):链上余额小于系统链上余额'
                    );
                }
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

     /**
     * 链上转账
     *
     * @param string $currency_name 钱包本身对应的币种名称
     * @param string $chain_currency 要转的链上币种类型,例如用usdt的钱包地址不仅可以转USDT还可以转BTC,用erc20的钱包地址不仅可以转ERC20代币还可以转ETH
     * @param string $to_address 转入地址
     * @param float $transfer_qty 转账数量
     * @param string $from_address 转出地址
     * @param string $from_private_key 转出私钥
     * @param integer $type 转账类型 1 归拢，2 打入手续费，3 提币
     * @param float $fee 链上手续费
     * @param string $verificationcode 验证码
     * @return string
     * @throws \Exception
     */
    public static function transfer($currency_name, $chain_currency, $to_address, $transfer_qty, $from_address, $from_private_key, $type, $fee = 0, $verificationcode = '')
    {
        try {
            $currency = Currency::where('name', $currency_name)->first();
            $chain_currency = Currency::where('name', $chain_currency)->first();
            if (!$currency) {
                throw new \Exception('货币不存在');
            }
            if (!in_array($currency->type, ['eth', 'erc20', 'usdt', 'btc'])) {
                throw new \Exception('货币类型不支持');
            }
            if (
                empty($to_address) 
                || empty($transfer_qty)
                || bc_comp($transfer_qty, 0) <= 0
                || empty($from_address)
                || empty($from_private_key)
            ) {
                throw new \Exception('参数不完整或不合法');
            }
            $origin_transfer_qty = $transfer_qty;
            $decimal_scale = $chain_currency->decimal_scale ?? 0; //调整为按链上通道的小数位数，解决代币和主链小数位数不一致的问题
            $lessen = bc_pow(10, $decimal_scale);
            $transfer_qty = bc_mul($transfer_qty, $lessen, 0); //转账数量转换为区块链上的单位
            $fee = bc_mul($currency->chain_fee, $lessen, 0); //手续费转换为区域链上的单位
            $http_client = app('LbxChainServer');
            $method = 'POST';
            $result = [];
            $params = [
                'multipart' => [
                    [
                        'name' => 'type',
                        'contents' => $type,
                    ],
                    [
                        'name' => 'fromaddress',
                        'contents' => $from_address,
                    ],
                    [
                        'name' => 'privkey',
                        'contents' => $from_private_key,
                    ],
                    [
                        'name' => 'toaddress',
                        'contents' => $to_address,
                    ],
                    [
                        'name' => 'amount',
                        'contents' => $transfer_qty,
                    ],
                    [
                        'name' => 'tokenaddress',
                        'contents' => $currency->contract_address ?? '',
                    ],
                    [
                        'name' => 'fee',
                        'contents' => $fee,
                    ],
                    [
                        'name' => 'verificationcode',
                        'contents' => $verificationcode,
                    ],
                ]
            ];
            switch ($chain_currency->type) {
                case 'erc20':
                    $uri = '/v3/wallet/eth/tokensendto';
                    break;
                case 'eth':
                    $uri = '/v3/wallet/eth/sendto';
                    break;
                case 'btc':
                    $uri = '/v3/wallet/btc/sendto';
                    break; 
                case 'usdt':
                    $uri = '/v3/wallet/usdt/sendto';
                    break;
                default:
                    throw new \Exception('暂不支持' . $chain_currency->type . '币种');
                    break;
            }

            $response = $http_client->request($method, $uri, $params);
            $result = json_decode($response->getBody()->getContents(), true); 
            isset($result['txid']) || $result['txid'] = $result['data']['txHex'] ?? ($result['data']['txid'] ?? '');
            if (isset($result['code']) && $result['code'] == 0) {
                $chain_hash = [
                    'code' => strtoupper($currency->type),
                    'txid' => $result['txid'],
                    'amount' => $origin_transfer_qty,
                    'sender' => $from_address,
                    'recipient' => $to_address,
                ];
                ChainHash::unguarded(function () use ($chain_hash) {
                    return ChainHash::create($chain_hash);
                });
            } else {
                throw new \Exception($result['msg'] ?? var_export($result, true));
            }
            //dump($params);
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * 打入手续费
     *
     * @param UsersWallet $wallet
     * @param boolean $refresh_balance
     * @return void
     */
    public static function transferPoundage(UsersWallet $wallet, $refresh_balance = false)
    {
        try {
            //检测当前是否已有手续费打入的交易hash
            //是否先刷新链上余额
            if ($refresh_balance) {
                $wallet->refresh();
                self::updateWalletBalance($wallet);
            }
            $wallet->refresh();
            if (bc_comp($wallet->old_balance, 0) <= 0) {
                throw new \Exception('用户链上余额为0,无须打入手续费');
            }
            $fee_currency = $currency = $wallet->currencyCoin;
            $currency_type = $currency->type;
            $fee_name = '';
            if ($currency_type == 'eth' || $currency_type == 'btc' || $currency_type == 'eos' || $currency_type == 'xrp') {
                throw new \Exception($wallet->currencyCoin->name . '币种无需额外打入归拢手续费');
            } elseif ($currency_type == 'erc20') {
                //从总账号往钱包打入eth
                $transfer_qty = $fee_currency->chain_fee;
                $from_address = $fee_currency->total_account;
                $from_private_key = $fee_currency->origin_key;
                $fee_name = 'eth';
            } elseif ($currency_type == 'usdt') {
                //从总账号往钱包打入btc
                $transfer_qty = bc_add($fee_currency->chain_fee, '0.00000546');
                $from_address = $fee_currency->total_account;
                $from_private_key = $fee_currency->origin_key;
                $fee_name = 'btc';
            } else {
                throw new \Exception('不支持的数字货币');
            }
            if (empty($from_address) || empty($from_private_key)) {
                throw new \Exception($fee_name . '币种总账号信息未设置');
            }
            $fee_balance = self::getChainBalance($wallet, $fee_name);
            //当链上手续费余额大于需要转入的手续费时,提示无须再打入手续费
            if (bc_comp($fee_balance, $transfer_qty) >= 0) {
                throw new \Exception('钱包内' . $fee_name . '余额充足,无须打入');
            } else {
                //当有余额时看相差多少,只打入相差的部分
                $transfer_qty = bc_sub($transfer_qty, bc_comp($fee_balance, 0) >= 0 ? $fee_balance : 0);
            }

            $params  = [
                'currency_type' => $currency_type,
                'fee_name' => $fee_name,
                'to_address' => $wallet->address,
                'transfer_qty' => $transfer_qty,
                'from_address' => $from_address, 
                'from_private_key' => $from_private_key,
                'type' => 2,
            ];
            $query_str = md5(http_build_query($params));
            if (Cache::has($query_str)) {
                throw new \Exception('当前链上已有手续费交易正在确认,请勿重复打入手续费!交易哈希:' . Cache::get($query_str));
            }

            // 从当日哈希表中检测是否已有未确认的打入手续费的交易
            $fee_transaction = LbxHash::where('wallet_id', $wallet->id)
                ->where('created_at', '>=', Carbon::today())
                ->where('type', 2)
                ->where('status', 0)
                ->first();
            if ($fee_transaction) {
                throw new \Exception('当前链上已有手续费交易正在确认,请勿重复打入手续费!交易哈希:' . $fee_transaction->txid);
            }

            DB::beginTransaction();
            $result = self::transfer($currency_type, $fee_name, $wallet->address, $transfer_qty, $from_address, $from_private_key, 2);
            if ($result['code'] == 0) {
                Cache::put($query_str, $result['txid'], 20);
                //记录链上哈希信息
                $lbx_hash_data = [
                    'wallet_id' => $wallet->id,
                    'txid' => $result['txid'],
                    'type' => 2, //打入手续费
                    'amount' => $transfer_qty,
                    'status' => 0,
                ];
                LbxHash::unguarded(function () use ($lbx_hash_data) {
                    return LbxHash::create($lbx_hash_data);
                });
            }
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 钱包链上余额归拢到总账号
     *
     * @param \App\UsersWallet $wallet 要归拢的钱包
     * @param bool $refresh_balance 是否从链上刷新余额
     * @return string
     * @throws \Exception
     */
    public static function collect(UsersWallet $wallet, $refresh_balance = false)
    {
        $currency = $wallet->currencyCoin;
        if (!$currency) {
            throw new \Exception('对应币种不存在');
        }
        $from_address = $wallet->address;
        $from_private_key = $wallet->private;
        
        //$to_address = $currency->total_account;
        $to_address = $currency->collect_account;
        $contract_address = $currency->contract_address;
        $currency_type = $currency->type;
        if (empty($to_address)) {
            throw new \Exception('归拢地址未设置');
        }
        if ($currency_type == 'erc20' && empty($contract_address)) {
            throw new \Exception('合约地址未设置');
        }
        // 根据币种手续费计算
        $base_transfer_use_qty = 0; //除手续费消耗主链的数量
        switch ($currency_type) {
            case 'eth':
                $fee_currency_name = 'eth';
                $transfer_fee = $currency->chain_fee ?? 0.001;
                break;
            case 'btc':
                $fee_currency_name = 'btc';
                $transfer_fee = $currency->chain_fee ?? 0.00006;
                break;
            case 'erc20':
                $fee_currency_name = 'eth';
                $transfer_fee = $currency->chain_fee ?? 0.001;
                break;
            case 'usdt':
                $fee_currency_name = 'btc';
                $base_transfer_use_qty = 0.00000546;
                $transfer_fee = $currency->chain_fee ?? 0.00006;
                break;
            default:
                $fee_currency_name = '';
                $transfer_fee = 0;
        }       
        // 查询上次归拢是否完成
        $lbx_hash = LbxHash::where('status', 0)
            ->where('type', 0)
            ->where('wallet_id', $wallet->id)
            ->first();
        if ($lbx_hash) {
            throw new \Exception('当前有归拢操作未完成');
        }
        // 是否先刷新链上余额
        if ($refresh_balance) {
            self::updateWalletBalance($wallet);
        }
        $wallet->refresh();
        if ($currency_type == 'erc20' || $currency_type == 'usdt') {
            //检测手续费是否充足:erc20扣eth, usdt扣btc
            $fee_balance = self::getChainBalance($wallet, $fee_currency_name);
            $base_total_use_qty = bc_add($base_transfer_use_qty, $transfer_fee); //手续费+链上交易额外消耗,例如USDT要额外消耗0.00000546BTC
            
            if (bc_comp($fee_balance, $base_total_use_qty) < 0) {
                throw new \Exception('钱包内手续费可用余额(' . $fee_balance . ')不足,不能归拢');
            }
            $transfer_qty = $wallet->old_balance; //代币有多少归多少
        } else {
            $transfer_qty = bc_sub($wallet->old_balance, $transfer_fee); //主链归拢减去手续费
        }
        //如果链上余额为空或者只有手续费(ETH、BTC)就没必要做归拢
        if (bc_comp($transfer_qty, 0) <= 0) {
            throw new \Exception('余额为空或手续费不足,不能归拢');
        }

        try {
            DB::beginTransaction();
            $result = self::transfer($currency->name, $currency->type, $to_address, $transfer_qty, $from_address, $from_private_key, 1);
            if (!isset($result['code']) || $result['code'] != 0) {
                throw new \Exception(var_export($result, true));
            }
            //记录链上哈希信息
            $lbx_hash_data = [
                'wallet_id' => $wallet->id,
                'txid' => $result['txid'],
                'type' => 0,
                'amount' => $transfer_qty,
                'status' => 0,
            ];
            LbxHash::unguarded(function () use ($lbx_hash_data) {
                return LbxHash::create($lbx_hash_data);
            });
            $wallet->refresh();
            $wallet->txid = $result['txid'];
            $wallet->gl_time = time();
            $wallet->save();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
