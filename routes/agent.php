<?php

//代理商管理员操作后台
Route::get('agent/index', function () {
    return view('agent.index');
})->name('agent');

Route::get('agent', function () {
    session()->put('agent_username', '');
    session()->put('agent_id', '');
    return view('agent.login');
});

Route::post('agent/login', 'Agent\MemberController@login');//登录
Route::any('order/order_excel', 'Agent\OrderController@order_excel');//导出订单记录Excel
Route::any('agent/users_excel', 'Agent\OrderController@user_excel');//导出用户记录Excel
Route::any('agent/dojie', 'Agent\ReportController@dojie');//阶段订单图表

//管理后台
Route::group(['prefix' => 'agent', 'middleware' => ['agent_auth']], function () {

    //========================new！！！==================
    Route::get('home', 'Agent\ReportController@home');//主页
    Route::get('user/index', 'Agent\UserController@index');//用户管理列表
    Route::get('salesmen/index', 'Agent\UserController@salesmenIndex');//代理商管理列表
    Route::get('salesmen/add', 'Agent\UserController@salesmenAdd');//添加代理商页面
    Route::get('salesmen/address', 'Agent\UserController@salesmenAddress');//添加代理商页面
    Route::post('salesmen/saveaddress','Agent\UserController@salesmenAddressSave');
    Route::get('transfer/index', 'Agent\UserController@transferIndex');//出入金列表页
    Route::get('set_password', 'Agent\MemberController@setPas');//修改密码
    Route::get('set_info', 'Agent\MemberController@setInfo');//基本信息

    Route::get('order_statistics', 'Agent\ReportController@orderSt');//订单统计
    Route::get('user_statistics', 'Agent\ReportController@userSt');//用户统计
    Route::get('money_statistics', 'Agent\ReportController@moneySt');//收益统计
    //==========================
    //首页
    Route::any('get_statistics', 'Agent\AgentIndexController@getStatistics');//首页获取统计信息

    Route::post('change_password', 'Agent\MemberController@changePWD');//修改密码

    Route::get('user_info', 'Agent\MemberController@getUserInfo');//获取用户信息
    Route::post('save_user_info', 'Agent\MemberController@saveUserInfo');//保存用户信息
    Route::any('lists', 'Agent\MemberController@lists');//代理商列表
    Route::post('addagent', 'Agent\MemberController@addAgent');//添加代理商
    Route::post('addsonagent', 'Agent\MemberController@addSonAgent');//给代理商添加代理商
    Route::post('update', 'Agent\MemberController@updateAgent');//添加代理商
    Route::post('searchuser', 'Agent\MemberController@searchuser');//查询用户
    Route::post('search_agent_son', 'Agent\MemberController@search_agent_son');//查询用户

    Route::any('logout', 'Agent\MemberController@logout');//退出登录
    Route::any('menu', 'Agent\MemberController@getMenu');//获取指定身份的菜单

    Route::post('jie', 'Agent\ReportController@jie');//阶段订单图表

    Route::post('day', 'Agent\ReportController@day');//阶段订单图表

    Route::post('order', 'Agent\ReportController@order');//阶段订单图表
    Route::post('order_num', 'Agent\ReportController@order_num');//阶段订单图表
    Route::post('order_money', 'Agent\ReportController@order_money');//阶段订单图表

    Route::post('user', 'Agent\ReportController@user');//阶段用户图表
    Route::post('user_num', 'Agent\ReportController@user_num');//阶段订单图表
    Route::post('user_money', 'Agent\ReportController@user_money');//阶段订单图表

    Route::post('agental', 'Agent\ReportController@agental');//阶段订单图表
    Route::post('agental_t', 'Agent\ReportController@agental_t');//阶段订单图表
    Route::post('agental_s', 'Agent\ReportController@agental_s');//阶段订单图表


    Route::get('order/lever_index', 'Agent\OrderController@leverIndex');//杠杆订单页面

    Route::post('order/list', 'Agent\OrderController@order_list');//团队所有订单

    Route::get('order/info', 'Agent\OrderController@order_info');//订单详情
    //秒合约
    Route::get('order/micro_index', 'Agent\OrderController@microIndex');
    Route::get('micro/currency_show', 'Agent\OrderController@microCurrency');
    Route::post('micro/list', 'Agent\OrderController@microList');

    Route::prefix('common')->namespace('Agent')->group(function () {
        Route::get('legal_currency', 'CommonController@legalCurrency');
    });

    //撮合订单
    Route::get('order/transaction_index', 'Agent\OrderController@transactionIndex');

    Route::get('order/transaction_list', 'Agent\OrderController@transactionList');

    Route::get('order/jie_index', 'Agent\OrderController@jieIndex');


    Route::post('jie/list', 'Agent\OrderController@jie_list');//团队所有结算
    Route::any('jie/export', 'Agent\OrderController@jie_export');//团队所有结算
    Route::post('jie/info', 'Agent\OrderController@jie_info');//结算详情

    Route::post('get_order_account' , 'Agent\OrderController@get_order_account');
    Route::post('get_user_num' , 'Agent\UserController@get_user_num');
    Route::post('get_my_invite_code' , 'Agent\UserController@get_my_invite_code');

   
    Route::any('user/lists', 'Agent\UserController@lists');//用户列表
    Route::any('lever_transaction/lists', 'Agent\LeverTransactionController@lists');//用户的订单
    Route::any('account/money_log', 'Agent\AccountController@moneyLog');//结算
    Route::any('agent/info', 'Agent\AgentController@info');//代理商信息

    //划转出入列表
    Route::any('user/huazhuan_lists', 'Agent\UserController@huazhuan_lists');//用户列表


    //提币和归拢
    Route::post('send/btc', 'Admin\UserController@sendBtc'); //打入btc
    Route::post('/user/balance', 'Admin\UserController@balance'); //链上余额归拢
    Route::post('/ajax/artisan', 'Admin\DefaultController@ajaxArtisan'); //eth归拢

    //出入金（充币、提币)
    Route::any('recharge/index', 'Agent\CapitalController@rechargeIndex');
    Route::any('recharge/apply', 'Agent\CapitalController@rechargeApply');
    Route::any('recharge/pass', 'Agent\CapitalController@passReq');
    Route::any('recharge/refuse', 'Agent\CapitalController@refuseReq');
    Route::any('withdraw/index', 'Agent\CapitalController@withdrawIndex');
    Route::get('capital/recharge', 'Agent\CapitalController@rechargeList');
    Route::get('capital/apply', 'Agent\CapitalController@applyList');
    Route::get('capital/withdraw', 'Agent\CapitalController@withdrawList');

    //用户资金
    Route::get('user/users_wallet', 'Agent\CapitalController@wallet');
    Route::get('users_wallet_total', 'Agent\CapitalController@wallettotalList');

    //用户订单
    Route::get('user/lever_order', 'Agent\OrderController@userLeverIndex');
    Route::get('user/lever_order_list', 'Agent\OrderController@userLeverList');

    //结算 提现到账
    Route::post('wallet_out/done', 'Agent\CapitalController@walletOut');
    //用户点控
    Route::get('user/risk', 'Agent\UserController@risk');
    Route::post('user/risk', 'Agent\UserController@postRisk');
});
