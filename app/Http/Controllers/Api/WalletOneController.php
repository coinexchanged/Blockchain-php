<?php
//钱包专用的控制器 交易所可以删掉这个控制器ldh
namespace App\Http\Controllers\Api;

use App\Currency;
use App\Ltc;
use App\LtcBuy;
use App\TransactionComplete;
use App\NewsCategory;
use App\Address;
use App\AccountLog;
use App\Setting;
use App\Users;
use App\UsersWallet;
use App\UsersWalletOut;
use App\WalletLog;
use App\Utils\RPC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;

class WalletOneController extends Controller
{
    public function add(){
        $user_id = Users::getUserId();
        // $token = Input::get("token",'');
        $memorizing_words = Input::get("memorizing_words","");
        $erc_address = Input::get("address","");
        $btc_address = Input::get("contentbtc","");
        // $wallet_name = Input::get("wallet_name","");
        $password_prompt = Input::get("password_prompt","");
        $password = Input::get("password","");
        if($password!=$password_prompt){
            return $this->error('两次密码不一致');
        }

        if (empty($user_id) || empty($memorizing_words) || empty($erc_address) || empty($password)) return $this->error("参数错误");

        $user = Users::find($user_id);
        if (empty($user)) return $this->error("用户未找到");

        $waller = UsersWallet::where("user_id",$user_id)->first();
        if ($waller) return $this->error("钱包已添加,请勿重复添加");
        // $currency = Currency::all()->toArray();
        DB::beginTransaction();
        try {
            $currency = Currency::all();
            $user->pay_password =  $password;
            $user->memorizing_words =  $memorizing_words;
            $user->save();
            // $address_url = config('wallet_api') . $user->id;
            $address_url = 'http://47.92.171.137:3000/word/getaddress_mll?password=swl910101&user_id=' . $user->id;
            $address = RPC::apihttp($address_url);
            $address = @json_decode($address, true);
            // return $address_url;
            foreach ($currency as $key => $value) {
                $userWallet = new UsersWallet();
                $userWallet->user_id = $user->id;
                if ($value->type == 'btc') {
                    $userWallet->address = $address["contentbtc"];
                    $userWallet->eth_address = $erc_address;
                } else {
                    $userWallet->eth_address = $btc_address;
                    $userWallet->address = $address["content"];
                }
                $userWallet->currency = $value->id;
                // $userWallet->memorizing_words = $memorizing_words;
                
                // $userWallet->address = $address;
                // $userWallet->password =
                $userWallet->create_time = time();
                $userWallet->save();//默认生成所有币种的钱包
            }
            DB::commit();
            return $this->success("添加成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
        
    }
    //钱包转交易所的方法
      //1111钱包的操作
      public function ltcSend(Request $request){
        $address = $request->input('address', '');
        $money = $request->input('money', '');
        $password = $request->input('password', '');
        $password = $request->input('currency_id', '');
        $user_id = Users::getUserId(Input::get("user_id"));
        $user= Users::find($user_id);
        $wallet = UsersWallet::where('user_id',$user_id)->first();
        if(empty($address)||empty($money)||$money<0||empty($wallet)){
            return $this->error('参数错误');
        }
        if($wallet->password!=$password){
            return $this->error('支付密码错误');
        }
        // $userWallet = UsersWallet::where('user_id',$user_id)->where('token','PB')->first();
        if($money>$userWallet->balance){
            return $this->error('余额不足');
        }
        // $user = Users::find($user_id);
     
        // $key = md5(time());
        $set_url = Settings::getValueByKey('send_url','');
        if(empty($set_url)){
            return $this->error('参数错误');
        }
        DB::beginTransaction();
        try{
            $userWallet->change_balance =  $userWallet->change_balance-$money;
            $userWallet->save();
            AccountLog::insertLog([
                'user_id'=>$user_id,
                'value'=>$money,
                'info'=>'转账至交易所钱包',
                'type'=>AccountLog::LTC_SEND
            ]); 

            $url = $set_url."/api/getLtcKMB?address=" . $address . "&money=" . $money;
            $data = RPC::apihttp($url);
            $data = @json_decode($data, true);
            if($data["type"]!='ok'){
                DB::rollBack();
                return $this->error($data["message"]);
            }
            DB::commit();
            return $this->success('转账成功');
        }catch(\Exception $rex){
            DB::rollBack();
            
            return $this->error($rex);
        }
    }
    //接收来自交易所的余额
    public function ltcGet(Request $request){
        $account_number = $request->input('account_number', '');
        $money = $request->input('money', '');
        // $key = $request->input('key', '');
        // if(md5(time())!=$key){
        //     return $this->error('系统错误');
        // }
        $user = Users::where('account_number',$account_number)->first();
        if(empty($user)) return $this->error('找不到用户');
        $userWallet = UsersWallet::where('user_id',$user->id)->first();
        if(empty($userWallet)) return $this->error('用户钱包未找到');
        DB::beginTransaction();
        try{
            $userWallet->balance =  $userWallet->balance+$money;
            $userWallet->save();
            AccountLog::insertLog([
                'user_id'=>$user->id,
                'value'=>$money,
                'info'=>'接收来自交易所的转账',
                'type'=>AccountLog::LTC_IN
            ]); 
            DB::commit();
            return $this->success('转账成功');
        }catch(\Exception $rex){
            DB::rollBack();
            
            return $this->error($rex);
        }
    }
    //钱包列表
    public function walletList(){
        $user_id = Users::getUserId();
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get();
        $userWallet = UsersWallet::where('user_id',$user_id)->first();
        if(empty($userWallet)){
            return $this->error('您还没有钱包');
        }
        $list = [];
        $total_cny = 0;
        foreach($currency as $k=>$v){
            $list[$k]['id'] = $v->id;
            $list[$k]['name'] = $v->name;
            $list[$k]['logo'] = $v->logo;
            $wallet = UsersWallet::where('user_id',$user_id)->where('currency',$v->id)->first();
            if(!empty($wallet)){
                $cny_price = Currency::getCnyPrice($v->id);
                // $list[$k]['cny_price'] = $cny_price;
                $list[$k]['balance'] = $wallet->change_balance;
                $list[$k]['lock_balance'] = $wallet->lock_change_balance;
                $list[$k]['cny_balance'] = bc_add($wallet->change_balance,$wallet->lock_change_balance,5)*$cny_price;
                $total_cny += $list[$k]['cny_balance'];
            }else{
                $list[$k]['balance'] = 0;
                $list[$k]['lock_balance'] = 0;
                $list[$k]['cny_balance'] = bc_add($wallet->change_balance,$wallet->lock_change_balance,5)*$cny_price;
                $total_cny += $list[$k]['cny_balance'];
            }
            
        }
        // $cny_price = Currency::getCnyPrice();
        // $total = 
        return $this->success(['wallet'=>$list,'total_cny'=>$total_cny]);
    }
    public function moneyRechange(Request $request){
        // $company_eth_address = Setting::getValueByKey("company_eth_address");
        // return $this->success(array("company_eth_address"=>$company_eth_address));
        $user_id = Users::getUserId();
        $currency_id = $request->input('currency_id', '');
        if(empty($user_id)||empty($currency_id)) return $this->error('参数错误');
        $userWallet = UsersWallet::where('user_id',$user_id)->where('currency',$currency_id)->first();
        $company_eth_address = $userWallet->eth_address;
        return $this->success(array("company_eth_address"=>$company_eth_address));

    }
    //转账
    public function walletChange(Request $request){
        $user_id = Users::getUserId();
        $currency_id = $request->input('id', '');
        $num = $request->input('number', '');
        $address = $request->input('address', '');
        $remarks = $request->input('remarks', '');
        $password = $request->input('password', '');
        if(empty($currency_id)||empty($num)||empty($address)||empty($remarks)||empty($password)){
            return $this->error('参数错误');
        }
        $user = Users::find($user_id);
        $wallet = UsersWallet::where('currency',$currency_id)->where('user_id',$user_id)->first();
        if($num>$wallet->change_balance) return $this->error('余额不足');
        if($num<=0) return $this->error('请输入正确的值');
        $to_wallet =  UsersWallet::where('address',$address)->where('currency',$currency_id)->first();
        if(empty($to_wallet)) return $this->error('地址输入有误');
        if($to_wallet->currency!=$currency_id) return $this->error('地址输入有误1');
        if($to_wallet->user_id==$user_id) return $this->error('不能转账给自己');
        if($password!=$user->pay_password) return $this->error('支付密码错误');
        $to_user = Users::find($to_wallet->user_id);
        
        
        DB::beginTransaction();
        try{
            $data_wallet1 = [
                'balance_type' =>  2,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' =>  $wallet->change_balance,
                'change' => -$num,
                'after' => bc_sub($wallet->change_balance, $num, 5),
            ];
            AccountLog::insertLog([
                'user_id' => $user_id,
                'value' => bc_mul($num, -1, 5),
                'info' => "向".$to_user->account_number."转账",
                'type' => AccountLog::CHANGEBALANCE,
                'currency' => $currency_id,
            ],$data_wallet1);
            $data_wallet2 = [
                'balance_type' =>  2,
                'wallet_id' => $to_wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' =>  $to_wallet->change_balance,
                'change' => $num,
                'after' => bc_add($to_wallet->change_balance, $num, 5),
            ];
            AccountLog::insertLog([
                'user_id' => $to_wallet->user_id,
                'value' => bc_mul($num, 1, 5),
                'info' => "来自".$user->account_number."的转账",
                'type' => AccountLog::CHANGEBALANCE,
                'currency' => $currency_id,
            ],$data_wallet2);
            $wallet->change_balance = bc_sub($wallet->change_balance,$num,5);
            $wallet->save();
            $to_wallet->change_balance= bc_add($wallet->change_balance,$num,5);
            $to_wallet->save();
            DB::commit();
            return $this->success('转账成功');
        }catch(\Exception $rex){
            DB::rollback();
            return $this->error($rex);
        }
    }
    public function accountList(){
        $user_id = Users::getUserId();
        $currency_id = Input::get('id', '');
        $limit = Input::get('limit','12');
        $page = Input::get('page','1');
        // if (empty($address)) return $this->error("参数错误");

        // $user = Users::fi->first();
        // if (empty($user)) return $this->error("数据未找到");

        $data = AccountLog::where("user_id",$user_id);
        if(!empty($currency_id)){
            $data = $data->where('currency',$currency_id);
        }
        $data = $data->orderBy('id', 'DESC')->paginate($limit);
        return $this->success(array(
            "user_id"=>$user_id,
            "data"=>$data->items(),
            "limit"=>$limit,
            "page"=>$page,
        ));
    }
    public function getInfo(){
        $user_id = Users::getUserId();
        return $this->success(Users::find($user_id));
    }
}
