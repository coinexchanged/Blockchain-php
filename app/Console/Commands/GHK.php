<?php


namespace App\Console\Commands;


use App\MarketHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

defined('ACCESS_KEY') or define('ACCESS_KEY', 'e480c999-bgbfh5tv3f-7a172162-43c3b'); // 你的ACCESS_KEY


class GHK extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_h_kline {period} {size}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取K线图数据';

    private $url = 'https://api.huobi.pro';//'https://api.huobi.pro';
    private $api = '';
    public $api_method = '';
    public $req_method = '';
    public $period ;
    public $size;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        global $argv;
        parent::__construct();
//        var_dump(ACCESS_KEY);exit;
        if(!array_key_exists(2,$argv) || ! array_key_exists(3,$argv)){
            return;
        }
        $this->period = $argv[2];
        $this->size = $argv[3];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $all = DB::table('currency')->where('is_display', '1')->get();
        $all_arr = $this->object2array($all);
        $legal = DB::table('currency')->where('is_display', '1')->where('is_legal', '1')->get();
        $legal_arr = $this->object2array($legal);
        //拼接所有的交易对
        $ar = [];
        foreach ($legal_arr as $legal) {
            foreach ($all_arr as $item) {
                if ($legal['id'] != $item['id']) {
//                    echo ("begin2");
                    $ar_a = [];
                    $ar_a['name'] = strtolower($item['name']) . strtolower($legal['name']);
                    $ar_a['currency_id'] = $item['id'];
                    $ar_a['legal_id'] = $legal['id'];
                    $ar_a['currency_name'] = $item['name'];
                    $ar_a['quote_name'] = $legal['name'];
                    $ar[] = $ar_a;
                }
            }
        }
        foreach ($ar as $vv) {
            if (in_array($vv["name"], array("btcusdt", "ethusdt", "ltcusdt", "bchusdt", "eosusdt"))) {
                // if (in_array($vv["name"], array("btcusdt","ethusdt","ltcusdt"))) {
                // if (in_array($vv["name"], array("btcusdt"))) {
                $ar_new[] = $vv;
            }
        }
        foreach ($ar_new as $it) {
            $data = $this->get_history_kline($it['name'], $this->period, $this->size);
            if ($data) {

            } else {
//                echo ("重新采集\n\r");
                // sleep(5);
                continue;
            }
            if ($data['status'] != 'ok') {
//                echo ("begin6");
//                var_dump($data);
                $this->error('请求失败');
                continue;
            }
            $list = $data['data'];
            foreach($list as $value){
                $data = [
                    'id' => $value['id'],
                    'period' => $this->period,
                    'base-currency' => strtoupper($it['currency_name']),
                    'quote-currency' => strtoupper($it['quote_name']),
                    'open' => $value['open'],
                    'close' => $value['close'],
                    'high' => $value['high'],
                    'low' => $value['low'],
                    'vol' => $value['vol'],
                    'amount' => $value['amount'],
                ];
                MarketHour::setEsearchMarket($data);
            }


        }

        }

    /**对象转数组
     * @param $obj
     * @return mixed
     */
    public function object2array($obj){
        return json_decode( json_encode( $obj),true);
    }
    //科学计算发转字符串
    public function sctonum($num, $double = 8){
        if(false !== stripos($num, "e")){
            $a = explode("e",strtolower($num));
            return bcmul($a[0], bcpow(10, $a[1], $double), $double);
        }else{
            return $num;
        }
    }


//    /**
//     * 行情类API
//     */
//    // 获取K线数据
    public function get_history_kline($symbol = '', $period='',$size=0) {
        $this->api_method = "/market/history/kline";
        $this->req_method = 'GET';
        $param = [
            'symbol' => $symbol,
            'period' => $period
        ];
        if ($size) $param['size'] = $size;
        $url = $this->create_sign_url($param);
//        echo $url;exit;
        return json_decode($this->curl($url) , TRUE);
    }
//    /**
//     * 类库方法
//     */
//    // 生成验签URL
    public function create_sign_url($append_param = []) {
        // 验签参数
        $param = [
            'AccessKeyId' => '89fcab07-1hrfj6yhgg-ae50f8cf-6c7db',
            'SignatureMethod' => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp' => date('Y-m-d\TH:i:s', time())
        ];
        if ($append_param) {
            foreach($append_param as $k=>$ap) {
                $param[$k] = $ap;
            }
        }
        return $this->url.$this->api_method.'?'.$this->bind_param($param);
    }
//    // 组合参数
    function bind_param($param) {
        $u = [];
        $sort_rank = [];
        foreach($param as $k=>$v) {
            $u[] = $k."=".urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "Signature=".urlencode($this->create_sig($u));
        return implode('&', $u);
    }
//    // 生成签名
    function create_sig($param) {
        $sign_param_1 = $this->req_method."\n".$this->api."\n".$this->api_method."\n".implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, '88dd5bbe-1799f6a1-e2a33400-924b1', true);
        return base64_encode($signature);
    }
    public function curl($url,$postdata=[]) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);


        return $output;
    }
}

