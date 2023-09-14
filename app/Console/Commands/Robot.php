<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\Currency;
use App\CurrencyMatch;
use App\Jobs\SendMarket;
use App\MarketHour;
use App\MyQuotation;
use App\RobotPlan;
use App\Setting;
use App\Transaction;
use App\TransactionIn;
use App\TransactionOut;
use App\Users;
use App\UsersWallet;
use Faker\Factory;
use Illuminate\Console\Command;
use App\Robot as RobotModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Robot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'robot {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '匹配交易自动挂单机器人';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('--------------------------------------------------');
        $this->info('开始执行机器人:' . now()->toDateTimeString());

        $id = $this->argument('id');

        while (true) {

            //设置行情
            $robot = RobotModel::find($id);
//            $robotPlan = RobotPlan::where('rid', $id)->where('itime', '<=', time())->orderBy('itime', 'desc')->get();
//            if ($robotPlan) {
//                $_robotPlan = $robotPlan[0];
//
//                $robot->float_number_down = $_robotPlan->float_down;
//                $robot->float_number_up = $_robotPlan->float_up;
//                $robot->save();
//                $currency_match = CurrencyMatch::where('currency_id', $robot->currency_id)->where('legal_id', $robot->legal_id)->get();
//                $currency_match = $currency_match[0];
//                $currency_match->fluctuate_min = $_robotPlan->min_price;
//                $currency_match->fluctuate_max = $_robotPlan->max_price;
//                $currency_match->save();
//            }

            $robot = RobotModel::find($id);

            if (!$robot) {
                $this->info('找不到此机器人');
                break;
            }

            if ($robot->status == RobotModel::STOP) {
                $this->info('机器人已关闭');
                break;
            }

            $this->info('当前交易对是:' . $robot->currency_info . '/' . $robot->legal_info);
            $this->info('当前数量区间:' . $robot->number_min . '-' . $robot->number_max);

            try {
                if ($robot->sell == RobotModel::OPEN) {
                    $this->info('模拟数据');
                    $this->sell($robot, $robot->number_max, $robot->number_min);

                }
            } catch (\Exception $e) {
                $this->info($e->getMessage());
            }
            $this->info('睡眠时间：' . $robot->second);
            sleep($robot->second);
        }

        $this->info('机器人执行结束:' . now()->toDateTimeString());
        $this->info('--------------------------------------------------');
    }

    protected function sell($robot, $number_max, $number_min)
    {
        $this->SaveQuotation($robot);

        //随机数量
        $num = $this->getNumber($number_min, $number_max);
        $total_number = $num;

        //随机价格
        $price = $this->getPrice(strtolower($robot->currency_info . $robot->legal_info), $robot->float_number_down, $robot->float_number_up);
//        var_dump($price);
        $faker = Factory::create();
        $now_price = $price['close'];
        $currency_name = strtolower($robot->currency_info . 'usdt');
        $key = "market.{$currency_name}.kline.1min";

        if (time() % 60 < 50 && time() % 60 > 10) {
            $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        }
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime() / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->sendDepthAndDetail($robot->currency_info, $robot);

        $this->info('模拟五分钟K线');
        $key = "market.{$currency_name}.kline.5min";
        $stamp = Robot::getNowTime('5min') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '5min');

        $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('5min') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->info('模拟15分钟K线');
        $key = "market.{$currency_name}.kline.15min";
        $stamp = Robot::getNowTime('15min') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '15min');
        $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('15min') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->info('模拟30分钟K线');
        $key = "market.{$currency_name}.kline.30min";
        $stamp = Robot::getNowTime('30min') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '30min');
        $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('30min') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->info('模拟60分钟K线');
        $key = "market.{$currency_name}.kline.60min";
        $stamp = Robot::getNowTime('60min') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '60min');
        $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('60min') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->info('模拟天K线');
        $key = "market.{$currency_name}.kline.1day";
        $stamp = Robot::getNowTime('1day') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '1day');
        $now_price = $faker->randomFloat(6, $price['close'] - $robot->float_number_down, $price['close'] + $robot->float_number_up);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('1day') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->info('模拟周分钟K线');
        $key = "market.{$currency_name}.kline.1week";
        $stamp = Robot::getNowTime('1week') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '1week');
        $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('1week') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);

        $this->info('模拟月线');
        $key = "market.{$currency_name}.kline.1mon";
        $stamp = Robot::getNowTime('1mon') / 1000;
        $_id = "market." . strtolower($currency_name) . ".kline.{$stamp}";
        $price = $this->getESData($_id, '1mon');
        $now_price = $faker->randomFloat(6, $price['low'], $price['high']);
        $hh = json_encode(['ch' => $key, 'ts' => self::getNowTime(), 'tick' => [
            'id' => self::getNowTime('1week') / 1000,
            'open' => $price['open'],
            'close' => $now_price,
            'low' => $price['low'],
            'high' => $price['high'],
            'vol' => $price['vol'],
            'amount' => $price['vol'],
            'count' => rand($robot->number_min, $robot->number_max)
        ]]);
        Redis::set($key, $hh);


    }

    public function getESData($id, $peroid)
    {
        $esclient = MarketHour::getEsearchClient();
        $params = [
            'index' => 'market.kline.' . $peroid,
            'type' => 'doc',
            'id' => $id,
        ];
        $res = $esclient->get($params);
        return $res['_source'];
    }

    public function SaveQuotation($robot)
    {
        $currency_match = CurrencyMatch::where('currency_id', $robot->currency_id)->where('legal_id', $robot->legal_id)->get();
        $currency_match = $currency_match[0]->toArray();

        $time = self::getNowTime();
        $last_time = strtotime('-1 min', $time / 1000);
        $esclient = MarketHour::getEsearchClient();

        $last_id = strtolower($robot->currency_info . $robot->legal_info) . '.1min.' . ($last_time * 1000);
        $id = strtolower($robot->currency_info . $robot->legal_info) . '.1min.' . ($time);

        $last_info = $this->getESDataQuotation($last_id);
        $info = $this->getESDataQuotation($id);

        $huobi_info = $this->getPriceHuobi($robot);

        $faker = Factory::create();
        $needle = [];
        $needle['open'] = floatval($last_info ? $last_info['close'] : $huobi_info['open']);
        if ($last_info) {

            $needle['close'] = $huobi_info['close'];
            $max = max($huobi_info['high'], $huobi_info['open'], $needle['close'],$needle['open']);
            $min = min($huobi_info['low'], $huobi_info['close'], $needle['open'],$needle['close']);
            $needle['high'] =  floatval($faker->randomFloat(6, $max, $max + $robot->float_number_up));
            $needle['low'] = floatval($faker->randomFloat(6, $min-$robot->float_number_down, $min));

        } else {
            $needle['high'] = floatval($huobi_info['high']);
            $needle['low'] = floatval($huobi_info['low']);
            $needle['close'] = floatval($huobi_info['close']);
        }


        $needle['vol'] = floatval(sprintf('%.2f', $faker->randomFloat(2, $robot->number_min, $robot->number_max) * 20));
        $needle['base'] = $robot->currency_info;
        $needle['target'] = $robot->legal_info;
        $needle['symbol'] = "{$robot->currency_info}/{$robot->legal_info}";
        $needle['itime'] = $time / 1000;

        $params = [
            'index' => 'market.quotation',
            'type' => 'doc',
            'id' => $id,
            'body' => json_encode($needle)
        ];

        try{
        $esclient->delete([
            'index' => 'market.quotation',
            'type' => 'doc',
            'id' => $id
        ]);
        }catch (\Exception $e)
        {}
        $res = $esclient->index($params);

    }

    public function getPriceHuobi($robot)
    {
        //获取最新价格

        $symbol = strtolower($robot->huobi_currency . 'usdt');
        $url = "https://api.huobi.pro/market/history/kline?symbol={$symbol}&period=1min&size=1";
        $con = json_decode(file_get_contents($url), true);

        //查看有没有当前区间的plan
        $time = time();
        $robotPlan = RobotPlan::where('itime', '<=', $time)->where('etime', '>', $time)->orderBy('itime','desc')->get();
//        var_dump(count($robotPlan));
        if (count($robotPlan) > 0) {
            $_robotPlan = $robotPlan[0];
        }
        if (is_array($con)) {
            $obj = $con['data'][0];
            if (isset($_robotPlan)) {
//                var_dump($_robotPlan);
                $obj['open'] = floatval(sprintf('%.6f', $obj['open'] * $robot['mult'] * (1+$_robotPlan->float_up)));
                $obj['high'] = floatval(sprintf('%.6f', $obj['high'] * $robot['mult'] *(1+$_robotPlan->float_up)));
                $obj['low'] = floatval(sprintf('%.6f', $obj['low'] * $robot['mult'] *(1+$_robotPlan->float_up)));
                $obj['close'] = floatval(sprintf('%.6f', $obj['close'] * $robot['mult'] *(1+$_robotPlan->float_up)));
            } else {
                $obj['open'] = floatval(sprintf('%.6f', $obj['open'] * $robot['mult']));
                $obj['high'] = floatval(sprintf('%.6f', $obj['high'] * $robot['mult']));
                $obj['low'] = floatval(sprintf('%.6f', $obj['low'] * $robot['mult']));
                $obj['close'] = floatval(sprintf('%.6f', $obj['close'] * $robot['mult']));
            }

            return $obj;
        }else{
            return false;
        }
    }

    public function getESDataQuotation($id)
    {
        try {
            $esclient = MarketHour::getEsearchClient();
            $params = [
                'index' => 'market.quotation',
                'type' => 'doc',
                'id' => $id,
            ];
            $res = $esclient->get($params);
//            var_dump('在里面查到的',$res);
            return $res['_source'];
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function sendDepthAndDetail($currency, $robot)
    {

        $currency = strtolower($currency);
        $currency_id = Currency::where('name', strtoupper($currency))->first();
        $rkey = "market.{$currency}usdt.kline.1min";
        $obj = json_decode(Redis::get($rkey));

        if (!$obj) {
            return;
        }

        $price = $obj->tick->close;

        $bids = [];
        $asks = [];

        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $bids[] = [$faker->randomFloat(4, $price - $robot->float_number_down, $price), $faker->randomFloat(2, $robot->number_min, $robot->number_max)];
        }
        for ($i = 0; $i < 10; $i++) {
            $asks[] = [$faker->randomFloat(4, $price, $price + $robot->float_number_down), $faker->randomFloat(2, $robot->number_min, $robot->number_max)];
        }

        $depth_data = [
            'type' => 'market_depth',
            'symbol' => strtoupper($currency) . '/USDT',
            'base-currency' => $currency_id->name,
            'quote-currency' => 'USDT',
            'currency_id' => $currency_id->id,
            'currency_name' => $currency_id->name,
            'legal_id' => 3,
            'legal_name' => 'USDT',
            'bids' => $bids, //买入盘口
            'asks' => $asks, //卖出盘口
        ];

        $infos = [];
        for ($i = 0; $i < rand(0, 30); $i++) {
            $way = rand(1, 10) % 2 === 0 ? 'buy' : 'sell';
            $obj = [
                "amount" => $faker->randomFloat(2, $robot->number_min, $robot->number_max),
                "ts" => intval(microtime(true) * 1000), //trade time
                "id" => time(),
                "time" => date('H:i:s'),
                "tradeId" => microtime(true),
                "price" => $price,
                "direction" => $way
            ];
            $infos[] = $obj;
        }

        $yeah = strtoupper($currency);
        $detail_data = [
            'type' => 'market_detail',
            'symbol' => $yeah . '/USDT',
            'base-currency' => $yeah,
            'quote-currency' => 'USDT',
            'currency_id' => $currency_id->id,
            'currency_name' => $yeah,
            'legal_id' => 3,
            'legal_name' => 'USDT',
            'data' => $infos, //卖出盘口
        ];;
        SendMarket::dispatch($depth_data)->onQueue('market.depth');
        SendMarket::dispatch($detail_data)->onQueue('market.detail');
    }


    /**
     * 获取整数时间
     */
    public static function getNowTime($type = '1min', $time = null)
    {
        $current = is_null($time) ? time() : $time;

        $yl = 60;
        if ($type == '5min') {
            $yl = 300;
        }
        if ($type == '15min') {
            $yl = 900;
        }
        if ($type == '30min') {
            $yl = 1800;
        }
        if ($type == '60min') {
            $yl = 3600;
        }

        $stamp = ($current % $yl) > 0 ? ($current - $current % $yl) : $current;

        if ($type == '1day') {
            $stamp = strtotime(date('Y-m-d', $current));
        }
        if ($type == '1week') {
            $stamp = strtotime('next Sunday', $current) - 60 * 60 * 24 * 7;
        }
        if ($type == '1mon') {
            $stamp = strtotime(date('Y-m', $current) . '-01');
        }
        return $stamp * 1000;
    }


    /**获取当前价格
     *
     * @param $symbol
     * @param $float_number_down
     * @param $float_number_up
     *
     * @return float
     */
    public function getPrice($symbol, $float_number_down, $float_number_up)
    {
        $this->info('交易对是：' . $symbol);
        $eclient = MarketHour::getEsearchClient();
        $type = $symbol . '.1min';
        $params = [
            'index' => 'market.quotation',
            'type' => 'doc',
            'id' => $type . '.' . self::getNowTime(),
        ];

        $result = $eclient->get($params);
//        var_dump($result);
        return $result['_source'];
    }

    /**获取买入卖出随机数
     *
     * @param $number_min
     * @param $number_max
     *
     * @return float
     */
    public function getNumber($number_min, $number_max)
    {
        $faker = Factory::create();
        $num = $faker->randomFloat(2, $number_min, $number_max);
        unset($faker);
        return $num;
    }

}
