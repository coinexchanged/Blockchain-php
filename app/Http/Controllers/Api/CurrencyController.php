<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Utils\RPC;
use App\Currency;
use App\TransactionComplete;
use App\Users;
use App\MarketHour;
use App\CurrencyQuotation;
use App\AreaCode;
use App\UsersWallet;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class CurrencyController extends Controller
{
    public function area_code()
    {
        $AreaCode = AreaCode::get()->toArray();
        return $this->success($AreaCode);
    }

    public function lists()
    {
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $legal = array();
        foreach ($currency as $c) {
            if ($c["is_legal"]) {
                array_push($legal, $c);
            }
        }

        return $this->success(array(
            "currency" => $currency,
            "legal" => $legal
        ));
    }

    public function lever()
    {
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $legal = array();
        foreach ($currency as $c) {
            if ($c["is_lever"]) {
                array_push($legal, $c);
            }
        }
        $time = strtotime(date("Y-m-d"));

        foreach ($legal as &$l) {
            $quotation = array();

            foreach ($currency as $cc) {
                if ($cc["id"] != $l["id"]) {
                    $last_price = 0;
                    $yesterday_last_price = 0;
                    $last = "";
                    $yesterday_last = "";
                    $proportion = 0.00;

                    $last = TransactionComplete::orderBy('create_time', 'desc')
                        ->where("currency", $cc["id"])
                        ->where("legal", $l["id"])
                        ->first();
                    $yesterday_last = TransactionComplete::orderBy('create_time', 'desc')
                        ->where("create_time", '<', $time)
                        ->where("currency", $cc["id"])
                        ->where("legal", $l["id"])
                        ->first();
                    !empty($last) && $last_price = $last->price;
                    !empty($yesterday_last) && $yesterday_last_price = $yesterday_last->price;

                    if (empty($last_price)) {
                        if ($yesterday_last_price) {
                            $proportion = -100.00;
                        }
                    } else {
                        if ($yesterday_last_price) {
                            $proportion = ($last_price - $yesterday_last_price) / $yesterday_last_price;
                        } else {
                            $proportion = +100.00;
                        }
                    }

                    array_push($quotation, array(
                        "id" => $cc["id"],
                        "name" => $cc["name"],
                        "last_price" => $last_price,
                        "proportion" => $proportion,
                        "yesterday_last_price" => $yesterday_last_price
                    ));
                }
            }
            $l["quotation"] = $quotation;
        }

        return $this->success($legal);
    }

    //BY tiandongliang
    public function quotation_tian()
    {
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $legal = array();
        foreach ($currency as $c) {
            if ($c["is_legal"]) {
                array_push($legal, $c);
            }
        }
        $time = strtotime(date("Y-m-d"));
        foreach ($legal as &$l) {
            $quotation = array();
            foreach ($currency as $key => $cc) {
                $l['quotation'] = CurrencyQuotation::orderBy('add_time', 'desc')->where("legal_id", $l["id"])->get()->toArray();
            }
            // $l["quotation"] = $cc;
            // var_dump($legal);die;
        }
        return $this->success($legal);
    }

    /**
     * 新行情for tradingview
     *
     * @return void
     */
    public function newTimeshars(Request $request)
    {
        $symbol = $request->get('symbol');
        $period = $request->get('period');
        $start = $request->get('from', null);
        $end = $request->get('to', null);

        $symbol = strtoupper($symbol);
        //类型，1=15分钟，2=1小时，3=4小时,4=一天,5=分时,6=5分钟，7=30分钟,8=一周，9=一月,10=一年
        $period_list = [
            '1min' => 5,
            '5min' => 6,
            '15min' => 1,
            '30min' => 7,
            '60min' => 2,
            '1D' => 4,
            '1W' => 8,
            '1M' => 9,
            '1day' => 4,
            '1week' => 8,
            '1mon' => 9,
            '1year' => 10,
        ];
        $periods = array_keys($period_list);
        $types = array_values($period_list);
        if ($start == null || $end == null) {
            return [
                'code' => -1,
                'msg' => 'error: start time or end time must be filled in',
                'data' => null
            ];
        }

        if ($start > $end) {
            return [
                'code' => -1,
                'msg' => 'error: start time should not exceed the end time.',
                'data' => null
            ];
        }
        if ($symbol == '' || stripos($symbol, '/') === false) {
            return [
                'code' => -1,
                'msg' => 'error: symbol invalid',
                'data' => null
            ];
        }

        if ($period == '' || !in_array($period, $periods)) {
            return [
                'code' => -1,
                'msg' => 'error: period invalid',
                'data' => null
            ];
        }
        $now = strtotime(date('Y-m-d H:i'));
        if ($period == '1min' && $end >= $now) {
            //最后一分钟数据不能使用
            $end = $now - 1;
        }
        $type = $period_list[$period];
        $symbol = explode('/', $symbol);
        list($base_currency, $quote_currency) = $symbol;
        $base_currency = Currency::where('name', $base_currency)
            ->where("is_display", 1)
            ->first();

        $quote_currency = Currency::where('name', $quote_currency)
            ->where("is_display", 1)
            ->where("is_legal", 1)
            ->first();
        if (!$base_currency || !$quote_currency) {
            return [
                'code' => -1,
                'msg' => 'error: symbol not exist',
                'data' => null
            ];
        }
        $legal_id = $quote_currency->id;
        $currency_id = $base_currency->id;
        //1分钟数据
        $minutes_quotation = MarketHour::orderBy('day_time', 'asc')
            ->where("currency_id", $currency_id)
            ->where("legal_id", $legal_id)->where('type', $type)
            ->where('day_time', '>=', $start)
            ->where('day_time', '<=', $end)
            ->get();
        $return = array();
        if ($minutes_quotation) {
            foreach ($minutes_quotation as $k => $v) {
                $arr = array(
                    "open" => $v->start_price,
                    "close" => $v->end_price,
                    "high" => $v->highest,
                    "low" => $v->mminimum,
                    "volume" => $v->number,
                    "time" => $v->day_time * 1000,
                );
                array_push($return, $arr);
            }
        } else {
            foreach ($minutes_quotation as $k => $v) {
                $arr = null;
                array_push($return, $arr);
            }
        }
        return [
            "code" => 1,
            "msg" => 'success:)',
            "data" => $return,
        ];
    }

    public function klineMarket(Request $request)
    {
        $symbol = $request->input('symbol');
        $period = $request->input('period');
        $from = $request->input('from', null);
        $to = $request->input('to', null);
        $symbol = strtoupper($symbol);
        $result = [];
        //类型，1=15分钟，2=1小时，3=4小时,4=一天,5=分时,6=5分钟，7=30分钟,8=一周，9=一月,10=一年
        $period_list = [
            '1min' => '1min',
            '5min' => '5min',
            '15min' => '15min',
            '30min' => '30min',
            '60min' => '60min',
            '1H' => '60min',
            '1D' => '1day',
            '1W' => '1week',
            '1M' => '1mon',
            '1Y' => '1year',
            '1day' => '1day',
            '1week' => '1week',
            '1mon' => '1mon',
            '1year' => '1year',
        ];
        if ($from == null || $to == null) {
            return [
                'code' => -1,
                'msg' => 'error: from time or to time must be filled in',
                'data' => $result,
            ];
        }
        if ($from > $to) {
            return [
                'code' => -1,
                'msg' => 'error: from time should not exceed the to time.',
                'data' => $result,
            ];
        }
        $periods = array_keys($period_list);
        if ($period == '' || !in_array($period, $periods)) {
            return [
                'code' => -1,
                'msg' => 'error: period invalid',
                'data' => $result,
            ];
        }
        if ($symbol == '' || stripos($symbol, '/') === false) {
            return [
                'code' => -1,
                'msg' => 'error: symbol invalid',
                'data' => $result,
            ];
        }
        $period = $period_list[$period];
        list($base_currency, $quote_currency) = explode('/', $symbol);
        $base_currency_model = Currency::where('name', $base_currency)
            ->where("is_display", 1)
            ->first();
        $quote_currency_model = Currency::where('name', $quote_currency)
            ->where("is_display", 1)
            ->where("is_legal", 1)
            ->first();
        if (!$base_currency_model || !$quote_currency_model) {
            return [
                'code' => -1,
                'msg' => 'error: symbol not exist',
                'data' => null
            ];
        }
        
        $result = MarketHour::getEsearchMarket($base_currency, $quote_currency, $period, $from, $to);
        // var_dump($result);
        // die;

        $result = array_map(function ($value) {
            $value['time'] = $value['id'] * 1000;
            $value['volume'] = $value['amount'] ?? 0;
            return $value;
        }, $result);
//        $result[10]['low']=$result[10]['low']-1200;
//        $result[10]['close']=$result[10]['close']-1000;
        return [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
    }

    public function TradeMarket(Request $request)
    {
        $quo = Currency::find($request->input('legal_id'))->name;
        $base = Currency::find($request->input('currency_id'))->name;


        $symbol = strtolower($base . $quo);
//        var_dump($base,$quo);
//        die;
        $url = "https://api.huobi.pro/market/history/trade?symbol={$symbol}&size=20";
        $res = json_decode(file_get_contents($url), true);


        $rsp = [];
        if (isset($res['data'])) {

            foreach ($res['data'] as $val) {
                if (count($rsp) >= 20) {
                    break;
                }
                array_walk($val['data'], function (&$v) use (& $rsp) {

                    $v['time'] = date('H:i:s', intVal($v['ts'] / 1000));
                    if (count($rsp) >= 20) {

                    } else {
                        $rsp[] = $v;
                    }
                });
            }
        }

//        var_dump($rsp);
        return $this->success($rsp);
    }

    public function newQuotation()
    {
        $currency = Currency::with('quotation')
            ->whereHas('quotation', function ($query) {
                $query->where('is_display', 1);
            })
            ->where('is_display', 1)
            ->where('is_legal', 1)
            ->get();
        return $this->success($currency);
    }

    public function dealInfo()
    {
        $legal_id = Input::get("legal_id");
        $currency_id = Input::get("currency_id");

        if (empty($legal_id) || empty($currency_id))
            return $this->error("参数错误");

        $legal = Currency::where("is_display", 1)
            ->where("id", $legal_id)
            ->where("is_legal", 1)
            ->first();
        $currency = Currency::where("is_display", 1)
            ->where("id", $currency_id)
            ->first();
        if (empty($legal) || empty($currency)) {
            return $this->error("币未找到");
        }
        $type = Input::get("type", "1");
        $seconds = 60;
        switch ($type) {
            case 2:
                $seconds = 15 * 60;
                break;
            case 3:
                $seconds = 60 * 60;
                break;
            case 4:
                $seconds = 4 * 60 * 60;
                break;
            case 5:
                $seconds = 24 * 60 * 60;
                break;
            default:
                $seconds = 60;
        }
        $time = time();
        $last_price = 0;
        $last = TransactionComplete::orderBy('create_time', 'desc')
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->first();
        $last && $last_price = $last->price;

        $now_quotation = TransactionComplete::getQuotation($legal_id, $currency_id, ($time - $seconds), $time);
        //$now_quotation = TransactionComplete::getQuotation_two($currency->name,$legal->name,$type);
        $quotation = array();
        for ($i = 0; $i < 10; $i++) {
            $end_time = $time - $i * $seconds;
            $start_time = $end_time - $seconds;

            $data = array();
            $data = $now_quotation = TransactionComplete::getQuotation($legal_id, $currency_id, $start_time, $end_time);
            array_push($quotation, $data);
        }
        return $this->success(array(
            "legal" => $legal,
            "currency" => $currency,
            "last_price" => $last_price,
            "now_quotation" => $now_quotation,
            "quotation" => $quotation
        ));
    }

    public function userCurrencyList()
    {
        $user_id = Users::getUserId();
        $currencies = Currency::where('is_display', 1)->orderBy('sort', 'desc')->get();
        $currencies = $currencies->filter(function ($item, $key) {
            $sum = array_sum([$item->is_legal, $item->is_lever, $item->is_match, $item->is_micro]);
            return $sum > 1;
        })->values();
        $currencies->transform(function ($item, $key) use ($user_id) {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $item->id)->first();
            $item->setVisible(['id', 'name', 'is_legal', 'is_lever', 'is_match', 'is_micro', 'wallet']);
            return $item->setAttribute('wallet', $wallet);
        });
        return $this->success($currencies);
    }
}
