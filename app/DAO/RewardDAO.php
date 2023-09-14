<?php
namespace App\DAO;

use Illuminate\Support\Facades\{DB, Log};
use App\{AccountLog, LeverTransaction, PrizePool, Setting, Users};
use App\DAO\PrizePool\{CandySender, CandyCalculator};

class RewardDAO
{
    /**
     * 向用户直属工作室发放奖励
     *
     * @return void
     */
    public static function rewardLeverFeeToAtelier($lever_trade)
    {
        $today = strtotime(date('Y-m-d'));
        $candy_tousdt = Setting::getValueByKey('candy_tousdt', 100);
        $candy_tousdt = bc_div($candy_tousdt, 100);
        $atelier_reward_day_must_trade = Setting::getValueByKey('atelier_reward_day_must_trade', 0); //工作室每日最低交易量
        $atelier_reward_day_limit = Setting::getValueByKey('atelier_reward_day_limit', 0); //工作室每日奖励上限
        $atelier_reward_ratio = Setting::getValueByKey('atelier_reward_ratio', 0); //工作室奖励比例百分比
        $atelier_reward_ratio = bc_div($atelier_reward_ratio, 100, 4); //工作室奖励比例转化小数
        $can_reward_qty = 0;

        $user_id = $lever_trade->user_id;
        $trade_id = $lever_trade->trade_id;
        $trade_fee = $lever_trade->trade_fee;
        $origin_reward_qty = bc_mul($trade_fee, $atelier_reward_ratio, 4); //奖励数量
        $convert_reward_qty = bc_div($origin_reward_qty, $candy_tousdt, 4); //换算成与usdt等值糖果
        $fact_convert_reward_qty = $convert_reward_qty;

        $user = Users::find($user_id);
        $parents_path = UserDAO::getParentsPathDesc($user);
        $ateliers = UserDAO::getParentsAtelier($user);

        if (count($ateliers) <= 0) {
            return;
        }
        $atelier =  $ateliers->first();
        if (!$atelier) {
            return;
        }
        $key = array_search($atelier->id, $parents_path);
        $current_level = $key + 1;
        //每日已开仓交易量
        $today_has_trades = LeverTransaction::where('user_id', $atelier->user_id)
            ->where('status', LeverTransaction::TRANSACTION)
            ->where('create_time', '>=', $today)
            ->count();
        if ($today_has_trades < $atelier_reward_day_must_trade) {
            return;
        }
        //每日已奖励数量
        $rewarded_qty = PrizePool::where('scene', PrizePool::LEVER_TRADE_FEE_ATELIER)
            ->where('sign', 0)
            ->where('status', 1)
            ->where('create_time', '>=', $today)
            ->sum('reward_qty');
        //奖励是否已达上限
        if (bc_comp($atelier_reward_day_limit, 0) > 0) {
            if (bc_comp($rewarded_qty, $atelier_reward_day_limit) >= 0) {
                return;
            }
            //计算还有多少才达到封顶
            $can_reward_qty = bc_sub($atelier_reward_day_limit, $rewarded_qty);
            //如果即将奖励的值超过封顶,就抹去多余的奖励,以保证奖励不会超过封顶
            bc_comp($convert_reward_qty, $can_reward_qty) > 0 && $fact_convert_reward_qty = $can_reward_qty;
        }

        $fact_reward_qty = bc_mul($fact_convert_reward_qty, $candy_tousdt, 4); //未折合usdt的糖果数量

        $prize_calculator = new CandyCalculator();
        $prize_sender = new CandySender();
        $attach_data = [
            'sign' => $current_level,
            'extra_data' => serialize([
                'trade_id' => $trade_id, //交易id
                'level' => $current_level, //用户是第几级
                'trade_fee' => $trade_fee, //交易手续费
                'atelier_reward_day_limit' => $atelier_reward_day_limit, //工作室日奖励上限
                'atelier_reward_ratio' => $atelier_reward_ratio, //奖励比例
                'rewarded_qty' => $rewarded_qty, //已奖励数量
                'can_reward_qty' => $can_reward_qty, //还能拿的奖励数量
                'convert_reward_qty' => $convert_reward_qty,
                'fact_convert_reward_qty' => $fact_convert_reward_qty,
                'fact_reward_qty' => $fact_reward_qty,
            ]),
        ];
        try {
            //插入奖励记录到奖池
            $prize_pool = PrizePool::calculate(
                $prize_calculator,
                PrizePool::LEVER_TRADE_FEE_ATELIER,
                $fact_reward_qty,
                $atelier,
                $user,
                '工作室' . $current_level . '级用户杠杆交易手续费结算',
                $attach_data
            );
            if (!$prize_pool) {
                throw new \Exception('交易id:' . $trade_id . ',向第' . $current_level . '级上级(id:' . $atelier->id . ')触发奖励失败');
            }
            //发放奖励
            $receive_result = PrizePool::send($prize_sender, $prize_pool);
            if (!$receive_result) {
                throw new \Exception('交易id:' . $trade_id . ',向第' . $current_level . '级上级(id:' . $atelier->id . ')发放奖励失败');
            }
        } catch (\Exception $e) {
            $path = base_path() . '/storage/logs/reward/lever_trade/atelier/';
            $filename = date('Ymd') . '.log';
            file_exists($path) || @mkdir($path);
            error_log(
                date('Y-m-d H:i:s') . PHP_EOL . $e->getMessage() . PHP_EOL,
                3,
                $path . $filename
            );
        }
    }

    /**
     * 奖励杠杆交易手续费
     * @param App\LeverTransaction $lever_trade 杠杆交易
     * @return void
     */
    public static function rewardLeverTransationFee($lever_trade)
    {
        $lever_fee_options = Setting::getValueByKey('lever_fee_options');
        $lever_fee_options = empty($lever_fee_options) ? [] : unserialize($lever_fee_options);

        //如果没有取到参数直接返回
        if (count($lever_fee_options) <= 0) {
            return ;
        }

        $generations = array_column($lever_fee_options, 'generation');
        $reward_ratio = array_column($lever_fee_options, 'reward_ratio');
        $need_has_trades_list = array_column($lever_fee_options, 'need_has_trades');
        array_multisort($generations, SORT_ASC, SORT_NUMERIC, $lever_fee_options);

        $max_generation = max($generations);

        $from_user_id = $lever_trade->user_id;
        $trade_fee = $lever_trade->trade_fee;
        $trade_id = $lever_trade->id;

        $from_user = Users::find($from_user_id);
        $parents = UserDAO::getParentsPathDesc($from_user, $max_generation);
        
        $prize_calculator = new CandyCalculator();
        $prize_sender = new CandySender();

        foreach ($parents as $key => $value) {
            try {
                $current_level = $key + 1; //当前用户是受奖励用户的第几级
                $has_trade_num = 0; //当前用户交易笔数
                $need_has_trades = 0; //当前用户需要交易的笔数
                if (!in_array($current_level, $generations)) {
                    continue;
                }
                $v_key = array_search($current_level, $generations); //查询键值

                $need_has_trades = $need_has_trades_list[$v_key];
                $current_rebate_ratio = $reward_ratio[$v_key];
                $current_user = Users::find($value);
                if (!$current_user) {
                    continue;
                }
                
                $candy_number = bc_div(bc_mul($trade_fee, $current_rebate_ratio), 100, 4);
                $attach_data = [
                    'sign' => $current_level,
                    'extra_data' => serialize([
                        'trade_id' => $trade_id, //交易id
                        'level' => $current_level, //用户是第几级
                        'trade_fee' => $trade_fee, //交易手续费
                        'current_rebate_ratio' => $current_rebate_ratio, //奖励比例
                    ]),
                ];
                //检测当前用户有没有自行体验X笔
                $has_trade_num = LeverTransaction::where('user_id', $value)
                    ->whereIn('status', [1, 2, 3])
                    ->count();
                if ($has_trade_num < $need_has_trades) {
                    continue;
                }
                //插入奖励记录到奖池
                $prize_pool = PrizePool::calculate(
                    $prize_calculator,
                    PrizePool::LEVER_TRADE_FEE,
                    $candy_number,
                    $current_user,
                    $from_user,
                    $current_level . '级用户杠杆交易手续费结算',
                    $attach_data
                );
                if (!$prize_pool) {
                    throw new \Exception('交易id:' . $trade_id . ',向第' . $current_level . '级上级(id:' . $current_user->id . ')触发奖励失败');
                }
                //发放奖励
                $receive_result = PrizePool::send($prize_sender, $prize_pool);
                if (!$receive_result) {
                    throw new \Exception('交易id:' . $trade_id . ',向第' . $current_level . '级上级(id:' . $current_user->id . ')发放奖励失败');
                }
            } catch (\Exception $e) {
                $path = base_path() . '/storage/logs/reward/lever_trade/';
                $filename = date('Ymd') . '.log';
                file_exists($path) || @mkdir($path);
                error_log(
                    date('Y-m-d H:i:s') . PHP_EOL . $e->getMessage() . PHP_EOL,
                    3,
                    $path . $filename
                );
            }
        }
    }

    /**
     * 取转账次数对应的积分奖励比例
     *
     * @param integer $transfer_out_times
     * @return float 返回奖励比例(百分比,需要自己除以100)
     */
    public static function getRatioByTurnsOutTimes($transfer_out_times)
    {
        $times_ratio = Setting::getValueByKey('transfer_out_ratio');
        empty($times_ratio) || $times_ratio = unserialize($times_ratio);
        $fact_ratio = self::getDataByRangeValue($times_ratio, $transfer_out_times);
        return $fact_ratio;
    }

    /**
     * 取范围内对应的值
     *
     * @param array $ratio_array 比例数组
     * @param float $compare_value 要比较的值
     * @return float
     */
    public static function getDataByRangeValue($ratio_array, $compare_value)
    {
        $fact_data = reset($ratio_array); //先给个默认值
        krsort($ratio_array);
        foreach ($ratio_array as $key => $value) {
            if ($compare_value >= $key) {
                $fact_data = $value;
                break;
            }
        }
        
        return $fact_data;
    }
    
    /**
     * 静态奖
     *
     * @param App\Users $user 用户模型实例
     * @return bool 成功返回真,失败返回假
     */
    public static function staticReward($user)
    {
        $today = strtotime(date('Y-m-d'));
        $static_day_release_ratio = Setting::getValueByKey('static_day_release_ratio');
        //先通过时间戳判断用户是否领取过
        if ($user->static_time > $today) {
            return false;
        }
        //再通过记录判断用户是否领取过
        $count = AccountLog::where('type', AccountLog::DAY_STATIC_RELEASE)
            ->where('created_time', '>=', $today)
            ->where('user_id', $user->id)
            ->count();
        $count || $count = 0;
        if ($count > 0) {
            return false;
        }
        try {
            DB::transaction(function () use ($user, $static_day_release_ratio) {
                $release_balance = round($user->integral * $static_day_release_ratio / 100, 5);
                $result = release_user_integral($user, $release_balance, AccountLog::DAY_STATIC_RELEASE, '每日静态释放奖励');
                if (!$result) {
                    throw new \Exception('释放积分失败');
                }
                $user->static_time = time();
                $user->save(); //更新用户的静态奖领取时间
            });
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * 余额转出级差奖
     *
     * @param app\Users $user 用户模型
     * @param float $transfer_out_qty 转出数量
     * @return bool
     */
    public static function levelDifferenceReward($user, $transfer_out_qty)
    {
        $parents = UserDAO::getParentsPathDesc($user);
        $times_ratio = Setting::getValueByKey('transfer_out_ratio');
        $times_ratio = empty($times_ratio) ? [] : unserialize($times_ratio);
        krsort($times_ratio);
        $max_ratio = reset($times_ratio);
        $current_max = self::getRatioByTurnsOutTimes($user->transfer_out_times); //取转账人的比例
        rsort($parents);
        $param = compact('times_ratio', 'max_ratio', 'current_max');
        try {
            DB::beginTransaction();
            foreach ($parents as $key => $value) {
                $parent = Users::find($value);
                if (!$parent) {
                    abort(400, '用户id:' . $user->id . '的上级(id:' . $value . ')不存在');
                    return false;
                }
                $transfer_out_times = $parent->transfer_out_times; //转出次数
                $current_ratio = self::getRatioByTurnsOutTimes($transfer_out_times); //根据转出次数算出比例
                if ($current_max >= $current_ratio) {
                    continue;
                }
                $fact_ratio = $current_ratio - $current_max; //比例差
                $param = compact('current_max', 'current_ratio',  'transfer_out_times', 'fact_ratio');
                $fact_reward_qty = $transfer_out_qty * $fact_ratio / 100; //应奖励的积分
                //向当前用户的钱包返比例差的积分
                $result = change_user_money($parent, 2, $fact_reward_qty, AccountLog::TRANSFER_OUT_LEVEL_DIFFERENCE_REWARD_INTEGRAL,'转出级差奖励积分');
                if ($result !== true) {
                    throw new \Exception('释放积分到余额失败:' . $result);
                }
                $current_max = $current_ratio; //改变指针
                if ($current_max == $max_ratio) {
                    break;
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            echo '<pre>';
            echo  '错误:' . $e->getMessage() . PHP_EOL . ',文件:' . $e->getFile() . PHP_EOL . '行号:'. $e->getLine();
            DB::rollBack();
            return false;
        }
    }

    /**
     * 余额兑换积分奖励(加速释放)
     *
     * @param app\common\model\User $user 用户模型
     * @param float $exchange_qty 兑换数量
     */
    public static function exchangeReward($user, $exchange_qty)
    {
        $path = UserDAO::getParentsPathDesc($user, 20);
        //array_shift($path); //删除直推上级
        if (count($path) < 1) {
            return false;
        }
        DB::beginTransaction();
        foreach ($path as $key => $value) {
            $fact_ratio = 0;
            $current_generations = $key + 1; //因为key从0开始所以加1;
            $parent = Users::find($value);
            //取当前被触发用户的级别
            $parent_level = $parent->level->code;
            if ($parent_level <= 1) {
                //普通会员不享受动态奖
                continue;
            }
            //根据代数来决定比例
            $can_generations = $parent->level->generations['exchange'];
            if ($can_generations < $current_generations) {
                //当前被触发人级别不能拿超过自己级别对应的代数
                continue;
            }
            if ($parent_level == 2 && $current_generations == 1) {
                //直推奖：根据直推人数来决定比例
                $children_qty = Users::where('parent_id', $value)->count();
                $children_qty || $children_qty = 0;
                $exchange_recommend_ratio = Setting::getValueByKey('exchange_recommend_ratio');
                $exchange_recommend_ratio = empty($exchange_recommend_ratio) ? [] : unserialize($exchange_recommend_ratio);
                $fact_ratio = self::getDataByRangeValue($exchange_recommend_ratio, $children_qty);
            } else {
                $ratio = Setting::getValueByKey('exchange_generations_ratio'); //取每一代对应的比例
                $ratio = empty($ratio) ? [] : unserialize($ratio);
                $fact_ratio = self::getDataByRangeValue($ratio, $current_generations);
            }   
            $fact_reward_qty = round($exchange_qty * $fact_ratio / 100, 5);
            //奖励不能超过积分
            $before_integral = $parent->integral;
            $before_integral < $fact_reward_qty && $fact_reward_qty = $parent->integral;
            if ($fact_reward_qty == 0) {
                continue;
            }
            try {
                $result = release_user_integral($parent, $fact_reward_qty, AccountLog::BALANCE_EXCHANGE_INTEGRAL_REWARD, '余额兑换积分奖励:');
                if (!$result) {
                    throw new \Exception('释放积分到余额失败');
                }
            } catch (\Exception $e) {
                echo '<pre>';
                echo  '错误:' . $e->getMessage() . PHP_EOL . ',文件:' . $e->getFile() . PHP_EOL . '行号:'. $e->getLine();
                DB::rollback();
                return false;
            }
        }
        DB::commit();
        return true;
    }

    /**
     * 余额增加奖励(转入加速释放)
     *
     * @param app\common\model\User $user 用户模型
     * @param float $add_qty 兑换数量
     * @return bool 成功返回真，否则假
     */
    public static function balanceAddReward($user, $add_qty)
    {
        $path = UserDAO::getParentsPathDesc($user, 20);
        if (count($path) < 1) {
            return false;
        }
        DB::beginTransaction();
        foreach ($path as $key => $value) {
            $fact_ratio = 0;
            $current_generations = $key + 1; //因为key从0开始所以加1;
            $parent = Users::find($value);
            //取当前被触发用户的级别
            $parent_level = $parent->level->code;
            if ($parent_level <= 1) {
                //普通会员不享受动态奖
                continue;
            } 
            $can_generations = $parent->level->generations['balance_add'];
            if ($can_generations < $current_generations) {
                //当前被触发人级别不能拿超过自己级别对应的代数
                continue;
            }
            if ($parent_level == 2 && $current_generations == 1) {
                //直推奖：根据直推人数来决定比例
                $children_qty = Users::where('parent_id', $value)->count();
                $children_qty || $children_qty = 0;
                $balanceadd_recommend_ratio = Setting::getValueByKey('balanceadd_recommend_ratio');
                $balanceadd_recommend_ratio = empty($balanceadd_recommend_ratio) ? [] : unserialize($balanceadd_recommend_ratio);
                $fact_ratio = self::getDataByRangeValue($balanceadd_recommend_ratio, $children_qty);
            } else {
                //根据代数来决定比例
                $ratio = Setting::getValueByKey('balanceadd_generations_ratio'); //取每一代对应的比例
                $ratio = empty($ratio) ? [] : unserialize($ratio);
                $fact_ratio = self::getDataByRangeValue($ratio, $current_generations);
            }
            $fact_reward_qty = round($add_qty * $fact_ratio / 100, 5);
            //奖励不能超过积分
            $before_integral = $parent->integral;
            $before_integral < $fact_reward_qty && $fact_reward_qty = $parent->integral;
            if ($fact_reward_qty == 0) {
                continue;
            }
            try {
                $result = release_user_integral($parent, $fact_reward_qty, AccountLog::TRANSFER_IN_PARENTS_REWARD_INTEGRAL, '余额转入奖励:');
                if (!$result) {
                    throw new \Exception('释放积分到余额失败');
                }
            } catch (\Exception $e) {
                echo '<pre>';
                echo  '错误:' . $e->getMessage() . PHP_EOL . ',文件:' . $e->getFile() . PHP_EOL . '行号:'. $e->getLine();
                DB::rollback();
                return false;
            }
        }
        DB::commit();
        return true;
    }

    /**
     * 余额减少奖励(转出加速释放)
     *
     * @param app\common\model\User $user 用户模型
     * @param float $sub_qty 兑换数量
     * @return bool 成功返回真，否则假
     */
    public static function balanceSubReward($user, $sub_qty)
    {
        $parents = UserDAO::getParentsPathDesc($user, 15);//查询用户的指定代数的上级(根据parents_path信息),$qty 要取的上级代数,不传或传null则取全部
        if (count($parents) < 1) {
            return false;
        }
        $fact_ratio = Setting::getValueByKey('balance_sub_ratio');
        DB::beginTransaction();
        foreach ($parents as $key => $value) {
            $current_generations = $key + 1; //因为key从0开始所以加1;
            $parent = Users::find($value);
            //取当前被触发用户的级别
            $parent_level = $parent->level->code;
            $can_generations = $parent->level->generations['balance_sub'];
            if ($parent_level <= 1 || $can_generations < $current_generations) {
                //普通会员不享受动态奖
                continue;
            }
            $fact_reward_qty = round($sub_qty * $fact_ratio / 100, 5);
            if ($fact_reward_qty == 0) {
                continue;
            }
            try {
                $result = change_user_money($parent, 2, $fact_reward_qty, AccountLog::TRANSFER_OUT_PARENTS_REWARD_INTEGRAL, '余额减少上级奖励-增加积分');
                if ($result !== true) {
                    throw new \Exception('奖励积分失败(余额转出上级奖励)'. $result);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return false;
            }
        }
        DB::commit();
        return true;
    }

    /**
     * 平级余额减少奖励积分(暂且只有转出)
     *
     * @param app\common\model\User $user 转出用户模型
     * @param float $qty 数量
     */
    public static function equalLevelReward($user, $qty)
    {
        $parents = UserDAO::getParentsPathDesc($user);
        if (count($parents) < 1) {
            return false;
        }
        $userlevel = $user->level->code;
        $fact_ratio = Setting::getValueByKey('equal_level_ratio');
        DB::beginTransaction();
        foreach ($parents as $key => $value) {
            $current_generations = $key + 1;
            //取当前被触发用户的级别
            $parent = Users::find($value);
            $parent_level = $parent->level->code;
            $can_generations = $parent->level->generations['equal_level'];
            if ($parent_level <= 1 || $can_generations < $current_generations || $userlevel != $parent_level) {
                //当前被触发人级别不能拿超过自己级别对应的代数,或者不是平级
                continue;
            }
            $fact_reward_qty = round($qty * $fact_ratio / 100, 4);
            try {
                $result = change_user_money($parent, 2, $fact_reward_qty, AccountLog::TRANSFER_OUT_EQUALLEVEL_REWARD_INTEGRAL, '余额减少平级奖励-增加积分');
                if ($result !== true) {
                    throw new \Exception('奖励积分失败(余额转出平级奖励):' . $result);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return false;
            }
        }
        DB::commit();
        return true;
    }
}
