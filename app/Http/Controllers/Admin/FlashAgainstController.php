<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\{
    Address, AccountLog, Currency, FlashAgainst, IdCardIdentit, Setting, Users, UserCashInfo, UserReal, UsersWallet
};

class FlashAgainstController extends Controller
{
    public function index(){
        return view("admin.flashagainst.index");
    }

    public function lists(Request $request){
        $limit=$request->get('limit');
        $start_time=$request->get('start_time','');
        $status=$request->get('status',-1);
        $end_time=$request->get('end_time','');
        $mobile=$request->get('mobile','');

        $list=FlashAgainst::where(function ($query) use ($start_time){
            if (!empty($start_time)){
                $query->where('create_time','>=',strtotime($start_time));
            }
        })->where(function ($query) use ($end_time){
            if (!empty($end_time)){
                $query->where('create_time','>=',strtotime($end_time));
            }
        })->where(function ($query) use ($status){
            if ($status!=-1){
                $query->where('status',$status);
            }
        })->where(function ($query) use ($mobile){
            if (!empty($mobile)){
                $user=Users::where('account_number',$mobile)->first();
                if (!empty($user)){
                    $query->where('user_id',$user->id);
                }
            }
        })->orderBy('id','desc')->paginate($limit);
        return $this->layuiData($list);
    }

    public function affirm(Request $request){
        $id=$request->get('id','');
        if (empty($id)) return $this->error('参数错误');
        $result=FlashAgainst::find($id);
        if (empty($result)) return $this->error('参数错误');
        if ($result->status==1) return $this->error('已经通过，无法二次通过');
        if ($result->status==2) return $this->error('已经驳回，无法通过');
      
        try{
            DB::beginTransaction();
            $l_wallet=UsersWallet::where('user_id',$result->user_id)->where('currency',$result->left_currency_id)->lockForUpdate()->first();
            $r_wallet=UsersWallet::where('user_id',$result->user_id)->where('currency',$result->right_currency_id)->lockForUpdate()->first();
            if (empty($l_wallet)||empty($r_wallet)){
                DB::rollBack();
                return $this->error('钱包不存在');
            }

            $result2=change_wallet_balance($l_wallet,2,-$result->num,AccountLog::DEBIT_BALANCE_MINUS_LOCK,'闪兑扣除锁定金额',true);
            if ($result2!==true){
                DB::rollBack();
                return $this->error('操作失败');
            }
            $result1=change_wallet_balance($r_wallet,4,$result->absolute_quantity,AccountLog::DEBIT_BALANCE_ADD,'闪兑通过,增加余额');
            
            if ($result1 !== true){
            
                DB::rollBack();
                return $this->error('操作失败');
            }
            $result->status=1;
            $result->review_time=time();
            $result->save();
            DB::commit();
            return $this->success('成功');

        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage().'----'.$e->getLine());
        }


    }


    public function reject(Request $request){
        $id=$request->get('id','');
        if (empty($id)) return $this->error('参数错误');
        $result=FlashAgainst::find($id);
        if (empty($result)) return $this->error('参数错误');
        if ($result->status==1) return $this->error('已经通过，无法驳回');
        if ($result->status==2) return $this->error('已经驳回，无法二次驳回');
               
        try{
            DB::beginTransaction();
            $l_wallet=UsersWallet::where('user_id',$result->user_id)->where('currency',$result->left_currency_id)->lockForUpdate()->first();
            
            if (empty($l_wallet)){
                DB::rollBack();
                return $this->error('钱包不存在');
            } 

            $result2=change_wallet_balance($l_wallet,2,-$result->num,AccountLog::DEBIT_BALANCE_MINUS_LOCK,'闪兑扣除锁定金额',true);
            if ($result2!==true){
                DB::rollBack();
                return $this->error('操作失败');
            }
            $result1= change_wallet_balance($l_wallet,2,$result->num,AccountLog::DEBIT_BALANCE_ADD_REJECT,'闪兑驳回，增加余额');
            if ($result1!==true){
                DB::rollBack();
                return $this->error('操作失败');
            }
            $result->status=2;
            $result->review_time=time();
            $result->save();
            
            DB::commit();
            return $this->success('成功');

        }catch (\Exception $e){
            DB::rollBack();
            return $this->error('操作失败');
        }
    }
}
