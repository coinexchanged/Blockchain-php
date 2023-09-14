<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\MicroNumber;
use App\MicroOrder;
use App\MicroSecond;
use App\Currency;
use App\CurrencyMatch;
use App\Setting;
use App\UsersWallet;
use App\Users;

class MicroController extends Controller
{
    public function index()
    {
        return view('admin.micro.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new MicroNumber();
        } else {
            $result = MicroNumber::find($id);
        }
        $currencies = Currency::where('is_micro', 1)->get();

        return view('admin.micro.add')->with('result', $result)->with('currencies', $currencies);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $currency_id = $request->get('currency_id', '');
        $number = $request->get('number', '');

        if (empty($id)) {
            $micro_number = new MicroNumber();
        } else {
            $micro_number = MicroNumber::find($id);
            if ($micro_number == null) {
                return redirect()->back();
            }
        }
        $micro_number->currency_id = $currency_id;
        $micro_number->number = $number;

        DB::beginTransaction();
        try {
            $micro_number->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroNumber();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $nicro_number = MicroNumber::find($id);
        if (empty($nicro_number)) {
            return $this->error('参数错误');
        }
        try {
            $nicro_number->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    //micro_seconds

    public function secondsIndex()
    {
        return view('admin.micro.seconds_index');
    }

    public function secondsAdd(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new MicroSecond();
        } else {
            $result = MicroSecond::find($id);
        }
        //        $currencies = Currency::where('is_micro',1)->get();

        return view('admin.micro.seconds_add')->with('result', $result);
    }

    public function secondsPostAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $seconds = $request->get('seconds', '');
        $status = $request->get('status', '');
        $profit_ratio = $request->get('profit_ratio', '');

        if (empty($id)) {
            $result = new MicroSecond();
        } else {
            $result = MicroSecond::find($id);
            if ($result == null) {
                return redirect()->back();
            }
        }
        $result->seconds = $seconds;
        $result->profit_ratio = $profit_ratio;
        $result->status = $status;

        DB::beginTransaction();
        try {
            $result->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function secondsLists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroSecond();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function secondsDel(Request $request)
    {
        $id = $request->get('id', 0);
        $result = MicroSecond::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        try {
            $result->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function secondsStatus(Request $request)
    {
        $id = $request->get('id', 0);
        $result = MicroSecond::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        if ($result->status == 1) {
            $result->status = 0;
        } else {
            $result->status = 1;
        }
        try {
            $result->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function order()
    {
        $currencies = Currency::where('is_micro', 1)->get();
        $currency_matches = CurrencyMatch::where('open_microtrade', 1)->get();
        return view('admin.micro.orders')
            ->with('currencies', $currencies)
            ->with('currency_matches', $currency_matches);
    }

    public function orderList(Request $request)
    {
        $currency_id = $request->input('currency_id', -1);
        $match_id = $request->input('match_id', -1);
        $type = $request->input('type', -1);
        $account = $request->input('account', '');
        $name = $request->input('name', '');
        $limit = $request->input('limit', 10);
        $status = $request->input('status', -1);
        $start = $request->input("start_time", '');
        $end = $request->input("end_time", '');
        $pre_profit_result = $request->input('pre_profit_result', -2);
        $profit_result = $request->input('profit_result', -2);

        $results = MicroOrder::with(['currency', 'currencyMatch', 'user'])
            ->when($currency_id != -1, function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })->when($match_id != -1, function ($query) use ($match_id) {
                $query->where('match_id', $match_id);
            })->when($type != -1, function ($query) use ($type) {
                $query->where('type', $type);
            })->when($status != -1, function ($query) use ($status) {
                $query->where('status', $status);
            })->when($pre_profit_result != -2, function ($query) use ($pre_profit_result) {
                $query->where('pre_profit_result', $pre_profit_result);
            })->when($profit_result != -2, function ($query) use ($profit_result) {
                $query->where('profit_result', $profit_result);
            })->when($account != '' || $name != '', function ($query) use ($account, $name) {
                $query->whereHas('user', function ($query) use ($account, $name) {
                    $account != '' && $query->where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%');
                    $query->when($name != '', function ($query) use ($name) {
                        $query->whereHas('userReal', function ($query) use ($name) {
                            $query->where("name", 'like', '%' . $name . '%');
                        });
                    });
                });
            })->when($start !='', function ($query) use ($start) {
                $query->where('created_at','>=', $start);
            })->when($end !='', function ($query) use ($end) {
                $query->where('created_at','<=', $end);
            })->orderBy('id', 'desc')
            ->paginate($limit);
        $items = $results->getCollection();
        $items->transform(function ($item, $key) {
            return $item->append('pre_profit_result_name')->makeVisible('pre_profit_result');
        });
        $results->setCollection($items);
        return $this->layuiData($results);
    }

    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = MicroOrder::findOrNew($id);

        return view('admin.micro.edit', ['result' => $result]);
    }

    //编辑用户信息  
    public function editPost()
    {

        $risk = Input::get('risk', 0);

        $id = Input::get("id");

        if (empty($id)) return $this->error("参数错误");

        $res = MicroOrder::find($id);
        if (empty($res)) {
            return $this->error("数据未找到");
        }
        if ($res->status != 1) {
            return $this->error("数据状态下不能修改");
        }

        $res->pre_profit_result = $risk;


        DB::beginTransaction();

        try {
            $res->save();

            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function batchRisk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $risk = $request->input('risk', 0);
            if (empty($ids)) {
                throw new \Exception('请先选择要处理的交易');
            }
            if (!in_array($risk, [-1, 0, 1])) {
                throw new \Exception('风控类型不正确');
            }
            $affect_rows = MicroOrder::where('status', MicroOrder::STATUS_OPENED)
                ->whereIn('id', $ids)
                ->update([
                    'pre_profit_result' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
