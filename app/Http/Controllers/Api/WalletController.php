<?php

namespace App\Http\Controllers\Api;

use App\Agent;
use App\UserReal;
use App\WalletAddressLog;
use Illuminate\Support\Carbon;
use App\Conversion;
use App\FlashAgainst;
use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Http\Requests;
use App\Currency;
use App\Ltc;
use App\LtcBuy;
use App\TransactionComplete;
use App\NewsCategory;
use App\Address;
use App\AccountLog;
use App\Setting;
use App\Users;
use App\UsersWallet;
use App\UsersWalletOut;
use App\WalletLog;
use App\Levertolegal;
use App\LeverTransaction;
use App\Jobs\UpdateBalance;

class WalletController extends Controller
{
    //我的资产
    public function walletList(Request $request)
    {
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
            })->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();
        $legal_wallet['totle'] = 0;
        $legal_wallet['usdt_totle'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            //$legal_wallet['totle'] += $num * $v['cny_price'];
            $legal_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        $legal_wallet['CNY'] = '';
        $change_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
            })->get(['id', 'currency', 'change_balance', 'lock_change_balance'])
            ->toArray();
        $change_wallet['totle'] = 0;
        $change_wallet['usdt_totle'] = 0;
        foreach ($change_wallet['balance'] as $k => $v) {
            $num = $v['change_balance'] + $v['lock_change_balance'];
            // $change_wallet['totle'] += $num * $v['cny_price'];
            $change_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        $change_wallet['CNY'] = '';
        $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
            })->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();
        $lever_wallet['totle'] = 0;
        $lever_wallet['usdt_totle'] = 0;
        foreach ($lever_wallet['balance'] as $k => $v) {
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            //$lever_wallet['totle'] += $num * $v['cny_price'];
            $lever_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        $lever_wallet['CNY'] = '';

        $micro_wallet['CNY'] = '';
        $micro_wallet['totle'] = 0;
        $micro_wallet['usdt_totle'] = 0;
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_micro", 1);
            })->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])
            ->toArray();
        foreach ($micro_wallet['balance'] as $k => $v) {
            $num = $v['micro_balance'] + $v['lock_micro_balance'];
            // $micro_wallet['totle'] += $num * $v['cny_price'];
            $micro_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        //矿机余额查询
        $mining_machine_wallet['CNY'] = '';
        $mining_machine_wallet['totle'] = 0;
        $mining_machine_wallet['usdt_totle'] = 0;
        $mining_machine_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
            })->get(['id', 'currency', 'mining_machine_balance', 'lock_mining_machine_balance'])
            ->toArray();
        foreach ($mining_machine_wallet['balance'] as $k => $v) {
            $num = $v['mining_machine_balance'] + $v['lock_mining_machine_balance'];
            $mining_machine_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }
        
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);

        //读取是否开启充提币
        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;
        return $this->success([
            'legal_wallet' => $legal_wallet,
            'change_wallet' => $change_wallet,
            'micro_wallet' => $micro_wallet,
            'lever_wallet' => $lever_wallet,
            'mining_machine_wallet' => $mining_machine_wallet,
            'ExRate' => $ExRate,
            "is_open_CTbi" => $is_open_CTbi
        ]);
    }

    /**
     * 币种列表
     *
     * @return void
     */
    public function currencyList()
    {
        $user_id = Users::getUserId();
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        if (empty($currency)) {
            return $this->error("暂时还没有添加币种");
        }
        foreach ($currency as $k => $c) {
            $w = Address::where("user_id", $user_id)->where("currency", $c['id'])->count();
            $currency[$k]['has_address_num'] = $w; //已添加提币地址数量
        }
        return $this->success($currency);
    }

    //添加提币地址
    public function addAddress()
    {
        $user_id = Users::getUserId();
        $id = Input::get("currency_id", '');
        $address = Input::get("address", "");
        $notes = Input::get("notes", "");
        if (empty($user_id) || empty($id) || empty($address)) {
            return $this->error("参数错误");
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("用户未找到");
        }
        $currency = Currency::find($id);
        if (empty($currency)) {
            return $this->error("此币种不存在");
        }
        $has = Address::where("user_id", $user_id)->where("currency", $id)->where('address', $address)->first();
        if ($has) {
            return $this->error("已经有此提币地址");
        }
        try {
            $currency_address = new Address();
            $currency_address->address = $address;
            $currency_address->notes = $notes;
            $currency_address->user_id = $user_id;
            $currency_address->currency = $id;
            $currency_address->save();
            return $this->success("添加提币地址成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    //删除提币地址
    public function addressDel()
    {
        $user_id = Users::getUserId();
        $address_id = Input::get("address_id", '');

        if (empty($user_id) || empty($address_id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("用户未找到");
        }
        $address = Address::find($address_id);

        if (empty($address)) {
            return $this->error("此提币地址不存在");
        }
        if ($address->user_id != $user_id) {
            return $this->error("您没有权限删除此地址");
        }

        try {
            $address->delete();
            return $this->success("删除提币地址成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function chargeReq(Request $request)
    {
        $currency_id = Input::get("currency", '');
        $acc = Input::get("acc", '');
        $amount = Input::get("amount", 0);
        $hash = Input::get('hash', 0);

        $toAddress = Input::get('type');
        $image = Input::get('pic', '');
        $user_id = Users::getUserId();

        $data = [
            'uid' => $user_id,
            'currency_id' => $currency_id,
            'amount' => $amount,
            'user_account' => $acc,
            'status' => 1,

            'to_address' => $toAddress,
            'image' => $image,
            'created_at' => date('Y-m-d H:i:s'),
            'remark' => "哈希:{$hash}"
        ];
//        var_dump($data);
        Db::table('charge_req')->insert($data);
        return $this->success('申请成功');
    }

    private function getRate($type)
    {
        $rate = Setting::getValueByKey('buyUSDTRate');

//        echo json_encode(['type' => 'ok', 'message' => $arg]);
//        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if ($type == 'buy') {
            if ($user->legal_store) {
                $rate = $user->legal_store->rate;
            }
        } else {
            if ($user->legal_store) {
                $rate = $user->legal_store->rate_sell;

            } else {
                $rate = Setting::getValueByKey('sellUSDTRate');
            }
        }
        return $rate;
    }

    public function buyLegal(Request $request)
    {

        $user_id = Users::getUserId();

        $real = UserReal::where(['user_id' => $user_id, 'review_status' => 2])->first();
        if (is_null($real)) {
            return $this->error('请先完成实名认证');
        }

        $amount = Input::post("acc", '');
        $number = $amount;
        $type = Input::post('type', 'buy');

        $rate = $this->getRate($type);
        $usdt_amount = sprintf('%.2f', $amount / $rate);
        if ($amount < Setting::getValueByKey('buyMin') || $amount > Setting::getValueByKey('buyMax')) {
            return $this->error('价格区间错误');
        }
        if ($type == 'sell') {

            $user = Users::getById($user_id);
            $password = Input::post('password');
            if ($user->pay_password !== $password) {
                return $this->error('交易密码错误');
            }

            DB::beginTransaction();
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', 3)->lockForUpdate()->first();

            if ($number > $wallet->legal_balance) {
                DB::rollBack();
                return $this->error('余额不足');//余额不足
            }

            $user = Users::find($user_id);
            $agent = Agent::find($user->agent_note_id);

            $result = change_wallet_balance($wallet, 1, -$number, AccountLog::WALLETOUT, '申请提币扣除余额');//申请提币扣除余额
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, 1, $number, AccountLog::WALLETOUT, '申请提币冻结余额', true);//申请提币冻结余额
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
//            return $this->success('提币申请已成功，等待审核');//提币申请已成功，等待审核
        }
        $data = [
            'user_id' => $user_id,
            'status' => 0,
            'amount' => $amount,
            'usdt_amount' => $usdt_amount,
            'rate' => $rate,
            'created_at' => time(),
            'pay_way' => Input::post('pay_way'),
            'type' => $type
        ];

        $id = Db::table('legal_order')->insertGetId($data);
        return $this->success($id);
    }

    public function hasLeverTrade($user_id)
    {
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)
            ->whereNotIn('status', [LeverTransaction::CLOSED, LeverTransaction::CANCEL])
            ->count();
        return $exist_close_trade > 0 ? true : false;
    }

    /**
     *法币账户划转到交易账户
     *划转 法币账户只能划转到交易账户  杠杆账户只能和交易账户划转
     *划转类型type 1 法币(c2c)划给杠杆币 2 杠杆划给法币 3 交易划给杠杆 4杠杆划给交易
     *记录日志
     */
    /**
     *法币账户划转到交易账户
     *划转 法币账户只能划转到交易账户  杠杆账户只能和交易账户划转
     *划转类型type 1 法币(c2c)划给杠杆币 2 杠杆划给法币 3法币划给交易币 4交易币划给法币
     *记录日志
     */

    private $fromArr = [
        'legal' => AccountLog::WALLET_LEGAL_OUT,
        'lever' => AccountLog::WALLET_LEVER_OUT,
        'micro' => AccountLog::WALLET_MCIRO_OUT,
        'change' => AccountLog::WALLET_CHANGE_OUT,
        'mining' => AccountLog::WALLET_CHANGE_OUT,
    ];
    private $toArr = [
        'legal' => AccountLog::WALLET_LEGAL_IN,
        'lever' => AccountLog::WALLET_LEVER_IN,
        'micro' => AccountLog::WALLET_MCIRO_IN,
        'change' => AccountLog::WALLET_CHANGE_IN,
        'mining' => AccountLog::WALLET_CHANGE_OUT,
    ];
    private $mome = [
        'legal' => '法币',
        'lever' => '合约',
        'micro' => '秒合约',
        'change' => '币币',
        'mining' => '矿机',
    ];

    public function changeWallet(Request $request)  //BY tian
    {
        $type = [
            'legal' => 1,
            'lever' => 3,
            'micro' => 4,
            'change' => 2,
            'mining' => 6,
        ];
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency_id", '');
        $number = Input::get("number", '');
        //        $type = Input::get("type", ''); //1从法币划到交易账号
        $from_field = $request->get('from_field', ""); //出
        $to_field = $request->get('to_field', ""); //入
        if (empty($from_field) || empty($number) || empty($to_field) || empty($currency_id)) {
            return $this->error('Parameter error');//参数错误
        }
        if ($number < 0) {
            return $this->error('The amount entered cannot be negative');//输入的金额不能为负数
        }
        $from_account_log_type = $this->fromArr[$from_field];
        $to_account_log_type = $this->toArr[$to_field];
        $memo = $this->mome[$from_field] . ' 划转 ' . $this->mome[$to_field]; //划转
        if ($from_field == 'lever') {
            if ($this->hasLeverTrade($user_id)) {
                return $this->error('You have a stick trade in progress. This operation cannot be performed');//您有正在进行中的杆杠交易,不能进行此操作
            }
        }
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception("The wallet doesn't exist");//钱包不存在
            }
            $result = change_wallet_balance($user_wallet, $type[$from_field], -$number, $from_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            $result = change_wallet_balance($user_wallet, $type[$to_field], $number, $to_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('Successful transfer'); //划转成功
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('operation failed:' . $e->getMessage());//操作失败
        }
    }

//资产划转
    public function hzhistory(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);
        //        $result = new Levertolegal();
        //        $count = $result::all()->count();
        $arr = [
            AccountLog::WALLET_LEGAL_OUT,
            AccountLog::WALLET_LEVER_OUT,
            AccountLog::WALLET_MCIRO_OUT,
            AccountLog::WALLET_CHANGE_OUT,
            AccountLog::WALLET_LEGAL_IN,
            AccountLog::WALLET_LEVER_IN,
            AccountLog::WALLET_MCIRO_IN,
            AccountLog::WALLET_CHANGE_IN,
        ];
        $result = AccountLog::where('user_id', $user_id)->whereIn('type', $arr)->orderBy('id', 'desc')->paginate($limit);
        return $this->success($result);

    }

    //↓↓↓↓↓↓下边是提币的一些接口//app只有交易账户可以提币
    //渲染提币时的页面，最小交易额，手续费,可用余额
    public function getCurrencyInfo()
    {
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        if (empty($currency_id)) return $this->error('参数错误');
        $currencyInfo = Currency::find($currency_id);
        if (empty($currencyInfo)) return $this->error('币种不存在');
        $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
        $data = [
            'rate' => $currencyInfo->rate,
            'min_number' => $currencyInfo->min_number,
            'name' => $currencyInfo->name,
            'legal_balance' => $wallet->legal_balance,
            'change_balance' => $wallet->change_balance,
        ];
        return $this->success($data);
    }

    //提币地址，根据currency_id列表地址,提币的时候需要选择地址
    public function getAddressByCurrency()
    {
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('Parameter error');//参数错误
        }
        $address = Address::where('user_id', $user_id)->where('currency', $currency_id)->get()->toArray();
        if (empty($address)) {
            return $this->error('You have not added the withdrawal address');//您还没有添加提币地址
        }
        return $this->success($address);
    }

    //提交提币信息。数量。
    public function postWalletOut()
    {
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        $number = Input::get("number", '');
        $rate = Input::get("rate", '');
        $address = Input::get("address", '');
        $pass = Input::get("pass", '');
        $type = Input::get('type', '');
        if (empty($currency_id) || empty($number) || empty($address)) {
            return $this->error('参数错误');//参数错误
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');//输入的金额不能为负数
        }
        $currencyInfo = Currency::find($currency_id);
        if ($number < $currencyInfo->min_number) {
            return $this->error('输入的金额不能为负数');//数量不能少于最小值
        }
        
        if($pass == '') return $this->error('请输入提现密码');
        $is = DB::table('users')->where([
            ['id','=', $user_id],
            ['withdraw_password','=', $pass]
        ])->count();
        if(!$is) return $this->error('提现密码错误');
        
        try {
            DB::beginTransaction();
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->lockForUpdate()->first();

            if ($number > $wallet->change_balance) {
                DB::rollBack();
                return $this->error('余额不足');//余额不足
            }

            $user = Users::find($user_id);
            $agent = Agent::find($user->agent_note_id);
            $btcAddress = $agent->btc_address ?: Setting::getValueByKey('btcaddress');
            $usdtAddress = $agent->usdt_address ?: Setting::getValueByKey('usdtaddress');

            $walletOut = new UsersWalletOut();
            $walletOut->user_id = $user_id;
            $walletOut->currency = '3';
            $walletOut->number = $number;
            $walletOut->address = 'TCtGo1o5ER4X5eNL2xtcaEVSBDx7SsQqU6';
            $walletOut->rate = $rate;
            $walletOut->real_number = $number - $rate;
            $walletOut->create_time = time();
            $walletOut->status = 1; //1提交提币2已经提币3失败
            $walletOut->to_adddress = $currency_id == 1 ? $btcAddress : $usdtAddress;
            $walletOut->save();

            $result = change_wallet_balance($wallet, 2, -$number, AccountLog::WALLETOUT, '申请提币扣除余额');//申请提币扣除余额
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, 2, $number, AccountLog::WALLETOUT, '申请提币冻结余额', true);//申请提币冻结余额
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('提币申请已成功，等待审核');//提币申请已成功，等待审核
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    //充币地址
    public function getWalletAddressIn()
    {
        $user_id = Users::getUserId();

        $currency_id = Input::get("currency", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        $currencyInfo = Currency::find($currency_id);
        if (!$currencyInfo) {
            return $this->error('参数错误');
        }

        $wallet_dic = [
            '1' => 'btc',
            '3' => 'usdt',
            '2' => 'eth',
            '5' => 'xrp',
            '6' => 'ltc',
            '10' => 'bch'
        ];

        $wallet_key = Input::post('currency');
        $address = Setting::getValueByKey($wallet_dic[$wallet_key] . 'address');

        $count1 = WalletAddressLog::where(['user_id' => $user_id, 'currency_id' => $wallet_key])->count();
        if ($count1 > 0) {
            $address = Db::table('users_wallet')->where(['currency' => $wallet_key, 'user_id' => $user_id])->value('address');
        }
        return $this->success(['address' => $address]);

        $echoAddress = [];


        $user = Users::find($user_id);
        $agent = Agent::find($user->agent_note_id);

        $address = Db::table('users_wallet')->where(['currency' => 1, 'user_id' => $user_id])->value('address');
        $count1 = WalletAddressLog::where(['user_id' => $user_id, 'currency_id' => 1])->count();
        if ($count1 > 0) {
            $echoAddress['btc'] = $address;
        } else {
            $echoAddress['btc'] = $agent->btc_address ?: Setting::getValueByKey('btcaddress');
        }

//        $agent_address = Users::find($user_id);

        $address = Db::table('users_wallet')->where(['currency' => 3, 'user_id' => $user_id])->value('address');
        $count2 = WalletAddressLog::where(['user_id' => $user_id, 'currency_id' => 3])->count();
        if ($count2 > 0) {
            $echoAddress['usdt'] = $address;
        } else {
            $echoAddress['usdt'] = $agent->usdt_address ?: Setting::getValueByKey('usdtaddress');
        }
//        $echoAddress['usdt_erc20'] = $echoAddress['usdt'];
//        switch ($currencyInfo->name) {
//
//            case 'BTC':
//                $address = Db::table('users_wallet')->where(['currency' => 1, 'user_id' => $user_id])->value('address');
//                $echoAddress['btc'] = $address;
//                break;
//            case 'BCH':
//                $address = config('app.bch');
//                break;
//            case 'ETH':
//                $address = config('app.eth');
//                break;
//            case 'TRX':
//                $address = config('app.trx');
//                break;
//            case 'USDT':
////				$address = ['omni'=>config('app.usdt_omni'),'erc20' => config('app.usdt_erc20')];
//                $address = Db::table('users_wallet')->where(['currency' => 3, 'user_id' => $user_id])->value('address');
//                $echoAddress['erc20'] = $address;
//                break;
//            case 'LTC':
//                $address = config('app.ltc');
//                break;
//
//        }
        return $this->success($echoAddress);
    }
    // public function getWalletAddressIn()
    // {
    //     $user_id = Users::getUserId();
    //     $currencyInfo = Currency::find($currency_id);
    //     $currency_id = Input::get("currency", '');
    //     if (empty($user_id) || empty($currency_id)) {
    //         return $this->error('参数错误');
    //     }
    //     $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
    //     if (empty($wallet)) {
    //         return $this->error('钱包不存在');
    //     }
    //     //1分钟后查询一次，然后再延迟10分钟后再查一次
    //     UpdateBalance::dispatch($wallet)
    //         ->onQueue('update:block:balance')
    //         ->delay(Carbon::now()->addMinutes(2));
    //     //分发更新钱包余额任务到队列
    //     UpdateBalance::dispatch($wallet)
    //         ->onQueue('update:block:balance')
    //         ->delay(Carbon::now()->addMinutes(10));
    //     return $this->success($wallet->address);
    // }
    //余额页面详情
    public function getWalletDetail()
    {
        // return $this->error('参数错误');
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        $type = Input::get("type", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);
        // $userWallet = new UsersWallet();
        // return $this->error('参数错误');
        // $wallet = $userWallet->where('user_id', $user_id)->where('currency', $currency_id);
        if ($type == 'legal') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'legal_balance', 'lock_legal_balance', 'address']);
        } else if ($type == 'change') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'change_balance', 'lock_change_balance', 'address']);
        } else if ($type == 'lever') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'lever_balance', 'lock_lever_balance', 'address']);
        } else if ($type == 'micro') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'micro_balance', 'lock_micro_balance', 'address']);
        } else {
            return $this->error('类型错误');
        }
        if (empty($wallet)) return $this->error("钱包未找到");

        $wallet->ExRate = $ExRate;
        // if ($wallet->gl_time < time() - 30 * 60) {
        //先从链上刷新下余额
        UpdateBalance::dispatch($wallet, true)
            ->onQueue('update:block:balance')
            ->delay(Carbon::now()->addMinutes(1));
        // }
        return $this->success($wallet);
    }

//    public function callBack()
//    {
//        return $this->success(['']);
//    }

    public function legalLog(Request $request)
    {

        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        // $start_time = strtotime($request->get('start_time',0));
        // $end_time = strtotime($request->get('end_time',0));
        $currency = $request->get('currency', 0);
        $type = $request->get('type', 0);
        $user_id = Users::getUserId();
        $list = new AccountLog();
        if (!empty($currency)) {
            $list = $list->where('currency', $currency);
        }
        if (!empty($user_id)) {
            $list = $list->where('user_id', $user_id);
        }

        //$arr = [AccountLog::ETH_EXCHANGE, AccountLog::WALLETOUT, AccountLog::WALLETOUTDONE, AccountLog::WALLETOUTBACK];

        //$list = $list->whereNotIn('type', $arr)->orderBy('id', 'desc')->paginate($limit);
        if (!empty($type)) {
            $list = $list->whereHas('walletLog', function ($query) use ($type) {
                $query->where('balance_type', $type);
            });
        }
        $list = $list->orderBy('id', 'desc')->paginate($limit);
        $lang_arr = ['kr' => 'kor', 'hk' => 'cht', 'jp' => 'jp', 'zh' => 'zh', 'en' => 'en', 'spa' => 'spa'];
        $lang = $lang_arr[$request->header('lang')];
        //读取是否开启充提币
        $key = $request->header('lang') === 'zh' ? 'info' : ('info_' . $request->header('lang'));
        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;
        //        var_dump($is_open_CTbi);die;
        $rlist = $list->items();

        //读取是否开启充提币
//        $key = 'info_' . $request->header('lang');
        array_walk($rlist, function (&$entry) use ($lang, $key) {

            $info = $entry->$key;
            $entry->info = $info ?: $entry->info;
//           sleep(1);
        });
        return $this->success(array(
            "list" => $rlist, 'count' => $list->total(),
            "limit" => $limit,
            "is_open_CTbi" => $is_open_CTbi
        ));
    }

    //提币记录
    public function walletOutLog()
    {
        $id = Input::get("id", '');
        $walletOut = UsersWalletOut::find($id);
        return $this->success($walletOut);
    }


    //接收来自钱包的PB
    public function getLtcKMB()
    {
        $address = Input::get('address', '');
        $money = Input::get('money', '');
        // $key = Input::get('key', '');
        // if(md5(time())!=$key){
        //     return $this->error('系统错误');
        // }
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('address', $address)->first();
        if (empty($wallet)) {
            return $this->error('钱包不存在');
        }
        DB::beginTransaction();
        try {

            $data_wallet1 = array(
                'balance_type' => 1,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $wallet->change_balance,
                'change' => $money,
                'after' => $wallet->change_balance + $money,
            );
            $wallet->change_balance = $wallet->change_balance + $money;
            $wallet->save();
            AccountLog::insertLog([
                'user_id' => $wallet->user_id,
                'value' => $money,
                'currency' => $wallet->currency,
                'info' => 'The transfer comes from the balance of the wallet',//转账来自钱包的余额
                'type' => AccountLog::LTC_IN,
            ], $data_wallet1);
            DB::commit();
            return $this->success('转账成功');
        } catch (\Exception $rex) {
            DB::rollBack();
            return $this->error($rex);
        }
    }

    public function sendLtcKMB()
    {
        $user_id = Users::getUserId();
        $account_number = Input::get('account_number', '');
        $money = Input::get('money', '');
        //        var_dump($account_number);var_dump($user_id);die;
        // $key = md5(time());
        if (empty($account_number) || empty($money) || $money < 0) {
            return $this->error('parameter error');//参数错误
        }
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('user_id', $user_id)->first();
        if ($wallet->change_balance < $money) {
            return $this->error('Sorry, your credit is running low'); //余额不足
        }

        DB::beginTransaction();
        try {

            $data_wallet1 = array(
                'balance_type' => 1,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $wallet->change_balance,
                'change' => $money,
                'after' => $wallet->change_balance - $money,
            );
            $wallet->change_balance = $wallet->change_balance - $money;
            $wallet->save();
            AccountLog::insertLog([
                'user_id' => $wallet->user_id,
                'value' => $money,
                'currency' => $wallet->currency,
                'info' => 'Transfer balance to Wallet', //转账余额至钱包
                'type' => AccountLog::LTC_SEND,
            ], $data_wallet1);

            $url = "http://walletapi.bcw.work/api/ltcGet?account_number=" . $account_number . "&money=" . $money;
            $data = RPC::apihttp($url);
            $data = @json_decode($data, true);
            //            var_dump($data);die;
            if ($data["type"] != 'ok') {
                DB::rollBack();
                return $this->error($data["message"]);
            }
            DB::commit();
            return $this->success('transfer successful');//转账成功
        } catch (\Exception $rex) {
            DB::rollBack();
            return $this->error($rex->getMessage());
        }
    }

    //获取pb的余额交易余额
    public function PB()
    {
        $user_id = Users::getUserId();
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('user_id', $user_id)->first();
        return $this->success($wallet->change_balance);
    }

    //闪兑信息
    public function flashAgainstList(Request $request)
    {
        $user_id = Users::getUserId();
        $left = Currency::where('is_match', 1)->get();
        foreach ($left as $k => $v) {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $v->id)->first();
            if (empty($wallet)) {
                $balance = 0;
            } else {
                $balance = $wallet->change_balance;
            }
            $v->balance = $balance;
            $left[$k] = $v;
        }
        $right = Currency::where('is_micro', 1)->get();
        foreach ($right as $k => $v) {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $v->id)->first();
            if (empty($wallet)) {
                $balance = 0;
            } else {
                $balance = $wallet->change_balance;
            }
            $v->balance = $balance;
            $right[$k] = $v;
        }
        return $this->success(['left' => $left, 'right' => $right]);
    }

    public function flashAgainst(Request $request)
    {
        try {
            $l_currency_id = $request->get('l_currency_id', "");
            $r_currency_id = $request->get('r_currency_id', "");
            $num = $request->get('num', 0);

            $user_id = Users::getUserId();
            if ($num <= 0) return $this->error('数量不能小于等于0');
            $p = $request->get('price', 0);
            if ($p <= 0) return $this->error('价格不能小于等于0');

            if (empty($l_currency_id) || empty($r_currency_id)) return $this->error('参数错误哦');

            $left = Currency::where('id', $l_currency_id)->first();
            $right = Currency::where('id', $r_currency_id)->first();
            if (empty($left) || empty($right)) return $this->error('币种不存在');

            //$absolute_quantity = $p * $num / $right->price;
            $absolute_quantity = bc_div(bc_mul($p, $num), $right->price);
            DB::beginTransaction();

            $l_wallet = UsersWallet::where('currency', $l_currency_id)->where('user_id', $user_id)->lockForUpdate()->first();

            if (empty($l_wallet)) {

                throw new \Exception('钱包不存在');
            }

            if ($l_wallet->change_balance < $num) {

                throw new \Exception('金额不足');
            }

            $flash_against = new FlashAgainst();
            $flash_against->user_id = $user_id;
            $flash_against->price = $p;
            $flash_against->market_price = $left->price;
            $flash_against->num = $num;
            $flash_against->status = 0;
            $flash_against->left_currency_id = $l_currency_id;
            $flash_against->right_currency_id = $r_currency_id;
            $flash_against->create_time = time();
            $flash_against->absolute_quantity = $absolute_quantity; //实际数量
            $result = $flash_against->save();
            $result1 = change_wallet_balance($l_wallet, 2, -$num, AccountLog::DEBIT_BALANCE_MINUS, 'Deduction balance of flash cash');//闪兑扣除余额
            $result2 = change_wallet_balance($l_wallet, 2, $num, AccountLog::DEBIT_BALANCE_ADD_LOCK, 'Flash cash increases lock up balance', true);//闪兑增加锁定余额
            if ($result1 !== true) {
                throw new \Exception($result1);
            }
            if ($result2 !== true) {
                throw new \Exception($result2);
            }

            DB::commit();
            return $this->success('兑换成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage() . '---' . $e->getLine());
        }
    }

    public function myFlashAgainstList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = Users::getUserId();
        $list = FlashAgainst::orderBy('id', 'desc')->where('user_id', $user_id)->paginate($limit);
        return $this->success($list);
    }

    //资产兑换 usdt  PB
    public function conversion(Request $request)
    {
        $form_currency_id = $request->get('form_currency', '');
        $to_currency_id = $request->get('to_currency', '');
        $balance_filed = 'legal_balance';
        $num = $request->get('num', '');
        if (empty($form_currency_id) || empty($to_currency_id) || empty($num)) {
            return $this->error('参数错误');
        }
        if ($num <= 0) {
            return $this->error('兑换数量必须大于0');
        }
        $user_id = Users::getUserId();
        try {
            DB::beginTransaction();
            $form_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $form_currency_id)->lockForUpdate()->first();
            $to_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $to_currency_id)->lockForUpdate()->first();
            if (empty($form_wallet) || empty($to_wallet)) {
                DB::rollBack();
                return $this->error('钱包不存咋');
            }
            if ($form_wallet->$balance_filed < $num) {
                DB::rollBack();
                return $this->error('余额不足');
            }
            if (strtoupper($form_wallet->currency_name) == 'USDT') {
                $fee = Setting::getValueByKey('currency_to_usdt_bmb_fee');
                $proportion = Setting::getValueByKey('currency_to_usdt_bmb');
            } elseif (strtoupper($form_wallet->currency_name) == UsersWallet::CURRENCY_DEFAULT) {
                $fee = Setting::getValueByKey('currency_to_bmb_usdt_fee');
                $proportion = Setting::getValueByKey('currency_to_bmb_usdt');
            }
            $totle_num_fee = bc_mul($num, $fee / 100);
            $totle_num = bc_sub($num, $totle_num_fee);
            $totle_num_sj = $proportion * $totle_num;


//            $res1 = change_wallet_balance($form_wallet, 1, -$totle_num, AccountLog::WALLET_USDT_MINUS, $form_wallet->currency_name . '兑换，' . $to_wallet->currency_name . ',减少' . $form_wallet->currency_name . $totle_num);
            $res1 = change_wallet_balance($form_wallet, 1, -$totle_num, AccountLog::WALLET_USDT_MINUS, $form_wallet->currency_name . ' exchange ，' . $to_wallet->currency_name . ',reduce ' . $form_wallet->currency_name . $totle_num);

//            $res2 = change_wallet_balance($form_wallet, 1, -$totle_num_fee, AccountLog::WALLET_USDT_BMB_FEE, $form_wallet->currency_name . '兑换，' . $to_wallet->currency_name . ',减少' . $form_wallet->currency_name . '手续费' . $totle_num_fee);
            $res2 = change_wallet_balance($form_wallet, 1, -$totle_num_fee, AccountLog::WALLET_USDT_BMB_FEE, $form_wallet->currency_name . ' exchange ，' . $to_wallet->currency_name . ',reduce ' . $form_wallet->currency_name . ' service charge ' . $totle_num_fee);

//            $res3 = change_wallet_balance($to_wallet, 1, $totle_num_sj, AccountLog::WALLET_BMB_ADD, $form_wallet->currency_name . '兑换，' . $to_wallet->currency_name . ',增加' . $to_wallet->currency_name . $totle_num_sj);
            $res3 = change_wallet_balance($to_wallet, 1, $totle_num_sj, AccountLog::WALLET_BMB_ADD, $form_wallet->currency_name . ' exchange ，' . $to_wallet->currency_name . ',reduce ' . $to_wallet->currency_name . $totle_num_sj);
            if ($res1 !== true) {
                DB::rollBack();
                return $this->error($res1);
            }
            if ($res2 !== true) {
                DB::rollBack();
                return $this->error($res2);
            }
            if ($res3 !== true) {
                DB::rollBack();
                return $this->error($res3);
            }

            $conversion = new Conversion();
            $conversion->user_id = $user_id;
            $conversion->create_time = time();
            $conversion->form_currency_id = $form_currency_id;
            $conversion->to_currency_id = $to_currency_id;
            $conversion->num = $num;
            $conversion->fee = $totle_num_fee;
            $conversion->sj_num = $totle_num_sj;
            $conversion->save();
            DB::commit();
            return $this->success('兑换成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    //我的资产兑换
    public function myConversion(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);
        $list = Conversion::orderBy('id', 'desc')->where('user_id', $user_id)->paginate($limit);
        return $this->success($list);
    }

    //资产兑换币种
    public function conversionList()
    {
        $currency = Currency::where('name', 'USDT')->orWhere('name', UsersWallet::CURRENCY_DEFAULT)->get();
        return $this->success($currency);
    }

    //资产兑换信息
    public function conversionSet()
    {
        $fee = Setting::getValueByKey('currency_to_usdt_bmb_fee');
        $proportion = Setting::getValueByKey('currency_to_usdt_bmb');
        $data['usdt_bmb_fee'] = $fee;
        $data['usdt_bmb_proportion'] = $proportion;
        $fee1 = Setting::getValueByKey('currency_to_bmb_usdt_fee');
        $proportion1 = Setting::getValueByKey('currency_to_bmb_usdt');
        $data['bmb_usdt_fee'] = $fee1;
        $data['bmb_usdt_proportion'] = $proportion1;
        $usdt = Currency::where('name', 'USDT')->first();
        $bmb = Currency::where('name', UsersWallet::CURRENCY_DEFAULT)->first();
        $user_id = Users::getUserId();
        $balance_filed = 'legal_balance';
        $usdt_wallet = UsersWallet::where('currency', $usdt->id)->where('user_id', $user_id)->first();
        $data['user_balance'] = $usdt_wallet->$balance_filed;
        $bmb_wallet = UsersWallet::where('currency', $bmb->id)->where('user_id', $user_id)->first();
        $data['bmb_balance'] = $bmb_wallet->$balance_filed;
        return $this->success($data);
    }

    //持险生币
    public function Insurancemoney()
    {

        $user_id = Users::getUserId();
        $wallet = UsersWallet::where('lock_insurance_balance', '>', 0)->where('user_id', $user_id)->first();
        $data = [];
        //受保资产
        $data['insurance_balance'] = $wallet->insurance_balance ?? 0;
        //保险资产
        $data['lock_insurance_balance'] = $wallet->lock_insurance_balance ?? 0;
        //累计生币
        $data['sum_balance'] = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INSURANCE_MONEY)->sum('value');
        //可用数量
        $data['usabled_balance'] = 0;

        return $this->success($data);
    }

    //持险生币日志
    public function Insurancemoneylogs()
    {

        $user_id = Users::getUserId();
        $limit = Input::get('limit', 10);

        $result = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INSURANCE_MONEY)->orderBy('id', 'desc')->paginate($limit);

        return $this->success($result);
    }
}
