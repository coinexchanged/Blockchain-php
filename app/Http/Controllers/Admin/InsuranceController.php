<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\InsuranceType;
use App\UsersInsurance;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceController extends Controller
{

    public function index()
    {
        return view('admin.insurance.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new InsuranceType();
        } else {
            $result = InsuranceType::find($id);
        }
        $currency = Currency::where('insurancable', 1)->get();
        return view('admin.insurance.add')->with([
            'result' => $result,
            'currency' => $currency
        ]);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $data = $request->except('id');

        try {
            DB::beginTransaction();
            InsuranceType::updateOrCreate(
                ['id' => $id],
                $data
            );
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
        $result = new InsuranceType();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $result = InsuranceType::find($id);
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

    /*
     * 开关
     */
    public function changeStatus(Request $request)
    {
        $id = $request->get('id', 0);
        $insurance_type = InsuranceType::find($id);
        if (empty($insurance_type)) {
            return $this->error('参数错误');
        }
        if ($insurance_type->status == 1) {
            $insurance_type->status = 0;
        } else {
            $insurance_type->status = 1;
        }
        try {
            $insurance_type->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function changeAutoClaim(Request $request)
    {
        $id = $request->get('id', 0);
        $insurance_type = InsuranceType::find($id);
        if (empty($insurance_type)) {
            return $this->error('参数错误');
        }
        if ($insurance_type->auto_claim == 1) {
            $insurance_type->auto_claim = 0;
        } else {
            $insurance_type->auto_claim = 1;
        }
        try {
            $insurance_type->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function changeTAdd1(Request $request)
    {
        $id = $request->get('id', 0);
        $insurance_type = InsuranceType::find($id);
        if (empty($insurance_type)) {
            return $this->error('参数错误');
        }
        if ($insurance_type->is_t_add_1 == 1) {
            $insurance_type->is_t_add_1 = 0;
        } else {
            $insurance_type->is_t_add_1 = 1;
        }
        try {
            $insurance_type->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    //============保险单===========

    public function orderIndex()
    {
        return view('admin.insurance.order_index');
    }

    public function orderLists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $mobile = $request->get('mobile', '');
        $name = $request->get('name', null);
        $type = $request->get('type', -1);
        $status = $request->get('status', -1);


        $result = new UsersInsurance();
        $result = $result->where(function ($query) use ($mobile) {
            if (!empty($mobile)) {
                $user = User::where('account_number', $mobile)->first();
                if (!empty($user)) {
                    $query->where('user_id', $user->id);
                }
            }
        })->where(function($query) use ($name){
            if(!empty($name)){
                $query->whereHas('user',function ($query) use ($name){
                    $query->whereHas('userReal',function ($query) use ($name){
                        $query->where('name',$name);
                    });
                });
            }
        })->where(function ($query) use ($type){
            if ($type != -1) {
                $query->whereHas('insurance_type',function ($query) use ($type){
                    $query->where('type',$type);
                });
            }
        })->where(function ($query) use ($status){
            if ($status != -1) {
                $query->where('status',$status);
            }
        });
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

}
