<?php

namespace App\Http\Controllers\Api;

use App\Currency;
use App\Seller;
use App\Bank;
use App\Setting;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\UserReal;
use App\Users;
use Illuminate\Support\Facades\DB;
use App\AccountLog;
use App\Wallet;
use App\WalletLog;



class SellerController extends Controller
{
    public function lists(Request $request){
        $limit = $request->get('limit',10);
        $currency_id = $request->get('currency_id',0);
        if (empty($currency_id)){
            return $this->error('参数错误');
        }
        $currency = Currency::find($currency_id);
        if (empty($currency)){
            return $this->error('无此币种');
        }
        if (empty($currency->is_legal)){
            return $this->error('该币不是法币');
        }
        $results = Seller::where('currency_id',$currency->id)->orderBy('id','desc')->paginate($limit);
        return $this->pageData($results);
    }

    public function postAdd(Request $request){
        $tobe_seller_lockusdt=Setting::getValueByKey("tobe_seller_lockusdt");
        $id = $request->get('id',0);
        $account_number = $request->get('account_number','');
        $name = $request->get('name','');
        $mobile = $request->get('mobile','');
        $currency_id = $request->get('currency_id','');
        $seller_balance = $request->get('seller_balance',0);
        $wechat_nickname = $request->get('wechat_nickname','');
        $wechat_account = $request->get('wechat_account','');
        $ali_nickname = $request->get('ali_nickname','');
        $ali_account = $request->get('ali_account','');
        $bank_id = $request->get('bank_id',0);
        $bank_account = $request->get('bank_account','');
        $bank_address = $request->get('bank_address','');
        $alipay_qr_code = $request->get('alipay_qr_code','');
        $wechat_qr_code = $request->get('wechat_qr_code','');
        if(empty($account_number)) return $this->error('用户名不能为空');
        if(empty($name)) return $this->error('名称不能为空');
        if(empty($mobile)) return $this->error('电话不能为空');
        if(empty($currency_id)) return $this->error('资产不能为空');
        //自定义验证错误信息
        $messages  = [
            'required'       => ':attribute 为必填字段',
        ];

        $validator = Validator::make($request->all(), [
            // 'account_number'=>'required',
            // 'name'=>'required',
            // 'mobile'=>'required',
            // 'currency_id'=>'required',
//            'seller_balance'=>'required',
            // 'wechat_nickname'=>'required',
            // 'wechat_account'=>'required',
            // 'ali_nickname'=>'required',
            // 'ali_account'=>'required',
            // 'bank_id'=>'required',
            // 'bank_account'=>'required',
            // 'bank_address'=>'required',
            // 'alipay_qr_code'=>'required',
            // 'wechat_qr_code'=>'required',
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
            return $this->error($this->returnStr('此法币').$name.$this->returnStr('商家名称已存在'));
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
        $acceptor->seller_balance = floatval($seller_balance);
        $acceptor->wechat_nickname = $wechat_nickname;
        $acceptor->wechat_account = $wechat_account;
        $acceptor->ali_nickname = $ali_nickname;
        $acceptor->ali_account = $ali_account;
        $acceptor->bank_id = intval($bank_id);
        $acceptor->bank_account = $bank_account;
        $acceptor->bank_address = $bank_address;
        $acceptor->alipay_qr_code = $alipay_qr_code;
        $acceptor->wechat_qr_code = $wechat_qr_code;
        try{

            //成为商家扣除usdt币并记录日志
            $usdt = Currency::where('name','USDT')->select(['id'])->first();
            $user_wallet=UsersWallet::where("user_id",$self->id)->where("currency", $usdt->id)->first();
            //日志开始

            //增加杠杆币日志记录
            $result = change_wallet_balance(//1.法币,2.币币交易,3.杠杆交易
                $user_wallet,
                1,
                -$tobe_seller_lockusdt,
                AccountLog::TOBE_SELLER_SUB_USDT,
                '申请成为商家，扣除USDT' . -$tobe_seller_lockusdt,
                false,
                $self->id,
                0
            );
            if($result!="true")
            {
                return $this->error($result);
            }

            $acceptor->save();
            return $this->success('操作成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }


    }



    public function show_news(Request $request){
//        $id = $request->get('id',0);
//        if (empty($id)){
//            $acceptor = new Seller();
//            $acceptor->create_time = time();
//        }else{
//            $acceptor = Seller::find($id);
//        }
        $banks = Bank::all();
        $currencies = Currency::where('is_legal',1)->orderBy('id','desc')->get();
        return $this->success(['banks'=>$banks,'currencies'=>$currencies]);

    }
}
