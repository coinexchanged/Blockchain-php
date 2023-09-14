<?php

namespace App\Http\Controllers\Api;

use App\AccountLog;
use App\Currency;
use App\LegalDeal;
use App\LegalDealSend;
use App\Seller;
use App\Users;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserReal;
use App\UserCashInfo;
use App\Events\RechargeEvent;
use App\Setting;
use App\C2cDeal;

class LegalDealController extends Controller
{


    //c2c倒计时结束后调用
    public function handle_one(Request $request)
    {
        $id = $request->get('id', null);
        $userLegalDealCancel_time=Setting::getValueByKey("userLegalDealCancel_time")*60;
        $result=LegalDeal::find($id);//0未确认 1已确认 2已取消 3已付款

        $time=time();
        $create_time=strtotime($result->create_time);
//            var_dump($create_time+$userLegalDealCancel_time); var_dump($time);die;
        if(($create_time+$userLegalDealCancel_time)<=$time)
        {
            $id =$result->id;
            $ppp=LegalDeal::cancelLegalDealById($id);
            //取消订单数加一
            $aaaa=Users::find($result->user_id);
//            var_dump($result->user_id);die;
            $aaaa->today_LegalDealCancel_num=$aaaa->today_LegalDealCancel_num+1;
            $aaaa->LegalDealCancel_num__update_time=time();
            $aaaa->save();
            return $this->success('订单取消成功');
        }

    }

    public function postSend(Request $request)
    {
        $type = $request->get('type', null);
        $way = $request->get('way', null);
        $price = $request->get('price', null);
        $total_number = $request->get('total_number', null);
        $min_number = $request->get('min_number', null);
        $currency_id = $request->get('currency_id', null);
        $max_number=$request->get('max_number', $total_number);

        if (empty($type)) return $this->error('请选择需求类型');
        if (empty($way)) return $this->error('请选择交易方式');
        if (empty($price)) return $this->error('请填写单价');
        if (empty($total_number)) return $this->error('请填写数量');
        if (empty($min_number)) return $this->error('请填写最小交易数量');
        if (empty($currency_id)) return $this->error('请选择币种');
        if ($min_number > $total_number) return $this->error('最小交易数量不能大于总数量');

        if (empty($max_number)) return $this->error('请填写最大交易量');
        if ($max_number>$total_number || $max_number<=0 || !is_numeric($max_number)){
            return $this->error('请填写正确的最大交易量');
        }
        $currency = Currency::where('id',$currency_id)->select(['id'])->first();
        if(empty($currency)){
            return $this->error('币种信息有误');
        }

        DB::BeginTransaction();
        try {
            $user_id = Users::getUserId();
            $seller = Seller::lockForUpdate()->where('user_id', $user_id)->where('currency_id', $currency_id)->first();
            if (empty($seller)) {
                DB::rollback();
                return $this->error('对不起，您不是该法币的商家');
            }
            if ($type == 'sell') {   //如果商家发布出售信息

                if ($seller->legal_balance < $total_number) {
                    DB::rollback();
                    return $this->error('对不起，您的商家账户不足');
                }

                $user_wallet=UsersWallet::lockForUpdate()->where("user_id",$user_id)->where("currency", $currency->id)->first();
                $res1 = change_wallet_balance($user_wallet, 1, $total_number * -1, AccountLog::LEGAL_DEAL_SEND_SELL, '商家发布出售');
                if ($res1 !== true) {
                    throw new \Exception($res1);
                }
                
                $res2 = change_wallet_balance($user_wallet, 1, $total_number, AccountLog::LEGAL_DEAL_SEND_SELL, '商家发布出售,冻结增加',1);
               
                if ($res2 !== true) {
                    throw new \Exception($res2);
                }

                $seller->lock_seller_balance = bc_add($seller->lock_seller_balance,$total_number,5);
                $seller->save();

            }

            $legal_deal_send = new LegalDealSend();
            $legal_deal_send->seller_id = $seller->id;
            $legal_deal_send->currency_id = $currency_id;
            $legal_deal_send->type = $type;
            $legal_deal_send->way = $way;
            $legal_deal_send->price = $price;
            $legal_deal_send->total_number = $total_number;
            $legal_deal_send->surplus_number = $total_number;
            $legal_deal_send->min_number = $min_number;
            $legal_deal_send->max_number = $max_number;
            $legal_deal_send->create_time = time();
            $legal_deal_send->save();
            DB::commit();
            return $this->success('发布成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }


    }

    /**
     * 商家详情信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerInfo(Request $request)
    {
        $id = $request->get('id', null);
        $type = $request->get('type',null);
        $was_done =  $request->get('was_done','false');
        $limit = $request->get('limit', 10);

        if (empty($id)) return $this->error('参数错误');
        $seller = Seller::find($id);
        if (empty($seller)) return $this->error('无此商家');
        $beforeThirtyDays = Carbon::today()->subDay(30)->timestamp;   //30天前
        $results = Seller::withCount(['legalDeal as total', 'legalDeal as done' => function ($query) {
            $query->where('is_sure', 1);
        }, 'legalDeal as thirtyDays' => function ($query) use ($beforeThirtyDays) {
            $query->where('is_sure', 1)->where('update_time', '>=', $beforeThirtyDays);
        }])->find($id);
        $lists = LegalDealSend::where('seller_id', $id);
        //是否完成
        if($was_done == 'true'){
            $lists = $lists->where('is_done','=','1');
        }elseif($was_done == 'false'){
            $lists = $lists->where('is_done','=','0');
        }
        //出售还是购买
        if($type == 'buy'){
            $type = 'buy';
            $lists = $lists->where('type',$type);
        }elseif($type == 'sell'){
            $type = 'sell';
            $lists = $lists->where('type',$type);
        }

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $results->lists = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($results);
    }

    //添加商家筛选
    public function tradeList(Request $request)
    {
        $type = $request->get('type',null);
        $was_done =  $request->get('was_done',null);
        $limit = $request->get('limit', 10);

        $id = $request->get('id',0);//商家id

        //$id = Users::getUserId();
        $seller = Seller::where('id', $id)->first();

        if (empty($seller)) {
            return $this->error('您不是商家');
        }
//        $lists = LegalDealSend::where('seller_id', $seller->id);
        $lists = LegalDealSend::where('seller_id', $seller->id);
        //是否完成
        if ($was_done == 'true') {
            //$lists = $lists->where('is_done','=','1');
            $lists = $lists->whereIn('is_done', ['1', '2']);
        } elseif ($was_done == 'false') {
            $lists = $lists->where('is_done','=','0');
        }
        //出售还是购买
        if($type == 'buy') {
            $type = 'buy';
            $lists = $lists->where('type', $type);
        } elseif ($type == 'sell') {
            $type = 'sell';
            $lists = $lists->where('type', $type);
        }

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }


    /**
     * 商家发布法币交易信息列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealPlatform(Request $request)
    {
        $limit = $request->get('limit', 10);
        $currency_id = $request->get('currency_id', '');
        $type = $request->get('type', 'sell');
        if (empty($currency_id)) return $this->error('参数错误');
        if (empty($type)) return $this->error('参数错误2');
        $currency = Currency::find($currency_id);
        if (empty($currency)) return $this->error('无此币种');
        if (empty($currency->is_legal)) return $this->error('该币不是法币');
        if($type=="sell")
        {
            $results = LegalDealSend::where('currency_id', $currency_id)->where('is_done', 0)->where('is_shelves', 1)->where('type', $type)->orderBy('price', 'asc')->orderBy('id', 'desc')->paginate($limit);
        }
        else
        {
            $results = LegalDealSend::where('currency_id', $currency_id)->where('is_done', 0)->where('is_shelves', 1)->where('type', $type)->orderBy('price', 'desc')->orderBy('id', 'desc')->paginate($limit);
        }

        return $this->pageData($results);
    }

    /**
     * 法币交易详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealSendInfo(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $legal_deal_send = LegalDealSend::find($id);

        $userWallet=UsersWallet::where('currency',$legal_deal_send->currency_id)
            ->where('user_id',Users::getUserId())->first();

        //加上用户的余额
        $legal_deal_send->user_legal_balance=$userWallet->legal_balance;

        if (empty($legal_deal_send)) return $this->error('无此记录');
        return $this->success($legal_deal_send);
    }

    /**
     * 法币交易按钮
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doDeal(Request $request)
    {
        $deal_send_id = $request->get('id', null);
        $value = $request->get('value', 0);
        $means = $request->get('means', '');
        if (empty($deal_send_id)) {
            return $this->error('参数错误');
        }
        if (!in_array($means, ['number', 'money'])) {
            return $this->error('购买参数错误');
        }

        if (empty($value)) {
            return $this->error('请填写购买额');
        }
        if (!is_numeric($value)) {
            return $this->error('购买额请填写数字');
        }
        $user_id = Users::getUserId();
        //实名认证检测
        $user_real = UserReal::where('user_id', $user_id)
            ->where('review_status', 2)
            ->first();
        if (!$user_real) {
            return response()->json(['type'=>'998','message'=>$this->returnStr('您还没有通过实名认证')]);
        }
        //收款信息检测
        $user_cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (!$user_cash_info) {
            return response()->json(['type'=>'997','message'=>$this->returnStr('您还没有设置收款信息')]);
        }


        //限制未完成单数最多为3单
        $is_morethan=LegalDeal::where("user_id","=",$user_id)->whereIn('is_sure', [0,3])->count();
        if($is_morethan>=3)
        {
            return $this->error('未完成单子超过3单，请完成后再操作!');
        }

        
        try {
            DB::beginTransaction();
            $legal_deal_send = LegalDealSend::lockForUpdate()->find($deal_send_id);

            if (empty($legal_deal_send)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_deal_send->is_shelves != 1 || $legal_deal_send->is_done != 0) {
                throw new \Exception('商家挂单状态异常,暂时不能交易');
            }
            if($legal_deal_send->surplus_number <= 0){
                throw new \Exception('商家挂单剩余可交易数量不足');
            }

            if (!empty($legal_deal_send->is_done)) {
                DB::rollback();
                return $this->error('此条交易已完成');
            }
            if ($means == 'money') {
                $number = bc_div($value, $legal_deal_send->price, 5);
            } else {
                $number = $value;
            }
            if ($number <= 0) {
                DB::rollback();
                return $this->error('非法提交，数量必须大于0');
            }

            $money = bc_mul($number, $legal_deal_send->price, 6);

            if (bc_comp($money, $legal_deal_send->limitation['min']) < 0) {
                DB::rollback();
                return $this->error('您低于最低限额');
            }
            if ($money > $legal_deal_send->limitation['max']) {
                DB::rollback();
                return $this->error('您高于最高限额');
            }
            if ($number > $legal_deal_send->max_number) {
                DB::rollback();
                return $this->error('您高于最大限制数量');
            }
            $seller = Seller::find($legal_deal_send->seller_id);
            if (empty($seller)) {
                DB::rollback();
                return $this->error('未找到该商家');
            }

            if ($user_id == $seller->user_id) {
                DB::rollback();
                return $this->error('不能操作自己的');
            }
            $users_wallet = UsersWallet::lockForUpdate()->where('user_id', $user_id)->where('currency', $legal_deal_send->currency_id)->first();
            if (empty($users_wallet)) {
                DB::rollback();
                return $this->error('您无此钱包账号');
            }
            if (!empty($users_wallet->status)) {
                DB::rollback();
                return $this->error('您的钱包已被锁定，请联系管理员');
            }

            if ($legal_deal_send->type == 'buy') { //求购
               
                if ($users_wallet->legal_balance < $number) {
                    DB::rollback();
                    return $this->error('您的法币余额不足');
                }
                if ($users_wallet->lock_legal_balance < 0) {
                    DB::rollback();
                    return $this->error('您的法币冻结资金异常,请查看您是否有正在进行的挂单');
                }

                //检查购买数量是否大于剩余余额 t add
                if($number>$legal_deal_send->surplus_number)
                {
                    DB::rollback();
                    return $this->error('您出售数量大于商家剩余数量!');
                }
                else
                {
                    $legal_deal_send->surplus_number = bc_sub($legal_deal_send->surplus_number,$number,5);
                }


                if ($legal_deal_send->surplus_number <= 0) {
                    //$legal_deal_send->is_done = 1;
                    $legal_deal_send->is_shelves = 2; // 下架交易,防止交易被其他用户再匹配
                }

                
                $result = change_wallet_balance($users_wallet, 1, -$number, AccountLog::LEGAL_DEAL_USER_SELL, '出售给商家法币:扣除余额');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $result1 = change_wallet_balance($users_wallet, 1, $number, AccountLog::LEGAL_DEAL_USER_SELL, '出售给商家法币:增加冻结', true);
                if ($result1 !== true) {
                    throw new \Exception($result1);
                }


            } elseif ($legal_deal_send->type == 'sell') {
                //出售

                if($number>$legal_deal_send->surplus_number)
                {   
                    DB::rollback();
                    return $this->error('您购买数量大于商家剩余数量!');
                }
                else
                {
                    $legal_deal_send->surplus_number = bc_sub($legal_deal_send->surplus_number,$number,5);
                }

                if ($legal_deal_send->surplus_number <= 0) {
                    //$legal_deal_send->is_done = 1;
                    $legal_deal_send->is_shelves = 2; // 下架交易,防止交易被其他用户再匹配
                }
                
            }

            $legal_deal_send->save();

            $legal_deal = new LegalDeal();
            $legal_deal->legal_deal_send_id = $deal_send_id;
            $legal_deal->user_id = $user_id;
            $legal_deal->seller_id = $seller->id;
            $legal_deal->number = $number; //交易数量
            $legal_deal->create_time = time();
            $legal_deal->save();
//            var_dump($legal_deal);die;
            DB::commit();
            return $this->success([
                'msg' => $this->returnStr('操作成功，请联系商家确认订单'),
                'data' => $legal_deal,
            ]);

        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage() . $this->returnStr(',错误位于第') . $exception->getLine() . $this->returnStr('行'));
        }
    }

    /**
     * 法币交易商家端列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerLegalDealList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $type = $request->get('type', 'sell');
        $currency_id = $request->get('currency_id', '');

        if (empty($currency_id)) {
            return $this->error('参数错误');
        }
        if (empty($type)) {
            return $this->error('参数错误2');
        }
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error('无此币种');
        }
        if (empty($currency->is_legal)) {
            return $this->error('该币不是法币');
        }
        $user_id = Users::getUserId();
        $seller = Seller::where('user_id', $user_id)->where('currency_id', $currency_id)->first();
        if (empty($seller)) {
            return $this->error('您不是此币商家');
        }
        $results = LegalDeal::where('seller_id', $seller->id)
            ->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            })->orderBy('id', 'desc')->paginate($limit);
        return $this->pageData($results);

    }

    /**
     * 法币交易用户端列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $type = $request->get('type', null);
        $currency_id = $request->get('currency_id', '');
        $is_sure = $request->get('is_sure', null);//0未确认 1已确认 2已取消 3已付款

        if (!empty($currency_id)) {
            $currency = Currency::find($currency_id);
            if (empty($currency)) return $this->error('无此币种');
            if (empty($currency->is_legal)) return $this->error('该币不是法币');
        }

        $user_id = Users::getUserId();

        $results = LegalDeal::where('user_id', $user_id);
        if (!empty($type)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });
        }

        if (!empty($currency_id)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            });
        }

        if (!is_null($is_sure)) {
            $results = $results->where('is_sure', $is_sure);
        }
        $results = $results->orderBy('id', 'desc')->paginate($limit);
//        var_dump($results);die;
        return $this->pageData($results);
    }



    /**
     * 法币交易用户端列表   作为商家
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerUserLegalDealList(Request $request)
    {
//        var_dump(66666);die;
        $limit = $request->get('limit', 10);
        $type = $request->get('type', null);
        $currency_id = $request->get('currency_id', '');
        $is_sure = $request->get('is_sure', null);//0未确认 1已确认 2已取消 3已付款

        if (!empty($currency_id)) {
            $currency = Currency::find($currency_id);
            if (empty($currency)) return $this->error('无此币种');
            if (empty($currency->is_legal)) return $this->error('该币不是法币');
        }

        $user_id = Users::getUserId();

        $my_seller_id=Seller::where("user_id",$user_id)->first()->id;

        $results = LegalDeal::where('seller_id', $my_seller_id);
        if (!empty($type)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });
        }

        if (!empty($currency_id)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            });
        }

        if (!is_null($is_sure)) {
            $results = $results->where('is_sure', $is_sure);
        }

        $results = $results->orderBy('id', 'desc')->paginate($limit);
//        var_dump($results);die;
//        var_dump($results);die;
        return $this->pageData($results);
    }


    /**
     * 订单详情页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealInfo(Request $request)
    {
        $userLegalDealCancel_time=Setting::getValueByKey("userLegalDealCancel_time")*60;
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $legal_deal = LegalDeal::find($id);
        if($legal_deal->is_sure==0)//0未确认 1已确认 2已取消 3已付款
        {
//            var_dump(strtotime($legal_deal->create_time));die;
            $legal_deal->cancel_time=(strtotime($legal_deal->create_time)+$userLegalDealCancel_time)-time();
        }

        $legal_deal->to_pay_info=Seller::where("id",$legal_deal->seller_id)->first();

        if (empty($legal_deal)) {
            return $this->error('无此记录');
        }
        return $this->success($legal_deal);
    }

    /**
     * 用户确认支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealPay(Request $request)
    {
        $id = $request->get('id', null);
        $pay_orders_img = $request->get('pay_orders_img', null);
        if (empty($id)) return $this->error('参数错误');

        $legal_deal = LegalDeal::find($id);
        if (empty($legal_deal)) {
            return $this->error('无此记录');
        }
        DB::beginTransaction();
        try {
            if ($legal_deal->is_sure > 0) {
                DB::rollback();
                return $this->error('该订单已操作过，请勿重复操作');
            }
            $user_id = Users::getUserId();
            if ($legal_deal->type == 'sell') { //用户端-购买
                if (empty($pay_orders_img)){
                    DB::rollback();
                    return $this->error('请上传支付凭证');
                } 
                if ($user_id != $legal_deal->user_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }
            } elseif ($legal_deal->type == 'buy') {
                $seller = Seller::find($legal_deal->seller_id);
                if ($user_id != $seller->user_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }
            }
            $legal_deal->is_sure = 3;
            $legal_deal->pay_orders_img = $pay_orders_img;
            $legal_deal->save();
            DB::commit();
            return $this->success('操作成功，请联系卖家确认');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }







    /**
     * 用户取消订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealCancel(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        
        
        try {
            DB::beginTransaction();
            $legal_deal = LegalDeal::lockForUpdate()->find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }

            if ($legal_deal->is_sure > 0) {
                DB::rollback();
                return $this->error('该订单已操作，请勿取消');
            }
            $user_id = Users::getUserId();
            if ($legal_deal->type == 'sell') { //用户端-购买
                if ($user_id != $legal_deal->user_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }
            } elseif ($legal_deal->type == 'buy') {//用户端出售
                $seller = Seller::find($legal_deal->seller_id);
                if ($user_id == $legal_deal->user_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }
            }

            LegalDeal::cancelLegalDealById($id);
            DB::commit();
            return $this->success('操作成功，订单已取消');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    public function mySellerList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if (empty($user->is_seller)) {
            return $this->error('对不起您不是商家');
        }
        $results = Seller::where('user_id', $user_id)->orderBy('id', 'desc')->paginate($limit);
        return $this->pageData($results);
    }

    public function legalDealSellerList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $id = $request->get('id', null);
        $is_sure = $request->get('is_sure', null);
        if (empty($id)) return $this->error('参数错误');
        $legal_send = LegalDealSend::find($id);
        if (empty($legal_send)) {
            return $this->error('参数错误2');
        }
        $seller = Seller::find($legal_send->seller_id);
        if (empty($seller->is_myseller)) {
            return $this->error('对不起，您不是该商家');
        }
        if(!is_null($is_sure))
        {
        $results = LegalDeal::where('legal_deal_send_id', $id)
            ->where('is_sure', $is_sure)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        }else
        {
            $results = LegalDeal::where('legal_deal_send_id', $id)
                ->orderBy('id', 'desc')
                ->paginate($limit);
        }

        return $this->pageData($results);
    }

    public function doSure(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        DB::beginTransaction();
        try {
            $legal_deal = LegalDeal::lockForUpdate()->find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_deal->is_sure != 3) {
                DB::rollback();
                return $this->error('该订单还未付款或已经操作过');
            }
            $seller = Seller::lockForUpdate()->find($legal_deal->seller_id);
            if (empty($seller->is_myseller)) {
                DB::rollback();
                return $this->error('对不起，您无权操作');
            }
            if (bc_comp($seller->lock_seller_balance, $legal_deal->number) < 0) {
                throw new \Exception('对不起，您的商家冻结余额不足,当前余额:' . $seller->lock_seller_balance);
            }

            $legal_send = LegalDealSend::find($legal_deal->legal_deal_send_id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('订单异常');
            }
            if ($legal_send->type == 'buy') {
                DB::rollback();
                return $this->error('您不能确认该订单');
            }
            $user_wallet = UsersWallet::where('user_id', $legal_deal->user_id)->where('currency', $legal_send->currency_id)->lockForUpdate()->first();
            if (empty($user_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }
            // 增加用户法币余额
            $result = change_wallet_balance($user_wallet, 1, $legal_deal->number, AccountLog::LEGAL_USER_BUY, '法币交易:在商家' . $seller->name . ' 购买法币成功');
            if ($result !== true) {
                throw new \Exception($result);
            }

            //更新交易状态
            $legal_deal->is_sure = 1;
            $legal_deal->update_time = time();
            $legal_deal->save();

            //商家钱包
            
            $seller_user_id=$seller->user_id;
            $seller_user_wallet = UsersWallet::where('user_id', $seller_user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();

            //减少商家法币锁定余额

            $seller->lock_seller_balance = bc_sub($seller->lock_seller_balance,$legal_deal->number,5);
            $seller->save();

            $result = change_wallet_balance($seller_user_wallet, 1, -$legal_deal->number, AccountLog::LEGAL_USER_BUY, '法币交易:卖出成功',true);
            if ($result !== true) {
                throw new \Exception($result);
            }
          
            DB::commit();
            //限定usdt才累加业绩
            if (strtolower($legal_send->currency_name) == 'usdt') {
                event(new RechargeEvent($user_wallet->user_id, AccountLog::LEGAL_USER_BUY, $legal_deal->number));
            }
            return $this->success('确认成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    public function userDoSure(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        DB::beginTransaction();
        try {
            $legal_deal = LegalDeal::lockForUpdate()->find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_deal->is_sure != 3) {
                DB::rollback();
                return $this->error('该订单还未付款或已经操作过');
            }
            $user_id = Users::getUserId();
            $user = Users::find($user_id);
            if ($legal_deal->user_id != $user_id) {
                DB::rollback();
                return $this->error('对不起，您无权操作');
            }
            $legal_send = LegalDealSend::find($legal_deal->legal_deal_send_id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('订单异常');
            }
            if ($legal_send->type == 'sell') {
                DB::rollback();
                return $this->error('您不能确认该订单');
            }
            
            $user_wallet = UsersWallet::where('user_id', $legal_deal->user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
//            var_dump($user_wallet->toArray());die;
            if (empty($user_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }
            $seller = Seller::lockForUpdate()->find($legal_deal->seller_id);
            if (empty($seller)) {
                DB::rollback();
                return $this->error('商家不存在');
            }
            $result = change_wallet_balance($user_wallet, 1, -$legal_deal->number, AccountLog::LEGAL_SELLER_BUY, '法币交易:用户' . $user->account_number . '向商家出售法币成功', true);
            if ($result !== true) {
                throw new \Exception($result);
            }
           
            //更新交易状态
            $legal_deal->is_sure = 1;
            $legal_deal->update_time = time();
            $legal_deal->save();

            //商家钱包           
            $seller_user_id=$seller->user_id;
            $seller_user_wallet = UsersWallet::where('user_id', $seller_user_id)
                ->where('currency', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();

            //增加商家法币余额
            $result2 = change_wallet_balance($seller_user_wallet, 1, $legal_deal->number, AccountLog::LEGAL_SELLER_BUY, '法币交易:购买成功');
            if ($result2!== true) {
                throw new \Exception($result2);
            }
           
            DB::commit();
            return $this->success('确认成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 标记交易下架
     *
     */
    public function down(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');

        try {
            DB::beginTransaction();

            $legal_send = LegalDealSend::lockForUpdate()->find($id);

            if (empty($legal_send)) {
                throw new \Exception('无此记录');
            }
            if ($legal_send->is_done != 0 || $legal_send->is_shelves != 1) {
                throw new \Exception('此状态下无法下架');
            }
            $user_id = Users::getUserId();
            $seller = Seller::where('user_id', $user_id)
                ->where('currency_id', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($seller)) {
                throw new \Exception('对不起，您不是该法币的商家');
            }
            $legal_send->is_shelves = 2;
            $legal_send->save();
            DB::commit();
            return $this->success('发布下架成功,将不会再与新用户匹配');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }




    //撤回
    public function backSend(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        
        try {
            DB::beginTransaction();
            $legal_send = LegalDealSend::lockForUpdate()->find($id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('已撤回发布');
            }
            if ($legal_send->is_sendback != 1) {
                throw new \Exception('已撤销');
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
            if (LegalDealSend::isHasIncompleteness($id)) {
                throw new \Exception('该发布信息下有交易正在进行中,请等待交易结束再撤回');
            }
            

//            $is_deal = LegalDeal::where('legal_deal_send_id', $id)->where('is_sure','>',2)->first();
            // $is_deal = LegalDeal::where(function ($query) use ($id) {
            //     $query->orWhere(function ($query) use ($id) {
            //         $query->where("is_sure","=",3);
            //     })->orWhere(function ($query) use ($id) {
            //         $query->where("is_sure","=",0);
            //     });
            // })->where('legal_deal_send_id', $id)->first();
            // if (!empty($is_deal)) {
            //     DB::rollback();

            //     return $this->error('该发布信息下有交易产生无法删除');
            // }
            // if ($legal_send->surplus_number <= 0) {
            //     throw new \Exception('当前发布剩余数量不足,无法撤销');
            // }

            $user_id = Users::getUserId();
            $seller = Seller::where('user_id', $user_id)->where('currency_id', $legal_send->currency_id)->lockForUpdate()->first();
            if (empty($seller)) {
                DB::rollback();
                return $this->error('对不起，您不是该法币的商家');
            }
            if ($legal_send->type == 'sell') {   //如果商家发布出售信息
                if ($seller->lock_seller_balance < $legal_send->surplus_number){
                    DB::rollback();
                    return $this->error('对不起，您的商家账户冻结资金不足');
                }

                $user_wallet=UsersWallet::where("user_id",$user_id)->where("currency", $legal_send->currency_id)->lockForUpdate()->first();
                $res1=change_wallet_balance($user_wallet,1,-$legal_send->surplus_number, AccountLog::LEGAL_DEAL_BACK_SEND_SELL, '商家撤回',true);
                if ($res1 !== true) {
                    throw new \Exception('撤回失败:减少冻结资金失败');
                }
                $res2=change_wallet_balance($user_wallet,1,$legal_send->surplus_number, AccountLog::LEGAL_DEAL_BACK_SEND_SELL, '商家撤回');
                if ($res2 !== true) {
                    throw new \Exception('撤回失败:增加余额失败');
                }

                $seller->lock_seller_balance = bc_sub($seller->lock_seller_balance,$legal_send->surplus_number,5);

                $seller->save();


            }
            $legal_send->is_shelves = 2;
            $legal_send->is_sendback=2;
            $legal_send->is_done = 2;
            $legal_send->save();

            DB::commit();
            return $this->success('撤回成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


    //异常处理   

    public function errorSend(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        
        try {
            DB::beginTransaction();
            $legal_send = LegalDealSend::lockForUpdate()->find($id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            $is = LegalDealSend::isHasIncompleteness($id);
            if ($is){
                DB::rollback();
                return $this->error('该发布信息下有交易未完成，不能标记为异常');
            }

            if ($legal_send->surplus_number >= $legal_send->min_number){
                DB::rollback();
                return $this->error('该发布信息无异常');
            }
            if ($legal_send->surplus_number <= 0){
                DB::rollback();
                return $this->error('该发布剩余数量不足,不法标记异常');
            }
            if ($legal_send->is_done != 0) {
                throw new \Exception('当前发布状态无法标记异常');
            }

            $user_id = Users::getUserId();
            $seller = Seller::where('user_id', $user_id)->where('currency_id', $legal_send->currency_id)->lockForUpdate()->first();
            if (empty($seller)) {
                DB::rollback();
                return $this->error('对不起，您不是该法币的商家');
            }
            if ($legal_send->type == 'sell') {   //如果商家发布出售信息
                if ($seller->lock_seller_balance < $legal_send->surplus_number){
                    DB::rollback();
                    return $this->error('对不起，您的商家账户冻结资金不足');
                }

                $user_wallet=UsersWallet::where("user_id",$user_id)->where("currency", $legal_send->currency_id)->lockForUpdate()->first();
                $res1=change_wallet_balance($user_wallet,1,-$legal_send->surplus_number, AccountLog::LEGAL_DEAL_ERROR_SEND_SELL, '商家处理异常',true);
                if ($res1 !== true) {
                    throw new \Exception('处理异常失败:减少冻结资金失败');
                }
                $res2=change_wallet_balance($user_wallet,1,$legal_send->surplus_number, AccountLog::LEGAL_DEAL_ERROR_SEND_SELL, '商家处理异常');
                if ($res2 !== true) {
                    throw new \Exception('处理异常失败:增加余额失败');
                }

                $seller->lock_seller_balance = bc_sub($seller->lock_seller_balance,$legal_send->surplus_number,5);
                $seller->save();
                
            }
            $legal_send->is_done = 1;
            $legal_send->save();
//            $legal_send->delete();
            DB::commit();
            return $this->success('处理成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }
}
