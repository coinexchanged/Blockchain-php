<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\UserAlgebra;
use Illuminate\Support\Facades\App;
use App\UserCashInfo;
use Illuminate\Http\Request;
use Session;
use App\UserChat;
use App\Users;
use App\UserReal;
use App\Token;
use App\AccountLog;
use App\UsersWallet;
use App\UsersWalletcopy;
use App\Bank;
use App\IdCardIdentity;
use App\Currency;
use App\InviteBg;
use App\Setting;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\Seller;
use App\CurrencyQuotation;

//use App\{Users, AccountLog};

class UserController extends Controller
{

    //添加/修改收款方式
    public function saveCashInfo(Request $request)
    {
        $bank_name = $request->get('bank_name', '');//开户銀行
        $bank_id = $request->get('bank_id', '');//开户銀行id
        $bank_branch = $request->get('bank_branch', '');//开户支行
        $bank_account = $request->get('bank_account', '');//银行账号
        $real_name = $request->get('real_name', '');//真实姓名,渲染出来
        $alipay_account = $request->get('alipay_account', '');//支付宝账号
        $wechat_nickname = $request->get('wechat_nickname', '');//微信昵称
        $wechat_account = $request->get('wechat_account', '');//微信账号
        $alipay_qr_code = $request->get('alipay_qr_code', '');//支付宝二维码
        $wechat_qr_code = $request->get('wechat_qr_code', '');//微信二维码

        $user_id = Users::getUserId();
        if (empty($real_name)) {
            return $this->error('真实姓名必须填写');
        }
        if ((empty($bank_id) || empty($bank_account))
            && (empty($wechat_nickname) || empty($wechat_account))
            && empty($alipay_account)) {
            return $this->error('收款信息至少选填一项');
        }
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (empty($cash_info)) {
            $cash_info = new UserCashInfo();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        }
        if (!empty($bank_name)) {
            $cash_info->bank_name = $bank_name;
        }
        if (!empty($bank_id)) {
            $cash_info->bank_id = $bank_id;
            $bank = Bank::find($bank_id);
            $bank_name = $bank ? $bank->name : '';
            $cash_info->bank_name = $bank_name;
        }
        if (!empty($bank_branch)) {
            $cash_info->bank_branch = $bank_branch;
        }
        if (!empty($bank_account)) {
            $cash_info->bank_account = $bank_account;
        }
        $cash_info->real_name = $real_name;
        if (!empty($alipay_account)) {
            $cash_info->alipay_account = $alipay_account;

        }
        if (!empty($wechat_account)) {
            $cash_info->wechat_account = $wechat_account;

        }
        if (!empty($wechat_nickname)) {
            $cash_info->wechat_nickname = $wechat_nickname;

        }
        if (!empty($wechat_account)) {
            $cash_info->wechat_account = $wechat_account;

        }
        if (!empty($alipay_qr_code)) {
            $cash_info->alipay_qr_code = $alipay_qr_code;

        }
        if (!empty($wechat_qr_code)) {
            $cash_info->wechat_qr_code = $wechat_qr_code;

        }
        try {
            $cash_info->save();
            //更新申请商家收付款方式
            $seller = Seller::where("user_id", $user_id)->first();
            if (!empty($seller)) {
                $seller->alipay_qr_code = $alipay_qr_code;
                $seller->wechat_qr_code = $wechat_qr_code;

                $seller->wechat_nickname = $wechat_nickname;
                $seller->wechat_account = $wechat_account;
                $seller->ali_account = $alipay_account;
                $seller->bank_account = $bank_account;
                $seller->bank_address = $bank_branch;
                $seller->bank_id = $bank_id;
                $seller->save();
            }
            return $this->success('保存成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function getLegalInfo(Request $request)
    {
//        function () {

        $arg = [
            'count' => Setting::getValueByKey('legalShopCount'),
            'rate' => Setting::getValueByKey('buyUSDTRate'),
            'alipay' => Setting::getValueByKey("alipayAccount"),
            'alipayCode' => Setting::getValueByKey("alipayQrcode"),
            'wechat' => Setting::getValueByKey("wechatAccount"),
            'wechatCode' => Setting::getValueByKey("wechatQrcode"),
            'bankName' => Setting::getValueByKey("bankName"),
            'bankNo' => Setting::getValueByKey("bankNo"),
            'bankAddress' => Setting::getValueByKey("bankAddress"),
            'min' => Setting::getValueByKey('buyMin'),
            'max' => Setting::getValueByKey('buyMax')
        ];
//        echo json_encode(['type' => 'ok', 'message' => $arg]);
//        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if ($request->get('way') == 'buy') {
            if ($user->legal_store) {
                $arg = [
                    'count' => Setting::getValueByKey('legalShopCount'),
                    'rate' => $user->legal_store->rate,
                    'alipay' => $user->legal_store->alipay_account,
                    'alipayCode' => $user->legal_store->alipay_qrcode,
                    'wechat' => $user->legal_store->wechat_account,
                    'wechatCode' => $user->legal_store->wechat_qrcode,
                    'bankName' => $user->legal_store->bank_user,
                    'bankNo' => $user->legal_store->bank_account,
                    'bankAddress' => $user->legal_store->bank_name,
                    'min' => $user->legal_store->min_num,
                    'max' => $user->legal_store->max_num
                ];
            } else {

            }
        } else {
            if ($user->legal_store) {
                $arg = [
                    'count' => Setting::getValueByKey('legalShopCount'),
                    'rate' => $user->legal_store->rate_sell,
                    'alipay' => $user->legal_store->alipay_account,
                    'alipayCode' => $user->legal_store->alipay_qrcode,
                    'wechat' => $user->legal_store->wechat_account,
                    'wechatCode' => $user->legal_store->wechat_qrcode,
                    'bankName' => $user->legal_store->bank_user,
                    'bankNo' => $user->legal_store->bank_account,
                    'bankAddress' => $user->legal_store->bank_name,
                    'min' => $user->legal_store->min_num_wid,
                    'max' => $user->legal_store->max_num_wid
                ];
            } else {
                $arg['rate'] = Setting::getValueByKey('sellUSDTRate');
            }
        }
        return $this->success($arg);
    }

    public function saveWalletInfo(Request $request)
    {
        $user_id = Users::getUserId();
//        $address = UsersWallet::where('user_id', $user_id)->where(['currency'=>3])->get(['address']);
        $address = $request->get('address');
        Db::table('users_wallet')->where(['user_id' => $user_id, 'currency' => 3])->update(['address' => $address]);
        return $this->success('保存成功');
    }

    public function getWalletInfo(Request $request)
    {
        $user_id = Users::getUserId();
        $address = Db::table('users_wallet')->where(['user_id' => $user_id, 'currency' => 3])->value('address');
        return $this->success($address);
    }

    public function checkPayPassword()
    {
        $password = Input::get('password', '');
        $user = Users::getById(Users::getUserId());
        if ($user->pay_password != Users::MakePassword($password)) {
            return $this->error('密码错误');
        } else {
            return $this->success('操作成功');
        }
    }

    public function currency_tousdt_log()
    {
        $limit = Input::get('limit', '10');
        $page = Input::get('page', '1');
        $user_id = Users::getUserId();
        $type1 = AccountLog::CURRENCY_TO_USDT_MUL;//资产兑换 减少兑换币
        $type2 = AccountLog::CURRENCY_TO_USDT_ADD;//资产兑换 增加USDT法币
        $prize_pool = AccountLog::where(function ($query) use ($type1, $type2, $user_id) {
            $query->orWhere(function ($query) use ($user_id, $type1) {
                $query->where("type", "=", $type1);
            })->orWhere(function ($query) use ($user_id, $type2) {
                $query->where("type", "=", $type2);
            });
        })->where("user_id", "=", $user_id)->orderBy("created_time", "desc")->paginate($limit);

        return $this->success(array(
            "data" => $prize_pool->items(),
            "limit" => $limit,
            "page" => $page,
        ));
    }

    public function currency_show()
    {

        $currency_id = Input::get('currency_id');
        $number = Input::get('number');
        if (!empty($number)) {
            $currency_to_usdt_fee = Setting::getValueByKey('currency_to_usdt_fee', 100);
            $currency_to_usdt_fee = bc_div($currency_to_usdt_fee, 100);
            $user_id = Users::getUserId();
//            $user = Users::find($user_id);
//            $user_walllet_currency=UsersWallet::where("user_id","=",$user_id)->where("currency","=",$currency_id)->first();
            $usdt = Currency::where('name', 'USDT')->select(['id'])->first();
//            $user_walllet_usdt=UsersWallet::where("user_id",$user_id)->where("currency", $usdt->id)->first();
            $service_charge = bc_mul($number, $currency_to_usdt_fee, 5);
            $now_price = CurrencyQuotation::where("legal_id", "=", $usdt->id)->where("currency_id", "=", $currency_id)->first()->now_price;
//        var_dump($now_price);die;
            $add_usdt_legal_balance = bc_mul($number, $now_price, 5);
            $add_usdt_legal_balance = bc_sub($add_usdt_legal_balance, $service_charge, 5);
        } else {
            $add_usdt_legal_balance = 0;
        }


        $currency = Currency::where("is_legal", "!=", 1)->where("is_display", 1)->get()->toArray();
        return $this->success(['currency' => $currency, 'add_usdt_legal_balance' => $add_usdt_legal_balance]);

    }

    public function currency_tousdt()
    {
        $currency_id = Input::get('currency_id');
        $currency_name = Currency::where("id", $currency_id)->first()->name;
        $number = Input::get('number');
        $currency_to_usdt_fee = Setting::getValueByKey('currency_to_usdt_fee', 100);
        $currency_to_usdt_fee = bc_div($currency_to_usdt_fee, 100);
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user_walllet_currency = UsersWallet::where("user_id", "=", $user_id)->where("currency", "=", $currency_id)->first();
        $usdt = Currency::where('name', 'USDT')->select(['id'])->first();
        $user_walllet_usdt = UsersWallet::where("user_id", $user_id)->where("currency", $usdt->id)->first();

        $now_price = CurrencyQuotation::where("legal_id", "=", $usdt->id)->where("currency_id", "=", $currency_id)->first()->now_price;
        if ($now_price <= 0) {
            return $this->error('当前行情小于等于零!');
        }
//        var_dump($now_price);die;
        $add_usdt_legal_balance = bc_mul($number, $now_price, 5);
        $service_charge = bc_mul($add_usdt_legal_balance, $currency_to_usdt_fee, 5);
        $add_usdt_legal_balance = bc_sub($add_usdt_legal_balance, $service_charge, 5);
        if (empty($number) || $number <= 0) {
            return $this->error('参数错误!');
        }
        if ($number > $user_walllet_currency->legal_balance) {
            return $this->error('兑换数量大于持有资产!');
        }
        DB::beginTransaction();
        try {
            //减少持有币法币
            $result1 = change_wallet_balance(
                $user_walllet_currency,
                1,//1.法币,2.币币交易,3.杠杆交易
                -$number,
                AccountLog::CURRENCY_TO_USDT_MUL,
                '资产兑换,减少' . $currency_name . '法币数量:' . -$number,
                false,
                $user->id,
                0
            );
            if ($result1 !== true) {
                throw new \Exception('资产兑换,减少持有币法币:' . $result1);
            }
            //增加USDT杠杆币
            $result2 = change_wallet_balance(
                $user_walllet_usdt,
                3,//1.法币,2.币币交易,3.杠杆交易
                +$add_usdt_legal_balance,
                AccountLog::CURRENCY_TO_USDT_ADD,
                '资产兑换,增加USDT杠杆币' . +$add_usdt_legal_balance . '扣除手续费' . -$service_charge,
                false,
                $user->id,
                0
            );
            if ($result2 !== true) {
                throw new \Exception('资产兑换,增加USDT杠杆币:' . $result2);
            }

            $user_walllet_usdt->lever_balance_add_allnum = $user_walllet_usdt->lever_balance_add_allnum + $add_usdt_legal_balance;//资产兑换累加产生的杠杆值(作为入金的一部分）
            $user_walllet_usdt->save();

            DB::commit();
            return $this->success('资产兑换成功!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }


    public function candy_tousdt()
    {
        $candy_tousdt = Setting::getValueByKey('candy_tousdt', 100);
        $candy_tousdt = bc_div($candy_tousdt, 100);
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $candy_number = Input::get('candy_number');
        if (empty($candy_number) || $candy_number <= 0) {
            return $this->error('参数错误!');
        }
        if ($candy_number > $user->candy_number) {
            return $this->error('兑换数量大于剩余数量!');
        }
        DB::beginTransaction();
        try {
            $change_result = change_user_candy($user, -$candy_number, AccountLog::CANDY_TOUSDT_CANDY, "通证兑换USDT");
            if ($change_result !== true) {
                throw new \Exception($change_result);
            }
            $aaaa = UsersWalletcopy::leftjoin("currency", "currency.id", "users_wallet.currency")
                ->where("currency.name", "USDT")
                ->where("users_wallet.user_id", $user_id)
                ->select("users_wallet.id", "users_wallet.lever_balance", "users_wallet.user_id", "currency.id as currency_id")
                ->first();
            $user_walllet = UsersWalletcopy::where("user_id", $aaaa->user_id)
                ->where("currency", $aaaa->currency_id)
                ->first();
            /*
            $user_walllet->lever_balance=bc_add($user_walllet->lever_balance,$candy_number*$candy_tousdt,8);
            var_dump($user_walllet->toArray());
            die;
            $user_walllet->save();
            */
            $change = bc_mul($candy_number, $candy_tousdt, 4);
            //增加杠杆币日志记录
            $result = change_wallet_balance(
                $user_walllet,
                3,
                $change,
                AccountLog::CANDY_LEVER_BALANCE,
                '通证兑换,杠杆币增加' . $change,
                false,
                $user->id,
                0
            );
            if ($result !== true) {
                throw new \Exception('通证兑换杠杆币增加失败:' . $result);
            }
            DB::commit();
            return $this->success('通证兑换成功!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }


    //获取本人收款方式信息
    public function cashInfo()
    {
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $result = UserCashInfo::where('user_id', $user_id)->firstOrNew([]);

        $banks = Bank::all();
        $result->banks = $banks;
        return $this->success($result);
    }

    //设置法币交易账号密码
    public function setAccount()
    {
        $account = Input::get('account', '');
        $password = Input::get('password', '');
        $repassword = Input::get('repassword', '');
        if (empty($account) || empty($password) || empty($repassword)) {
            return $this->error('必填项信息不完整');
        }
        if ($password != $repassword) {
            return $this->error('两次输入密码不一致');
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error('此用户不存在');
        }
        if ($user->account_number) {
            return $this->error('此交易账号已经设置');
        }
        $res = Users::where('account_number', $account)->first();
        if ($res) {
            return $this->error('此账号已经存在');
        }
        try {
            $user->account_number = $account;
            $user->pay_password = Users::MakePassword($password, $user->type);
            $user->save();
            return $this->success('交易账号设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->电话邮箱绑定信息
    public function safeCenter()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $safeInfo = array(
            'mobile' => $user->phone,//如果为空，未绑定
            'email' => $user->email,
            'gesture_password' => $user->gesture_password,
            //手势密码如果存在就是个蓝色的框，默认登录的时候就是手势密码登录
            //再次点击蓝色的框就是取消手势密码，取消就不用手势密码登录，删除字段中的值
            //如果不存在，是个灰色的框。点击之后是重新设置添加手势密码
        );
        return $this->success($safeInfo);
    }

    //安全中心-->绑定电话
    public function setMobile()
    {
        $user_id = Users::getUserId();
        $mobile = Input::get('mobile', '');
        $code = Input::get('code', '');
        if (empty($user_id) || empty($mobile) || empty($code)) {
            return $this->error('参数错误');
        }
        if ($code != session('code')) {
            return $this->error('验证码错误');
        }
        try {
            $user = Users::find($user_id);
            $user->phone = $mobile;
            $user->save();
            return $this->success('手机绑定成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->绑定邮箱
    public function setEmail()
    {
        $user_id = Users::getUserId();
        $email = Input::get('email', '');
        $code = Input::get('code', '');
        if (empty($user_id) || empty($email) || empty($code)) {
            return $this->error('参数错误');
        }
        if ($code != session('code')) {
            return $this->error('验证码错误');
        }
        try {
            $user = Users::find($user_id);
            $user->email = $email;
            $user->save();
            return $this->success('邮箱绑定成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->手势密码-->添加手势密码
    public function gesturePassAdd()
    {
        $password = Input::get('password', '');//获取的是一个数组[1,2,3]
        $re_password = Input::get('re_password', '');
        if (mb_strlen($password) < 6) {
            return $this->error('手势密码至少连接6个点');
        }
        if ($password != $re_password) {
            return $this->error('两次手势密码不相同');
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user->gesture_password = $password;
        try {
            $user->save();
            return $this->success('手势密码添加成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->手势密码-->取消手势密码
    public function gesturePassDel()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user->gesture_password = "";
        try {
            $user->save();
            return $this->success('取消手势密码成功');//按钮变成灰色的
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->修改交易密码
    public function updatePayPassword()
    {

        $oldpassword = Input::get('oldpassword', '');
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        if ($password != $re_password) {
            return $this->error('两次密码不一致');
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if (!empty($user->pay_password) && $user->pay_password != $oldpassword) {
            return $this->error('原密码错误');
        }

        $user->pay_password = $password;
        try {
            $user->save();
            return $this->success('交易密码设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //邀请返佣榜单  前20名
    public function inviteList()
    {
        $time = Input::get('time', '');//邀请返佣时间段
        if ($time) {
            $time = strtotime($time);
        } else {
            $time = 0;
        }


        $list = AccountLog::has('user')
            ->select(DB::raw('sum(value) as total, user_id'))
            ->where('type', AccountLog::INVITATION_TO_RETURN)
            ->where('created_time', '>=', $time)
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get()
            ->toArray();

        if (empty($list)) {
            return $this->error("暂时还没有邀请排行榜，快去邀请吧");
        }


        foreach ($list as $key => $val) {

            $user = Users::find($val['user_id']);


            $list[$key]['account'] = $user->account;

        }

        return $this->success($list);


    }


    //邀请
    public function invite()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error("会员未找到");
        }


        //邀请排行榜 前3
        $list = AccountLog::has('user')
            ->select(DB::raw('sum(value) as total, user_id'))
            ->where('type', AccountLog::INVITATION_TO_RETURN)
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get()
            ->toArray();
        if (empty($list)) {
            $list = [];
        } else {

            foreach ($list as $key => $val) {

                $users = Users::find($val['user_id']);

                $list[$key]['account'] = $users->account;

            }


        }

        //邀请广告图片及链接
        $ad = [];
        $ad['image'] = "/upload/invite.png";

        $data = [];
        $data['extension_code'] = $user['extension_code'];
        $data['ad'] = $ad;
        $data['inviteList'] = $list;


        //获取用户邀请人数
        //邀请返佣金数量
        $num = Users::where('parent_id', $user_id)->count();

        if ($num > 0) {
            $data['invite_num'] = $num;
            $total = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INVITATION_TO_RETURN)->sum('value');
            $data['invite_return_total'] = $total;
        } else {
            $data['invite_num'] = 0;
            $data['invite_return_total'] = 0;
        }

        return $this->success($data);

    }


    //我的邀请记录  0启用  1禁用 2全部
    public function myInviteList()
    {

        $status = Input::get('status', 2);//邀请会员状态
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error("会员未找到");
        }

        $list = Users::where('parent_id', $user_id);
        if ($status != 2) {
            $list = $list->where('status', $status);
        }
        $list = $list->orderBy('id', 'desc')->get()->toArray();

        return $this->success($list);

    }

    //我的返佣记录
    public function myAccountReturn()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error("会员未找到");
        }

        $time = Input::get('time', '');//邀请返佣时间段
        if ($time) {
            $time = strtotime($time);
        } else {
            $time = 0;
        }


        $list = AccountLog::where('user_id', $user_id)
            ->where('type', AccountLog::INVITATION_TO_RETURN)
            ->where('created_time', '>=', $time)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();


        return $this->success($list);


    }

    //钱包地址
    public function walletaddress()
    {
//        $user_id = Users::getUserId();
        $user_id = Input::get('user_id');
        $wallet_address = Input::get('wallet_address');

        $usermyself = Users::where("id", $user_id)->first()->toArray();
        $user = Users::where("wallet_address", $wallet_address)->where("id", '!=', $user_id)->first();
        if ($usermyself['wallet_address']) {
            return $this->error("你已绑定，不可更改!");
        } elseif (!empty($user)) {
            return $this->error("该地址已被绑定,请重新输入");
        } else {
            $pdo = new Users();
            $pdo->where("id", "=", $user_id)->update(['wallet_address' => $wallet_address]);
            return $this->success('绑定成功!');
        }
    }


    //我的
    public function info(Request $request)
    {
        $request_user_id = $request->get('user_id', 0);
        $user_id = Users::getUserId();
        if ($request_user_id) {
            $user_id = $request_user_id;
        }

        $currency_usdt_id = Currency::where('name', 'USDT')->select(['id', 'name'])->first();

        //$user = Users::where("id",$user_id)->first(['id','phone','email','head_portrait','status']);
        $user = Users::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $user['is_open_transfer_candy'] = Setting::getValueByKey("is_open_transfer_candy");
        //用户认证状况
        $res = UserReal::where('user_id', $user_id)->first();
        if (empty($res)) {
            $user['review_status'] = 0;
            $user['name'] = '';
        } else {
            $user['review_status'] = $res['review_status'];
            $user['name'] = $res['name'];
        }
        $seller = Seller::where('user_id', $user_id)->get()->toArray();
        if (!empty($seller)) {
            $user['seller'] = $seller;
        }
        $user['tobe_seller_lockusdt'] = Setting::getValueByKey("tobe_seller_lockusdt");
        $user['currency_usdt_id'] = $currency_usdt_id->id;
        $user['currency_usdt_name'] = $currency_usdt_id->name;


        //添加杠杆币折合人民币
        $currency_name = $request->input('currency_name', '');
        $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
            })->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();
//        var_dump($lever_wallet);die;
        $lever_wallet['totle'] = 0;
        foreach ($lever_wallet['balance'] as $k => $v) {
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            $lever_wallet['totle'] += $num * $v['cny_price'];
        }
        $user["lever_wallet"] = $lever_wallet;
        //添加法币折合人民币
        $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
            })->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();
        $legal_wallet['totle'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            $legal_wallet['totle'] += $num * $v['cny_price'];
        }
        $user["legal_wallet"] = $legal_wallet;
        // $legal_wallet['totle'] = 0.10011000;
//        $legal_wallet['CNY'] = '';

        //添加折合人民币end
        //秒合约钱包
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) {
                $query->where('is_micro', 1);
            })->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])->toArray();
        $user["micro_wallet"] = $micro_wallet;

        return $this->success($user);


    }

    //身份认证
    public function realName()
    {

        $user_id = Users::getUserId();
        $name = Input::get("name", "");//真实姓名
        $card_id = Input::get("card_id", "");//身份证号
        $front_pic = Input::get("front_pic", "");//正面照片
        $reverse_pic = Input::get("reverse_pic", "");//反面照片
//        $hand_pic = Input::get("hand_pic", "");//手持身份证照片


        if (empty($name) || empty($card_id) || empty($front_pic) || empty($reverse_pic)) {
            return $this->error("请提交完整的信息");
        }

        /* //校验  身份证号码合法性
         $idcheck = new IdCardIdentity();
         $res = $idcheck->check_id($card_id);
         if (!$res) {
             return $this->error("请输入合法的身份证号码");
         }*/
        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error("会员未找到");
        }

        $userreal_number = UserReal::where("card_id", $card_id)->count();
//        var_dump($userreal_number);die;
        if ($userreal_number > 0) {
            return $this->error("该身份证号已实名认证过!");
        }

        $userreal = UserReal::where('user_id', $user_id)->first();
        if (!empty($userreal)) {
            return $this->error("您已经申请过了");
        }

        try {

            $userreal = new UserReal();

            $userreal->user_id = $user_id;
            $userreal->name = $name;
            $userreal->card_id = $card_id;
            $userreal->create_time = time();
            $userreal->front_pic = $front_pic;
            $userreal->reverse_pic = $reverse_pic;
//            $userreal->hand_pic = $hand_pic;

            $userreal->save();

            return $this->success('提交成功，等待审核');
        } catch (\Exception $e) {

            return $this->error($e->getMessage());
        }


    }


    //个人中心  身份认证信息
    public function userCenter()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'phone', 'email']);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $userreal = UserReal::where('user_id', $user_id)->first();

        if (empty($userreal)) {
            $user['review_status'] = 0;
            $user['name'] = '';
            $user['card_id'] = '';
        } else {
            $user['review_status'] = $userreal['review_status'];
            $user['name'] = $userreal['name'];
            $user['card_id'] = $userreal['card_id'];

        }


        if (!empty($user['card_id'])) {
            $user['card_id'] = mb_substr($user['card_id'], 0, 2) . '******' . mb_substr($user['card_id'], -2, 2);
        }
        return $this->success($user);


    }

    //专属海报信息
    public function posterBg()
    {
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'extension_code']);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $pics = InviteBg::all(['id', 'pic'])->toArray();

        $data['extension_code'] = $user['extension_code'];
        $data['share_url'] = Setting::getValueByKey('share_url', '');
        $data['pics'] = $pics;

        return $this->success($data);

    }

    //我的邀请分享
    public function share()
    {
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'extension_code']);
        if (empty($user)) {
            return $this->error("会员未找到");
        }

        $data['share_title'] = Setting::getValueByKey('share_title', '');
        $data['share_content'] = Setting::getValueByKey('share_content', '');
        $data['share_url'] = Setting::getValueByKey('share_url', '');
        $data['extension_code'] = $user['extension_code'];

        return $this->success($data);

    }


    //退出
    public function logout()
    {

        $user_id = Users::getUserId();
        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error("会员未找到");
        }
        //清除用户的token  session
        session(['user_id' => '']);
        $token = Token::getToken();
        //删除当前token
        Token::deleteToken($user_id, $token);

        return $this->success('退出登录成功');


    }


    public function vip()
    {
        $user_id = Users::getUserId(Input::get("user_id"));
        $password = Input::get('password', '');


        if (empty($password)) return $this->error("请输入支付密码");

        $vip = Input::get("vip");
        if (empty($user_id) || empty($vip)) {
            return $this->error("参数错误");
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        if ($user->vip >= $vip) {
            return $this->error("无需升级");
        }
        if ($vip == "2") {
            if ($user->vip == 1) {
                $money = 9000;
            } else {
                $money = 9999;
            }
        } else {
            $money = 999;
        }

        $wallet = UsersWallet::where("user_id", $user_id)
            ->where("token", Users::TOKEN_DEFAULT)
            ->select("id", "user_id", "password", "address", "balance", "lock_balance", "remain_lock_balance", "create_time", "wallet_name", "password_prompt")
            ->first();
        if (empty($wallet)) {
            return $this->error("暂无钱包");
        }
        if ($password != $wallet->password) {
            return $this->error("支付密码错误");
        }
        if ($wallet->balance < $money) {
            return $this->error("余额不足");
        }

        $walletn = UsersWallet::find($wallet->id);
        $data_wallet = [
            'balance_type' => AccountLog::UPDATE_VIP,
            'wallet_id' => $walletn->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $walletn->balance,
            'change' => -$money,
            'after' => bc_sub($walletn->balance, $money, 5),
        ];
        $user->vip = $vip;
        $walletn->balance = $walletn->balance - $money;
        $user->save();
        $walletn->save();
        AccountLog::insertLog(
            array(
                "user_id" => $user_id,
                "value" => -$money,
                "type" => AccountLog::UPDATE_VIP,
                "info" => "升级会员"
            ),
            $data_wallet
        );
        return $this->success("升级成功");
    }

    //提交虚拟币收货地址
    public function updateCurrencyAddress()
    {

    }

    public function updateAddress()
    {
        $address = Users::getUserId();

        $eth_address = trim(Input::get('eth_address'));
        if (empty($address) || empty($eth_address)) {
            return $this->error('参数错误');
        }
        $user = Users::find($address);
        if (empty($user)) {
            return $this->error('没有此用户');
        }

        if ($other = Users::where('eth_address', $eth_address)->first()) {
            if ($other->id != $user->id) {
                return $this->error('该地址别人已经绑定过了');
            }
        }
        try {
            $user->eth_address = $eth_address;
            $user->save();
            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getUserByAddress()
    {
        $user_id = Users::getUserId();
        if (empty($user_id))
            return $this->error("参数错误");
        $user = Users::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        if (empty($user->extension_code)) {
            $user->extension_code = Users::getExtensionCode();
            $user->save();
        }

        $wallet = UsersWallet::where("user_id", $user_id)
            ->where("token", Users::TOKEN_DEFAULT)
            ->select("id", "user_id", "address", "balance", "lock_balance", "remain_lock_balance", "create_time", "wallet_name", "password_prompt")
            ->first();
        $user->wallet = $wallet;
        return $this->success($user);
    }

    public function chatlist()
    {
        $user_id = Users::getUserId(Input::get('user_id', ''));
        if (empty($user_id)) return $this->error("参数错误");

        $user = Users::find($user_id);
        if (empty($user)) return $this->error("用户未找到");

        $chat_list = UserChat::orderBy('id', 'DESC')->paginate(20);

        $datas = $chat_list->items();

        krsort($datas);
        $return = array();
        foreach ($datas as $d) {
            array_push($return, $d);
        }
        return $this->success(array(
            "user" => $user,
            "chat_list" => [
                'total' => $chat_list->total(),
                'per_page' => $chat_list->perPage(),
                'current_page' => $chat_list->currentPage(),
                'last_page' => $chat_list->lastPage(),
                'next_page_url' => $chat_list->nextPageUrl(),
                'prev_page_url' => $chat_list->previousPageUrl(),
                'from' => $chat_list->firstItem(),
                'to' => $chat_list->lastItem(),
                'data' => $return,
            ]
        ));
    }

    public function getExtension()
    {
        $user_id = Users::getUserId();
        if (empty($user_id))
            return $this->error("参数错误");
        $user = Users::where("id", $user_id)->first();
        return $this->success(Users::where('parent_id', $user->id)->get()->toArray());
    }

    public function sendchat()
    {
        $user_id = Users::getUserId(Input::get('user_id', ''));

        $content = Input::get('content', '');
        if (empty($user_id) || empty($content)) return $this->error("参数错误");

        $user = Users::find($user_id);
        if (empty($user)) return $this->error("会员未找到");

        $data["user_id"] = $user_id;
        $data["user_name"] = $user->account_number;
        $data["head_portrait"] = $user->head_portrait;
        $data["content"] = $content;
        $data["type"] = "1";


        try {
            $res = UserChat::sendChat($data);
            if ($res == "ok") {
                $user_chat = new UserChat();
                $user_chat->from_user_id = $user_id;
                $user_chat->to_user_id = 0;
                $user_chat->content = $content;
                $user_chat->type = 1;
                $user_chat->add_time = time();
                $user_chat->save();
                return $this->success("ok");
            } else {
                return $this->error("请重试");
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //用户信息导入
    public function into_users()
    {
        $password = Input::get('password', '');
        $account_number = Input::get('account_number', '');
        $pay_password = Input::get('pay_password', '');
        $parent_id = Input::get('parent_id', '');//邀请人账户
        if (empty($parent_id) || empty($pay_password) || empty($password) || empty($password)) {
            return $this->error('请把参数填写完整');
        }
        //判断用户是否存在
        $user = Users::getByAccountNumber($account_number);
        if (!empty($user)) {
            return $this->error('用户已存在');
        }
        //判断推荐人是否存在
        $invit = Users::getByAccountNumber($parent_id);
        if (empty($invit)) {
            return $this->error('推荐用户不存在');
        }

        $users = new Users();
        $users->password = Users::MakePassword($password, 1);
        $users->pay_password = Users::MakePassword($pay_password, 0);
        $users->parent_id = $invit->id;
        $users->account_number = $account_number;
        $users->type = 1;
        $users->head_portrait = URL("mobile/images/user_head.png");
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->save();//保存到user表中
            $currency = Currency::all();
            $address_url = config('wallet_api') . $users->id;
            $address = RPC::apihttp($address_url);
            $address = @json_decode($address, true);

            foreach ($currency as $key => $value) {
                $userWallet = new UsersWallet();
                $userWallet->user_id = $users->id;
                if ($value->type == 'btc') {
                    $userWallet->address = $address["contentbtc"];
                } else {
                    $userWallet->address = $address["content"];
                }
                $userWallet->currency = $value->id;
                $userWallet->create_time = time();
                $userWallet->save();//默认生成所有币种的钱包
            }
            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }

    }

    //美丽链转入(imc)
    public function into_tra()
    {
        $account_number = Input::get('account_number', '');
        // var_dump($account_number);die;
        $password = Input::get('password', '');
        $number = Input::get('number', '');
        $type = Input::get('type', '1'); ///type:0 法币交易，type:1币币交易，type:2杠杆交易
        //dump( $type);die;
        if (empty($account_number)) {
            return $this->error('转入账户不能为空');
        }
        if (empty($password)) {
            return $this->error('密码不能为空');
        }
        if (empty($number)) {
            return $this->error('转入数量不能为空');
        }
        $tra_user = Users::getByAccountNumber($account_number);
        if (empty($tra_user)) {
            return $this->error('用户未找到');
        }
        if ($tra_user->password != Users::MakePassword($password, $tra_user->type)) {
            return $this->error('用户密码错误');
        }
        //当前用户钱包信息
        $currency = Currency::where('name', 'IMC')->first();
        $waller_info = UsersWallet::where('currency', $currency->id)->where('user_id', $tra_user->id)->first();
        //dump( $waller_info);die;
        DB::beginTransaction();
        $data_wallet = [
            //'balance_type' =>  0,
            'wallet_id' => $waller_info->id,
            'lock_type' => 0,
            'create_time' => time(),
            //'before' => 0,
            'change' => $number,
            //'after' => 0,
        ];
        try {
            if ($type == 0) {
                $data_wallet['balance_type'] = 1;
                $data_wallet['before'] = $waller_info->legal_balance;
                $data_wallet['after'] = bc_add($waller_info->legal_balance, $number, 5);
                $waller_info->legal_balance = $waller_info->legal_balance + $number;
                $info = '美丽链法币交易余额转入';
                $type_info = AccountLog::INTO_TRA_FB;
            } else if ($type == 1) {
                $data_wallet['balance_type'] = 2;
                $data_wallet['before'] = $waller_info->change_balance;
                $data_wallet['after'] = bc_add($waller_info->change_balance, $number, 5);
                $waller_info->change_balance = $waller_info->change_balance + $number;
                $info = '美丽链币币交易余额转入';
                $type_info = AccountLog::INTO_TRA_BB;
            } else {
                $data_wallet['balance_type'] = 3;
                $data_wallet['before'] = $waller_info->lever_balance;
                $data_wallet['after'] = bc_add($waller_info->lever_balance, $number, 5);
                $waller_info->lever_balance = $waller_info->lever_balance + $number;
                $info = '美丽链杠杆交易余额转入';
                $type_info = AccountLog::INTO_TRA_GG;
            }
            $waller_info->save();
            //冻结余额

            $waller_info->save();
            AccountLog::insertLog([
                'user_id' => $tra_user->id,
                'value' => $number,
                'currency' => $currency->id,
                'info' => $info,
                'type' => $type_info,
            ], $data_wallet);
            DB::commit();
            return $this->success('转入成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }


    }

    //转入记录
    public function into_tra_log()
    {
        $user_id = Users::getUserId();
        $list = AccountLog::whereIn("type", array(65, 66, 67))->where('user_id', $user_id)->orderBy('id', 'desc')->get()->toArray();
        return $this->success($list);
    }

    //修改密码
    public function e_pwd()
    {
        $account_number = Input::get('account_number', '');
        $password = Input::get('password', '');
        $type = Input::get('type', '1'); ///type:1登录密码，type:2支付密码
        if (empty($account_number)) {
            return $this->error('转入账户不能为空');
        }
        if (empty($password)) {
            return $this->error('密码不能为空');
        }
        $tra_user = Users::getByAccountNumber($account_number);
        if (empty($tra_user)) {
            return $this->error('用户未找到');
        }
        DB::beginTransaction();
        try {
            if ($type == 1) {
                $tra_user->password = Users::MakePassword($password, $tra_user->type);

            } else {
                $tra_user->pay_password = Users::MakePassword($password, $tra_user->type);
            }

            $tra_user->save();
            DB::commit();
            return $this->success('密码修改成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function updateBalance()
    {
        exit('close');
        $user_id = Users::getUserId();
        // $this->updateWalletAddress();
        try {
            DB::beginTransaction();
            $user_wallets = UsersWallet::lockForUpdate()->where('user_id', $user_id)->where('gl_time', '<', time() - 60 * 60)->get();
            foreach ($user_wallets as $user_wallet) {

                // UsersWallet::updateBalance($user_wallet);
                $currency = Currency::find($user_wallet->currency);
                if (empty($currency)) {
                    return false;
                }
                if (empty($user_wallet->address)) {
                    return false;
                }
                $address = $user_wallet->address;
                if ($currency->type == 'eth') {
                    echo $user_wallet->currency_name;
                    $url = "https://api.etherscan.io/api?module=account&action=balance&address=" . $address . "&tag=latest&apikey=YourApiKeyToken";
                    // $content = RPC::apihttp($url);
                    $content = RPC::curl($url, false, 0, 1);
                    $content = @json_decode($content, true);
                    // echo($url);
                    // dd($content);
                    // $content = json_decode($content,true);
                    $message = $content["message"];

                    // dd($content);
                } else if ($currency->type == 'erc20') {
                    $url = "https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=" . $currency->contract_address . "&address=" . $address . "&tag=latest&apikey=579R8XPDUY1SHZNEZP9GA4FEF1URNC3X45" . rand(1, 10000);
                    $content = RPC::curl($url, false, 0, 1);
                    $content = @json_decode($content, true);
                    $message = $content["message"];
                } else if ($currency->type == 'btc') {
                    $url = 'http://47.92.148.83:82/wallet/btc/balance?address=' . $address;
                    $content = RPC::curl($url, false, 0, 0);
                    $content = @json_decode($content, true);
                    if (isset($content["code"]) && $content["code"] == 0) {
                        $content["result"] = $content['data']['balance'];
                    }

                    $code = $content["code"];
                } else if ($currency->type == 'usdt') {
                    // echo $address;
                    $url = 'http://47.92.148.83:82/wallet/usdt/balance?address=' . $address;
                    $content = RPC::curl($url, false, 0, 0);
                    $content = @json_decode($content, true);
                    if (isset($content["code"]) && $content["code"] == 0) {
                        $content["result"] = $content['data']['balance'];
                    }
                }
                if (!$content) {
                    return false;
                }
                if (isset($content["message"]) && $content["message"] == "OK") {
                    $decimal = $currency->decimal_scale;//小数位
                    empty($decimal) && $decimal = 18;
                    echo $user_wallet->currency_name;
                    echo $content["result"];
                    $lessen = bc_pow(10, $decimal);
                    $content["result"] = bc_div($content["result"], $lessen, 8);
                    if ($content["result"] > $user_wallet->old_balance) {
                        $result = bc_sub($content["result"], $user_wallet->old_balance, 8);
                        $user_wallet->old_balance = $content["result"];
                        $user_wallet->save();
                        change_wallet_balance($user_wallet, 1, $result, AccountLog::ETH_EXCHANGE, '充币增加', false);
                    }
                }
                if (isset($content["code"]) && $content["code"] == 0) {
                    $content["result"] = bc_div($content["result"], 100000000, 8);
                    echo $user_wallet->currency_name;
                    echo $content["result"];
                    if ($content["result"] > $user_wallet->old_balance) {
                        $result = bc_sub($content["result"], $user_wallet->old_balance, 8);
                        $user_wallet->old_balance = $content["result"];
                        $user_wallet->save();
                        change_wallet_balance($user_wallet, 1, $result, AccountLog::ETH_EXCHANGE, '充币增加', false);
                    }
                }

            }

            DB::commit();
            return $this->success('更新成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage() . '-' . $exception->getFile() . '-' . $exception->getLine());
        }

    }

    public function mining()
    {
        $user_id = Users::getUserId();
        $user = Users::where('id', $user_id)->first();
        $num = UserAlgebra::where('user_id', $user_id)->sum('value');
        $count = Users::where('parent_id', $user_id)->where('level', '>=', 1)->count('id');
        $level = $user->level;
        $sum = Users::whereRaw("FIND_IN_SET(" . $user_id . ",parents_path)")->count('id');
        $data['sum'] = $sum;
        $data['count'] = $count;
        $data['level'] = $level;
        $data['num'] = $num;
        return $this->success($data);
    }


    public function test()
    {
        $lang = session('lang', 'en');
        App::setLocale($lang);
        $this->success_ceshi([1]);
    }
}

?>
