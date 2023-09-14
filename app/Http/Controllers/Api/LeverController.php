<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Setting;
use App\AccountLog;
use App\Currency;
use App\CurrencyQuotation;
use App\CurrencyMatch;
use App\LeverTransaction;
use App\Users;
use App\UsersWallet;
use App\TransactionComplete;
use App\TransactionIn;
use App\TransactionOut;
use App\Jobs\LeverClose;
use App\LeverMultiple;
use App\Events\LeverSubmitOrder;

class LeverController extends Controller
{
    /**
     * 取交易信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deal()
    {
        $user_id = Users::getUserId();
        $legal_id = Input::get("legal_id");
        $currency_id = Input::get("currency_id");
        if (empty($legal_id) || empty($currency_id)) {
            return $this->error("参数错误:(");
        }
        $lever_share_limit = [
            'min' => 1,
            'max' => 0,
        ];
        $curreny_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if ($curreny_match) {
            $lever_share_limit = array_merge($lever_share_limit, [
                'min' => $curreny_match->lever_min_share,
                'max' => $curreny_match->lever_max_share,
            ]);
        }
        $my_transaction = LeverTransaction::with('user')
            ->orderBy('id', 'desc')
            ->where("user_id", $user_id)
            ->where("status", LeverTransaction::TRANSACTION)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->orderBy("id", "desc")
            ->take(10)
            ->get();
        $last_price = LeverTransaction::getLastPrice($legal_id, $currency_id);
        $user_lever = 0;
        $all_levers = 0;
        if (!empty($user_id)) {
            $legal = UsersWallet::where("user_id", $user_id)->where("currency", $legal_id)->first();
            if ($legal) {
                $user_lever = $legal->lever_balance;
            }
            $all_levers = LeverTransaction::where("legal", $legal_id)
                ->where("currency", $currency_id)
                ->where("user_id", $user_id)
                ->where("status", LeverTransaction::TRANSACTION)
                ->selectRaw('sum(`number` * `price`) as `all_levers`')
                ->value('all_levers');
            $all_levers || $all_levers = 0;

        }
        //$match_transaction = $this->getLastMathTransaction($legal_id, $currency_id);
        $lever_transaction = $this->getLastLeverTransaction($legal_id, $currency_id);
        $ustd_price = 0;
        $last = TransactionComplete::orderBy('id', 'desc')
            ->where("currency", $legal_id)
            ->where("legal", 3)
            ->first();
        if (!empty($last)) {
            $ustd_price = $last->price;
        }
        if ($legal_id == 3) {
            $ustd_price = 1;
        }
        return $this->success([
            //"match_transaction" => $match_transaction,
            "lever_transaction" => $lever_transaction,
            "my_transaction" => $my_transaction,
            "lever_share_limit" => $lever_share_limit,
            "multiple" => LeverTransaction::leverMultiple($key = 0,$currency_id),
            "last_price" => $last_price,
//            "last_price" => 10,
            "user_lever" => $user_lever,
            "all_levers" => $all_levers,
            "ustd_price" => $ustd_price,
            "ExRAte" => Setting::getValueByKey('USDTRate', 6.5),
        ]);
    }

    /**
     * 交易列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealAll()
    {
        $user_id = Users::getUserId();
        $legal_id = Input::get("legal_id");
        $currency_id = Input::get("currency_id");
        $limit = Input::get("limit", 10);
        $page = Input::get("page", 1);
        if (empty($legal_id) || empty($currency_id)) {
            return $this->error("参数错误");
        }
        $lever_transaction = LeverTransaction::with('user')
            ->orderBy('id', 'desc')
            ->where("user_id", $user_id)
            ->where("status", LeverTransaction::TRANSACTION)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->paginate($limit);
        $user_wallet = UsersWallet::where('currency', $legal_id)->where('user_id', $user_id)->first();
        $balance = $user_wallet ? $user_wallet->lever_balance : 0;
        //取盈亏总额
        list(
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all
        ) = LeverTransaction::getUserProfit($user_id, $legal_id);
        //取该交易对盈亏总额
        list(
            'caution_money_total' => $caution_money,
            'origin_caution_money_total' => $origin_caution_money,
            'profits_total' => $profits
        ) = LeverTransaction::getUserProfit($user_id, $legal_id, $currency_id);
        $total_all_money = bc_add($caution_money_all, $balance);
        $hazard_rate = LeverTransaction::getWalletHazardRate($user_wallet);
        $data = [
            'balance' => $balance,
            'hazard_rate' => $hazard_rate,//风险率
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all,//持仓总盈亏
            'caution_money' => $caution_money,
            'origin_caution_money' => $origin_caution_money,
            'profits' => $profits,
            'order' => $lever_transaction,
        ];
//        var_dump($lever_transaction->toArray());die;
        return $this->success($data);
    }

    /**
     * 我的交易
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTrade()
    {
        $user_id = Users::getUserId();
        $legal_id = Input::get("legal_id", 0);
        $currency_id = Input::get("currency_id", 0);
        $status = Input::get("status", -1);
        $limit = Input::get("limit", 10);

        //接入风险率和持仓总盈亏
//        if (empty($legal_id) || empty($currency_id)) {
//            return $this->error("参数错误");
//        }
//        $lever_transaction = LeverTransaction::with('user')
//            ->orderBy('id', 'desc')
//            ->where("user_id", $user_id)
//            ->where("status", LeverTransaction::TRANSACTION)
//            ->where("currency", $currency_id)
//            ->where("legal", $legal_id)
//            ->paginate($limit);
//        $user_wallet = UsersWallet::where('currency', $legal_id)->where('user_id', $user_id)->first();
        $user_wallet = UsersWallet::where('currency', 3)->where('user_id', $user_id)->first();//法币只有USDT
        $balance = $user_wallet ? $user_wallet->lever_balance : 0;
        //取盈亏总额
        list(
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all
        ) = LeverTransaction::getUserProfit($user_id, 3);
        //取该交易对盈亏总额
        list(
            'caution_money_total' => $caution_money,
            'origin_caution_money_total' => $origin_caution_money,
            'profits_total' => $profits
        ) = LeverTransaction::getUserProfit($user_id, 3, $currency_id);
//        $total_all_money = bc_add($caution_money_all, $balance);
        $hazard_rate = LeverTransaction::getWalletHazardRate($user_wallet);
        $lever_transaction['rate_profits_total'] = [
            'hazard_rate' => $hazard_rate,
            'profits_total' => $profits_all,
        ];
        //接入风险率和持仓总盈亏end

        $param = compact('status', 'legal_id', 'currency_id');
        $lever_transaction['message'] = LeverTransaction::where(function ($query) use ($param) {
            extract($param);
            $status != -1 && $query->where('status', $status);
            $legal_id > 0 && $query->where('legal', $legal_id);
            $currency_id > 0 && $query->where('currency', $currency_id);
        })->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success($lever_transaction);
    }

    /**
     * 提交杆杠交易
     *
     * @return void
     */
//    public function submit()
//    {
//        $user_id = Users::getUserId();
//        $share = Input::get("share");
//        $multiple = Input::get("multiple");
//        $type = Input::get("type", "1");
//        $legal_id = Input::get("legal_id");
//        $currency_id = Input::get("currency_id");
//        $status = Input::get('status', LeverTransaction::TRANSACTION); //默认是市价交易,为0则是挂单交易
//        $target_price = Input::get('target_price', 0); //目标价格
//        $now = time();
//        $user_lever = 0;
//
//        if (empty($legal_id) || empty($currency_id) || empty($share) || empty($multiple)) {
//            return $this->error("缺少参数或传值错误");
//        }
//        $currency_match = CurrencyMatch::where('legal_id', $legal_id)
//            ->where('currency_id', $currency_id)
//            ->first();
//        if (!$currency_match) {
//            return $this->error('指定交易对不存在');
//        }
//        if ($currency_match->open_lever != 1) {
//            return $this->error('您未开通本交易对的交易功能');
//        }
//        //手数判断:大于0的整数,且在区间范围内
//        if ($share != intval($share) || !is_numeric($share) || $share <= 0) {
//            return $this->error('手数必须是大于0的整数');
//        }
//        if (bc_comp($currency_match->lever_min_share, $share) > 0) {
//            return $this->error('手数不能低于' . $currency_match->lever_min_share);
//        }
//        if (bc_comp($currency_match->lever_max_share, $share) < 0 && bc_comp($currency_match->lever_max_share, 0) > 0) {
//            return $this->error('手数不能高于' . $currency_match->lever_max_share);
//        }
//        //倍数判断
//        $multiples = LeverMultiple::where("type", 1)->pluck('value')->all();
//        if (!in_array($multiple, $multiples)) {
//            return $this->error('选择倍数不在系统范围');
//        }
//        //$lever_min_share->lever_max_share
//        $exist_close_trade = LeverTransaction::where('user_id', $user_id)->where('status', LeverTransaction::CLOSING)->count();
//        if ($exist_close_trade > 0) {
//            return $this->error('您有正在平仓中的交易,暂不能进行买卖');
//        }
//        if (!in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION])) {
//            return $this->error('交易类型错误');
//        }
//        if ($status == LeverTransaction::ENTRUST) {
//            $open_lever_entrust = Setting::getValueByKey('open_lever_entrust', 0);
//            if ($open_lever_entrust <= 0) {
//                return $this->error('该功能暂未开放');
//            }
//        }
//        //判断是否委托交易 (限价交易)
//        if ($status == LeverTransaction::ENTRUST && $target_price <= 0) {
//            return $this->error('限价交易价格必须大于0');
//        }
//        $overnight = $currency_match->overnight ?? 0;
//        //优先从行情取最新价格
//        $last_price = LeverTransaction::getLastPrice($legal_id, $currency_id);
//        if (empty($last_price)) {
//            return $this->error('当前没有获取到行情价格,请稍后重试');
//        }
//        //挂单委托(限价交易)价格取用户设置的
//        if ($status == LeverTransaction::ENTRUST) {
//            if ($type == LeverTransaction::SELL && $target_price <= $last_price) {
//                return $this->error('限价交易卖出不能低于当前价');
//            } elseif ($type == LeverTransaction::BUY && $target_price >= $last_price) {
//                return $this->error('限价交易买入价格不能高于当前价');
//            }
//            $origin_price = $target_price;
//        } else {
//            $origin_price = $last_price;
//        }
//        //交易手数转换
//        $lever_share_num = $currency_match->lever_share_num ?? 1;
//        $num = bc_mul($share, $lever_share_num);
//        //点差率
//        $spread = $currency_match->spread;
//        $spread_price = bc_div(bc_mul($origin_price, $spread), 100);
//        $type == LeverTransaction::SELL && $spread_price = bc_mul(-1, $spread_price); //买入应加上点差,卖出就减去点差
//        $fact_price = bc_add($origin_price, $spread_price); //收取点差之后的实际价格
//        $all_money = bc_mul($fact_price, $num, 5);
//        //计算手续费
//        $lever_trade_fee_rate = bc_div($currency_match->lever_trade_fee ?? 0, 100);
//        $trade_fee = bc_mul($all_money, $lever_trade_fee_rate);
//        DB::beginTransaction();
//        try {
//            $legal = UsersWallet::where("user_id", $user_id)
//                ->where("currency", $legal_id)
//                ->lockForUpdate()
//                ->first();
//            if (!$legal) {
//                throw new \Exception("钱包未找到,请先添加钱包");
//            }
//            $user_lever = $legal->lever_balance;
//            $caution_money = bc_div($all_money, $multiple); //保证金
//            $shoud_deduct = bc_add($caution_money, $trade_fee); //保证金+手续费
//            if (bc_comp($user_lever, $shoud_deduct) < 0) {
//                throw new \Exception($currency_match->legal_name . "余额不足,不能小于" . $shoud_deduct . '(手续费:' . $trade_fee . ')');
//            }
//            $lever_transaction = new LeverTransaction();
//            $lever_transaction->user_id = $user_id;
//            $lever_transaction->type = $type;
//            $lever_transaction->overnight = $overnight;
//            $lever_transaction->origin_price = $origin_price;
//            $lever_transaction->price = $fact_price;
//            $lever_transaction->update_price = $last_price;
//            $lever_transaction->share = $share;
//            $lever_transaction->number = $num;
//            $lever_transaction->origin_caution_money = $caution_money;
//            $lever_transaction->caution_money = $caution_money;
//            $lever_transaction->currency = $currency_id;
//            $lever_transaction->legal = $legal_id;
//            $lever_transaction->multiple = $multiple;
//            $lever_transaction->trade_fee = $trade_fee;
//            $lever_transaction->transaction_time = $now;
//            $lever_transaction->create_time = $now;
//            $lever_transaction->status = $status;
//            $result = $lever_transaction->save();
//            if (!$result) {
//                throw new \Exception("提交失败");
//            }
//            //扣除保证金
//            $result = change_wallet_balance(
//                $legal,
//                3,
//                -$caution_money,
//                AccountLog::LEVER_TRANSACTION,
//                '提交' . $currency_match->symbol . '杠杆交易,价格' . $fact_price . ',扣除保证金',
//                false,
//                0,
//                0,
//                serialize([
//                    'trade_id' => $lever_transaction->id,
//                    'all_money' => $all_money,
//                    'multiple' => $multiple,
//                ])
//            );
//            if ($result !== true) {
//                throw new \Exception('扣除保证金失败:' . $result);
//            }
//            //扣除手续费
//            $result = change_wallet_balance(
//                $legal,
//                3,
//                -$trade_fee,
//                AccountLog::LEVER_TRANSACTION_FEE,
//                '提交' . $currency_match->symbol . '杠杆交易,扣除手续费',
//                false,
//                0,
//                0,
//                serialize([
//                    'trade_id' => $lever_transaction->id,
//                    'all_money' => $all_money,
//                    'lever_trade_fee_rate' => $lever_trade_fee_rate,
//                ])
//            );
//            if ($result !== true) {
//                throw new \Exception('扣除手续费失败:' . $result);
//            }
//            DB::commit();
//            //推荐奖:手续费结算
//            event(new LeverSubmitOrder($lever_transaction));
//            return $this->success("提交成功");
//        } catch (\Exception $ex) {
//            DB::rollBack();
//            return $this->error($ex->getMessage());
//        }
//    }

    public function submit()
    {
        $user_id = Users::getUserId();
        $share = Input::get("share");
        $multiple = Input::get("multiple");
        $type = Input::get("type", "1");
        $legal_id = Input::get("legal_id");
        $currency_id = Input::get("currency_id");
        $status = Input::get('status', LeverTransaction::TRANSACTION); //默认是市价交易,为0则是挂单交易
        $target_price = Input::get('target_price', 0); //目标价格
        $now = time();
        $user_lever = 0;

        if (empty($legal_id) || empty($currency_id) || empty($share) || empty($multiple)) {
            return $this->error("缺少参数或传值错误");
        }
        $currency_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$currency_match) {
            return $this->error('指定交易对不存在');
        }
        if ($currency_match->open_lever != 1) {
            return $this->error('您未开通本交易对的交易功能');
        }
        //手数判断:大于0的整数,且在区间范围内
        if ($share != intval($share) || !is_numeric($share) || $share <= 0) {
            return $this->error('手数必须是大于0的整数');
        }
        if (bc_comp($currency_match->lever_min_share, $share) > 0) {
            return $this->error($this->returnStr('手数不能低于') . $currency_match->lever_min_share);
        }
        if (bc_comp($currency_match->lever_max_share, $share) < 0 && bc_comp($currency_match->lever_max_share, 0) > 0) {
            return $this->error($this->returnStr('手数不能高于') . $currency_match->lever_max_share);
        }
        //倍数判断
        $multiples = LeverMultiple::where("type", 1)->pluck('value')->all();
        if (!in_array($multiple, $multiples)) {
            return $this->error('选择倍数不在系统范围');
        }
        //$lever_min_share->lever_max_share
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)->where('status', LeverTransaction::CLOSING)->count();
        if ($exist_close_trade > 0) {
            return $this->error('您有正在平仓中的交易,暂不能进行买卖');
        }
        if (!in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION])) {
            return $this->error('交易类型错误');
        }
        if ($status == LeverTransaction::ENTRUST) {
            $open_lever_entrust = Setting::getValueByKey('open_lever_entrust', 0);
            if ($open_lever_entrust <= 0) {
                return $this->error('该功能暂未开放');
            }
        }
        //判断是否委托交易 (限价交易)
        if ($status == LeverTransaction::ENTRUST && $target_price <= 0) {
            return $this->error('限价交易价格必须大于0');
        }
        $overnight = $currency_match->overnight ?? 0;
        //优先从行情取最新价格
        $last_price = LeverTransaction::getLastPrice($legal_id, $currency_id);
        if (bc_comp($last_price, 0) <= 0) {
            return $this->error('当前没有获取到行情价格,请稍后重试');
        }
        //挂单委托(限价交易)价格取用户设置的
        if ($status == LeverTransaction::ENTRUST) {
            if ($type == LeverTransaction::SELL && $target_price <= $last_price) {
                return $this->error('限价交易卖出不能低于当前价');
            } elseif ($type == LeverTransaction::BUY && $target_price >= $last_price) {
                return $this->error('限价交易买入价格不能高于当前价');
            }
            $origin_price = $target_price;
        } else {
            $origin_price = $last_price;
        }
        //交易手数转换
        $lever_share_num = $currency_match->lever_share_num ?? 1;
        $num = bc_mul($share, $lever_share_num);
        //点差率
//        $spread = $currency_match->spread;
//        $spread_price = bc_div(bc_mul($origin_price, $spread), 100);
//        $type == LeverTransaction::SELL && $spread_price = bc_mul(-1, $spread_price); //买入应加上点差,卖出就减去点差
//        $fact_price = bc_add($origin_price, $spread_price); //收取点差之后的实际价格
//        $all_money = bc_mul($fact_price, $num, 5);


        //点差率  点差变成固定值 by tian
        $spread_price = $spread = $currency_match->spread;
        $type == LeverTransaction::SELL && $spread_price = bc_mul(-1, $spread_price); //买入应加上点差,卖出就减去点差
        $fact_price = bc_add($origin_price, $spread_price); //收取点差之后的实际价格
        $all_money = bc_mul($fact_price, $num, 5);

        //计算手续费
        $lever_trade_fee_rate = bc_div($currency_match->lever_trade_fee ?? 0, 100);
        $trade_fee = bc_mul($all_money, $lever_trade_fee_rate);
        DB::beginTransaction();
        try {
            $legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", $legal_id)
                ->lockForUpdate()
                ->first();
            if (!$legal) {
                throw new \Exception("钱包未找到,请先添加钱包");
            }
            $user_lever = $legal->lever_balance;
            $caution_money = bc_div($all_money, $multiple); //保证金

            $shoud_deduct = bc_add($caution_money, $trade_fee); //保证金+手续费
            if (bc_comp($user_lever, $shoud_deduct) < 0) {
                throw new \Exception($currency_match->legal_name . $this->returnStr('余额不足,不能小于') . $shoud_deduct . $this->returnStr('(手续费:') . $trade_fee . ')');
            }

            $lever_transaction = new LeverTransaction();
            $lever_transaction->user_id = $user_id;
            $lever_transaction->type = $type;
            $lever_transaction->overnight = $overnight;
            $lever_transaction->origin_price = $origin_price;
            $lever_transaction->price = $fact_price;
            $lever_transaction->update_price = $last_price;
            $lever_transaction->share = $share;
            $lever_transaction->number = $num;
            $lever_transaction->origin_caution_money = $caution_money;
            $lever_transaction->caution_money = $caution_money;
            $lever_transaction->currency = $currency_id;
            $lever_transaction->legal = $legal_id;
            $lever_transaction->multiple = $multiple;
            $lever_transaction->trade_fee = $trade_fee;
            $lever_transaction->transaction_time = $now;
            $lever_transaction->create_time = $now;
            $lever_transaction->status = $status;
            
            //追加用户的代理商关系
            $user=Users::find($user_id);
            $lever_transaction->agent_path =$user->agent_path;

            $result = $lever_transaction->save();
            if (!$result) {
                throw new \Exception("提交失败");
            }
            //扣除保证金
            $result = change_wallet_balance(
                $legal,
                3,
                -$caution_money,
                AccountLog::LEVER_TRANSACTION,
                '提交' . $currency_match->symbol . '杠杆交易,价格' . $fact_price . ',扣除保证金',
                false,
                0,
                0,
                serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'multiple' => $multiple,
                ])
            );
            if ($result !== true) {
                throw new \Exception($this->returnStr('扣除保证金失败:') . $result);
            }
            //扣除手续费
            $result = change_wallet_balance(
                $legal,
                3,
                -$trade_fee,
                AccountLog::LEVER_TRANSACTION_FEE,
                '提交' . $currency_match->symbol . '杠杆交易,扣除手续费',
                false,
                0,
                0,
                serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'lever_trade_fee_rate' => $lever_trade_fee_rate,
                ])
            );
            if ($result !== true) {
                throw new \Exception($this->returnStr('扣除手续费失败:') . $result);
            }
            DB::commit();
//            var_dump($lever_transaction->toArray());
            //推荐奖:手续费结算
//            $PP=event(new LeverSubmitOrder($lever_transaction));
//            var_dump($PP);die;
            return $this->success("提交成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 设置止盈止亏
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStopPrice()
    {
        $user_set_stopprice = Setting::getValueByKey('user_set_stopprice', 0);
        if (!$user_set_stopprice) {
            return $this->error('此功能系统未开放');
        }
        $id = Input::get('id', 0);
        $user_id = Users::getUserId();
        $target_profit_price = Input::get('target_profit_price', 0);
        $stop_loss_price = Input::get('stop_loss_price', 0);
        if ($target_profit_price <= 0 || $stop_loss_price <= 0) {
            return $this->error('止盈止损价格不能为0');
        }
        $lever_transaction = LeverTransaction::where('user_id', $user_id)
            ->where('status', LeverTransaction::TRANSACTION)
            ->find($id);
        if (!$lever_transaction) {
            return $this->error('找不到该笔交易');
        }
        if ($lever_transaction->type == 1) {
            //买入
            if ($target_profit_price <= $lever_transaction->price || $target_profit_price <= $lever_transaction->update_price) {
                return $this->error('买入(做多)止盈价不能低于开仓价和当前价');
            }
            if ($stop_loss_price >= $lever_transaction->price || $stop_loss_price >= $lever_transaction->update_price) {
                return $this->error('买入(做多)止亏价不能高于开仓价和当前价');
            }
        } else {
            //卖出
            if ($target_profit_price >= $lever_transaction->price || $target_profit_price >= $lever_transaction->update_price) {
                return $this->error('卖出(做空)止盈价不能高于开仓价和当前价');
            }
            if ($stop_loss_price <= $lever_transaction->price || $stop_loss_price <= $lever_transaction->update_price) {
                return $this->error('卖出(做空)止亏价不能低于开仓价和当前价');
            }
        }
        $target_profit_price > 0 && $lever_transaction->target_profit_price = $target_profit_price;
        $stop_loss_price > 0 && $lever_transaction->stop_loss_price = $stop_loss_price;
        $result = $lever_transaction->save();
        return $result ? $this->success('设置成功') : $this->error('设置失败');
    }

    /**
     * 平仓
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function close()
    {
        $user_id = Users::getUserId();
        $id = Input::get("id");
        if (empty($id)) {
            return $this->error("参数错误");
        }
        DB::beginTransaction();
        try {
            $lever_transaction = LeverTransaction::lockForupdate()->find($id);
            if (empty($lever_transaction)) {
                throw new \Exception("数据未找到");
            }
            if ($lever_transaction->user_id != $user_id) {
                throw new \Exception("无权操作");
            }
            if ($lever_transaction->status != LeverTransaction::TRANSACTION) {
                throw new \Exception("交易状态异常,请勿重复提交");
            }
            $return = LeverTransaction::leverClose($lever_transaction);
            if (!$return) {
                throw new \Exception("平仓失败,请重试");
            }
            if($lever_transaction->origin_price <= 0){
                throw new \Exception("交易异常，无法平仓");
            }
            DB::commit();
            return $this->success("操作成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 批量平仓(按买卖方向)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchCloseByType(Request $request)
    {
        $user_id = Users::getUserId();
        $legal_id = $request->input('legal_id', 0);
        $currency_id = $request->input('currency_id', 0);
        $type = $request->input('type', 0); //0.所有,1.买入(做多),2.卖出(做空)
        if (!in_array($type, [0, 1, 2])) {
            return $this->error('买入方向传参错误');
        }
        $lever = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where('user_id', $user_id)
            ->where(function ($query) use ($type, $legal_id, $currency_id) {
                !empty($legal_id) && $query->where('legal', $legal_id);
                !empty($currency_id) && $query->where('currency', $currency_id);
                !empty($type) && $query->where('type', $type);
            })->get();
        $task_list = $lever->pluck('id')->all();
        $result = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->whereIn('id', $task_list)
            ->update([
                'status' => LeverTransaction::CLOSING,
                'handle_time' => microtime(true),
            ]);
        if ($result > 0) {
            LeverClose::dispatch($task_list, true)->onQueue('lever:close');
        }
        return $result > 0 ? $this->success('提交成功,请等待系统处理') : $this->error('未找到需要平仓的交易');
    }

    /**
     * 批量平仓(按盈亏)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchCloseByProfit(Request $request)
    {
        $user_id = Users::getUserId();
        $type = $request->input('type'); //0.所有,1.盈,2.亏
        $lever = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where('user_id', $user_id)
            ->get();
        switch ($type) {
            case 1:
                $lever = $lever->where('profits', '>', 0);
                break;
            case 2:
                $lever = $lever->where('profits', '<', 0);
                break;
            default:
        }
        $task_list = $lever->pluck('id')->all();
        $result = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->whereIn('id', $task_list)
            ->update([
                'status' => LeverTransaction::CLOSING,
                'handle_time' => microtime(true),
            ]);
        if ($result > 0) {
            LeverClose::dispatch($task_list, true)->onQueue('lever:close');
        }
        return $result > 0 ? $this->success('提交成功,请等待系统处理') : $this->error('未找到需要平仓的交易');
    }

    /**
     * 取最近几条撮合交易
     *
     * @param integer $legal_id 法币id
     * @param integer $currency_id 交易币id
     * @param integer $limit 限制条数,默认5
     * @return array
     */
    public function getLastMathTransaction($legal_id, $currency_id, $limit = 5)
    {
        $in = TransactionIn::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'desc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit($limit)
            ->get();
        $out = TransactionOut::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'asc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit($limit)
            ->get()
            ->sortByDesc('price')
            ->values();
        return [
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * 取最近几条杠杆交易
     *
     * @param integer $legal_id 法币id
     * @param integer $currency_id 交易币id
     * @param integer $limit 限制条数,默认5
     * @return array
     */
    public function getLastLeverTransaction($legal_id, $currency_id, $limit = 5)
    {
        $in = LeverTransaction::with('user')
            ->where('legal', $legal_id)
            ->where('currency', $currency_id)
            ->where('type', LeverTransaction::BUY)
            ->where('status', LeverTransaction::TRANSACTION)
            ->orderBy('price', 'desc')
            ->limit($limit)
            ->get();
        $out = LeverTransaction::with('user')
            ->where('legal', $legal_id)
            ->where('currency', $currency_id)
            ->where('type', LeverTransaction::SELL)
            ->where('status', LeverTransaction::TRANSACTION)
            ->orderBy('price', 'asc')
            ->limit($limit)
            ->get()
            ->sortByDesc('price')
            ->values();
        return [
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * 取消挂单(撤单)
     *
     * @return boolean
     */
    public function cancelTrade(Request $request)
    {
        $user_id = Users::getUserId();
        $id = $request->input('id');
        try {
            //退手续费和保证金
            DB::transaction(function () use ($user_id, $id) {
                $lever_trade = LeverTransaction::where('user_id', $user_id)
                    ->where('status', LeverTransaction::ENTRUST)
                    ->lockForUpdate()
                    ->find($id);
                if (!$lever_trade) {
                    throw new \Exception('交易不存在或已撤单,请刷新后重试');
                }
                $legal_id = $lever_trade->legal;
                $refund_trade_fee = $lever_trade->trade_fee;
                $refund_caution_money = $lever_trade->caution_money;
                $legal_wallet = UsersWallet::where('user_id', $user_id)
                    ->where('currency', $legal_id)
                    ->first();
                if (!$legal_wallet) {
                    throw new \Exception('撤单失败:用户钱包不存在');
                }
                $result = change_wallet_balance(
                    $legal_wallet,
                    3,
                    $refund_trade_fee,
                    AccountLog::LEVER_TRANSACTIO_CANCEL,
                    '杠杆' . $lever_trade->type_name . '委托撤单,退回手续费',
                    false,
                    0,
                    0,
                    '',
                    true
                );
                if ($result !== true) {
                    throw new \Exception($this->returnStr('撤单失败:') . $result);
                }
                $result = change_wallet_balance(
                    $legal_wallet,
                    3,
                    $refund_caution_money,
                    AccountLog::LEVER_TRANSACTIO_CANCEL,
                    '杠杆' . $lever_trade->type_name . '委托撤单,退回保证金',
                    false,
                    0,
                    0,
                    '',
                    true
                );
                if ($result !== true) {
                    throw new \Exception($this->returnStr('撤单失败:') . $result);
                }
                $lever_trade->status = LeverTransaction::CANCEL;
                $lever_trade->complete_time = time();
                $result = $lever_trade->save();
                if (!$result) {
                    throw new \Exception('撤单失败:变更状态失败');
                }
            });
            return $this->success('撤单成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
