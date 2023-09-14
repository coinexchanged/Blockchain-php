<?php

namespace App\Http\Controllers\Admin;

use App\Bank;
use App\Currency;
use App\LegalDeal;
use App\LegalDealSend;
use App\Seller;
use App\UserReal;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    public function index(){
        return view('admin.seller.index');
    }

    public function status(Request $request)
    {
        $id = $request->get('id', 0);
        $currency = Seller::find($id);
        if (empty($currency)) {
            return $this->error('参数错误');
        }
        if ($currency->status == 1) {
            $currency->status = 0;
        } else {
            $currency->status = 1;
        }
        try {
            $currency->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function lists(Request $request){
        $limit = $request->get('limit',10);
        $account_number = $request->get('account_number','');
        $result = new Seller();
        if (!empty($account_number)){
            $users = Users::where('account_number','like','%'.$account_number.'%')->get()->pluck('id');
            $result = $result->whereIn('user_id',$users);
        }
        $result = $result->orderBy('id','desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function add(Request $request){
        $id = $request->get('id',0);
        if (empty($id)){
            $acceptor = new Seller();
            $acceptor->create_time = time();
        }else{
            $acceptor = Seller::find($id);
        }
        $banks = Bank::all();
        $currencies = Currency::where('is_legal',1)->orderBy('id','desc')->get();
        return view('admin.seller.add')->with(['result'=>$acceptor,'banks'=>$banks,'currencies'=>$currencies]);
    }

    public function postAdd(Request $request){
        $id = $request->get('id',0);
        $account_number = $request->get('account_number','');
        $name = $request->get('name','');
        $mobile = $request->get('mobile','');
        $currency_id = $request->get('currency_id','');
        //$seller_balance = $request->get('seller_balance',0);
        $wechat_nickname = $request->get('wechat_nickname','');
        $wechat_account = $request->get('wechat_account','');
        $ali_nickname = $request->get('ali_nickname','');
        $ali_account = $request->get('ali_account','');
        $bank_id = $request->get('bank_id',0);
        $bank_account = $request->get('bank_account','');
        $bank_address = $request->get('bank_address','');

        //自定义验证错误信息
        $messages  = [
            'required'       => ':attribute 为必填字段',
        ];

        $validator = Validator::make($request->all(), [
            'account_number'=>'required',
            'name'=>'required',
            'mobile'=>'required',
            'currency_id'=>'required',
           //'seller_balance'=>'required',
            'wechat_nickname'=>'required',
            'wechat_account'=>'required',
            'ali_nickname'=>'required',
            'ali_account'=>'required',
            'bank_id'=>'required',
            'bank_account'=>'required',
            'bank_address'=>'required',
        ], $messages);

        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $self = Users::where('account_number',$account_number)->first();
        if (empty($self)){
            return $this->error('找不到此交易账号的用户');
        }
        $real = UserReal::where('user_id',$self->id)->where('review_status',2)->first();
        if (empty($real)) return $this->error('此用户还未通过实名认证');
        $currency = Currency::find($currency_id);
        if (empty($currency)){
            return $this->error('币种不存在');
        }
        if (empty($currency->is_legal)){
            return $this->error('该币不是法币');
        }
        $has = Seller::where('name',$name)->where('user_id','!=',$self->id)->where('currency_id',$currency_id)->first();
        if (empty($id) && !empty($has)){
            return $this->error('此法币 '.$name.' 商家名称已存在');
        }
        $has_user = Seller::where('user_id',$self->id)->where('currency_id',$currency_id)->first();
        if (!empty($has_user) && empty($id)){
            return $this->error('此用户已是此法币商家');
        }

        if (empty($id)){
            $acceptor = new Seller();
            $acceptor->create_time = time();
        }else{
            $acceptor = Seller::find($id);
        }
        $acceptor->user_id = $self->id;
        $acceptor->name = $name;
        $acceptor->mobile = $mobile;
        $acceptor->currency_id = $currency_id;
        //$acceptor->seller_balance = floatval($seller_balance);
        $acceptor->wechat_nickname = $wechat_nickname;
        $acceptor->wechat_account = $wechat_account;
        $acceptor->ali_nickname = $ali_nickname;
        $acceptor->ali_account = $ali_account;
        $acceptor->bank_id = intval($bank_id);
        $acceptor->bank_account = $bank_account;
        $acceptor->bank_address = $bank_address;
        $acceptor->status = 1;//后台添加默认通过
        try{
            $acceptor->save();
            return $this->success('操作成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }


    }


    public function delete(Request $request){
        $id = $request->get('id',0);
        $acceptor = Seller::find($id);
        if (empty($acceptor)){
            return $this->error('无此用户');
        }
        try{
            $acceptor->delete();
            return $this->success('删除成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }
    }


    public function sendBack(Request $request){
        $id = $request->get('id',0);
        if (empty($id)){
            return $this->error('参数错误');
        }
        
        try{
            DB::beginTransaction();
            $legal_send = LegalDealSend::lockForUpdate()->find($id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_send->is_shelves != 2) {
                throw new \Exception('必须下架后才可以撤销');
            }
            if ($legal_send->is_done != 0) {
                throw new \Exception('当前发布状态无法撤销');
            }
            if (bc_comp($legal_send->surplus_number) <= 0) {
                throw new \Exception('当前发布剩余数量不足,无法撤销');
            }
            LegalDealSend::sendBack($id);
            DB::commit();
            return $this->success('发布撤回成功,此发布改变为已完成状态');
        }catch (\Exception $exception){
            DB::rollback();
            return $this->error($exception->getMessage());
        }

    }

    public function sendDel(Request $request){
        $id = $request->get('id',0);
        if (empty($id)){
            return $this->error('参数错误');
        }

        $is = LegalDealSend::isHasIncompleteness($id);
        if ($is){
            return $this->error('该发布信息下有未完成订单，请先运行撤回发布再来删除');
        }

        DB::beginTransaction();
        try{
            $send = LegalDealSend::lockForUpdate()->find($id);
            if (empty($send)){
                return $this->error('无此记录');
            }
            $deals = LegalDeal::where('legal_deal_send_id',$id)->get();
            if (!empty($deals)){
                foreach ($deals as $deal){
                    $deal->delete();
                }
            }
            if ($send->type == 'sell'){
                
                if($send->surplus_number > 0){
                    $seller = Seller::lockForUpdate()->find($send->seller_id);
                    if (!empty($seller)){
                        $user_id = $seller->user_id;
                        
                        $user_wallet=UsersWallet::lockForUpdate()->where("user_id",$user_id)->where("currency", $seller->currency_id)->first();
                        $res1=change_wallet_balance($user_wallet,1,-$send->surplus_number, AccountLog::SELLER_BACK_SEND, '系统删除',true);
                        if ($res1 !== true) {
                            throw new \Exception('删除失败:减少冻结资金失败');
                        }
                        $res2=change_wallet_balance($user_wallet,1,$send->surplus_number, AccountLog::SELLER_BACK_SEND, '系统删除');
                        if ($res2 !== true) {
                            throw new \Exception('删除失败:增加余额失败');
                        }

                        $seller->lock_seller_balance = bc_sub($seller->lock_seller_balance,$send->surplus_number,5);

                        $seller->save();
                        
                    }

                }

            }

            $send->delete();
            DB::commit();
            return $this->success('删除成功');
        }catch (\Exception $exception){
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }



    public function is_shelves(Request $request){
        $id= $request->get('id',0);
        $is_shelves = $request->get('is_shelves',1);
        if (empty($id)){
            return $this->error('参数错误');
        }
        $send = LegalDealSend::find($id);
        if (empty($send)){
            return $this->error('无此记录');
        }
        if(empty($send->is_shelves))
        {
            $send->is_shelves=1;
            $send->save();
        }
        DB::beginTransaction();
        try{
            if($send->is_shelves==1)
            {
                $send->is_shelves=2;
            }
            elseif($send->is_shelves==2)
            {
                $send->is_shelves=1;
            }
            $send->save();
            DB::commit();
            return $this->success('操作成功');
        }catch (\Exception $exception){
            DB::rollback();
            return $this->error($exception->getMessage());
        }

    }
}
