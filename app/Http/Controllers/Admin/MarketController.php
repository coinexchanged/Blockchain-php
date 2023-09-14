<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\UsersWallet;
use App\Users;
use App\Currency;
use App\MarketDay;
use App\MarketHour;

class marketController extends Controller
{
    public function index()
    {
        return view('admin.market.index');
    }

    public function add()
    {
        $legal = Currency::where('is_legal', 1)->where('is_display', 1)->get();
        $currency = Currency::where('is_display', 1)->get();

        return view('admin.market.add')->with(['rest' => $legal, 'list' => $currency]);
    }

    public function postAdd(Request $request)
    {
        $currency_id = $request->get('currency_id', 0);
        $legal_id = $request->get('legal_id', '');
        $start_price = $request->get('start_price', 0);
        $end_price = $request->get('end_price', '');
        $type = $request->get('type', '');
        $highest = $request->get('highest', '');
        $mminimum = $request->get('mminimum', '');
        $number = $request->get('number', 0);
        $times = $request->get('start_time', 0);
        if ($legal_id == $currency_id) {
            return $this->error('法币和币种不能一样');
        }
        if (empty($start_price) || empty($end_price) || empty($highest) || empty($mminimum)) {
            return $this->error('请把数据填写完整');
        }
        if ($type == 0) {
            $market = new MarketDay();
            $times = date('Y-m-d', strtotime($times));
            $market->times = $times;
        } else {
            $market = new MarketHour();
            $market->type = $type;
            $times = strtotime($times);
            $market->day_time = $times;
        }

        $market->currency_id = $currency_id;
        $market->legal_id = $legal_id;
        $market->start_price = $start_price;
        $market->end_price = $end_price;
        $market->highest = $highest;
        $market->mminimum = $mminimum;
        $market->number = $number;


        DB::beginTransaction();
        try {
            $market->save();//保存币种

            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }
//行情数据展示
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 20);
        //$account_number = $request->get('account_number','');
        $result = new MarketHour();
        $result = $result->orderBy('id', 'asc')->orderBy('id', 'desc')->paginate($limit);
        foreach ($result as $k => $v) {
            $legal = Currency::find($v->legal_id);
            $v->legal_name = $legal->name;
            $currency = Currency::find($v->currency_id);
            $v->currency_name = $currency->name;
            $v->day_time=date("Y-m-d H:i:s",$v->day_time);
        }
        return $this->layuiData($result);
    }

    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        $acceptor = MarketDay::find($id);
        if (empty($acceptor)) {
            return $this->error('无此记录');
        }
        try {
            $acceptor->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function isDisplay(Request $request)
    {
        $id = $request->get('id', 0);
        $currency = Currency::find($id);
        if (empty($currency)) {
            return $this->error('参数错误');
        }
        if ($currency->is_display == 1) {
            $currency->is_display = 0;
        } else {
            $currency->is_display = 1;
        }
        try {
            $currency->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

}
