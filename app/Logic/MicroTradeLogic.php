<?php

namespace App\Logic;

use App\UsersInsurance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\{
    AccountLog, Currency, CurrencyMatch, CurrencyQuotation, Level, MicroSecond, MicroOrder, Users, UsersWallet, MarketHour, Setting
};
use App\Utils\Probability;
use App\Jobs\SendClosedMicroOrder;

class MicroTradeLogic
{
    /**
     * 添加一个交易订单
     *
     * @param array $param
     * @return \App\MicroOrder
     * @throws \Exception
     */
    public static function addOrder($param)
    {
        list (
            'user_id' => $user_id,
            'type' => $type,
            'match_id' => $match_id,
            'currency_id' => $currency_id,
            'seconds' => $seconds,
            'price' => $price,
            'number' => $number,
            'use_insurance' => $use_insurance,
        ) = $param;
        try {
            DB::beginTransaction();
            //检测用户是否存在
            $user = Users::find($user_id);
            throw_unless($user, new \Exception('用户无效'));
            //检测交易对是否存在
            $currency_match = CurrencyMatch::find($match_id);
            throw_unless($currency_match, new \Exception('交易对不存在'));
            throw_unless($currency_match->open_microtrade, new \Exception('交易未开启'));
            $currency = Currency::where('is_micro', 1)->find($currency_id);
            throw_unless($currency, new \Exception('币种不存在或不允许被交易'));
            //检测秒数是否在合法范围内
            $seconds = MicroSecond::where('seconds', $seconds)->first();
            throw_unless($seconds, new \Exception('到期时间不允许'));
            //检测数量是否在合法范围内
            /*
            $number_not_exist = $currency->microNumbers->where('number', $number)->isEmpty();
            throw_if($number_not_exist, new \Exception('数量不在有效范围内'));
            */
            //整数判断
            if (!preg_match('/^\d+$/', $number)) {
                throw new \Exception('下单数量必须是整数');
            }
            if (bc_comp($currency->micro_min, 0) > 0 && $number % $currency->micro_min != 0) {
                throw new \Exception('下单数量必须是' . $currency->micro_min . '的整数倍');
            }
            if (bc_comp($currency->micro_max, $number) < 0  && bc_comp($currency->micro_max, 0) > 0) {
                throw new \Exception('最大下单数量不能超过:' . $currency->micro_max);
            }
            if (bc_comp($currency->micro_min, $number) > 0  && bc_comp($currency->micro_min, 0) > 0) {
                throw new \Exception('最小下单数量不能小于:' . $currency->micro_min);
            }
            //持仓类计判断
            
            //扣款
            $wallet = UsersWallet::where('currency', $currency_id)
                ->where('user_id', $user_id)
                ->first();
            throw_unless($wallet, new \Exception('用户钱包不存在'));
            if($use_insurance != 0){
                $balance_type = 5;
            }else{
                $balance_type = 4;
            }
            $is_insurance = $use_insurance;
            $result = change_wallet_balance($wallet, $balance_type, -$number, AccountLog::MICRO_TRADE_CLOSE_SETTLE, '秒合约下单扣除本金');
            throw_unless($result === true, new \Exception($result));
            //扣手续费
            $fee = bc_div(bc_mul($number, $currency->micro_trade_fee), 100);
            $result = change_wallet_balance($wallet, $balance_type, -$fee, AccountLog::MICRO_TRADE_CLOSE_SETTLE, '秒合约下单扣除' . $currency->micro_trade_fee . '%手续费');
            $level=Level::where('level',$user->level)->first();


            $insurance_start = Setting::getValueByKey('insurance_start','09:00');
            $insurance_end = Setting::getValueByKey('insurance_end','12:00');

            $insurance_start_datetime = Carbon::parse(date("Y-m-d {$insurance_start}:00"));
            $insurance_end_datetime = Carbon::parse(date("Y-m-d {$insurance_end}:00"));

            //在受保时间段的话
            if (Carbon::now()->gte($insurance_start_datetime) && Carbon::now()->lte($insurance_end_datetime)) {
                $at_protected = true;
            }else{
                $at_protected = false;
            }
            if (!empty($level) && !$at_protected){
                Users::rebate($user_id,$user_id,$currency_id,$fee,1,$level->max_algebra);
            }

            throw_unless($result === true, new \Exception($result));
            $now = Carbon::now();
            //生成交易数据
            $order_data = [
                'user_id' => $user_id,
                'match_id' => $currency_match->id,
                'currency_id' => $currency->id,
                'type' => $type,
                'seconds' => $seconds->seconds,
                'number' => $number,
                'open_price' => $price,
                'end_price' => $price,
                'profit_ratio' => $seconds->profit_ratio,
                'fee' => $fee,
                'status' => MicroOrder::STATUS_OPENED,
                'pre_profit_result' => 0, //预设赢利
                'handled_at' => $now->addSeconds($seconds->seconds),
                'is_insurance' => $is_insurance,
                'agent_path' =>$user->agent_path,
            ];
            //添加一个交易
            $result = MicroOrder::unguarded(function () use ($order_data) {
                $micro_order = MicroOrder::create($order_data);
                return $micro_order;
            });
            DB::commit();
            return $result;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * 批量更新交易对的价格
     *
     * @param integer $match_id
     * @return void
     */
    public static function newPrice($match_id, $price)
    {
        MicroOrder::where('status', MicroOrder::STATUS_OPENED)
            ->where('match_id', $match_id)
            ->where('pre_profit_result', 0)
            ->update([
                'end_price' => $price,
            ]);
             
        self::risk($match_id);
    }

    public static function mergeCountPrice($shoud_price_list)
    {
        $merge_price = [
            'max_price' => 0,
            'min_price' => 0,
        ];
        foreach ($shoud_price_list as $key => $value) {
            if (isset($value['max_price']) && bc_comp($value['max_price'], $merge_price['max_price']) > 0) {
                $merge_price['max_price'] = $value['max_price'];
            }
            if (
                isset($value['min_price']) 
                && (bc_comp($value['min_price'], $merge_price['min_price']) < 0 || bc_comp($merge_price['min_price'], 0) == 0)
            ) {
                $merge_price['min_price'] = $value['min_price'];
            }
        }
        return $merge_price;
    }

    /**
     * 交易订单统计
     *
     * @param $orders
     * @return array
     */
    public static function orderCount($orders)
    {
        //按盈亏进行分组
        $profit_group_order = $orders->groupBy('pre_profit_result');
        $shoud_price_list = [];
        $profit_group_order->each(function (\Illuminate\Database\Eloquent\Collection $item, $key) use (&$shoud_price_list) {
            $result_type = $key;
            //按交易类型(买涨,买跌)来分组统计
            $type_group_orders = $item->groupBy('type');
            $type_group_orders->each(function (\Illuminate\Database\Eloquent\Collection $item, $key) use ($result_type, &$shoud_price_list) {
                $submit_type = $key;
                list(
                    'name' => $price_name,
                    'price' => $right_price,
                ) = self::countPrice($result_type, $submit_type, $item);
                $shoud_price_list[$result_type][$price_name . '_price'] = $right_price;
            });
        });
        return $shoud_price_list;
    }

    /**
     * 统计订单类型和盈亏推断价格
     *
     * @param integer $result_type 盈亏(-1亏,1盈)
     * @param integer $type 下单类型(买涨、买跌)
     * @param \Illuminate\Database\Eloquent\Collection $orders
     * @return array
     */
    public static function countPrice($result_type, $type, $orders)
    {
        if ($result_type == MicroOrder::RESULT_LOSS) {
            //亏损处理
            if ($type == MicroOrder::TYPE_RISE) {
                //统计出买涨的最低价让行情价格低于最低价
                $name = 'min';
                $right_price = $orders->min('open_price');
            } elseif ($type == MicroOrder::TYPE_FALL) {
                //统计出买跌的最高价让行情价格高于最高价
                $name = 'max';
                $right_price = $orders->max('open_price');
            }
        } elseif ($result_type == MicroOrder::RESULT_PROFIT) {
            //盈利处理
            if ($type == MicroOrder::TYPE_RISE) {
                //统计出买涨的最高价让行情价格高于最高价
                $name = 'max';
                $right_price = $orders->max('open_price');
            } elseif ($type == MicroOrder::TYPE_FALL) {
                //统计出买跌的最低价让行情价格低于最低价
                $name = 'min';
                $right_price = $orders->min('open_price');
            }
        } else {
            //平局处理
        }
        return [
            'name' => $name,
            'price' => $right_price,
        ];
    }

    /**
     * 根据指定订单推断出价格并更新
     *
     * @param \App\CurrencyMatch $currency_match
     * @param \Illuminate\Database\Eloquent\Collection $micro_order
     * @return void
     */
    public static function deduceOrderPrice($currency_match, $micro_order)
    {
        // echo date('Y-m-d H:i:s ') . '订单推算中...' . PHP_EOL;
        $micro_order = $micro_order->where('match_id', $currency_match->id); //过滤一下交易对
        $shoud_price_list = self::orderCount($micro_order);
        $price_range = self::mergeCountPrice($shoud_price_list);
        /*
        1.当最高价小于最低价时,为最理想情况,一个价格(区间:max<price<min)就可以满足所有订单的盈亏预设;
        2.当最高价大于最低价时,为冲突情况,一个价格同一时刻满足不了所有订单的盈亏预设;
        3.当最高价等于最低价时,为冲突情况,代表一个价格即做了买涨又做了买跌，一个价格同一时刻满足不了所有订单的盈亏预设;
        */
        $faker = \Faker\Factory::create();
        $decimal = 0;
        if (stripos($currency_match->fluctuate_min, '.') !== false) {
            $fluctuate_min = rtrim($currency_match->fluctuate_min, '0'); //移除掉小数点后面右侧多余的0
            $fluctuate_min = rtrim($fluctuate_min, '.'); //如果是整数再移除掉小数点
            $decimal_index = stripos($fluctuate_min, '.'); //查找小数点的位置
            if ($decimal_index !== false) {
                $decimal = strlen($fluctuate_min) - $decimal_index - 1;
            }
        }
        trim($currency_match->fluctuate_min, '0');
        $float_diff = $faker->randomFloat($decimal, $currency_match->fluctuate_min, $currency_match->fluctuate_max); //产生一个随机浮动价格
        $start = microtime(true);
        foreach ($price_range as $key => $value) {
            if ($key == 'max_price') {
                if (bc_comp($value, 0) > 0) {
                    $price_range[$key] = $value = bc_add($value, $float_diff);
                    MarketHour::batchEsearchMarket($currency_match->currency_name, $currency_match->legal_name, $value, time());
                }
            } elseif ($key == 'min_price') {
                if (bc_comp($value, 0) > 0) {
                    $price_range[$key] = $value = bc_sub($value, $float_diff);
                    MarketHour::batchEsearchMarket($currency_match->currency_name, $currency_match->legal_name, $value, time());
                }
            }
        }
        $end = microtime(true);
        // echo '插入K线耗时:' . ($end - $start) . '秒' . PHP_EOL;
        $handle_orders = $micro_order->where('status', '<>', MicroOrder::STATUS_CLOSED)->where('pre_profit_result', '<>', 0);
        $handle_orders = $handle_orders->groupBy('pre_profit_result');
        $handle_orders->each(function (\Illuminate\Database\Eloquent\Collection $item, $key) use ($price_range) {
            $pre_profit_result = $key;
            $item->each(function ($item, $key) use ($price_range, $pre_profit_result) {
                // dump($price_range);
                if ($pre_profit_result == 1) {
                    // 盈利
                    // echo date('Y-m-d H:i:s ') . '订单：' . $item->id . ',买' . $item->type_name . ',盈利处理:' . PHP_EOL;
                    $item->refresh();
                    if ($item->type == MicroOrder::TYPE_RISE) {
                        $item->end_price = $price_range['max_price'];
                    } elseif ($item->type == MicroOrder::TYPE_FALL) {
                        $item->end_price = $price_range['min_price'];
                    }
                    $item->save();
                } elseif ($pre_profit_result == -1) {
                    // 亏损
                    // echo date('Y-m-d H:i:s ') . '订单：' . $item->id . ',买' . $item->type_name . ',亏损处理:' . PHP_EOL;
                    $item->refresh();
                    if ($item->type == MicroOrder::TYPE_RISE) {
                        $item->end_price = $price_range['min_price'];
                    } elseif ($item->type == MicroOrder::TYPE_FALL) {
                        $item->end_price = $price_range['max_price'];
                    }
                    $item->save();
                }
            });
        });
        // echo date('Y-m-d H:i:s ') . '订单推算完成' . PHP_EOL;
    }

    /**
     * 取风控开始影响秒数
     *
     * @return void
     */
    public static function getRiskAdvanceSeconds()
    {
        $risk_end_ago_min = Setting::getValueByKey('risk_end_ago_min', 0);
        $risk_end_ago_max = Setting::getValueByKey('risk_end_ago_max', 0);
        $risk_advance_seconds = $risk_end_ago_max;
        return $risk_advance_seconds;
    }

    /**
     * 按用户进行风控
     *
     * @param \App\CurrencyMatch $currency_match
     * @param integer $risk_advance_seconds 风控影响秒数
     * @param Illuminate\Support\Carbon $now 当前时间
     * @return void
     */
    public static function riskByUser($currency_match, $risk_advance_seconds, $now)
    {
        //先找出来设定了按用户风控的列表
        $user_list = Users::where('risk', '<>', 0)
            ->pluck('id')
            ->all();
        if (count($user_list) <= 0) {
            //throw new \Exception('当前没有要风控的用户');
            return false;
        }
        $user_orders = MicroOrder::where('match_id', $currency_match->id)
            ->whereIn('user_id', $user_list)
            ->where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->where('handled_at', '<=', $now->addSeconds($risk_advance_seconds))
            ->get();
        if (count($user_orders) <= 0) {
            //throw new \Exception('当前被控用户没有要处理的订单或不在风控时间内');
            return false;
        }
        //按用户进行分类
        $user_groupby_order = $user_orders->groupBy('user_id');
        $user_count = $user_groupby_order->count(); //当前受风控影响的用户数
        $user_groupby_order->each(function (\Illuminate\Database\Eloquent\Collection $item, $key) {
            $user = Users::find($key);
            $ids = $item->pluck('id')->all();
            if (!empty($user->risk)) {
                //更新订单的预设盈亏结果
                MicroOrder::whereIn('id', $ids)
                    ->where('pre_profit_result', '<>', $user->risk)
                    ->update([
                        'pre_profit_result' => $user->risk,
                    ]);
            }
        });
        $user_id_list = $user_orders->pluck('id')->all();
        $micro_order = MicroOrder::where('match_id', $currency_match->id)
            ->whereIn('id', $user_id_list)
            ->where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->get();
        self::deduceOrderPrice($currency_match, $micro_order);
    }

    /**
     * 按订单进行风控
     *
     * @param \App\CurrencyMatch $currency_match
     * @param integer $risk_advance_seconds 风控影响秒数
     * @param Illuminate\Support\Carbon $now 当前时间
     * @return void
     */
    public static function riskByOrders($currency_match, $risk_advance_seconds, $now)
    {
        $pre_profit_orders = MicroOrder::where('match_id', $currency_match->id)
            ->where('pre_profit_result', '<>', 0)
            ->where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->where('handled_at', '<=', $now->addSeconds($risk_advance_seconds))
            ->get();
        self::deduceOrderPrice($currency_match, $pre_profit_orders);
    }

    /**
     * 按用户群进行风控
     *
     * @param \App\CurrencyMatch $currency_match
     * @param integer $risk_advance_seconds 风控影响秒数
     * @param Illuminate\Support\Carbon $now 当前时间
     * @return void
     */
    public static function riskByGroup($currency_match, $risk_advance_seconds, $now, $group_type = 0)
    {
        if ($group_type == 1 && $currency_match->risk_group_result == 0) {
            return false;
        }
        $need_risk_orders = MicroOrder::where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->when($currency_match->id > 0, function ($query) use ($currency_match) {
                $query->where('match_id', $currency_match->id);
            })
            ->where('handled_at', '<=', $now->addSeconds($risk_advance_seconds))
            ->get();
        if (count($need_risk_orders) <= 0) {
            return false;
        }
        $ids = $need_risk_orders->pluck('id')->all();
        $grouped_type_orders = $need_risk_orders->groupBy('type');
        $group_sum = [];
        //计算一下当前时间内未预设盈亏结果的是买涨的多还是买跌的多(按价值,不是按数量哦)
        $grouped_type_orders->each(function (\Illuminate\Database\Eloquent\Collection $item, $key) use (&$group_sum) {
            $sum_cost = 0;
            $sum_cost_with_profit = 0;
            $item->each(function ($item, $key) use (&$sum_cost, &$sum_cost_with_profit) {
                $sum_cost = bc_add($sum_cost, $item->cost);
                $sum_cost_with_profit = bc_add($sum_cost_with_profit, $item->cost_with_profit);
            });
            $group_sum[$key] = [
                'cost' => $sum_cost,
                'cost_with_profit' => $sum_cost_with_profit,
            ];
        });
        //dump($group_sum);
        if (count($group_sum) == 1) {
            $type_keys = array_keys($group_sum);
            $buy_type_many = reset($type_keys);
        } else {
            list(
                '1' => $rise,
                '2' => $fall
            ) = $group_sum;
            $compare_result = bc_comp($rise['cost_with_profit'], $fall['cost_with_profit']);
            if ($compare_result == 0) {
                //两者一样多
                $buy_type_many = mt_rand(1, 2); //随机
            } elseif ($compare_result == 1) {
                //买涨多
                $buy_type_many = MicroOrder::TYPE_RISE;
            } elseif ($compare_result == -1) {
                //买跌多
                $buy_type_many = MicroOrder::TYPE_FALL;
            }
        }
        if ($group_type == 0) {
            $risk_group_result = Setting::getValueByKey('risk_group_result');
        } else {
            $risk_group_result = $currency_match->risk_group_result;
        }
        //下单方向多的按群控结果处理
        MicroOrder::whereIn('id', $ids)
            ->where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->where('type', $buy_type_many)
            //->where('pre_profit_result', 0)
            ->update([
                'pre_profit_result' => $risk_group_result
            ]);
        //下单方向少的按相反结果处理
        MicroOrder::whereIn('id', $ids)
            ->where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->where('type', '<>', $buy_type_many)
            //->where('pre_profit_result', 0)
            ->update([
                'pre_profit_result' => $risk_group_result //原来是负值
            ]);
        //重新查询处理过预设盈亏的交易
        $need_risk_orders = MicroOrder::whereIn('id', $ids)
            ->where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->get();
        self::deduceOrderPrice($currency_match, $need_risk_orders);
    }

    /**
     * 按金额进行风控
     *
     * @param \App\CurrencyMatch $currency_match
     * @param integer $risk_advance_seconds 风控影响秒数
     * @param Illuminate\Support\Carbon $now 当前时间
     * @return void
     */
    public static function riskByMoney($currency_match, $risk_advance_seconds, $now)
    {
        $risk_money_profit_probability = Setting::getValueByKey('risk_money_profit_probability', '');
        //转换配置
        $rules = explode('|', $risk_money_profit_probability);
        $rules = array_map(function ($item) {
            list($range, $probability) = explode(':', $item);
            list($min, $max) = explode('-', $range);
            $profit_probability = $probability;
            $loss_probability  = 100 - ($probability > 100 ? 100 : $probability);
            return compact('min', 'max', 'profit_probability', 'loss_probability');
        }, $rules);
        //查询交易
        $need_risk_orders = MicroOrder::where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->when($currency_match->id > 0, function ($query) use ($currency_match) {
                $query->where('match_id', $currency_match->id);
            })
            ->where('handled_at', '<=', $now->addSeconds($risk_advance_seconds))
            ->get();
        $need_risk_orders->transform(function ($item, $key) use ($rules) {
            $item->refresh();
            if ($item->pre_profit_result <> 0) {
                return $item;
            }
            //检索当前金额在哪个风控区间内
            $current_rule = [];
            foreach ($rules as $value) {
                if (
                    $item->cost >= $value['min'] &&
                    ($item->cost < $value['max'] && $value['max'] > 0 || $value['max'] == 0)
                ) {
                    $current_rule = $value;
                    continue;
                }
            }
            if (empty($current_rule)) {
                return $item;
            }
            $probability_data = [
                [
                    'pre_profit_result' => 1,
                    'chance' => $current_rule['profit_probability'],
                ],
                [
                    'pre_profit_result' => -1,
                    'chance' => $current_rule['loss_probability'],
                ]
            ];
            $profit_result = Probability::lotteryRaffle($probability_data);
            //根据概率决定单子的否盈亏
            $item->pre_profit_result = $profit_result['pre_profit_result'];
            $item->save();
            return $item;
        });
        self::deduceOrderPrice($currency_match, $need_risk_orders);
    }

    /**
     * 按概率进行风控
     *
     * @param \App\CurrencyMatch $currency_match
     * @param integer $risk_advance_seconds 风控影响秒数
     * @param Illuminate\Support\Carbon $now 当前时间
     * @return void
     */
    public static function riskByProbability($currency_match, $risk_advance_seconds, $now)
    {
        //当前盈利概率
        $risk_profit_probability = Setting::getValueByKey('risk_profit_probability', 0);
        $risk_loss_probability = 100 -  ($risk_profit_probability > 100 ? 100 : $risk_profit_probability);
        //查询订单
        $need_risk_orders = MicroOrder::where('status', '<>', MicroOrder::STATUS_CLOSED)
            ->when($currency_match->id > 0, function ($query) use ($currency_match) {
                $query->where('match_id', $currency_match->id);
            })
            ->where('handled_at', '<=', $now->addSeconds($risk_advance_seconds))
            ->get();
        if (count($need_risk_orders) <= 0) {
            return false;
        }
        $probability_data = [
            [
                'pre_profit_result' => 1,
                'chance' => $risk_profit_probability,
            ],
            [
                'pre_profit_result' => -1,
                'chance' => $risk_loss_probability,
            ]
        ];
        $need_risk_orders->transform(function ($item, $key) use ($probability_data) {
            $profit_result = Probability::lotteryRaffle($probability_data);
            $item->refresh();
            //不处理已经风控过的订单
            if ($item->pre_profit_result == 0) {
                $item->pre_profit_result = $profit_result['pre_profit_result'];
                $item->save();
            }
            return $item;
        });
        self::deduceOrderPrice($currency_match, $need_risk_orders);    
    }

    /**
     * 内控检测和处理
     *
     * @param integer $match_id
     * @return void
     */
    public static function risk($match_id)
    {
        try {
            // echo date('Y-m-d H:i:s ') . '开始风控' . PHP_EOL;
            $currency_match = CurrencyMatch::find($match_id);
            $currency = Currency::find($currency_match->currency_id);
            if (!$currency_match) {
                return false;
            }
            $now = Carbon::now();
            $risk_advance_seconds = self::getRiskAdvanceSeconds();
            $risk_mode = Setting::getValueByKey('risk_mode', 0);
            // 0.无,1.点控，2.群控，3.金额，4.单控，5.概率
            // echo '当前风控模式：' . $risk_mode . PHP_EOL;
            switch ($risk_mode) {
                case 1:
                    self::riskByUser($currency_match, $risk_advance_seconds, $now);
                    break;
                case 2:
                    self::riskByGroup($currency_match, $risk_advance_seconds, $now);
                    break;
                case 3:
                    self::riskByMoney($currency_match, $risk_advance_seconds, $now);
                    break;
                case 4:
                    self::riskByOrders($currency_match, $risk_advance_seconds, $now);
                    break;
                case 5:
                    self::riskByGroup($currency_match, $risk_advance_seconds, $now, 1);
                    break;
                /*
                case 6:
                    self::riskByProbability($currency_match, $risk_advance_seconds, $now);
                    break;
                */
                default:
                    break;
            }
            self::riskByProbability($currency_match, $risk_advance_seconds, $now);
            // echo date('Y-m-d H:i:s ') . '风控完成' . PHP_EOL;
        } catch (\Throwable $th) {
            echo '发生异常:' . PHP_EOL;
            dump($th->getFile() . ','. $th->getLine() . ',' . $th->getMessage());
        }
    }

    /**
     * 平仓
     *
     * @param integer $match_id
     * @return void
     */
    public static function close($match_id)
    {
        try {
            $currency_match = CurrencyMatch::find($match_id);
            if (!$currency_match) {
                return false;
            }
            $opened_orders = self::getNeedCloseOrder($match_id);
            if (count($opened_orders) <= 0) {
                return false;
            }
            // $currency = Currency::find($currency_match->currency_id);
            // $quotation = CurrencyQuotation::where('match_id', $match_id)->first();
            // if (!$quotation) {
            //     return false;
            // }
            self::risk($match_id);
            
            MicroOrder::whereIn('id', $opened_orders->pluck('id')->all())
                ->where('status', 1)
                ->update([
                    'status' => 2,
                ]);
            $ids = $opened_orders->pluck('id')->all();
            
            //profit_type 1盈利,0平,-1亏损
            $closing_oreders = MicroOrder::where('status', MicroOrder::STATUS_CLOSING)
                ->whereIn('id', $ids)
                //->selectRaw('*')
                //->selectRaw('(CASE WHEN `end_price`=`open_price` THEN 0 WHEN (`type`=1 AND `end_price` > `open_price`) OR (`type`=2  AND  `end_price` < `open_price` ) THEN 1 ELSE -1 END) AS `profit_type`')
                ->get();
            $closing_oreders->transform(function (\App\MicroOrder $item, $key) {
                return $item->append('profit_type');
            });
            // echo date('Y-m-d H:i:s ') . '+++开始平仓+++' . PHP_EOL;
            foreach ($closing_oreders as $key => $value) {

                // 根据盈亏生成相关参数
                if ($value->profit_type == 1) {
                    //结算本金和利息
                    $profit_ratio = bc_div($value->profit_ratio, 100);
                    $capital = $value->number;
                    $fact_profit = bc_mul($capital, $profit_ratio); 
                    $change = bc_add($capital, $fact_profit);  
                    $memo =  '秒合约订单平仓,盈利结算';
                } elseif ($value->profit_type == 0) {
                    //结算本金,利息为0
                    $capital = $value->number;
                    $fact_profit = 0;
                    $change = $capital;  
                    $memo =  '秒合约订单平仓结算,平局结算';
                } elseif ($value->profit_type == -1) {
                    //本金填补亏损
                    $capital = 0;
                    $fact_profit = -$value->number;
                    $change = $capital;
                    $memo =  '秒合约订单,亏损结算';
                }
                $value->profit_result = $value->profit_type;
                $value->status = MicroOrder::STATUS_CLOSED;
                $value->fact_profits = $fact_profit;
                $value->complete_at = Carbon::now();
                // echo date('Y-m-d H:i:s ') . '===发送平仓通知消息===' . PHP_EOL;
                SendClosedMicroOrder::dispatch($value)->onQueue('micro_order:closed'); //先推送数据过去
                $value->save();
                // 查找钱包并结算
                $wallet = UsersWallet::where('currency', $value->currency_id)
                    ->where('user_id', $value->user_id)
                    ->first();

                // 判断是否保险订单
                if($value->is_insurance){
                    $currency_id = $value->currency_id;
                    $user_id = $value->user_id;
                    $user_insurance = UsersInsurance::where('user_id', $user_id)
                        ->whereHas('insurance_type', function ($query) use ($currency_id) {
                            $query->where('currency_id', $currency_id);
                        })
                        ->where('status', 1)
                        ->where('claim_status', 0)
                        ->first();
                    //如果是保险订单，并且该用户当前没有生效保险
                    if(!$user_insurance){
                        $change = 0;
                        $memo =  '秒合约订单平仓结算，无生效保险，资金不变';
                    }
                    $balance_type = 5;
                }else{
                    $balance_type = 4;
                }
                change_wallet_balance($wallet, $balance_type, $change, AccountLog::MICRO_TRADE_CLOSE_SETTLE, $memo, false, 0, 0, '订单号'.$value->id, true);
                //如果是保险仓交易
                if ($value->is_insurance) {
                    $insurance_if_end = self::checkInsuranceIfEnd($value->user_id, $value->currency_id);
                    if($insurance_if_end === true){
                        //解约
                        $rescission_res = self::insuranceRescission($value->user_id, $value->currency_id);
                        if($rescission_res !== true){
                            throw new \Exception($rescission_res);
                        }
                    }
                }
            }
            return $ids;
        } catch (\Throwable $th) {
            dump($th->getFile() . ','. $th->getLine() . ',' . $th->getMessage());
        }
    }

    /**
     * 取需要平仓的交易
     *
     * @param integer $match_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNeedCloseOrder($match_id = 0)
    {
        $now = Carbon::now();
        //待平仓的任务,按交易对进行分组
        $lists = MicroOrder::where('status', 1)
            ->when($match_id > 0, function ($query) use ($match_id) {
                $query->where('match_id', $match_id);
            })->where('handled_at', '<=', $now)
            ->get();
        return $lists;
    }

    /**
     * 检查保险是否爆仓
     */
    public static function checkInsuranceIfEnd($user_id, $currency_id){

        $user_insurance = UsersInsurance::where('user_id', $user_id)
            ->whereHas('insurance_type', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })
            ->where('status', 1)
            ->where('claim_status', 0)
            ->first();
        if(!$user_insurance){
            return false;
        }
        //保险类型
        $insurance_type = $user_insurance->insurance_type;

        //用户钱包
        $user_wallet = UsersWallet::where('user_id', $user_id)
            ->where('currency', $insurance_type->currency_id)
            ->first();
        //爆仓盈利条件
        $rescission_profit = bc_mul($user_insurance->amount, 1 + bc_div($insurance_type->profit_termination_condition, 100), 2);
        //达到条件，为真
        if($user_wallet->insurance_balance >= $rescission_profit){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 保险解约
     */
    public static function insuranceRescission($user_id, $currency_id, $auto = 0)
    {
        $user_insurance = UsersInsurance::where('user_id', $user_id)
            ->whereHas('insurance_type', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })
            ->where('status', 1)
            ->where('claim_status', 0)
            ->first();
        if(!$user_insurance){
            return "找不到该保险";
        }
        //保险类型
        $insurance_type = $user_insurance->insurance_type;

        //用户钱包
        $user_wallet = UsersWallet::where('user_id', $user_id)
            ->where('currency', $insurance_type->currency_id)
            ->first();
        //爆仓盈利条件
        $rescission_profit = bc_mul($user_insurance->amount, 1 + bc_div($insurance_type->profit_termination_condition, 100), 2);

        try {
            DB::beginTransaction();

            //将用户的保险状态改变
            $user_insurance->status = 0;
            $user_insurance->rescinded_at = \Carbon\Carbon::now()->toDateTimeString();
            $user_insurance->rescinded_type = $auto;//解约类型
            $user_insurance->save();

            //将平仓额度给予用户
            change_wallet_balance($user_wallet, 4, $rescission_profit, AccountLog::INSURANCE_RESCISSION_ADD,
                '保险解约，赔付金额');

            //扣除用户钱包保险金额
            change_wallet_balance($user_wallet, 5, -$user_wallet->insurance_balance, AccountLog::INSURANCE_RESCISSION1,
                '保险解约，扣除受保金额', false);

            change_wallet_balance($user_wallet, 5, -$user_wallet->lock_insurance_balance,
                AccountLog::INSURANCE_RESCISSION2, '保险解约，扣除保险金额', true);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return "解约失败:".$e->getMessage();
        }
    }
}
