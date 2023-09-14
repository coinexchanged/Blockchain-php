<?php

use App\Setting;
use App\Users;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Route::get('testabcd',function(){
     Redis::set('testkey','haha');
     Redis::expire('testkey',100);
     return 'dasdsa';
 });
Route::get('api/newprice',function(Request $request){
    $symbol = strtolower($request->get("symbol"));
    
     $needlekey = "needle.market.{$symbol}.kline.1min";
     $obj = Redis::get($needlekey);
     if($obj)
     { }else{
         $rkey = "market.{$symbol}.kline.1min";
         $obj = Redis::get($rkey);
     }
                
    // $key = "market.{$symbol}.kline.1min";
    // $obj = Illuminate\Support\Facades\Redis::get($key);
    if($obj)
    {
        return json_encode(['type'=>'ok','message'=>$obj]);
    }else{
        return 'currency not exists';
    }
});

Route::any('/callback', function (Request $request) {
    $raw = file_get_contents('php://input');
    file_put_contents('/tmp/callback.txt' . time(), $raw);
    $obj = json_decode($raw, true);
    if ($obj) {
        $wallet = Db::table('users_wallet')->where('address', $obj['data']['to'])->first();
//        var_dump($wallet);
        if ($wallet->address) {

            if ($obj['data']['type'] === 1 && $obj['data']['state'] === 1) {

                if (strtolower($obj['name']) === 'usdt') {
                    $res = Db::table('users_wallet')->where('id', $wallet->id)->where('currency', 3)->update(['legal_balance' => $wallet->legal_balance + $obj['data']['amount'], 'currency' => 3]);

                    if ($res) {
                        Db::table('charge_req')->insert(['uid' => $wallet->user_id,
                            'amount' => $obj['data']['amount'],
                            'user_account' => $obj['data']['from'],
                            'status' => 2,
                            'currency_id' => 3,
                            'remark' => "交易哈希{$obj['data']['hash']},手续费{$obj['data']['fee_amount']}",
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        Db::table('account_log')->insert(['user_id' => $wallet->user_id,
                            'value' => $obj['data']['amount'],
                            'created_time' => time(),
                            'type' => 200,
                            'currency' => 3,
                            'info' => "充值成功，交易哈希{$obj['data']['hash']},手续费{$obj['data']['fee_amount']}，转出钱包{$obj['data']['from']}"
                        ]);

                    } else {
                        exit('failed');
                    }

                } else if (strtolower($obj['name']) === 'btc') {
                    $res = Db::table('users_wallet')->where('id', $wallet->id)->where('currency', 1)->update(['legal_balance' => $wallet->legal_balance + $obj['data']['amount']]);

                    if ($res) {

                        Db::table('charge_req')->insert(['uid' => $wallet->user_id,
                            'amount' => $obj['data']['amount'],
                            'user_account' => $obj['data']['from'],
                            'status' => 2,
                            'currency_id' => 1,
                            'remark' => "交易哈希{$obj['data']['hash']},手续费{$obj['data']['fee_amount']}",
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        Db::table('account_log')->insert(['user_id' => $wallet->user_id,
                            'value' => $obj['data']['amount'],
                            'created_time' => time(),
                            'type' => 200,
                            'currency' => 1,
                            'info' => "充值成功，交易哈希{$obj['data']['hash']},手续费{$obj['data']['fee_amount']}，转出钱包{$obj['data']['from']}"
                        ]);
                    } else {
                        exit('failed');
                    }
                }
            }

//            $data = array(
//                'appid' => trim(env('ZTPAY_APPID')),
//                'method' => "get_balance",
//                'name' => trim('USDT_ERC20'),
//                'address' => trim($address),
//            );
//            $data['sign'] = \App\Utils\ZtPay::getSign($data);
//            $res = \App\Utils\ZtPay::http_request($data);
//            if ($res) {
//                if ($res['code'] == 0) {
//                    if (intval($res['data']['state']) === 1) {
//                        if (intval($res['data']['type']) === 1) {
//                            //转入
//                        } else {
//                            //转出
//                        }
//                    }
//
//                    $amount = $res['data']['USDT'];
//                    Db::table('users_wallet')->where('address', $address)->update(['legal_balance' => $amount]);
//                }
//            }
        }


    }
    exit('success');

});
Route::get('/api/custorm', function (Request $request) {
    $c1 = Setting::getValueByKey('custorm_url_pc', 'javascript:alert("not yet");');
    $c2 = Setting::getValueByKey('custorm_url_mobile', 'javascript:alert("not yet");');
    $c3 = Setting::getValueByKey('custorm_url_recharge', 'javascript:alert("not yet");');
    $terminal = $request->get('terminal','');
    if($terminal=='mobile')
    {
        return $c2;
    }
    if($terminal==='recharge')
    {
        return $c3;
    }
    return $c1;
//    return ['c_pc' => $c1, 'c_mobile' => $c2, 'c_recharge' => $c3];
});
Route::get('/mobile/download.html', function (Request $request) {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
        return redirect(Setting::getValueByKey('ios_direct'));
    } else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return view('in_wechat');
        } else {
            return redirect(Setting::getValueByKey('android_direct'));
        }
    } else {
        return view('in_mobile');
    }
});

Route::group(['middleware' => ['lang', /*'check_user'*/]], function () {
    Route::post('api/set/lang', 'Api\DefaultController@language');//导入会员
    Route::post('api/get/lang', 'Api\DefaultController@getlanguage');//导入会员

    Route::get('/login', function () {
        session()->put('admin_username', '');
        session()->put('admin_id', '');
        session()->put('admin_role_id', '');
        session()->put('admin_is_super', '');
        return view('admin/login');
    });
    Route::get('/admin', function () {

        return redirect('/login');
    });

    Route::get('/phpinfo', function () {
        phpinfo();
    });

    Route::get('/', function () {
        $agent = new Jenssegers\Agent\Agent();
        if ($agent->isMobile()) {
            return redirect('/mobile');
        } else {
            return redirect('/app');
        }
    });

    Route::get('/api/trans', function (Request $request) {
        $from = $request->input('from');
        $to = $request->input('to');
        $val = $request->input('val');
        $rsp = translate($val, $to, $from);
        echo json_encode(['type' => 'ok', 'message' => $rsp]);
    });


    Route::get('/gzip', function (\Illuminate\Http\Request $request) {
        dump($request->getScheme());
        $protocol = $request->getScheme();
        $host = $request->getHost();

        dump($request->getSchemeAndHttpHost());
        dump(gzencode('hehe'));
    });
 Route::get('qr_create', 'Api\Qrpng@qr_create');
//******************************api接口不需要登录的**********************
//<--LDH-->
    Route::get('api/env.json', function () {
        $request = request();
        $result = \App\Utils\RPC::apihttp($request->getSchemeAndHttpHost() . '/env.json');
        return json_decode($result, true);
    });   //取env.json

     Route::get('fanyi', 'Api\Qrpng@fanyi');
    Route::post('api/user/import', 'Api\LoginController@import');//导入会员
    Route::post('api/user/login', 'Api\LoginController@login');//登录
    Route::post('api/user/register', 'Api\LoginController@register');//注册
    Route::post('api/user/forget', 'Api\LoginController@forgetPassword');//忘记密码
    Route::post('api/user/check_mobile', 'Api\LoginController@checkMobileCode');//验证短信验证码
    Route::post('api/user/check_email', 'Api\LoginController@checkEmailCode');//验证邮件验证码

    Route::post('/api/news/list', 'Api\NewsController@getArticle');//获取文章列表
    Route::post('/api/news/detail', 'Api\NewsController@get');//获取文章详情
    Route::post('/api/news/help', 'Api\NewsController@getCategory');//帮助中心分类
    Route::post('/api/news/recommend', 'Api\NewsController@recommend');//推荐文章

    Route::post('/api/news/get_invite_return_news', 'Api\NewsController@getInviteReturn');//获取邀请规则详情
    Route::get('/api/get_version', 'Api\DefaultController@getVersion');//获取版本号

    Route::post('/api/market/market', 'Api\MarketController@marketData');//行情数据
    Route::post('/api/sms_send', 'Api\SmsController@smsSubmailSend');//获取短信验证码 赛有短信
    // Route::post('/api/sms_mail', 'Api\SmsController@submail_sendMail'); //获取邮箱验证码
    //Route::post('/api/sms_send', 'Api\SmsController@smsBaoSend');//获取短信验证码
    Route::post('/api/sms_mail', 'Api\SmsController@sendMail'); //获取邮箱验证码

    Route::post('/api/upload', 'Api\DefaultController@upload');//上传图片接口
    Route::get('/api/qrcode', function (Request $request) {
        $qrCode = new Endroid\QrCode\QrCode($request->get('text'));

        header('Content-Type: ' . $qrCode->getContentType());
        echo $qrCode->writeString();
    });
    Route::any('/api/upload2', 'Api\DefaultController@upload2');//上传base64图片格式
    Route::post('/api/transaction/legal_list', 'Api\TransactionController@legalList');//法币交易市场
    Route::get('/api/seller_list', 'Api\SellerController@lists');//商家列表


    Route::get('api/legal_deal_platform', 'Api\LegalDealController@legalDealPlatform'); //商家发布法币交易信息列表
    Route::get('api/c2c_deal_platform', 'Api\C2cDealController@legalDealPlatform'); //用户发布c2c法币交易信息列表

    Route::get('api/currency/list', 'Api\CurrencyController@lists');//币种列表
    Route::get('api/currency/quotation', 'Api\CurrencyController@quotation');//币种列表带行情
    Route::any('api/currency/quotation_new', 'Api\CurrencyController@newQuotation'); //币种列表带行情(支持交易对)
    Route::post('api/deal/info', 'Api\CurrencyController@dealInfo');//行情详情

    Route::post('api/deal/market_k', 'Api\CurrencyController@market_k');//行情详情  测试接口

    Route::get('api/iepn', function () {
        $total = Setting::getValueByKey('iepn_total');
        $start_num = Setting::getValueByKey('iepn_total_start');
        $sum = Setting::getValueByKey('iepn_total_sum');
        $price = Setting::getValueByKey('iepn_price');
        $starttime = Setting::getValueByKey('iepn_start_time');
        $endtime = Setting::getValueByKey('iepn_end_time');
        $seconds = time() - strtotime($starttime);

        $days = $seconds % (3600 * 24) > 0 ? intval($seconds / (3600 * 24)) : (intval($seconds / (3600 * 24)) + 1);
        $yfx = $days * $sum + $start_num;
        if ($yfx > $total) {
            $yfx = $total;
        }
        $obj = ['type' => 'ok', 'message' => ['total' => $total, 'yfx' => $yfx, 'sy' => $total - $yfx, 'price' => $price, 'start' => date('m/d/Y', strtotime($starttime)), 'end' => date('m/d/Y', strtotime($endtime))]];
        echo json_encode($obj);
    });

//Route::any('api/currency/market_day', 'Api\CurrencyController@market_day');//当天行情
//Route::any('api/currency/new_timeshar', 'Api\CurrencyController@newTimeshars')->middleware(['cross']); //K线分时数据，对接tradeingview
    Route::get('api/currency/new_timeshar',
        'Api\CurrencyController@klineMarket')->middleware(['cross']); //K线分时数据，对接tradeingview
    Route::get('api/currency/new_tradehistory',
        'Api\CurrencyController@tradeMarket')->middleware(['cross']);
    Route::any('api/currency/kline_market',
        'Api\CurrencyController@klineMarket')->middleware(['cross']); //K线分时数据，对接tradeingview
    Route::any('api/currency/timeshar', 'Api\CurrencyController@timeshar');//分时
    Route::any('api/currency/fifteen_minutes', 'Api\CurrencyController@fifteen_minutes');//15分钟
    Route::any('api/currency/market_hour', 'Api\CurrencyController@market_hour');//一个小时
    Route::any('api/currency/four_hour', 'Api\CurrencyController@four_hour');//4个小时

    Route::any('api/currency/five_minutes', 'Api\CurrencyController@five_minutes');//5分钟
    Route::any('api/currency/thirty_minutes', 'Api\CurrencyController@thirty_minutes');//30分钟
    Route::any('api/currency/one_week', 'Api\CurrencyController@one_week');//一周
    Route::any('api/currency/one_month', 'Api\CurrencyController@one_month');//一个月

    Route::get('api/currency/lever', 'Api\CurrencyController@lever');//行情详情
    Route::any('api/user/into_users', 'Api\UserController@into_users');//导入用户
    Route::any('api/user/into_tra', 'Api\UserController@into_tra');//美丽链转入的接口(imc)
    Route::get('api/legalInfo', 'Api\UserController@getLegalInfo');
    Route::any('api/user/test', 'Api\UserController@test');//美丽链转入的接口(imc)
    Route::any('api/user/e_pwd', 'Api\UserController@e_pwd');//修改密码
    Route::any('api/currency/update_date', 'Api\CurrencyController@update_date');//测试
    Route::any('user/walletaddress', 'Api\UserController@walletaddress');//钱包地址

    Route::any('/test555', 'Api\PrizePoolController@test555');

    Route::any('api/area_code', 'Api\CurrencyController@area_code');//国家区号

    Route::get('api/kline', 'Api\MarketController@test');//行情详情
    Route::get('api/getLtcKMB', 'Api\WalletController@getLtcKMB');

    Route::post('api/getNode', 'Api\DefaultController@getNode');//节点关系
    Route::get('api/wallet/flashAgainstList', 'Api\WalletController@flashAgainstList'); //兑换列表

    Route::prefix('api')->post('user/real_name', 'Api\UserController@realName')->middleware([
        'demo_limit',
        'check_api'
    ]);//身份认证

    Route::get('mining_machine_daily_income_1_0', 'Api\MiningMachine@mining_machine_daily_income');//期货建仓用户日收益-服务器定时任务
    Route::get('mining_machine_refund_deposit_1_0', 'Api\MiningMachine@mining_machine_refund_deposit');//到期自动返还押金-服务器定时任务
    Route::get('online_service_list', 'Api\Qrpng@online_service_list');//获取在线客服列表
//<--LDHend-->
    Route::post('api/transaction/inApi', 'Api\TransactionController@inApi');//买入
    Route::post('api/transaction/outApi', 'Api\TransactionController@outApi');//卖出
    Route::post('api/out_log', 'Api\OutLog@out_log');
//******************************api接口需要登录的**********************
    Route::group(['prefix' => 'api', 'middleware' => ['check_api', /*'check_user'*/]], function () {
        Route::post('set_card', 'Api\Card@set_card');//提交发卡订单
        Route::get('get_card', 'Api\Card@get_card');//获取发卡订单
        Route::get('get_analyst_list', 'Api\Analyst@get_analyst_list');//获取分析师列表
        //期货建仓类
        Route::get('get_mining_machine_list', 'Api\MiningMachine@get_mining_machine_list');//查询期货列表
        Route::get('get_mining_machine_info', 'Api\MiningMachine@get_mining_machine_info');//查询期货信息
        Route::post('buy_mining_machine', 'Api\MiningMachine@buy_mining_machine');//购买期货
        Route::get('my_mining_machine', 'Api\MiningMachine@my_mining_machine');//期货订单
        Route::get('get_mining_incomes', 'Api\MiningMachine@get_mining_incomes');//期货收益
        Route::get('mining_machine_new_benefits', 'Api\MiningMachine@mining_machine_new_benefits');//注册送矿机福利
        
        Route::post('set_withdraw_password', 'Api\Qrpng@set_withdraw_password');//设置提现密码
        
        //chat
        Route::post('chat/bind', 'Api\ChatController@bind');//绑定client_id
        Route::post('chat/send', 'Api\ChatController@send');//发送信息
        Route::post('chat/get_chat_logs', 'Api\ChatController@getChatLog');//获取聊天日志
        Route::post('chat/get_unread_msg', 'Api\ChatController@getUnreadMsg');//获取未读信息

        //申请成为商家show_news
        Route::any('seller/show_news', 'Api\SellerController@show_news');
        Route::any('/seller/seller_add', 'Api\SellerController@postAdd')->middleware(['demo_limit']);
        Route::get('/currency/user_currency_list', 'Api\CurrencyController@userCurrencyList');

        //通证转账
        Route::any('show_candynum', 'Api\CandyTransferController@show_candynum');
        Route::any('transfer_candy', 'Api\CandyTransferController@transfer_candy');
        Route::any('show_transfer_candylist', 'Api\CandyTransferController@show_transfer_candylist');

        //资产兑换
        Route::any('/currency/currency_show', 'Api\UserController@currency_show');
        Route::any('/currency/currency_tousdt', 'Api\UserController@currency_tousdt');//
        Route::any('/currency/currency_tousdt_log', 'Api\UserController@currency_tousdt_log');//
        Route::get('update_balance', 'Api\UserController@updateBalance');//充币更新
        //通证兑换
        Route::any('/candy/candyshow', 'Api\UserController@candyshow');//通证显示
        Route::any('/candy/candy_tousdt', 'Api\UserController@candy_tousdt');//通证兑换

        Route::any('/candy/candyhistory', 'Api\PrizePoolController@candyhistory'); //通证奖励记录
        Route::any('/candy/candy_tousdthistory', 'Api\PrizePoolController@candy_tousdthistory'); //通证兑换usdt记录

        Route::any('/profits/show_profits', 'Api\AccountController@show_profits'); //盈亏返还记录
        Route::get('/charge_mention/log', 'Api\AccountController@chargeMentionMoney'); //充提记录
        Route::get('/legal_buy/log', 'Api\AccountController@legalBuy'); //充提记录
        Route::get('/legal_buy/detail', 'Api\AccountController@LegalBuyInfo'); //充提记录
        Route::post('/legal_buy/cancel', 'Api\AccountController@CancelBuyInfo');
        Route::post('/legal_buy/payed', 'Api\AccountController@PayBuyInfo');

        //个人中心//<--LDH-->
        Route::post('user/cash_info', 'Api\UserController@cashInfo')->middleware(['demo_limit']);//个人收款信息
        Route::post('user/cash_save', 'Api\UserController@saveCashInfo')->middleware(['demo_limit']);//添加修改收款方式
        Route::post('user/wallet_save', 'Api\UserController@saveWalletInfo')->middleware(['demo_limit']);//添加修改收款方式
        Route::get('user/get_wallet', 'Api\UserController@getWalletInfo')->middleware(['demo_limit']);//添加修改收款方式
        Route::post('/checkpassword', 'Api\UserController@checkPayPassword');//验证法币交易密码
        Route::get('user/extension', 'Api\UserController@getExtension');
        //反馈建议
        Route::post('/feedback/list', 'Api\FeedBackController@myFeedBackList');//反馈信息列表
        Route::post('/feedback/detail', 'Api\FeedBackController@feedBackDetail');//反馈信息内容，包括回复信息
        Route::post('/feedback/add', 'Api\FeedBackController@feedBackAdd');//添加反馈信息
        //安全中心
        Route::post('safe/safe_center', 'Api\UserController@safeCenter');//安全中心绑定信息
        Route::post('safe/gesture_add', 'Api\UserController@gesturePassAdd');//添加手势密码
        Route::post('safe/gesture_del', 'Api\UserController@gesturePassDel');//删除手势密码
        Route::post('safe/update_password', 'Api\UserController@updatePayPassword');//修改交易密码
        Route::post('safe/mobile', 'Api\UserController@setMobile');//绑定电话
        Route::post('safe/email', 'Api\UserController@setEmail'); //绑定邮箱
        Route::get('mining', 'Api\UserController@mining'); //绑定邮箱
        //资产
        Route::post('wallet/list', 'Api\WalletController@walletList');//用户账户资产信息
        Route::post('wallet/detail', 'Api\WalletController@getWalletDetail');//用户账户资产详情
        //Route::post('wallet/change', 'Api\WalletController@changeWallet')->middleware(['demo_limit']);//账户划转
        Route::post('wallet/change', 'Api\WalletController@changeWallet');//账户划转
        Route::any('wallet/hzhistory', 'Api\WalletController@hzhistory');//账户历史记录

        Route::post('wallet/charge_req', 'Api\WalletController@chargeReq');
        Route::post('wallet/buy_legal', 'Api\WalletController@buyLegal');
        Route::post('wallet/get_info', 'Api\WalletController@getCurrencyInfo');//获取提币信息
        Route::post('wallet/get_address', 'Api\WalletController@getAddressByCurrency');//获取提币地址
        Route::post('wallet/out', 'Api\WalletController@postWalletOut')->middleware([
            'demo_limit',
            'validate_locked',
            'lever_hold_check',
            'check_user'
        ]);//提交提币信息
        Route::post('wallet/get_in_address',
            'Api\WalletController@getWalletAddressIn')->middleware(['demo_limit']);//充币地址
        Route::post('wallet/legal_log', 'Api\WalletController@legalLog');//财务记录
        Route::any('wallet/out_log', 'Api\WalletController@walletOutLog');//提币记录


        Route::post('/wallet/flashAgainst', 'Api\WalletController@flashAgainst')->middleware('validate_locked');//闪兑
        Route::get('/wallet/myFlashAgainstList', 'Api\WalletController@myFlashAgainstList'); //我的闪兑列表

        Route::any('wallet/my_conversion', 'Api\WalletController@myConversion');//USDT兑换BMB列表
        Route::any('wallet/conversion_list', 'Api\WalletController@conversionList');//USDT兑换BMB列表
        Route::post('wallet/conversion', 'Api\WalletController@conversion');//USDT兑换BMB
        Route::post('wallet/conversion_set', 'Api\WalletController@conversionSet');//USDT兑换BMB

        //交易记录
        Route::post('transaction_in', 'Api\TransactionController@TransactionInList');
        Route::post('transaction_out', 'Api\TransactionController@TransactionOutList');
        Route::post('transaction_complete', 'Api\TransactionController@TransactionCompleteList');
        Route::post('transaction_del', 'Api\TransactionController@TransactionDel');//取消交易

        //<--LDHend-->


        Route::get('/test', 'Api\UserController@test');


        Route::get('/index', 'Api\DefaultController@index');
        // Route::get('/get_version','Api\DefaultController@getVersion');
        //发送短信

        Route::post('/user/vip', 'Api\UserController@vip');

        //<--LDH-->
//     Route::get('/bank','Api\UserController@bankList');//获取收款方式

        // Route::post('/payment/add','Api\UserController@paymentAdd');//添加收款方式
        // Route::post('/payment/del','Api\UserController@paymentDel'); //删除收款方式
        // Route::post('/payment/status','Api\UserController@paymentStatus');//是否启用收款方式
        //
        //<--LDHend-->


        Route::post('/historical_data', 'Api\DefaultController@historicalData');


        Route::post('/quotation', 'Api\DefaultController@quotation');
        Route::post('/quotation/info', 'Api\DefaultController@quotationInfo');

        Route::post('/transaction/revoke', 'Api\TransactionController@revoke');//撤销委托

        Route::post('/transaction/entrust', 'Api\TransactionController@entrust');//当前委托
        Route::post('/transaction/entrustlog', 'Api\TransactionController@entrustlog');//历史委托
        Route::post('/transaction/deal', 'Api\TransactionController@deal');//deal
        Route::post('/transaction/in', 'Api\TransactionController@in')->middleware('validate_locked');//买入
        Route::post('/transaction/out', 'Api\TransactionController@out')->middleware('validate_locked');//卖出
       

        Route::post('/lever/deal', 'Api\LeverController@deal'); //杠杆deal
        Route::post('/lever/dealall', 'Api\LeverController@dealAll'); //杠杆全部
        Route::post('/lever/submit',
            ['uses' => 'Api\LeverController@submit', 'middleware' => ['validate_locked', 'check_user']]); //杠杆下单
        Route::post('/lever/close', ['uses' => 'Api\LeverController@close', 'middleware' => ['validate_locked', 'check_user']]); //杠杆平仓
        Route::post('/lever/cancel', ['uses' => 'Api\LeverController@cancelTrade', 'middleware' => ['validate_locked', 'check_user']]); //撤销委托(取消)
        Route::post('/lever/batch_close', ['uses' => 'Api\LeverController@batchCloseByType', 'middleware' => ['validate_locked', 'check_user']]); //一键平仓
        Route::post('/lever/setstop', 'Api\LeverController@setStopPrice'); //设置止盈止损价
        Route::post('/lever/my_trade', 'Api\LeverController@myTrade'); //我的交易记录

        Route::post('/false/data', 'Api\DefaultController@falseData');//虚拟数据
        Route::post('/data/graph', 'Api\DefaultController@dataGraph');//数据图

        Route::get('/money/exit', 'Api\WalletController@moneyExit');
        Route::post('/money/exit', 'Api\WalletController@doMoneyExit');

        Route::get('/money/rechange', 'Api\WalletController@moneyRechange');
        Route::post('/wallet/add', 'Api\WalletController@add');
        // Route::get('/wallet/list','Api\WalletController@list');
        Route::get('/wallet/lista', 'Api\WalletController@lista');

        Route::post('/t/add', 'Api\TransactionController@tadd');//提交交易

        Route::post('/account/list', 'Api\AccountController@list');//账目明细
        Route::post('/transaction/add', 'Api\TransactionController@add');//提交交易
        Route::post('/transaction/list', 'Api\TransactionController@list');//交易列表
        Route::post('/transaction/info', 'Api\TransactionController@info');//交易详情

        Route::post('/user/update_address', 'Api\UserController@updateAddress');//更新地址
        Route::post('/user/getuserbyaddress', 'Api\UserController@getUserByAddress');//根据地址获取用户信息

        Route::post('/user/chat', 'Api\UserController@sendchat');//发送聊天
        Route::post('/user/chatlist', 'Api\UserController@chatlist');//发送聊天

        Route::any('handle_one', [
            'uses' => 'Api\LegalDealController@handle_one',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //倒计时结束 请求取消订单
        Route::post('legal_send', 'Api\LegalDealController@postSend')->middleware([
            'demo_limit',
            'validate_locked'
        ]); //商家发布法币交易信息
        Route::get('legal_deal_info', [
            'uses' => 'Api\LegalDealController@legalDealSendInfo',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易信息详情
        Route::post('do_legal_deal', [
            'uses' => 'Api\LegalDealController@doDeal',
            'middleware' => ['check_user', 'demo_limit', 'validate_locked']
        ]); //法币交易信息详情
        Route::get('legal_seller_deal', [
            'uses' => 'Api\LegalDealController@sellerLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易商家端交易列表
        Route::get('legal_user_deal', [
            'uses' => 'Api\LegalDealController@userLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易用户端交易列表

        Route::any('seller_legal_user_deal', [
            'uses' => 'Api\LegalDealController@sellerUserLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]); //作为商家订单信息

        Route::get('seller_info', 'Api\LegalDealController@sellerInfo')->middleware(['demo_limit']); //商家详情信息
        Route::get('seller_trade', 'Api\LegalDealController@tradeList')->middleware(['demo_limit']); //商家交易


        Route::get('legal_deal', [
            'uses' => 'Api\LegalDealController@legalDealInfo',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //交易详情信息
        Route::post('user_legal_pay', [
            'uses' => 'Api\LegalDealController@userLegalDealPay',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //法币交易用户确认付款
        Route::post('user_legal_pay_cancel', [
            'uses' => 'Api\LegalDealController@userLegalDealCancel',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);


        Route::get('my_seller', [
            'uses' => 'Api\LegalDealController@mySellerList',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //我的商铺
        Route::get('legal_send_deal_list', [
            'uses' => 'Api\LegalDealController@legalDealSellerList',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //发布交易列表
        Route::post('legal_deal_sure', [
            'uses' => 'Api\LegalDealController@doSure',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //商家确认收款
        Route::post('legal_deal_user_sure', [
            'uses' => 'Api\LegalDealController@userDoSure',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //用户确认收款
        Route::post('back_send', [
            'uses' => 'Api\LegalDealController@backSend',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //商家撤回发布
        Route::post('error_send', [
            'uses' => 'Api\LegalDealController@errorSend',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //商家撤回异常发布

        Route::post('down_send', 'Api\LegalDealController@down')->middleware(['demo_limit', 'check_user', 'validate_locked']); //商家下架发布


        Route::post('user/invite_list', 'Api\UserController@inviteList')->middleware(['demo_limit']);//邀请返佣榜单
        Route::get('user/invite', 'Api\UserController@invite')->middleware(['demo_limit']);//我的邀请

        Route::post('user/my_invite_list', 'Api\UserController@myInviteList')->middleware(['demo_limit']);//我的邀请会员列表
        Route::post('user/my_account_return',
            'Api\UserController@myAccountReturn')->middleware(['demo_limit']);//我的邀请返佣列表
        Route::get('user/my_poster', 'Api\UserController@posterBg')->middleware(['demo_limit']);//我的专属海报
        Route::get('user/my_share', 'Api\UserController@share')->middleware(['demo_limit']);//邀请好友分享

        //钱包地址
        //Route::any('user/walletaddress','Api\UserController@walletaddress');

        Route::get('user/info', 'Api\UserController@info');//我的
        Route::get('user/center', 'Api\UserController@userCenter');//个人中心
        Route::get('user/logout', 'Api\UserController@logout');//退出登录
        Route::post('user/setaccount', 'Api\UserController@setAccount')->middleware(['demo_limit']);//设置法币交易账号

        Route::get('/wallet/currencylist', 'Api\WalletController@currencyList');//币种列表
        Route::post('/wallet/addaddress', 'Api\WalletController@addAddress');//添加提币地址
        Route::post('/wallet/deladdress', 'Api\WalletController@addressDel');//删除提币地址

        Route::get('/transaction/checkinout', 'Api\TransactionController@checkInOut');//验证法币交易购买 出售按钮
        Route::get('/user/into_tra_log', 'Api\UserController@into_tra_log');//用户转入记录

        //转账给矿机余额
        Route::post('sendLtcKMB', 'Api\WalletController@sendLtcKMB');
        //获取PB的交易余额
        Route::get('PB', 'Api\WalletController@PB');

        //钱包需要的接口
        //Route::post('/transaction/deal', 'Api\TransactionController@deal');//deal
        Route::post('/transaction/walletIn', 'Api\TransactionController@walletIn')->middleware(['demo_limit']);//买入
        Route::post('/transaction/walletOut', 'Api\TransactionController@walletOut')->middleware(['demo_limit']);//卖出
        Route::get('/transaction/balance', 'Api\TransactionController@balance')->middleware(['demo_limit']);//卖出
        Route::post('wallet/ltcSend', 'Api\WalletController@ltcSend')->middleware(['demo_limit']);//
        Route::post('wallet_add', 'Api\WalletOneController@add');//
        Route::get('new/walletList', 'Api\WalletOneController@walletList');//
        Route::get('new/money/rechange', 'Api\WalletOneController@moneyRechange');
        Route::post('account/newlist', 'Api\WalletOneController@accountList');
        Route::post('transaction/newadd', 'Api\WalletOneController@walletChange');
        Route::post('get/userinfo', 'Api\WalletOneController@getInfo');

        //c2c交易
        Route::post('c2c_send', [
            'uses' => 'Api\C2cDealController@postSend',
            'middleware' => ['check_user', 'validate_locked']
        ])->middleware(['demo_limit']); //用户发布交易信息
        Route::get('c2c_deal_info', [
            'uses' => 'Api\C2cDealController@legalDealSendInfo',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']); //c2c法币交易信息详情
        Route::post('c2c/do_legal_deal', [
            'uses' => 'Api\C2cDealController@doDeal',
            'middleware' => ['check_user', 'demo_limit', 'validate_locked']
        ]); //法币交易信息详情
        Route::post('wallet/real_name', 'Api\UserController@walletRealName');//钱包身份认证
        Route::get('c2c/seller_info', 'Api\C2cDealController@sellerInfo')->middleware(['demo_limit']); //用户c2c店铺详情信息
        Route::get('c2c/seller_trade', 'Api\C2cDealController@tradeList')->middleware(['demo_limit']); //我的发布交易列表
        Route::get('c2c_seller_deal', [
            'uses' => 'Api\C2cDealController@sellerLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易商家端交易列表
        Route::get('c2c_user_deal', [
            'uses' => 'Api\C2cDealController@userLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易用户端交易列表
        Route::get('c2c_deal',
            ['uses' => 'Api\C2cDealController@legalDealInfo', 'middleware' => ['demo_limit', 'check_user']]); //交易详情信息
        Route::post('c2c/user_legal_pay_cancel', [
            'uses' => 'Api\C2cDealController@userLegalDealCancel',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易用户取消订单

        Route::post('c2c/user_vv',
            ['uses' => 'Api\C2cDealController@handle', 'middleware' => ['check_user', 'demo_limit']]); //法币交易用户取消订单

        Route::post('c2c/user_legal_pay', [
            'uses' => 'Api\C2cDealController@userLegalDealPay',
            'middleware' => ['check_user', 'demo_limit']
        ]); //法币交易用户确认付款
        Route::post('c2c/legal_deal_sure',
            ['uses' => 'Api\C2cDealController@doSure', 'middleware' => ['check_user', 'demo_limit']]); //商家确认收款
        Route::post('c2c/legal_deal_user_sure',
            ['uses' => 'Api\C2cDealController@userDoSure', 'middleware' => ['check_user', 'demo_limit']]); //用户确认收款
        Route::post('c2c/back_send',
            ['uses' => 'Api\C2cDealController@backSend', 'middleware' => ['check_user', 'demo_limit']]); //商家撤回发布
        Route::get('c2c/legal_send_deal_list', [
            'uses' => 'Api\C2cDealController@legalDealSellerList',
            'middleware' => ['check_user', 'demo_limit']
        ]); //发布交易列表

        //秒合约路由
        Route::prefix('microtrade')->namespace('Api')->group(function () {
            Route::get('payable_currencies', 'MicroOrderController@getPayableCurrencies'); //可支付的币种列表
            Route::get('seconds', 'MicroOrderController@getSeconds'); //到期时间
            Route::post('submit', 'MicroOrderController@submit')->middleware('validate_locked', 'check_user'); //提交下单
            Route::get('lists', 'MicroOrderController@lists')->middleware('validate_locked'); //下单记录
        });

        Route::prefix('insurance')->namespace('Api')->group(function () {
            Route::post('buy_insurance', 'InsuranceController@buyInsurance'); //购买保险
            Route::post('get_insurance_type', 'InsuranceController@getInsuranceType'); //获取币种保险类型
            Route::post('get_user_currency_insurance', 'InsuranceController@getUserCurrencyInsurance'); //获取币种保险类型
            Route::post('claim_apply', 'InsuranceController@claimApply'); //保险申请索赔
            Route::post('manual_rescission', 'InsuranceController@manualRescission'); //手动解约
        });

        //持险生币
        Route::get('insurance_money', 'Api\WalletController@Insurancemoney');
        Route::get('insurance_money_logs', 'Api\WalletController@Insurancemoneylogs');

    });
    Route::post('api/user/walletRegister', 'Api\LoginController@walletRegister');//钱包注册
    Route::get('api/ltcGet', 'Api\WalletController@ltcGet');//钱包获取交易所的转账
    Route::post('/admin/login', 'Admin\DefaultController@login');
    Route::get('new/walletList', 'Api\WalletOneController@walletList');//

    Route::post('/admin/login', 'Admin\DefaultController@login');
//Route::get('/admin/login1', 'Admin\DefaultController@login1');
//管理后台
    Route::group(['prefix' => 'winadmin', 'middleware' => ['admin_auth']], function () {
        Route::get('/index', 'Admin\DefaultController@index');
    });
//管理后台
    Route::group(['prefix' => 'admin', 'middleware' => ['admin_auth']], function () {
        Route::any('analysis/index', 'Admin\Analysis@index');
        Route::any('analysis/list', 'Admin\Analysis@getlist');
        Route::any('analysis/edit', 'Admin\Analysis@edit');
        Route::any('analysis/delete', 'Admin\Analysis@delete');
        Route::any('analysis/add', 'Admin\Analysis@add');
        Route::any('analysis/post_add', 'Admin\Analysis@post_add');
        Route::any('analysis/postedit', 'Admin\Analysis@postedit');
        Route::any('analysis/jilu', 'Admin\Analysis@jilu');
        Route::any('analysis/jilu_list', 'Admin\Analysis@jilu_list');
        Route::any('analysis/jilu_edit', 'Admin\Analysis@jilu_edit');
        Route::any('analysis/post_jilu_edit', 'Admin\Analysis@post_jilu_edit');
        Route::any('analysis/jilu_delete', 'Admin\Analysis@jilu_delete');
        Route::any('analysis/jilu_add', 'Admin\Analysis@jilu_add');
        Route::any('analysis/post_jilu_add', 'Admin\Analysis@post_jilu_add');
        
        Route::any('ltc/index', 'Admin\LtcController@index');
        Route::any('ltc/list', 'Admin\LtcController@lists');
        Route::any('ltc/add', 'Admin\LtcController@add');
        Route::any('ltc/postAdd', 'Admin\LtcController@postAdd');
        Route::any('ltc/edit', 'Admin\LtcController@edit');
        Route::any('ltc/del', 'Admin\LtcController@del');
        Route::any('ltc/ltcBuy', 'Admin\LtcController@ltcBuy');
        Route::any('ltc/buyList', 'Admin\LtcController@buyList');
        
        Route::any('faka/index', 'Admin\FaKa@index');
        Route::any('faka/list', 'Admin\FaKa@getlist');
        Route::any('faka/edit', 'Admin\FaKa@edit');
        Route::any('faka/postedit', 'Admin\FaKa@postedit');
        Route::any('faka/delete', 'Admin\FaKa@delete');
        
        Route::any('legal/store', 'Admin\LegalOrder@store');
        Route::any('legal/confirm', 'Admin\LegalOrder@confirm');
        Route::any('legal/cancel', 'Admin\LegalOrder@cancel');
        Route::any('legal/delete', 'Admin\LegalOrder@delete');
        Route::any('legal/order', 'Admin\LegalOrder@order');
        Route::any('legalstore/list_data','Admin\LegalOrder@storeList');
        Route::any('legalstore/add','Admin\LegalOrder@storeAdd');
        Route::post('user/setStore',function(Request $request){
            Users::whereIn('id',$request->post('ids'))->update(['store_id'=>$request->post('store_id')]);
        });
        Route::any('exchange/index', 'Admin\Exchange@index');

        Route::any('needle/all_needle', 'Admin\Needle@index');
        Route::any('myquotation/all', 'Admin\MyQuotation@index');
        Route::get('myquotation/list', 'Admin\MyQuotation@lists');
        Route::post('myquotation/delete','Admin\MyQuotation@delete');
        Route::get('myquotation/reset','Admin\MyQuotation@reset');
        Route::get('needle/add', function () {
            return view('admin.needle.add');
        });
        Route::get('user/quotation', 'Admin\UserController@quotation');
        Route::post('user/quotation', 'Admin\UserController@saveQuotation');
        Route::post('needle/add', function (Request $request) {
            $needle = new \App\Needle();
            $needle->open = $request->input('open');
            $needle->close = $request->input('close');
            $needle->high = $request->input('high');
            $needle->low = $request->input('low');
            $needle->base = $request->input('base');
            $needle->target = $request->input('target');
            $needle->symbol = "{$needle->base}/{$needle->target}";
            $needle->itime = $request->input('itime');
            $needle->save();
            return response()->json(['type' => 'ok', 'message' => '添加成功']);
        });
        Route::delete('needle/del', function (Request $request) {
            \App\Needle::find($request->input('id'))->delete();
            return response()->json(['type' => 'ok', 'message' => '添加成功']);
        });
        Route::post('app/save', function (Request $request) {

            $save_path = dirname(__DIR__) . '/public/static';

            $ifile = $_FILES['ifile'] ?? false;

            if (is_array($ifile)) {
                rename($ifile['tmp_name'], $save_path . '/IFTIEX.ipa');
            }

//            var_dump(IA_ROOT);
            $afile = $_FILES['afile'] ?? false;
            if (is_array($afile)) {
                rename($afile['tmp_name'], $save_path . '/IFTIEX.apk');
//                rename($afile['tmp_'])
            }

            echo 'ok';

        });
        Route::any('ueditor/uploader', 'Admin\UeditorController@ueditor');
        Route::get('/safe/verificationcode', 'Admin\DefaultController@getVerificationCode');
        //闪兑
        Route::get('/flashagainst/index', 'Admin\FlashAgainstController@index');
        Route::get('/flashagainst/list', 'Admin\FlashAgainstController@lists');
        Route::post('/flashagainst/affirm', 'Admin\FlashAgainstController@affirm');
        Route::post('/flashagainst/reject', 'Admin\FlashAgainstController@reject');

        //成为商家审核
        Route::any('/seller/status', 'Admin\SellerController@status');
        //通证记录
        Route::get('/candytransfer/detail', 'Admin\CandyTransferController@feedBackDetail');
        Route::get('/candytransfer/del', 'Admin\CandyTransferController@feedBackDel');
        Route::post('/candytransfer/reply', 'Admin\CandyTransferController@reply');
        Route::get('/candytransfer/index', 'Admin\CandyTransferController@index');
        Route::get('/candytransfer/list', 'Admin\CandyTransferController@candytransfer_List');

        Route::any('admin_legal_pay_cancel', 'Admin\LegalDealController@adminLegalDealCancel'); //法币交易用户取消订单
        Route::any('legal_deal_admin_sure', 'Admin\LegalDealController@adminDoSure'); //商家确认收款
        Route::post('legal_deal_admin_user_sure', 'Admin\LegalDealController@admin_userDoSure'); //用户确认收款

        Route::any('Leverdeals/Leverdeals_show', 'Admin\TransactionController@Leverdeals_show');
        Route::any('Leverdeals/list', 'Admin\TransactionController@Leverdeals');//杠杆交易 团队所有订单
        Route::get('Leverdeals/csv', 'Admin\TransactionController@csv');//导出杠杆交易 团队所有订单

        ///LDH
        Route::get('/legal', 'Admin\LegalDealSendController@index')->middleware(['demo_limit']);
        Route::get('/legal/list', 'Admin\LegalDealSendController@list');
        Route::get('/legal_deal', 'Admin\LegalDealController@index')->middleware(['demo_limit']);
        Route::get('/legal_deal/list', 'Admin\LegalDealController@list');
        //C2C
        Route::get('/c2c', 'Admin\C2cDealSendController@index')->middleware(['demo_limit']);
        Route::get('/c2c/list', 'Admin\C2cDealSendController@list');
        Route::get('/c2c_deal', 'Admin\C2cDealController@index')->middleware(['demo_limit']);
        Route::get('/c2c_deal/list', 'Admin\C2cDealController@list');
        Route::post('c2c/send/back', 'Admin\C2cDealSendController@sendBack');//撤回发布
        Route::post('c2c/send/del', 'Admin\C2cDealSendController@sendDel');//删除

        //投诉建议
        Route::get('/feedback/detail', 'Admin\FeedBackController@feedBackDetail');
        Route::get('/feedback/del', 'Admin\FeedBackController@feedBackDel');
        Route::post('/feedback/reply', 'Admin\FeedBackController@reply');
        Route::get('/feedback/index', 'Admin\FeedBackController@index');
        Route::get('/feedback/list', 'Admin\FeedBackController@feedbackList');
        //系统设置
        Route::get('/setting/index', 'Admin\SettingController@index');//设置首页
        Route::get('/setting/list', 'Admin\SettingController@list');//设置首页
        Route::get('/setting/add', 'Admin\SettingController@add');//设置奖金
        Route::post('/setting/postadd', 'Admin\SettingController@postAdd');//设置奖金
        Route::get('/setting/set_base', 'Admin\SettingController@base');//基础设置
        Route::post('/setting/basesite', 'Admin\SettingController@setBase');//提交基础设置
        Route::get('/setting/data/index', 'Admin\SettingController@dataSetting');//提交基础设置
        //提币
        Route::get('cashb', 'Admin\CashbController@index')->middleware(['demo_limit']);
        Route::get('cashb_list', 'Admin\CashbController@cashbList');
        Route::get('cashb_show', 'Admin\CashbController@show')->middleware(['demo_limit']);//提币详情页面
        Route::post('cashb_done', 'Admin\CashbController@done')->middleware(['demo_limit']);//确认提币成功
        Route::get('cashb_back', 'Admin\CashbController@back')->middleware(['demo_limit']);//执行退回申请
        //导出数据到excel文件
        Route::get('/user/csv', 'Admin\UserController@csv')->middleware(['demo_limit']);//导出会员
        Route::get('/cashb/csv', 'Admin\CashbController@csv')->middleware(['demo_limit']);//导出提币记录
        Route::get('/feedback/csv', 'Admin\FeedBackController@csv')->middleware(['demo_limit']);//导出提币记录
        Route::get('/c2c_deal/csv', 'Admin\C2cDealController@csv')->middleware(['demo_limit']);//导出c2c交易信息

        ////TransactionLegal
        Route::get('/index', 'Admin\DefaultController@indexnew');
        Route::get('/user/user_index', 'Admin\UserController@index');
        Route::get('/user/list', 'Admin\UserController@lists');
        Route::get('/user/users_wallet', 'Admin\UserController@wallet');
        Route::get('/user/users_wallet_sync', function (Request $request) {
            $user_id = $request->input('id');
            UsersWallet::makeWallet($user_id);
            echo json_encode(['message' => '操作完成']);

        });


        Route::get('/user/walletList', 'Admin\UserController@walletList');
        Route::post('/user/wallet_lock', 'Admin\UserController@walletLock');//钱包锁定

        Route::get('/user/conf', 'Admin\UserController@conf');
        Route::post('/user/conf', 'Admin\UserController@postConf');//调节钱包账户
        Route::post('/user/del', 'Admin\UserController@del')->middleware(['demo_limit']); //删除用户
        Route::post('/user/delw', 'Admin\UserController@delw')->middleware(['demo_limit']); //删除指定id钱包
        // Route::post('/user/lock', 'Admin\UserController@lock')->middleware(['demo_limit']);//账号锁定
        Route::get('/user/lock', 'Admin\UserController@lockUser');
        Route::post('/user/lock', 'Admin\UserController@dolock');

        Route::post('/user/blacklist', 'Admin\UserController@blacklist')->middleware(['demo_limit']);//加入黑名单
        Route::get('user/candy_conf/{id}', 'Admin\UserController@candyConf'); //
        Route::post('user/candy_conf/{id}', 'Admin\UserController@postCandyConf'); //

        Route::get('/user/address', 'Admin\UserController@address');//提币地址信息
        Route::post('/user/address_edit', 'Admin\UserController@addressEdit');//修改地址信息

        Route::get('/user/edit', 'Admin\UserController@edit');
        Route::post('/user/edit', 'Admin\UserController@doedit');


        Route::get('/user/editltc', 'Admin\UserController@editltc');
        Route::post('/user/editltc', 'Admin\UserController@doeditltc');
        Route::post('/user/batch_risk', 'Admin\UserController@batchRisk'); //链上余额归拢

        //实名认证管理
        Route::get('/user/real_index', 'Admin\UserRealController@index');
        Route::get('/user/real_list', 'Admin\UserRealController@list');
        Route::get('/user/real_info', 'Admin\UserRealController@detail');
        Route::post('/user/real_del', 'Admin\UserRealController@del');
        Route::post('/user/real_auth', 'Admin\UserRealController@auth');


        Route::get('/user/false_data', 'Admin\UserController@falseData');
        Route::get('/user/chart_data', 'Admin\UserController@chartData');
        Route::post('/user/chart_data', 'Admin\UserController@dochartData');

        Route::get('/user/falsedata_add', 'Admin\UserController@falsedataadd');
        Route::post('/user/falsedata_add', 'Admin\UserController@dofalsedataadd');
        Route::post('/user/falsedata_del', 'Admin\UserController@dofalsedatadel');
        Route::get('/user/falsedata', 'Admin\UserController@falsedatas');

        Route::get('/user/count_index', 'Admin\UserController@countData');
        Route::get('/account/account_index', 'Admin\AccountLogController@index');
        Route::get('/account/list', 'Admin\AccountLogController@lists');
        Route::get('/account/viewDetail', 'Admin\AccountLogController@view');

        //邀请返佣
        Route::get('/invite/account_return', 'Admin\InviteController@return');//邀请返佣
        Route::get('/invite/return_list', 'Admin\InviteController@returnList');//邀请返佣列表
        Route::get('/invite/childs', 'Admin\InviteController@childs');//会员邀请关系图
        Route::get('/invite/share', 'Admin\InviteController@share');//邀请分享设置
        Route::post('/invite/share', 'Admin\InviteController@postShare');//邀请分享设置提交

        Route::get('/invite/getTree', 'Admin\InviteController@getTree');//
        Route::post('/invite/del', 'Admin\InviteController@del');

        Route::get('/invite/edit', 'Admin\InviteController@edit');
        Route::post('/invite/edit', 'Admin\InviteController@doedit');
        Route::post('/invite/bgdel', 'Admin\InviteController@bgdel');


        Route::get('/transaction/tran_index', 'Admin\TransactionController@index');
        Route::get('/transaction/list', 'Admin\TransactionController@lists');

        //后台管理员
        Route::get('/manager/manager_index', function () {
            return view('admin.manager.index');
        });
        Route::get('/manager/users', 'Admin\AdminController@users');
        Route::get('/manager/add', 'Admin\AdminController@add');//添加管理员
        Route::post('/manager/add', 'Admin\AdminController@postAdd');//添加管理员
        Route::post('/manager/delete', 'Admin\AdminController@del');//删除管理员
        Route::get('/manager/manager_roles', function () {
            return view('admin.manager.admin_roles');
        });//角色管理
        Route::get('/manager/manager_roles_api', 'Admin\AdminRoleController@users');
        Route::get('/manager/role_add', 'Admin\AdminRoleController@add');
        Route::post('/manager/role_add', 'Admin\AdminRoleController@postAdd');
        Route::post('/manager/role_delete', 'Admin\AdminRoleController@del');
        Route::get('/manager/role_permission', 'Admin\AdminRolePermissionController@update');
        Route::post('/manager/role_permission', 'Admin\AdminRolePermissionController@postUpdate');

        //秒合约数量设置
        Route::get('/micro_number_index', function () {
            return view('admin.micro.index');
        });
        Route::get('/micro_number_add', 'Admin\MicroController@add');//添加设置
        Route::post('/micro_number_add', 'Admin\MicroController@postAdd');//添加设置
        Route::get('/micro_numbers_list', 'Admin\MicroController@lists');
        Route::post('/micro_number_del', 'Admin\MicroController@del');

        //秒合约秒数设置
        Route::get('/micro_seconds_index', function () {
            return view('admin.micro.seconds_index');
        });

        Route::get('/micro_seconds_add', 'Admin\MicroController@secondsAdd');//添加设置
        Route::post('/micro_seconds_add', 'Admin\MicroController@secondsPostAdd');//添加设置
        Route::get('/micro_seconds_list', 'Admin\MicroController@secondsLists');
        Route::post('/micro_seconds_status', 'Admin\MicroController@secondsStatus');
        Route::post('/micro_seconds_del', 'Admin\MicroController@secondsDel');
        //秒合约日志
        Route::get('/micro_order', 'Admin\MicroController@order');
        Route::get('/micro_order_list', 'Admin\MicroController@orderList');
        Route::get('/micro_order_edit', 'Admin\MicroController@edit');
        Route::post('/micro_order_edit', 'Admin\MicroController@editPost');
        Route::post('/micro/batch_risk', 'Admin\MicroController@batchRisk');

        //广告管理
        Route::get('/ad/ad_index', 'Admin\AdController@index');
        Route::get('/ad/list', 'Admin\AdController@lists');
        Route::get('/ad/edit', 'Admin\AdController@edit');
        Route::post('/ad/edit', 'Admin\AdController@doEdit');
        Route::post('/ad/del', 'Admin\AdController@del');
        Route::post('/ad/lock', 'Admin\AdController@lock');

        //广告位管理
        Route::get('/ad/position_index', 'Admin\AdController@positionIndex');
        Route::get('/ad/position_list', 'Admin\AdController@positionLists');
        Route::get('/ad/position_edit', 'Admin\AdController@positionEdit');
        Route::post('/ad/position_edit', 'Admin\AdController@doPositionEdit');
        Route::post('/ad/position_del', 'Admin\AdController@positionDel');
        Route::post('/ad/position_show', 'Admin\AdController@positionShow');

        //新闻路由
        Route::get('news_index', 'Admin\NewsController@index');
        Route::get('news_add', 'Admin\NewsController@add');
        Route::post('news_add', 'Admin\NewsController@postAdd');
        Route::get('news_edit/{id}', 'Admin\NewsController@edit');
        Route::post('news_edit/{id}', 'Admin\NewsController@postEdit');
        Route::get('news_del/{id}/{togetherDel?}', 'Admin\NewsController@del');
        //新闻分类路由
        Route::get('news_cate_index', 'Admin\NewsController@cateIndex');
        Route::get('news_cate_add', 'Admin\NewsController@cateAdd');
        Route::get('news_cate_list', 'Admin\NewsController@getCateList');
        Route::post('news_cate_add', 'Admin\NewsController@postCateAdd');
        Route::get('news_cate_edit/{id}', 'Admin\NewsController@cateEdit');
        Route::post('news_cate_edit/{id}', 'Admin\NewsController@postCateEdit');
        Route::get('news_cate_del/{id}', 'Admin\NewsController@cateDel');


        //商家
        Route::get('seller', 'Admin\SellerController@index');//商家首页
        Route::get('seller_list', 'Admin\SellerController@lists');
        Route::get('seller_add', 'Admin\SellerController@add')->middleware(['demo_limit']);
        Route::post('seller_add', 'Admin\SellerController@postAdd')->middleware(['demo_limit']);
        Route::post('seller_del', 'Admin\SellerController@delete')->middleware(['demo_limit']);
        Route::post('send/back', 'Admin\SellerController@sendBack');//撤回发布
        Route::post('send/del', 'Admin\SellerController@sendDel');//撤回发布
        Route::post('send/is_shelves', 'Admin\SellerController@is_shelves');//下架
        //交易
        Route::get('complete', 'Admin\TransactionController@completeIndex');
        Route::get('in', 'Admin\TransactionController@inIndex');
        Route::get('out', 'Admin\TransactionController@outIndex');
        Route::get('cny', 'Admin\TransactionController@cnyIndex');
        Route::get('complete_list', 'Admin\TransactionController@completeList');
        Route::get('in_list', 'Admin\TransactionController@inList');
        Route::get('out_list', 'Admin\TransactionController@outList');
        Route::get('cny_list', 'Admin\TransactionController@cnyList');
        //新充值
        Route::get('/user/charge_req', 'Admin\UserController@chargeReq');//提币申请
        Route::get('/user/charge_list', 'Admin\UserController@chargeList');
        Route::post('/user/pass_req', 'Admin\UserController@passReq');
        Route::post('/user/refuse_req', 'Admin\UserController@refuseReq');
        //币种
        Route::get('currency', 'Admin\CurrencyController@index');//首页
        Route::post('/is_insurancable', 'Admin\CurrencyController@isInsurancable');
        Route::get('currency_add', 'Admin\CurrencyController@add')->middleware(['demo_limit']);//添加币种
        Route::post('currency_add', 'Admin\CurrencyController@postAdd')->middleware(['demo_limit']);//添加币种
        Route::get('currency_list', 'Admin\CurrencyController@lists');//币种
        Route::post('currency_del', 'Admin\CurrencyController@delete')->middleware(['demo_limit']);//删除币种
        Route::post('currency_display', 'Admin\CurrencyController@isDisplay');//币种显示
        Route::post('currency_execute', 'Admin\CurrencyController@executeCurrency');//币种显示
        Route::get('currency/match/{legal_id}', 'Admin\CurrencyController@match'); //交易对
        Route::get('currency/match_list/{legal_id}', 'Admin\CurrencyController@matchList'); //交易对列表
        Route::get('currency/match_add/{legal_id}', 'Admin\CurrencyController@addMatch'); //添加交易对页
        Route::post('currency/match_add/{legal_id}',
            'Admin\CurrencyController@postAddMatch')->middleware(['demo_limit']); //添加交易对
        Route::get('currency/match_edit/{id}', 'Admin\CurrencyController@editMatch'); //编辑交易对页
        Route::post('currency/match_edit/{id}', 'Admin\CurrencyController@postEditMatch'); //编辑交易对
        Route::any('currency/match_del/{id}', 'Admin\CurrencyController@delMatch')->middleware(['demo_limit']); //删除交易对
        Route::get('currency/micro_match', 'Admin\CurrencyController@microMatch'); //微交易交易对
        Route::get('currency/micro_match_list', 'Admin\CurrencyController@microMatchList'); //微交易交易对
        Route::post('currency/micro_risk', 'Admin\CurrencyController@microRisk');

        Route::get('currency/set_in_address/{id}', 'Admin\CurrencyController@setInAddress')->middleware(['demo_limit']);
        Route::get('currency/set_out_address/{id}', 'Admin\CurrencyController@setOutAddress')->middleware(['demo_limit']);
        Route::post('currency/set_in_address', 'Admin\CurrencyController@postSetInAddress')->middleware(['demo_limit']);
        Route::post('currency/set_out_address', 'Admin\CurrencyController@postSetOutAddress')->middleware(['demo_limit']);

        //行情数据
        Route::get('market', 'Admin\MarketController@index');//首页
        Route::get('market_add', 'Admin\MarketController@add');//添加行情
        Route::post('market_add', 'Admin\MarketController@postAdd');//添加行情
        Route::get('market_list', 'Admin\MarketController@lists');//行情
        Route::post('market_del', 'Admin\MarketController@delete');//删除行情
        Route::post('market_display', 'Admin\MarketController@isDisplay');//行情显示

        //虚拟下单
        Route::get('auto_index', 'Admin\AutoController@index');//首页
        Route::get('auto_add', 'Admin\AutoController@add');//添加机器人
        Route::post('auto_add', 'Admin\AutoController@postAdd');//添加机器人
        Route::get('auto_list', 'Admin\AutoController@lists');//添加机器人
        Route::post('auto_start', 'Admin\AutoController@postStart');//开启机器人

        //下单机器人
        Route::any('robot/add', 'Admin\RobotController@add');//添加机器人
        Route::any('robot/list', 'Admin\RobotController@list');//机器人列表
        Route::any('robot/list_data', 'Admin\RobotController@listData');//机器人列表
        Route::any('robot/delete', 'Admin\RobotController@delete');//删除机器人
        Route::any('robot/start', 'Admin\RobotController@start');//开启关闭机器人

        Route::any('robot/sche', 'Admin\RobotController@sche');//添加机器人
        Route::any('robot/sche_data', 'Admin\RobotController@scheData');//机器人列表
        Route::any('robot/sche_add', 'Admin\RobotController@scheAdd');//添加机器人
        Route::any('robot/sche_delete', 'Admin\RobotController@scheDelete');//添加机器人

        //杠杆交易风险率
        Route::get('hazard/index', 'Admin\HazardRateController@index');
        Route::get('hazard/lists', 'Admin\HazardRateController@lists');
        Route::get('hazard/total', 'Admin\HazardRateController@total');
        Route::get('hazard/total_lists', 'Admin\HazardRateController@totalLists');
        Route::get('hazard/handle', 'Admin\HazardRateController@handle');
        Route::post('hazard/handle', 'Admin\HazardRateController@postHandle');
        Route::get('lever/index', 'Admin\LeverTransactionController@index');
        Route::get('lever/lists', 'Admin\LeverTransactionController@lists');
        Route::any('agent', 'Admin\AdminController@agent');//后台管理员代理进入界面

        //杠杆交易倍数手数设置
        Route::get('levermultiple/index', 'Admin\LeverMultipleController@index');
        Route::get('levermultiple/list', 'Admin\LeverMultipleController@lists');
        Route::post('levermultiple/del', 'Admin\LeverMultipleController@del');
        Route::any('levermultiple/edit', 'Admin\LeverMultipleController@edit');
        Route::any('levermultiple/doedit', 'Admin\LeverMultipleController@doedit');
        Route::any('levermultiple/add', 'Admin\LeverMultipleController@add');
        Route::any('levermultiple/doadd', 'Admin\LeverMultipleController@doadd');

        //杠杆转法币审核
        Route::get('levertolegal/index', 'Admin\LevertolegalController@index');
        Route::get('levertolegal/list', 'Admin\LevertolegalController@lists');
        Route::any('/levertolegal/addshow', 'Admin\LevertolegalController@addshow');//审核
        Route::any('/levertolegal/postAddyes', 'Admin\LevertolegalController@postAddyes');//审核通过
        Route::any('/levertolegal/postAddno', 'Admin\LevertolegalController@postAddno');//审核不通过

        Route::get('prizepool/index', 'Admin\PrizePoolController@index'); //会员奖励记录
        Route::get('prizepool/lists', 'Admin\PrizePoolController@lists'); //会员奖励接口
        Route::get('prizepool/count', 'Admin\PrizePoolController@count'); //会员奖励接口
        Route::get('profits/index', 'Admin\AccountLogController@indexprofits'); //亏损返还记录
        Route::get('profits/lists', 'Admin\AccountLogController@listsprofits'); //亏损返还记录
        Route::get('profits/count', 'Admin\AccountLogController@countprofits'); //亏损返还记录

        //提币和归拢
        Route::get('/generalaccount', 'Admin\SettingController@generalaccount'); //提币总账号
        Route::post('/generalaccount', 'Admin\SettingController@dogeneralaccount'); //提币总账号
        Route::post('send/btc', 'Admin\UserController@sendBtc'); //打入btc
        Route::post('/user/balance', 'Admin\UserController@balance'); //链上余额归拢

        //ip地址管理
        Route::get('/ipaddr/manager_roles_api', 'Admin\IpAddrController@users');
        Route::get('/ipaddr/role_add', 'Admin\IpAddrController@add');
        Route::post('/ipaddr/role_add', 'Admin\IpAddrController@postAdd');
        Route::post('/ipaddr/role_delete', 'Admin\IpAddrController@del');
        Route::get('/ipaddr/role_permission', 'Admin\IpAddrController@update');
        Route::post('/ipaddr/role_permission', 'Admin\IpAddrController@postUpdate');

        //链上钱包
        Route::get('/wallet/index', 'Admin\WalletController@index'); //钱包管理页面
        Route::get('/wallet/list', 'Admin\WalletController@lists'); //钱包列表搜索
        Route::get('/wallet/make', 'Admin\WalletController@makeWallet'); //生成钱包
        Route::get('/wallet/update_balance', 'Admin\WalletController@updateBalance'); //更新链上余额
        Route::get('/wallet/transfer_poundage', 'Admin\WalletController@transferPoundage'); //打入手续费
        Route::get('/wallet/collect', 'Admin\WalletController@collect'); //余额归拢
        Route::post('/wallet/edit', 'Admin\WalletController@edit'); //钱包地址修改
        Route::get('/wallet/clear', 'Admin\WalletController@clearLog'); //钱包地址修改
        Route::get('/wallet/change_his','Admin\WalletController@changeHistory');

        //升级设置
        Route::get('/level_index', function () {
            return view('admin.level.index');
        });
        Route::get('/level_add', 'Admin\LevelController@add');//添加设置
        Route::post('/level_add', 'Admin\LevelController@postAdd');//添加设置
        Route::get('/level_list', 'Admin\LevelController@lists');
        Route::post('/level_del', 'Admin\LevelController@del');
        //升级代数设置
        Route::get('/level_algebra_index', function () {
            return view('admin.level.algebra_index');
        });
        Route::get('/level_algebra_add', 'Admin\LevelController@algebraAdd');//添加设置
        Route::post('/level_algebra_add', 'Admin\LevelController@algebraPostAdd');//添加设置
        Route::get('/level_algebra_list', 'Admin\LevelController@algebraLists');
        Route::post('/level_algebra_del', 'Admin\LevelController@algebraDel');

        Route::get('/level_order_index', function () {
            return view('admin.level.orders');
        });

        Route::get('/level_order_list', 'Admin\LevelController@levelOrderList');//


        //保险设置
        Route::get('/insurance_rules_index', function () {
            return view('admin.insurancerule.index');
        });
        Route::get('/insurance_rules_add', 'Admin\InsuranceRuleController@add');//添加设置
        Route::post('/insurance_rules_add', 'Admin\InsuranceRuleController@postAdd');//添加设置
        Route::get('/insurance_rules_list', 'Admin\InsuranceRuleController@lists');
        Route::post('/insurance_rules_del', 'Admin\InsuranceRuleController@del');

        //保险理赔
        Route::get('/claim_index', 'Admin\ClaimController@index');//
        Route::get('/claim_list', 'Admin\ClaimController@lists');
        Route::post('/claim_affirm', 'Admin\ClaimController@affirm');
        Route::post('/claim_reject', 'Admin\ClaimController@reject');

        Route::group(['prefix' => 'insurance'], function () {
            Route::get('index', 'Admin\InsuranceController@index');//险种管理
            Route::get('lists', 'Admin\InsuranceController@lists');
            Route::get('add', 'Admin\InsuranceController@add');
            Route::post('add', 'Admin\InsuranceController@postAdd');
            Route::post('del', 'Admin\InsuranceController@del');
            Route::post('change_auto_claim', 'Admin\InsuranceController@changeAutoClaim');
            Route::post('change_status', 'Admin\InsuranceController@changeStatus');
            Route::post('change_t_add_1', 'Admin\InsuranceController@changeTAdd1');

            Route::get('order_index', 'Admin\InsuranceController@orderIndex');
            Route::get('order_lists', 'Admin\InsuranceController@orderLists');
        });

    });


// //代理商管理员操作后台
// Route::get('agent', function () {
//     session()->put('agent_username', '');
//     session()->put('agent_id', '');
//     return view('agent.login');
// })->name('agent');

//     Route::get('agent', function () {
//         return view('agent.index');
//     })->name('agent');

//     Route::post('agent/login', 'Agent\MemberController@login');//登录
//     Route::any('order/order_excel', 'Agent\OrderController@order_excel');//导出订单记录Excel
//     Route::any('order/users_excel', 'Agent\OrderController@user_excel');//导出用户记录Excel

//     Route::any('agent/dojie', 'Agent\ReportController@dojie');//阶段订单图表

// //管理后台
//     Route::group(['prefix' => 'agent', 'middleware' => ['agent_auth']], function () {


//         //代理商权限管理
//         Route::get('/manager/manager_index', function () {
//             return view('agent.manager.index');
//         });
//         Route::get('/manager/users', 'Agent\AgentController@users');
//         Route::get('/manager/add', 'Agent\AgentController@add');//添加管理员
//         Route::post('/manager/add', 'Agent\AgentController@postAdd');//添加管理员
//         Route::post('/manager/delete', 'Agent\AgentController@del');//删除管理员
//         Route::get('/manager/manager_roles', function () {
//             return view('agent.manager.admin_roles');
//         });
//         // Route::any('addjuese', 'Agent\AgentRoleController@addJuese');
//         Route::get('/manager/manager_roles_api', 'Agent\AgentRoleController@users');
//         Route::get('/manager/role_add', 'Agent\AgentRoleController@add');
//         Route::any('/manager/show', 'Agent\AgentRoleController@show');//展示角色
//         Route::any('/manager/role_add', 'Agent\AgentRoleController@postAdd');
//         Route::post('/manager/role_delete', 'Agent\AgentRoleController@del');
//         Route::get('/manager/role_permission', 'Agent\AgentRolePermissionController@update');
//         Route::post('/manager/role_permission', 'Agent\AgentRolePermissionController@postUpdate');

//         //代理商管理员
//         Route::any('/agentadmin_add_show', 'Agent\AgentAdminController@agentadmin_add_show');//添加代理商管理员显示角色
//         Route::any('/agentadmin_list', 'Agent\AgentAdminController@list1');
//         // Route::any('agentadmin_add', 'Agent\AgentAdminController@show');//
//         Route::any('agentadmin_add', 'Agent\AgentAdminController@postAdd');
//         // Route::post('agentadmin_add', 'Agent\AgentAdminController@del');

//         //首页
//         Route::any('get_statistics', 'Agent\AgentIndexController@getStatistics');//首页获取统计信息

//         Route::post('change_password', 'Agent\MemberController@changePWD');//修改密码

//         Route::get('user_info', 'Agent\MemberController@getUserInfo');//获取用户信息
//         Route::post('save_user_info', 'Agent\MemberController@saveUserInfo');//保存用户信息
//         Route::any('lists', 'Agent\MemberController@lists');//代理商列表addSonAgent
//         Route::post('addagent', 'Agent\MemberController@addAgent');//添加代理商
//         Route::post('addsonagent', 'Agent\MemberController@addSonAgent');//添加代理商
//         Route::post('update', 'Agent\MemberController@updateAgent');//添加代理商
//         Route::post('searchuser', 'Agent\MemberController@searchuser');//查询用户
//         Route::post('search_agent_son', 'Agent\MemberController@search_agent_son');//查询用户
//         Route::get('user/all_child_agent', 'Agent\MemberController@allChildAgent'); //查询所有下级代理

//         Route::any('logout', 'Agent\MemberController@logout');//退出登录
//         Route::any('menu', 'Agent\MemberController@getMenu');//获取指定身份的菜单

//         Route::post('jie', 'Agent\ReportController@jie');//阶段订单图表

//         Route::post('day', 'Agent\ReportController@day');//阶段订单图表

//         Route::post('order', 'Agent\ReportController@order');//阶段订单图表
//         Route::post('order_num', 'Agent\ReportController@order_num');//阶段订单图表
//         Route::post('order_money', 'Agent\ReportController@order_money');//阶段订单图表

//         Route::post('user', 'Agent\ReportController@user');//阶段订单图表
//         Route::post('user_num', 'Agent\ReportController@user_num');//阶段订单图表
//         Route::post('user_money', 'Agent\ReportController@user_money');//阶段订单图表

//         Route::post('agental', 'Agent\ReportController@agental');//阶段订单图表
//         Route::post('agental_t', 'Agent\ReportController@agental_t');//阶段订单图表
//         Route::post('agental_s', 'Agent\ReportController@agental_s');//阶段订单图表


//         Route::post('order/list', 'Agent\OrderController@order_list');//团队所有订单
//         Route::post('order/info', 'Agent\OrderController@order_info');//订单详情
//         Route::post('jie/list', 'Agent\OrderController@jie_list');//团队所有结算
//         Route::post('jie/info', 'Agent\OrderController@jie_info');//结算详情

//         Route::post('get_order_account', 'Agent\OrderController@get_order_account');
//         Route::post('get_user_num', 'Agent\UserController@get_user_num');
//         Route::post('get_my_invite_code', 'Agent\UserController@get_my_invite_code');

//         Route::any('user/lists', 'Agent\UserController@lists');//用户列表
//         Route::any('lever_transaction/lists', 'Agent\LeverTransactionController@lists');//用户的订单
//         Route::any('account/money_log', 'Agent\AccountController@moneyLog');//结算
//         Route::any('agent/info', 'Agent\AgentController@info');//代理商信息

//         Route::get('capital/recharge', 'Agent\CapitalController@rechargeList');
//         Route::get('capital/withdraw', 'Agent\CapitalController@withdrawList');
//         //划转出入列表
//         Route::any('user/huazhuan_lists', 'Agent\UserController@huazhuan_lists');//用户列表

//         Route::any('order/order_excel', 'Agent\OrderController@order_excel');//导出订单记录Excel

//         //秒合约
//         Route::get('micro/currency_show', 'Agent\OrderController@microCurrency');
//         Route::post('micro/list', 'Agent\OrderController@microList');

//         Route::prefix('common')->namespace('Agent')->group(function () {
//             Route::get('legal_currency', 'CommonController@legalCurrency');
//         });
//     });
//     Route::any('order/churu_excel', 'Agent\OrderController@churu_excel');//导出出入金记录Excel


});
