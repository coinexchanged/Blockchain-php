<?php

namespace App\Http\Controllers\Admin;

use App\InsuranceRule;
use App\InsuranceType;
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

class InsuranceRuleController extends Controller
{
    public function index()
    {
        return view('admin.insurancerule.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new InsuranceRule();
        } else {
            $result = InsuranceRule::find($id);
        }
        $insurance_type = InsuranceType::all();

        return view('admin.insurancerule.add')->with('result', $result)->with('insurance_type', $insurance_type);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $existing_number = $request->get('existing_number', '');
        $amount = $request->get('amount', '');
        $place_an_order_max = $request->get('place_an_order_max', '');
        $insurance_type_id = $request->get('insurance_type_id', '');

        if (empty($id)) {
            $result = new InsuranceRule();
        } else {
            $result = InsuranceRule::find($id);
            if ($result == null) {
                return redirect()->back();
            }
        }
        $result->insurance_type_id = $insurance_type_id;
        $result->amount = $amount;
        $result->place_an_order_max = $place_an_order_max;
        $result->existing_number = $existing_number;

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

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new InsuranceRule();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $result = InsuranceRule::find($id);
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

}
