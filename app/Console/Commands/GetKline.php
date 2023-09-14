<?php

namespace App\Console\Commands;

use App\Currency;
use App\UserChat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// 定义参数
defined('ACCOUNT_ID') or define('ACCOUNT_ID', '50154012'); // 你的账户ID
defined('ACCESS_KEY') or define('ACCESS_KEY', 'c96392eb-b7c57373-f646c2ef-25a14'); // 你的ACCESS_KEY
defined('SECRET_KEY') or define('SECRET_KEY', ''); // 你的SECRET_KEY
//defined('ACCESS_KEY') or define('ACCESS_KEY', '3c6ec3fd-h6n2d4f5gh-ccfa06f7-7392d'); // 你的ACCESS_KEY
//defined('SECRET_KEY') or define('SECRET_KEY', 'a8d3b39e-a304b838-f120d01e-aad4b'); // 你的SECRET_KEY

class GetKline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_kline_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取K线图数据';

    // 定义参数
    //    const ACCOUNT_ID = 50154012; // 你的账户ID
    //    const ACCESS_KEY = 'c96392eb-b7c57373-f646c2ef-25a14'; // 你的ACCESS_KEY
    //    const SECRET_KEY = ''; // 你的SECRET_KEY

    private $url = 'https://api.huobi.br.com'; //'https://api.huobi.pro';
    private $api = '';
    public $api_method = '';
    public $req_method = '';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // header('Content-Type: text/html; charset=utf-8');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // header("content-Type: text/html; charset=utf-8");

        while (true) {
            try {
                //code...

                echo ("开始推送\n\r");
                $all = DB::table('currency')->where('is_display', '1')->get();
                $all_arr = $this->object2array($all);
                $legal = DB::table('currency')->where('is_display', '1')->where('is_legal', '1')->get();
                $legal_arr = $this->object2array($legal);
                //拼接所有的交易对
                $ar = [];
                foreach ($legal_arr as $legal) {
                    foreach ($all_arr as $item) {
                        if ($legal['id'] != $item['id']) {
                            echo ("begin2");
                            $ar_a = [];
                            $ar_a['name'] = strtolower($item['name']) . strtolower($legal['name']);
                            $ar_a['currency_id'] = $item['id'];
                            $ar_a['legal_id'] = $legal['id'];
                            $ar[] = $ar_a;
                        }
                    }
                }
                //获取火币交易平台上面的有数据的交易对
                /*  $kko = json_decode($this->curl('https://api.huobi.br.com/v1/common/symbols'), true);
                if ($kko['status'] != 'ok') {
                echo ("begin3");
                $this->error('请求出错');
                //return false;
                continue;
                } */

                // $trade = array_column($kko['data'], 'symbol');
                echo ("开始遍历币种\n\r");
                foreach ($ar as $vv) {
                    if (in_array($vv["name"], array("btcusdt", "ethusdt", "ltcusdt", "bchusdt", "eosusdt"))) {
                        // if (in_array($vv["name"], array("btcusdt","ethusdt","ltcusdt"))) {
                        // if (in_array($vv["name"], array("btcusdt"))) {
                        $ar_new[] = $vv;
                    }
                }
                file_put_contents("ar_new.txt", json_encode($ar_new) . PHP_EOL, FILE_APPEND);
                // foreach ($ar as $it) {
                foreach ($ar_new as $it) {
                    echo ("遍历币种开始\n\r");
                    //不在火币交易对中直接跳过
                    /*  if (!in_array($it['name'], $trade)) {
                    echo ("begin5");
                    $this->error('不在火币交易对中直接跳过-' . $it['name']);
                    continue;
                    } */
                    $data = array();
                    echo ("开始请求\n\r");
                    $data = $this->get_history_kline($it['name'], '1min', 1);
                    if ($data) {

                    } else {
                        echo ("重新采集\n\r");
                        // sleep(5);
                        continue;
                    }
                    echo ("请求结束\n\r");
                    //请求失败直接跳过
                    if ($data['status'] != 'ok') {
                        echo ("begin6");
                        $this->error('请求失败');
                        continue;
                    }
                    $info = $data['data'][0];
                    $insert_instance = DB::table('market_hour')->where('currency_id', $it['currency_id'])
                        ->where('legal_id', $it['legal_id'])
                        ->where('day_time', '=', $info['id'])
                        ->where('type', 5)
                        ->where('period','1min')
                        ->where('sign',2)
                        // ->where('end_price', $this->sctonum($info['close']))
                        ->first();

                    if ($insert_instance) {
                        //如果指定时间行情已存在,直接跳过
                        echo ("begin7");
                        $this->error('指定时间行情已存在,直接跳过');
                        continue;
                    }
                    $insert_Data = array();
                    $insert_Data['currency_id'] = $it['currency_id'];
                    $insert_Data['legal_id'] = $it['legal_id'];
                    $insert_Data['start_price'] = $this->sctonum($info['open']);
                    $insert_Data['end_price'] = $this->sctonum($info['close']);
                    $insert_Data['mminimum'] = $this->sctonum($info['low']);
                    $insert_Data['highest'] = $this->sctonum($info['high']);
                    $insert_Data['type'] = 5;
                    $insert_Data['sign'] = 2;
                    $insert_Data['day_time'] = $info['id'];
                    $insert_Data['period'] = '1min';
                    $insert_Data['number'] = bcmul($info['amount'], 1, 5);
                    $insert_Data['mar_id'] = $info['id'];
                    DB::table('market_hour')->insert($insert_Data);

                    $update_Data = []; //每分钟获取到的最新的交易行情
                    $update_Data['currency_id'] = $it['currency_id'];
                    $update_Data['legal_id'] = $it['legal_id'];
                    $update_Data['now_price'] = $this->sctonum($info['close']);
                    $update_Data['add_time'] = time();
                    $update_Data['volume'] = '0.00000';
                    $update_Data['change'] = '+0.00';

                    //该交易对当天0点的交易行情。
                    $time = strtotime(date("Y-m-d")); //获取今天0点的时间戳
                    $day_Data = DB::table('market_hour')->where('currency_id', $it['currency_id'])
                        ->where('legal_id', $it['legal_id'])
                        ->where('period', '1day')
                        ->where('sign', 2)
                        ->where('day_time', '<=', $time)
                        ->where('end_price', '>', '0.00000')
                        ->orderby('id', 'DESC')
                        ->first();
                    //当天0点的成交价
                    if (!empty($day_Data)) {
                        $_zero_price = $day_Data->end_price;
                    } else {
                        $_zero_price = 0;
                    }

                    $update_Data['volume'] = DB::table('market_hour')->where('day_time', '>', $time)
                        ->where('currency_id', $it['currency_id'])
                        ->where('legal_id', $it['legal_id'])
                        ->where('period', '1min')
                        ->where('sign', 2)
                        ->sum('number');

                    switch (bccomp($update_Data['now_price'], $_zero_price, 5)) {
                        case 1:
                            if ($_zero_price === 0) {
                                $update_Data['change'] = '+0.000';
                            } else {
                                $a = bcsub($update_Data['now_price'], $_zero_price, 5);
                                $_pencet_num = bcdiv($a, $_zero_price, 5);
                                $update_Data['change'] = '+' . bcmul($_pencet_num, 100, 3);
                            }
                            break;

                        case 0:
                            $update_Data['change'] = '+0.000';
                            break;

                        case -1:
                            if ($_zero_price === 0) {
                                $update_Data['change'] = '+0.000';
                            } else {
                                $a = bcsub($_zero_price, $update_Data['now_price'], 5);
                                $_pencet_num = bcdiv($a, $_zero_price, 5);
                                $update_Data['change'] = '-' . bcmul($_pencet_num, 100, 3);
                            }
                            break;

                        default:
                            $update_Data['change'] = '+0.000';
                    }

                    $que_data = DB::table('currency_quotation')
                        ->where('currency_id', $it['currency_id'])
                        ->where('legal_id', $it['legal_id'])
                        ->orderby('id', 'DESC')->first();
                    if (!empty($que_data)) {
                        DB::table('currency_quotation')->where('id', $que_data->id)->update($update_Data);
                    } else {
                        DB::table('currency_quotation')->insert($update_Data);
                    }
                    $currency = Currency::find($it['currency_id']);
                    $legal = Currency::find($it['legal_id']);
                    $update_Data['currency_name'] = $currency->name;
                    $update_Data['legal_name'] = $legal->name;
                    $update_Data['type'] = 'daymarket';
                    $update_Data['high'] = $insert_Data['highest'];
                    $update_Data['low'] = $this->sctonum($info['low']);
                    $update_Data['symbol'] = $currency->name . '/' . $legal->name;

                    //推送K线行情
                    echo ("begin8");
                    $new_data = [
                        'type' => 'kline',
                        'period' => $insert_Data['period'],
                        'currency_id' => $insert_Data['currency_id'],
                        'currency_name' => $currency->name,
                        'legal_id' => $insert_Data['legal_id'],
                        'legal_name' => $legal->name,
                        'symbol' => $currency->name . '/' . $legal->name,
                        //  'symbol' => $currency->name . '/USDT',
                        'open' => $insert_Data['start_price'],
                        'close' => $insert_Data['end_price'],
                        'high' => $insert_Data['highest'],
                        'low' => $insert_Data['mminimum'],
                        'volume' => $insert_Data['number'],
                        'time' => $insert_Data['day_time'] * 1000,
                    ];
                    // echo("ts");
                    echo ("开始推送\n\r");
                    print_r($update_Data);
                    //  print_r($new_data);
                    UserChat::sendChat($update_Data);
                    UserChat::sendChat($new_data);
                    unset($currency);
                    unset($legal);
                    echo ("遍历币种结束\n\r");
                    // sleep(1);
                    // usleep(500000);
                }

                 sleep(5);
            } catch (Exception $e) {
                continue;
            }

        }

    }

    /**对象转数组
     * @param $obj
     * @return mixed
     */
    public function object2array($obj)
    {
        return json_decode(json_encode($obj), true);
    }

    //科学计算发转字符串
    public function sctonum($num, $double = 8)
    {
        if (false !== stripos($num, "e")) {
            $a = explode("e", strtolower($num));
            return bcmul($a[0], bcpow(10, $a[1], $double), $double);
        } else {
            return $num;
        }
    }

//
    //    /**
    //     * 行情类API
    //     */
    //    // 获取K线数据
    public function get_history_kline($symbol = '', $period = '', $size = 0)
    {
        echo ("获取K线数据\n\r");
        $this->api_method = "/market/history/kline";
        $this->req_method = 'GET';
        $param = [
            'symbol' => $symbol,
            'period' => $period,
        ];
        if ($size) {
            $param['size'] = $size;
        }

        $url = $this->create_sign_url($param);
        //echo($url);
        file_put_contents("log.txt", $url . PHP_EOL, FILE_APPEND);
        echo ("获取K线数据结束\n\r");
        return json_decode($this->curl($url), true);
        //return json_decode(file_get_contents($url), true);
    }
//    // 获取聚合行情(Ticker)
    //    function get_detail_merged($symbol = '') {
    //        $this->api_method = "/market/detail/merged";
    //        $this->req_method = 'GET';
    //        $param = [
    //            'symbol' => $symbol,
    //        ];
    //        $url = $this->create_sign_url($param);
    //        return json_decode($this->curl($url));
    //    }
    //    // 获取 Market Depth 数据
    //    function get_market_depth($symbol = '', $type = '') {
    //        $this->api_method = "/market/depth";
    //        $this->req_method = 'GET';
    //        $param = [
    //            'symbol' => $symbol,
    //            'type' => $type
    //        ];
    //        $url = $this->create_sign_url($param);
    //        return json_decode($this->curl($url));
    //    }
    //    // 获取 Trade Detail 数据
    //    function get_market_trade($symbol = '') {
    //        $this->api_method = "/market/trade";
    //        $this->req_method = 'GET';
    //        $param = [
    //            'symbol' => $symbol
    //        ];
    //        $url = $this->create_sign_url($param);
    //        return json_decode($this->curl($url));
    //    }
    //    // 批量获取最近的交易记录
    //    function get_history_trade($symbol = '', $size = '') {
    //        $this->api_method = "/market/history/trade";
    //        $this->req_method = 'GET';
    //        $param = [
    //            'symbol' => $symbol
    //        ];
    //        if ($size) $param['size'] = $size;
    //        $url = $this->create_sign_url($param);
    //        return json_decode($this->curl($url));
    //    }
    //    // 获取 Market Detail 24小时成交量数据
    //    function get_market_detail($symbol = '') {
    //        $this->api_method = "/market/detail";
    //        $this->req_method = 'GET';
    //        $param = [
    //            'symbol' => $symbol
    //        ];
    //        $url = $this->create_sign_url($param);
    //        return json_decode($this->curl($url));
    //    }
    //    /**
    //     * 公共类API
    //     */
    //    // 查询系统支持的所有交易对及精度
    //    function get_common_symbols() {
    //        $this->api_method = '/v1/common/symbols';
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url([]);
    //        return json_decode($this->curl($url));
    //    }
    //    // 查询系统支持的所有币种
    //    function get_common_currencys() {
    //        $this->api_method = "/v1/common/currencys";
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url([]);
    //        return json_decode($this->curl($url));
    //    }
    //    // 查询系统当前时间
    //    function get_common_timestamp() {
    //        $this->api_method = "/v1/common/timestamp";
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url([]);
    //        return json_decode($this->curl($url));
    //    }
    //    // 查询当前用户的所有账户(即account-id)
    //    function get_account_accounts() {
    //        $this->api_method = "/v1/account/accounts";
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url([]);
    //        return json_decode($this->curl($url));
    //    }
    //    // 查询指定账户的余额
    //    function get_account_balance() {
    //        $this->api_method = "/v1/account/accounts/".ACCOUNT_ID."/balance";
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url([]);
    //        return json_decode($this->curl($url));
    //    }
    //    /**
    //     * 交易类API
    //     */
    //    // 下单
    //    function place_order($account_id=0,$amount=0,$price=0,$symbol='',$type='') {
    //        $source = 'api';
    //        $this->api_method = "/v1/order/orders/place";
    //        $this->req_method = 'POST';
    //        // 数据参数
    //        $postdata = [
    //            'account-id' => $account_id,
    //            'amount' => $amount,
    //            'source' => $source,
    //            'symbol' => $symbol,
    //            'type' => $type
    //        ];
    //        if ($price) {
    //            $postdata['price'] = $price;
    //        }
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url,$postdata);
    //        return json_decode($return);
    //    }
    //    // 申请撤销一个订单请求
    //    function cancel_order($order_id) {
    //        $source = 'api';
    //        $this->api_method = '/v1/order/orders/'.$order_id.'/submitcancel';
    //        $this->req_method = 'POST';
    //        $postdata = [];
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url,$postdata);
    //        return json_decode($return);
    //    }
    //    // 批量撤销订单
    //    function cancel_orders($order_ids = []) {
    //        $source = 'api';
    //        $this->api_method = '/v1/order/orders/batchcancel';
    //        $this->req_method = 'POST';
    //        $postdata = [
    //            'order-ids' => $order_ids
    //        ];
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url,$postdata);
    //        return json_decode($return);
    //    }
    //    // 查询某个订单详情
    //    function get_order($order_id) {
    //        $this->api_method = '/v1/order/orders/'.$order_id;
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url);
    //        return json_decode($return);
    //    }
    //    // 查询某个订单的成交明细
    //    function get_order_matchresults($order_id = 0) {
    //        $this->api_method = '/v1/order/orders/'.$order_id.'/matchresults';
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url,$postdata);
    //        return json_decode($return);
    //    }
    //    // 查询当前委托、历史委托
    //    function get_order_orders($symbol = '', $types = '',$start_date = '',$end_date = '',$states = '',$from = '',$direct='',$size = '') {
    //        $this->api_method = '/v1/order/orders';
    //        $this->req_method = 'GET';
    //        $postdata = [
    //            'symbol' => $symbol,
    //            'states' => $states
    //        ];
    //        if ($types) $postdata['types'] = $types;
    //        if ($start_date) $postdata['start-date'] = $start_date;
    //        if ($end_date) $postdata['end-date'] = $end_date;
    //        if ($from) $postdata['from'] = $from;
    //        if ($direct) $postdata['direct'] = $direct;
    //        if ($size) $postdata['size'] = $size;
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        return json_decode($return);
    //    }
    //    // 查询当前成交、历史成交
    //    function get_orders_matchresults($symbol = '', $types = '',$start_date = '',$end_date = '',$from = '',$direct='',$size = '') {
    //        $this->api_method = '/v1/order/matchresults';
    //        $this->req_method = 'GET';
    //        $postdata = [
    //            'symbol' => $symbol
    //        ];
    //        if ($types) $postdata['types'] = $types;
    //        if ($start_date) $postdata['start-date'] = $start_date;
    //        if ($end_date) $postdata['end-date'] = $end_date;
    //        if ($from) $postdata['from'] = $from;
    //        if ($direct) $postdata['direct'] = $direct;
    //        if ($size) $postdata['size'] = $size;
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url,$postdata);
    //        return json_decode($return);
    //    }
    //    // 获取账户余额
    //    function get_balance($account_id=ACCOUNT_ID) {
    //        $this->api_method = "/v1/account/accounts/{$account_id}/balance";
    //        $this->req_method = 'GET';
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    /**
    //     * 借贷类API
    //     */
    //    // 现货账户划入至借贷账户
    //    function dw_transfer_in($symbol = '',$currency='',$amount='') {
    //        $this->api_method = "/v1/dw/transfer-in/margin";
    //        $this->req_method = 'POST';
    //        $postdata = [
    //            'symbol    ' => $symbol,
    //            'currency' => $currency,
    //            'amount' => $amount
    //        ];
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    // 借贷账户划出至现货账户
    //    function dw_transfer_out($symbol = '',$currency='',$amount='') {
    //        $this->api_method = "/v1/dw/transfer-out/margin";
    //        $this->req_method = 'POST';
    //        $postdata = [
    //            'symbol    ' => $symbol,
    //            'currency' => $currency,
    //            'amount' => $amount
    //        ];
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    // 申请借贷
    //    function margin_orders($symbol = '',$currency='',$amount='') {
    //        $this->api_method = "/v1/margin/orders";
    //        $this->req_method = 'POST';
    //        $postdata = [
    //            'symbol    ' => $symbol,
    //            'currency' => $currency,
    //            'amount' => $amount
    //        ];
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    // 归还借贷
    //    function repay_margin_orders($order_id='',$amount='') {
    //        $this->api_method = "/v1/margin/orders/{$order_id}/repay";
    //        $this->req_method = 'POST';
    //        $postdata = [
    //            'amount' => $amount
    //        ];
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    // 借贷订单
    //    function get_loan_orders($symbol='',$currency='',$start_date,$end_date,$states,$from,$direct,$size) {
    //        $this->api_method = "/v1/margin/loan-orders";
    //        $this->req_method = 'GET';
    //        $postdata = [
    //            'symbol' => $symbol,
    //            'currency' => $currency,
    //            'states' => $states
    //        ];
    //        if ($currency) $postdata['currency'] = $currency;
    //        if ($start_date) $postdata['start-date'] = $start_date;
    //        if ($end_date) $postdata['end-date'] = $end_date;
    //        if ($from) $postdata['from'] = $from;
    //        if ($direct) $postdata['direct'] = $direct;
    //        if ($size) $postdata['size'] = $size;
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    // 借贷账户详情
    //    function margin_balance($symbol='') {
    //        $this->api_method = "/v1/margin/accounts/balance";
    //        $this->req_method = 'POST';
    //        $postdata = [
    //        ];
    //        if ($symbol) $postdata['symbol'] = $symbol;
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    /**
    //     * 虚拟币提现API
    //     */
    //    // 申请提现虚拟币
    //    function withdraw_create($address='',$amount='',$currency='',$fee='',$addr_tag='') {
    //        $this->api_method = "/v1/dw/withdraw/api/create";
    //        $this->req_method = 'POST';
    //        $postdata = [
    //            'address' => $address,
    //            'amount' => $amount,
    //            'currency' => $currency
    //        ];
    //        if ($fee) $postdata['fee'] = $fee;
    //        if ($addr_tag) $postdata['addr-tag'] = $addr_tag;
    //        $url = $this->create_sign_url($postdata);
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    // 申请取消提现虚拟币
    //    function withdraw_cancel($withdraw_id='') {
    //        $this->api_method = "/v1/dw/withdraw-virtual/{$withdraw_id}/cancel";
    //        $this->req_method = 'POST';
    //        $url = $this->create_sign_url();
    //        $return = $this->curl($url);
    //        $result = json_decode($return);
    //        return $result;
    //    }
    //    /**
    //     * 类库方法
    //     */
    //    // 生成验签URL
    public function create_sign_url($append_param = [])
    {
        // 验签参数
        $param = [
            'AccessKeyId' => ACCESS_KEY,
            'SignatureMethod' => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp' => date('Y-m-d\TH:i:s', time()),
        ];
        if ($append_param) {
            foreach ($append_param as $k => $ap) {
                $param[$k] = $ap;
            }
        }
        return $this->url . $this->api_method . '?' . $this->bind_param($param);
    }
//    // 组合参数
    public function bind_param($param)
    {
        $u = [];
        $sort_rank = [];
        foreach ($param as $k => $v) {
            $u[] = $k . "=" . urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "Signature=" . urlencode($this->create_sig($u));
        return implode('&', $u);
    }
//    // 生成签名
    public function create_sig($param)
    {
        $sign_param_1 = $this->req_method . "\n" . $this->api . "\n" . $this->api_method . "\n" . implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, SECRET_KEY, true);
        return base64_encode($signature);
    }
    public function curl($url, $postdata = [])
    {

        echo ("curl开始\n\r");
        $start = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$this->Rand_IP(), 'CLIENT-IP:'.$this->Rand_IP()));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if (empty($output)) {
            echo ("curl没有采集到\n\r");
        };
        echo ("curl结束\n\r");
        $end = microtime(true);
        file_put_contents("haoshi.txt", ($end - $start) . PHP_EOL, FILE_APPEND);
        // echo "\t" . date('Y-m-d H:i:s') . $value->symbol . '处理完成,耗时' .($end - $start) . '秒' . PHP_EOL;
        //  print_r($output);
        //  echo("\n\r");
        // print_r($info);
        return $output;
    }

}
