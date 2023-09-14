<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\C2cDealSend;
use App\Currency;
use Illuminate\Support\Facades\DB;

class C2cDealSendController extends Controller
{
    public function index(){

        return view('admin.c2c.index');
    }
    public function list(Request $request){
        $limit = $request->get('limit', 10);
        $account_number = $request->get('account_number', '');
        //$seller_name = $request->get('seller_name', '');
        $type = $request->get('type', '');
       // $currency_id = $request->get('currency_id', 0);
        $result = new C2cDealSend();

        if(!empty($account_number)){

            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }

        if (!empty($type)) {

            $result = $result->where('type', $type);
        }
        // if (!empty($currency_id)) {

        //     $result = $result->where('currency_id', $currency_id);
        // }
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    
    //撤销
    public function sendBack(Request $request){
        $id = $request->get('id',0);
        if (empty($id)){
            return $this->error('参数错误');
        }
        $send = C2cDealSend::find($id);
        if (empty($send)){
            return $this->error('无此记录');
        }
        DB::beginTransaction();
        try{
            C2cDealSend::sendBack($id);
            DB::commit();
            return $this->success('发布撤回成功,此发布改变为已完成状态');
        }catch (\Exception $exception){
            DB::rollback();
            return $this->error($exception->getMessage());
        }

    }



}