<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Setting;
use App\Utils\ZtPay;
use App\WalletAddressLog;
use Illuminate\Http\Request;
use App\{Currency, UsersWallet};
use App\DAO\BlockChain;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateBalance;
use think\Exception;

class WalletController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return view('admin.wallet.index', ['currencies' => $currencies]);
    }

    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $query = UsersWallet::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number', '');
            $account_number != '' && $query->where('account_number', $account_number)->orWhere('phone', $account_number);
        })->where(function ($query) use ($request) {
            $currency = $request->input('currency', -1);
            $address = $request->input('address', '');
            $currency != -1 && $query->where('currency', $currency);
            $address != '' && $query->where('address', $address);
        });
        $query_total = clone $query;
        $total = $query_total->select([
            DB::raw('sum(legal_balance) as legal_balance'),
            DB::raw('sum(lock_legal_balance) as lock_legal_balance'),
            DB::raw('sum(change_balance) as change_balance'),
            DB::raw('sum(lock_change_balance) as lock_change_balance'),
            DB::raw('sum(lever_balance) as lever_balance'),
            DB::raw('sum(lock_lever_balance) as lock_lever_balance'),
            DB::raw('sum(micro_balance) as micro_balance'),
            DB::raw('sum(lock_micro_balance) as lock_micro_balance'),
        ])->first();
        $total = $total->setAppends([]);
        $user_wallet = $query->orderBy('old_balance', 'desc')->paginate($limit);
        $list = $user_wallet->getCollection();
        $list->transform(function ($item, $key) {
            $item->append('account_number');
            return $item;
        });
        $user_wallet->setCollection($list);
        return $this->layuiData($user_wallet, ['total' => $total]);
    }

    public function updateBalance(Request $request)
    {
//        die('dsa');
        $id = $request->input('id', 0);
        $wallet = UsersWallet::find($id);
        if (!$wallet) {
            return $this->error('钱包不存在');
        }

        if (in_array($wallet->currency, ['1', '3'])) {

            $amount = $this->getBalance($wallet['address'], $wallet->currency == 1 ? 'btc' : 'usdt');
            $wallet->old_balance = $amount[$wallet->currency == 1 ? 'btc' : 'usdt'];
            $wallet->update();
        } else {
            return $this->error('只支持USDT和BTC钱包更新');
        }

        //更改为队列方式更新
//       UpdateBalance::dispatch($wallet)->onQueue('update:block:balance');
        return $this->success("读取到链上余额是{$wallet->old_balance}，ETH余额{$amount['eth']}");
    }

    private function getBalance($address, $name = 'usdt')
    {
        $data = array(
            'appid' => trim(env('ZTPAY_APPID')),
            'method' => "get_balance",
            'name' => $name === 'usdt' ? trim('USDT_ERC20') : 'BTC',
            'address' => trim($address),
        );
        $data['sign'] = \App\Utils\ZtPay::getSign($data);
        $res = \App\Utils\ZtPay::http_request($data);

//        var_dump($res);
        if ($res) {
            if ($res['code'] == 0) {
                $amount = [
                    $name => $res['data'][$name === 'usdt' ? trim('USDT') : 'BTC'] ?? 0,
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

    /**
     * 代入手续费
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferPoundage(Request $request)
    {
        $id = $request->input('id', 0);
        $refresh_balance = $request->input('refresh_balance', 0);
        try {
            $wallet = UsersWallet::find($id);
            throw_unless($wallet, new \Exception('钱包不存在'));
            $result = BlockChain::transferPoundage($wallet, $refresh_balance);
            return $this->success('请求成功,交易哈希:' . ($result['txid'] ?? $result['data']['txHex']));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    private function transfer($address, $usdt)
    {
        $data = [
            'appid' => Setting::getValueByKey('ztpay_appid'),
            'method' => "transfer",
            'name' => 'USDT_ERC20',
            'from' => $address,
            'to' => Setting::getValueByKey('usdtAddressCenter'),
            'amount' => $usdt,
            'gas' => $this->getGas(),
            'gasPrice' => $this->getGasPrice()
        ];
        $data['sign'] = ZtPay::getSign($data);
        $res = ZtPay::http_request($data);

//        var_dump($data,$res);

        return $res;
    }

    private function transferBTC($address, $amount)
    {
        $data = [
            'appid' => Setting::getValueByKey('ztpay_appid'),
            'method' => "transfer",
            'name' => 'BTC',
            'from' => $address,
            'to' => Setting::getValueByKey('btcAddressCenter'),
            'amount' => $amount,
        ];
        $data['sign'] = ZtPay::getSign($data);
        $res = ZtPay::http_request($data);

        return $res;

    }


    protected function getGas()
    {
        return intval(Setting::getValueByKey('ztpay_gas', 5000));
    }


    protected function getGasPrice()
    {
        $data = [
            'appid' => Setting::getValueByKey('ztpay_appid'),
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

    /**
     * 修改记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeHistory(Request $request)
    {
        $uid = $request->get('user_id');
        $list = WalletAddressLog::where('user_id',$uid)->where('currency_id',$request->get('currency'))->get();
        return $this->success($list);
    }

    /**
     * 修改钱包地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function edit(Request $request)
    {
        $id = $request->post('id');
        $wallet = UsersWallet::find($id);
        if ($wallet) {
            $old_address = $wallet->address;
            $wallet->address = $request->post('address');


            $admin = session()->get('admin_username');

            $admin_user = Admin::where('username', $admin)->select()->first();
            try {
                DB::transaction(function () use ($wallet, $admin_user, $old_address) {
                    $wallet->update();
                    $res = WalletAddressLog::insertLog([
                        'user_id' => $wallet->user_id,
                        'old_address' => $old_address,
                        'new_address' => $wallet->address,
                        'manager_id' => $admin_user->id,
                        'ctime' => time(),
                        'currency_id' => $wallet->currency
                    ]);
                });
                return $this->success('修改完成');
            } catch (\Exception $exception) {
                return $this->error($exception->getMessage());
            }
        }else{
//            var_dump($request->post());
        }
    }

    public function clearLog(Request $request)
    {
        WalletAddressLog::where('user_id',$request->get('user_id'))->delete();
        return $this->success('清空完成');
    }

    /**
     * 钱包归拢
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function collect(Request $request)
    {
        $id = $request->input('id', 0);

        try {
            $wallet = UsersWallet::find($id);

            if (!in_array($wallet->currency, [1, 3])) {
                throw new \Exception('钱包类型错误');
            }

            $currency_name = $wallet->currency == 1 ? 'btc' : 'usdt';

            $amount = $this->getBalance($wallet['address'], $wallet->currency == 1 ? 'btc' : 'usdt');
            var_dump($amount);
//            var_dump($amount);
            if ($amount[$currency_name] > 0) {

                if ($wallet->currency == 1) {
                    $res = $this->transferBTC($wallet->address, $amount['btc']);
                } else if ($wallet->currency == 3) {
                    $res = $this->transfer($wallet->address, $amount['usdt']);
                } else {
                    $res = false;
                }

                if ($res) {
                    if ($res['code'] == 0) {
                        $this->success($res['message']);
                    } else {
                        $this->error($res['message']);
                    }
                } //                    $this->success('操作完成');
                else {
                    $this->error($res['message']);
                }

            } else {
                throw new \Exception('钱包余额过少，停止归拢');
            }


//            throw_unless($wallet, new \Exception('钱包不存在'));
//            $result = BlockChain::collect($wallet, $refresh_balance);
//            return $this->success('请求成功,HASH:' . $result['txid']);
        } catch (\Exception $th) {
            return $this->error($th->getMessage());
        }
    }
}
