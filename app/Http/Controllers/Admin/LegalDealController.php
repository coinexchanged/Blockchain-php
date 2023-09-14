<?php

namespace App\Http\Controllers\Admin;
use App\UsersWallet;
use App\AccountLog;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\LegalDealSend;
use Illuminate\Http\Request;
use App\LegalDeal;
use App\Currency;
use App\Seller;
use App\Users;

class LegalDealController extends Controller
{
    public function index()
    {

        $currency = Currency::where('is_legal', 1)->orderBy('id', 'desc')->get();//获取法币


        $start =strtotime(date('Y-m-d 00:00:00'));
        $end = strtotime(date('Y-m-d H:i:s'));

        //获取当天购买USDT
        $aaaa=LegalDeal::leftJoin("legal_deal_send","legal_deal.legal_deal_send_id","=","legal_deal_send.id")->where("legal_deal_send.type","=","sell")->where("legal_deal.create_time",">",$start)->where("legal_deal.is_sure","=",1)->select("legal_deal.*","legal_deal_send.type")->get();
        $todaybuy_usdt=0;
        foreach($aaaa as $key=>$value)
        {
            $todaybuy_usdt=$todaybuy_usdt+$value->number;
        }

        //获取当天出售USDT
        $bbbb=LegalDeal::leftJoin("legal_deal_send","legal_deal.legal_deal_send_id","=","legal_deal_send.id")->where("legal_deal_send.type","=","buy")->where("legal_deal.create_time",">",$start)->where("legal_deal.is_sure","=",1)->select("legal_deal.*","legal_deal_send.type")->get();
        $todaysell_usdt=0;
        foreach($bbbb as $key=>$value)
        {
            $todaysell_usdt=$todaysell_usdt+$value->number;
        }

        //USDT购买总数
        $cccc=LegalDeal::leftJoin("legal_deal_send","legal_deal.legal_deal_send_id","=","legal_deal_send.id")->where("legal_deal_send.type","=","sell")->where("legal_deal.is_sure","=",1)->select("legal_deal.*","legal_deal_send.type")->get();
        $buyall_usdt=0;
        foreach($cccc as $key=>$value)
        {
            $buyall_usdt=$buyall_usdt+$value->number;
        }

        //USDT出售总数
        $dddd=LegalDeal::leftJoin("legal_deal_send","legal_deal.legal_deal_send_id","=","legal_deal_send.id")->where("legal_deal_send.type","=","buy")->where("legal_deal.is_sure","=",1)->select("legal_deal.*","legal_deal_send.type")->get();
        $sellall_usdt=0;
        foreach($dddd as $key=>$value)
        {
            $sellall_usdt=$sellall_usdt+$value->number;
        }

        //usdt总冻结数量
        $eeee=UsersWallet::leftjoin("currency","users_wallet.currency","=","currency.id")->where("currency.name","=","USDT")->select("users_wallet.*","currency.name")->get();
        $all_lock_legal_balance=0;
        foreach($eeee as $key=>$value)
        {
            $all_lock_legal_balance=$all_lock_legal_balance+$value->lock_legal_balance;
        }


        //usdt总可用余额
        $ffff=UsersWallet::leftjoin("currency","users_wallet.currency","=","currency.id")->where("currency.name","=","USDT")->select("users_wallet.*","currency.name")->get();
        $all_usdt_can_use=0;
        foreach($ffff as $key=>$value)
        {
            $all_usdt_can_use=$all_usdt_can_use+$value->legal_balance;
        }

        return view('admin.legal.deal', ['currency' => $currency,'todaybuy_usdt'=>$todaybuy_usdt,'todaysell_usdt'=>$todaysell_usdt,'buyall_usdt'=>$buyall_usdt,'sellall_usdt'=>$sellall_usdt,'all_lock_legal_balance'=>$all_lock_legal_balance,'all_usdt_can_use'=>$all_usdt_can_use]);
    }

    public function list(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account_number = $request->get('account_number', '');
        $seller_name = $request->get('seller_name', '');
        $type = $request->get('type', '');
        $currency_id = $request->get('currency_id', 0);
        $result = new LegalDeal();
        if (!empty($account_number)) {
            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }
        if (!empty($seller_name))
        {
            $result = $result->whereHas('seller', function ($query) use ($seller_name) {
                $query->where('name', 'like', '%' . $seller_name . '%');
            });
//            var_dump($seller_name);die;
        }

        if (!empty($type)) {
            $result = $result->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });

        }
        if (!empty($currency_id)) {
            $result = $result->whereHas('legalDealSend', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            });
        }

        $result = $result->orderBy('id', 'desc')->paginate($limit);
//var_dump($result->toArray());die;
        return $this->layuiData($result);
    }



    /**
     * 后台取消订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLegalDealCancel(Request $request)  //tian add
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $legal_deal = LegalDeal::find($id);
        if (empty($legal_deal)) {
            return $this->error('无此记录');
        }
        DB::beginTransaction();
        try {
//            if ($legal_deal->is_sure > 0) {
//                DB::rollback();
//                return $this->error('该订单已操作，请勿取消666');
//            }
            $user_id = Users::getUserId();
//            if ($legal_deal->type == 'sell') { //用户端-购买
//                if ($user_id != $legal_deal->user_id) {
//                    DB::rollback();
//                    return $this->error('对不起，您无权操作');
//                }
//            } elseif ($legal_deal->type == 'buy') {//用户端出售
//                $seller = Seller::find($legal_deal->seller_id);
//                if ($user_id == $legal_deal->user_id) {
//                    DB::rollback();
//                    return $this->error('对不起，您无权操作');
//                }
//            }
    //            $number = $legal_deal->number;
    //            $legal_deal_send = LegalDealSend::LockForUpdate()->find($legal_deal->legal_deal_send_id);
    //            $users_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $legal_deal_send->currency_id)->first();
    //            if ($legal_deal_send->type == 'buy') { //求购
    //                // do something
    ////                if ($users_wallet->legal_balance < $number){
    ////                    DB::rollback();
    ////                    return $this->error('您的法币余额不足');
    ////                }
    ////                $legal_deal_send->surplus_number += $legal_deal->number;//
    //                $legal_deal_send->surplus_number = bc_add($legal_deal_send->surplus_number,$legal_deal->number,5);//
    //                if ($legal_deal_send->surplus_number > 0) {
    //                    $legal_deal_send->is_done = 0;
    //                }
    //                $data_wallet1 = [
    //                    'balance_type' => 1,
    //                    'wallet_id' => $users_wallet->id,
    //                    'lock_type' => 0,
    //                    'create_time' => time(),
    //                    'before' => $users_wallet->legal_balance,
    //                    'change' => $legal_deal->number,
    //                    'after' => bc_add($users_wallet->legal_balance, $legal_deal->number, 5),
    //                ];
    //                $data_wallet2 = [
    //                    'balance_type' => 1,
    //                    'wallet_id' => $users_wallet->id,
    //                    'lock_type' => 1,
    //                    'create_time' => time(),
    //                    'before' => $users_wallet->lock_legal_balance,
    //                    'change' => -$legal_deal->number,
    //                    'after' => bc_sub($users_wallet->legal_balance, $legal_deal->number, 5),
    //                ];
    ////                $users_wallet->legal_balance += $legal_deal->number;
    //                $users_wallet->legal_balance = bc_add($users_wallet->legal_balance,$legal_deal->number,5);
    ////                $users_wallet->lock_legal_balance -= $legal_deal->number;
    //                $users_wallet->lock_legal_balance = bc_sub($users_wallet->lock_legal_balance,$legal_deal->number,5);
    //                $users_wallet->save();
    //                $legal_deal_send->save();
    //
    //                AccountLog::insertLog(
    //                    [
    //                        'user_id' => $user_id,
    //                        'value' => $legal_deal->number,
    //                        'info' => '取消出售给商家法币',
    //                        'type' => AccountLog::LEGAL_DEAL_USER_SELL_CANCEL,
    //                        'currency' => $legal_deal_send->currency_id
    //                    ],
    //                    $data_wallet1
    //                );
    //                AccountLog::insertLog(
    //                    [
    //                        'user_id' => $user_id,
    //                        'value' => -$legal_deal->number,
    //                        'info' => '取消出售给商家法币',
    //                        'type' => AccountLog::LEGAL_DEAL_USER_SELL_CANCEL,
    //                        'currency' => $legal_deal_send->currency_id
    //                    ],
    //                    $data_wallet2
    //                );
    //            } elseif ($legal_deal_send->type == 'sell') { //出售
    ////                $legal_deal_send->surplus_number += $legal_deal->number;
    //                $legal_deal_send->surplus_number = bc_add($legal_deal_send->surplus_number,$legal_deal->number,5);
    //                if ($legal_deal_send->surplus_number > 0) {
    //                    $legal_deal_send->is_done = 0;
    //                }
    //                $legal_deal_send->save();
    //            }
    //            $legal_deal->is_sure = 2;
    //            $legal_deal->save();
    //            $legal_deal->update_time = time();
    //            $legal_send = LegalDealSend::find($legal_deal->legal_deal_send_id);
    //            $legal_send->surplus_number += $legal_deal->number;
    //            $legal_send->save();
            LegalDeal::cancelLegalDealById($id);
            DB::commit();
            return $this->success('操作成功，订单已取消');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


    //后台确认收到付款，订单状态改为完成
    //商家卖法币（后台确定,将币拨到用户）
    public function adminDoSure(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        
        try {
            DB::beginTransaction();
            $legal_deal = LegalDeal::find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }
           if ($legal_deal->is_sure == 1) {
               DB::rollback();
               return $this->error('该订单还未付款或已经操作过');
           }
            $seller = Seller::lockForUpdate()->find($legal_deal->seller_id);

            $legal_send = LegalDealSend::find($legal_deal->legal_deal_send_id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('订单异常');
            }
//            if ($legal_send->type == 'buy') {
//                DB::rollback();
//                return $this->error('您不能确认该订单');
//            }
            $user_wallet = UsersWallet::where('user_id', $legal_deal->user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
            $seller_user_wallet = UsersWallet::where('user_id', $seller->user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($user_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }

            //更新交易状态
            $legal_deal->is_sure = 1;
            $legal_deal->update_time = time();
            $legal_deal->save();

            //减少商家法币锁定余额
    
            $seller->lock_seller_balance = bc_sub($seller->lock_seller_balance,$legal_deal->number,5);
            change_wallet_balance($seller_user_wallet, 1,-$legal_deal->number,AccountLog::LEGAL_USER_BUY,"用户购买商家法币，解除商家法币冻结", true);

            //增加用户法币余额
            change_wallet_balance($user_wallet, 1,$legal_deal->number,AccountLog::LEGAL_USER_BUY,'在 ' . $seller->name . ' 购买法币成功');
     
            
            $seller->save();
            
            DB::commit();
            return $this->success('确认成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


    //商家购买用户法币(后台确定，将币拨到商家)
    public function admin_userDoSure(Request $request)  //用户确认收款 后台确认
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        DB::beginTransaction();
        try {
            $legal_deal = LegalDeal::find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_deal->is_sure != 3) {
                DB::rollback();
                return $this->error('该订单还未付款或已经操作过');
            }
            if ($legal_deal->is_sure == 1) {
                DB::rollback();
                return $this->error('该订单已经操作过');
            }
            
            $user_id = Users::getUserId();
            $user = Users::find($user_id);
//            if ($legal_deal->user_id != $user_id) {
//                DB::rollback();
//                return $this->error('对不起，您无权操作');
//            }
            $legal_send = LegalDealSend::find($legal_deal->legal_deal_send_id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('订单异常');
            }
//            if ($legal_send->type == 'sell') {
//                DB::rollback();
//                return $this->error('您不能确认该订单');
//            }
            $seller = Seller::find($legal_deal->seller_id);
            $user_wallet = UsersWallet::where('user_id', $legal_deal->user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();

            $seller_user_wallet = UsersWallet::where('user_id', $seller->user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($user_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }

            if (empty($seller)) {
                DB::rollback();
                return $this->error('商家不存在');
            }
           
            //更新交易状态
            $legal_deal->is_sure = 1;
            $legal_deal->update_time = time();
            $legal_deal->save();

            //减少用户法币锁定余额
           
            change_wallet_balance($user_wallet, 1,-$legal_deal->number,AccountLog::LEGAL_SELLER_BUY,'卖币成功',true);

        //增加商家法币余额
            change_wallet_balance($seller_user_wallet, 1,$legal_deal->number,AccountLog::LEGAL_SELLER_BUY,"商家购买用户法币，增加商家法币");
           
            DB::commit();
            return $this->success('确认成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


}