<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\{
    Address, AccountLog, Currency, UsersInsurance, InsuranceType, InsuranceClaimApply, Setting, Users, UserCashInfo, UserReal, UsersWallet
};

class ClaimController extends Controller
{
    public function index()
    {
        //保险类型
        $ins_type = InsuranceType::where('status', 1)->get();
        return view("admin.claim.index")->with('ins_type', $ins_type);
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $status = $request->get('apply_status', -1);
        $type = $request->get('type', -1);

        $mobile = $request->get('mobile', '');
        $name = $request->get('name', null);

        $list = InsuranceClaimApply::where(function ($query) use ($start_time) {
            if (!empty($start_time)) {
                $query->where('created_at', '>=', $start_time);
            }
        })->where(function ($query) use ($end_time) {
            if (!empty($end_time)) {
                $query->where('created_at', '<=', $end_time);
            }
        })->where(function ($query) use ($status) {
            if ($status != -1) {
                $query->where('apply_status', $status);
            }
        })->where(function ($query) use ($type) {
            if ($type != -1) {
                $query->where('insurance_type', $type);
            }
        })->where(function ($query) use ($mobile) {
            if (!empty($mobile)) {
                $user = Users::where('account_number', $mobile)->first();
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
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($list);
    }

    /**
     * 确认索赔
     */
    public function affirm(Request $request)
    {
        $claim_apply_id = $request->get('id', 0);
        $claim_apply = InsuranceClaimApply::find($claim_apply_id);
        $admin = session()->get('admin_username');
        if (!$claim_apply) {
            return $this->error('错误参数');
        }
        if ($claim_apply->apply_status) {
            return $this->error('该理赔已处理');
        }
        $user_insurance = UsersInsurance::where('id', $claim_apply->user_insurance_id)->first();
        //保险类型
        $insurance_type = $user_insurance->insurance_type;

        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $claim_apply->user_id)
                ->where('currency', $insurance_type->currency_id)
                ->lockForUpdate()
                ->first();


            switch ($insurance_type->claim_direction) {
                case 1:
                    //索赔清除用户受保金额
                    change_wallet_balance($user_wallet, 5, -$user_wallet->insurance_balance,
                        AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[清除受保金额]', false);

                    //将保险受保金额给予用户保险账户
                    change_wallet_balance($user_wallet, 5, $claim_apply->compensate,
                        AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[赔偿受保金额]', false);
                    break;
                case 2:
                    //将用户的保险状态改变
                    $user_insurance->status = 0;
                    $user_insurance->rescinded_at = \Carbon\Carbon::now()->toDateTimeString();
                    $user_insurance->rescinded_type = 1;//解约类型
                    $user_insurance->save();
                    //索赔清除用户受保金额
                    change_wallet_balance($user_wallet, 5, -$user_wallet->insurance_balance,
                        AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[清除受保金额]', false);

                    //将保险受保金额给予用户的秒合约账户
                    change_wallet_balance($user_wallet, 4, $claim_apply->compensate,
                        AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[赔偿受保金额]', false);

                    change_wallet_balance($user_wallet, 5, -$user_wallet->lock_insurance_balance,
                        AccountLog::INSURANCE_RESCISSION2, '保险解约，扣除保险金额', true);
                    break;
                default:
                    throw  new \Exception('未知受保金额去向状态');
            }
            //更改索赔状态
            $user_insurance->claim_status=0;
            $user_insurance->save();

            //更改申请状态
            $claim_apply->apply_status = 1;
            $claim_apply->operator = $admin;
            $claim_apply->save();
            DB::commit();
            return $this->success('处理成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('处理失败：' . $e->getMessage());
        }
    }


    /**
     * 拒绝
     */
    public function reject(Request $request)
    {
        $claim_apply_id = $request->get('id', 0);
        $claim_apply = InsuranceClaimApply::find($claim_apply_id);
        if (!$claim_apply) {
            return $this->error('错误参数');
        }
        $user_insurance = UsersInsurance::find($claim_apply->user_insurance_id);
        $refuse_reason = $request->get('refuse_reason', '');
        $admin = session()->get('admin_username');
        try {
            DB::beginTransaction();
            $user_insurance->claim_status = 0;
            $user_insurance->save();

            $claim_apply->refuse_reason = $refuse_reason;
            $claim_apply->operator = $admin;
            $claim_apply->apply_status = 2;
            $claim_apply->save();
            DB::commit();
            return $this->success('成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->success('失败：' . $e->getMessage());
        }
    }
}
