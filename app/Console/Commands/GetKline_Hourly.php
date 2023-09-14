<?php

namespace App\Console\Commands;

use App\CurrencyMatch;
use App\MarketHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Faker\Factory;
// 定义参数

class GetKline_Hourly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_kline_data_hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取K线图数据一小时';


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
        $faker = Factory::create();
        while (true) {
            //获取已存在的最新的一条5分钟 K线数据
            try {
                $currencys = CurrencyMatch::where('market_from', 0)->get();
                $peroid = '60min';
                $eclient = MarketHour::getEsearchClient();
                foreach ($currencys as $currency) {
                    $currency_name = $currency->currency_name;

                    $param = [
                        'index' => 'market.kline.' . $peroid,
                        'type' => 'doc',//$type,
                        'body' => [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        ['match' => ['base' => $currency_name]],
                                    ],
                                ],
                            ],
                            'sort' => [
                                'itime' => ['order' => 'desc']
                            ],
                            'size' => 1,
                            'from' => 0
                        ],
                    ];
                    $esclient = MarketHour::getEsearchClient();

                    try {
                        $result = $esclient->search($param);
                        if ($result['hits']) {
                            $last = $result['hits']['hits'][0]['_source'];

                            if ($last['itime'] === Robot::getNowTime($peroid) / 1000 || $last['itime'] < Robot::getNowTime($peroid) / 1000) {
                                $current = false;
                                if ($last['itime'] < Robot::getNowTime($peroid) / 1000) {
                                    $current = true;
                                    $last_time = strtotime('+60 min', $last['itime']);
                                } else {
                                    $last_time = $last['itime'];
                                }
                                $start = $this->getInfo($last_time, strtotime('+60 min', $last_time), $currency_name);

                                $open = sprintf('%.6f', $start['open']);
                                $close = sprintf('%.6f', $start['close']);
                                $high = sprintf('%.6f', $start['high']);
                                $low = sprintf('%.6f', $start['low']);;
                                $vol = $start['vol'];
                                $stamp = Robot::getNowTime($peroid, $last_time) / 1000;
                                $_id = "market." . strtolower($currency_name) . "usdt.kline.{$stamp}";
                                $obj = [
                                    'base' => $currency_name,
                                    'target' => 'USDT',
                                    'itime' => $stamp,
                                    'peroid' => $peroid,
                                    'open' => floatval($open),
                                    'high' => floatval($high),
                                    'low' => floatval($low),
                                    'close' => floatval($close),
                                    'vol' => floatval($vol),
                                    'id' => $_id
                                ];
//                            }
                                $params = [
                                    'index' => 'market.kline.' . $peroid,
                                    'type' => 'doc',
                                    'id' => $_id,
                                    'body' => json_decode(json_encode($obj)),
                                ];

                                $delete_param = [
                                    'index' => 'market.kline.' . $peroid,
                                    'type' => 'doc',
                                    'id' => $_id
                                ];

                                try {
                                    $esclient->get($delete_param) && $esclient->delete($delete_param);
                                } catch (\Exception $exception) {

                                }
                                $esclient->index($params);
                                $this->info('已设置一条信息' . $_id);
                            } else {
                                $this->info('超出当前时间戳');
                            }
                        }
                    } catch (\Exception $exception) {
                        $result = false;
                    }

                    if ($result === false) {
                        //第一次
                        $param = [
                            'index' => 'market.quotation',
                            'type' => 'doc',//$type,
                            'body' => [
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            ['match' => ['base' => $currency_name]],
                                        ],
                                    ],
                                ],
                                'sort' => [
                                    'itime' => ['order' => 'asc']
                                ],
                                'size' => 1,
                                'from' => 0
                            ],
                        ];

                        $result_quotation = $esclient->search($param);
                        if (isset($result_quotation['hits'])) {
                            $id = $result_quotation['hits']['hits'][0]['_id'];
                            $body = $result_quotation['hits']['hits'][0]['_source'];
                            $time = $body['itime'];
                            $start = $this->getInfo($time, strtotime('+60 min', $time), $currency_name);

                            $open = sprintf('%.6f', $start['open']);
                            $close = sprintf('%.6f', $start['close']);
                            $high = sprintf('%.6f', $start['high']);
                            $low = sprintf('%.6f', $start['low']);;
                            $vol = $start['vol'];
                            $first_stamp = Robot::getNowTime($peroid, $time) / 1000;

                            $_id = "market." . strtolower($currency_name) . "usdt.kline.{$first_stamp}";
                            $obj = [
                                'base' => $currency_name,
                                'target' => 'USDT',
                                'itime' => $first_stamp,
                                'peroid' => $peroid,
                                'open' => $open,
                                'high' => $high,
                                'low' => $low,
                                'close' => $close,
                                'vol' => $vol,
                                'id' => $_id
                            ];

                            $params = [
                                'index' => 'market.kline.' . $peroid,
                                'type' => 'doc',
                                'id' => $_id,
                                'body' => json_decode(json_encode($obj)),
                            ];

                            $delete_param = [
                                'index' => 'market.kline.' . $peroid,
                                'type' => 'doc',
                                'id' => $_id
                            ];

                            try {
                                $esclient->get($delete_param) && $esclient->delete($delete_param);
                            } catch (\Exception $exception) {

                            }
                            $esclient->index($params);
                            $this->info('已设置一条信息新增' . $_id);
                        }
                    }
                }
            }catch (\Exception $exception)
            {

            }
            sleep(2);
        }
    }

    /**
     * 聚合查询
     * @param $start
     * @param $end
     */
    public function getInfo($start, $end, $currency)
    {
        $params = [
            'index' => 'market.quotation',
            'type' => 'doc',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['base' => $currency]],
                        ],
                        'filter' => [
                            'range' => [
                                'itime' => [
                                    'gte' => $start,
                                    'lte' => $end - 1,
                                ],
                            ]
                        ],
                    ]
                ],
                'aggs' => [
                    "sum_value" => [
                        'sum' => [
                            'field' => 'vol'
                        ],
                    ],
                    "min_value" => [
                        'min' => [
                            'field' => 'low',
                        ]
                    ],
                    "max_value" => [
                        'max' => [
                            'field' => 'high',
                        ]
                    ]
                ],
                'size' => 0
            ],
        ];

        $es = MarketHour::getEsearchClient();
        $res = $es->search($params);

        $info = $res['aggregations'];

        $open = $this->getOpenAndClose($currency, $start, $end);

        $close = $this->getOpenAndClose($currency, $start, $end, 'desc');

        return ['open' => $open['open'], 'vol' => $info['sum_value']['value'], 'high' => $info['max_value']['value'], 'low' => $info['min_value']['value'], 'close' => $close['close']];
    }

    public function getOpenAndClose($currency, $start, $end, $sort = 'asc')
    {
        $params = [
            'index' => 'market.quotation',
            'type' => 'doc',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['base' => $currency]],
                        ],
                        'filter' => [
                            'range' => [
                                'itime' => [
                                    'gte' => $start,
                                    'lte' => $end - 1,
                                ],
                            ]
                        ],
                    ]
                ],
                'sort' => [
                    'itime' => ['order' => $sort]
                ],
                'size' => 1
            ],
        ];

        $es = MarketHour::getEsearchClient();
        $res = $es->search($params);
        return $res['hits']['hits'][0]['_source'];
    }

}
