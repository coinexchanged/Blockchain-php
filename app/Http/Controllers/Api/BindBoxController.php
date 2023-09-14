<?php


namespace App\Http\Controllers\Api;


use App\AccountLog;
use App\LegalDealSend;
use App\LhBankAccount;
use App\LhBankAccountLog;
use App\LhBankTeamMember;
use App\LhDepositOrder;
use App\LhDepositOrderLog;
use App\LhLoanOrder;
use App\Logic\LhBankProfitLogic;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Cache\RedisLock;
use Illuminate\Support\Facades\Redis;
use App\{
    Address,
    Currency,
    UsersInsurance,
    InsuranceType,
    InsuranceClaimApply,
    Users,
    UserCashInfo,
    UserReal,
    UsersWallet,
    BindBox,
    BindBoxOrder,
    BindBoxQuotationLog,
    BindBoxCollect,
    BindBoxMarginLog,
    BindBoxRaityHouse,
    BindBoxSuccessOrder
};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Service\RedisService;

class BindBoxController extends Controller
{
    
    
    //获取艺术品列表
    public function getBoxList(Request $request){
        $user_id = Users::getUserId();
        
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 2);
        
        $list = DB::table('bind_box')
                ->join('currency', 'currency.id', '=', 'bind_box.currency_id')
                ->join('users', 'users.id', '=', 'bind_box.author');
        
        
        // 购买所需货币种类
        $currency = $request->get('currency');
        if (!empty($currency)) {
            $list = $list->where('bind_box.currency_id', $currency);
        }
        
        // nft的售卖类型,1：一口价，2：竞拍
        $pay_type = $request->get('pay_type');
        if (!empty($pay_type)) {
            $list = $list->where('bind_box.pay_type', $pay_type);
        }
        
        // 状态:1=开始,0=已结束,2=未开始
        $status = $request->get('status');
        if (isset($status)) {
            $list = $list->where('bind_box.status', $status);
        }
        
        //产品类型，1:图片 2：动图3：音频4：视频
        $type = $request->get('type');
        if (!empty($type)) {
            $list = $list->where('bind_box.type', $type);
        }
        
        $keyword = $request->get('keyword');
        if (!empty($keyword)) {
            $list = $list->where('bind_box.name','like',"%$keyword%");
        }
        $list = $list->orderBy('bind_box.status','DESC')->orderBy('bind_box.id','DESC');
        $list = $list->select('bind_box.*','currency.name as currency_name','users.id as author_id','users.head_portrait as author_avatar','users.nickname as author_name')->skip($limit*($page-1))->take($limit)->get();
        foreach ($list as $li){
            $bind_box_collect = BindBoxCollect::where('code',$li->code)->where('user_id',$user_id)->first();
            $li->collect =$bind_box_collect ? true : false;
        }
        return $this->success($list);
    }
    
    // 获取艺术品详情
    public function getBoxDetail(Request $request){
        $user_id = Users::getUserId();
        $id = $request->get('id');
        $code = $request->get('code','');
        if(!$id){
            return $this->success('Parameter error');
        }
        $box = DB::table('bind_box')
                ->join('currency', 'currency.id', '=', 'bind_box.currency_id')
                ->join('users', 'users.id', '=', 'bind_box.author')
                ->where('bind_box.id',$id)
                ->orWhere('bind_box.code',$code)
                ->select('bind_box.*','currency.name as currency_name','users.id as author_id','users.head_portrait as author_avatar','users.nickname as author_name')
                ->first();
        $bind_box_collect = BindBoxCollect::where('code',$box->code)->where('user_id',$user_id)->first();
        $bind_box_collect_number = BindBoxCollect::where('code',$box->code)->where('user_id',$user_id)->count();
        
        $box->collect = $bind_box_collect ? true : false;
        $box->collect_number = $bind_box_collect_number;
        return $this->success($box);
    }
    
    //获取艺术家列表
    public function getArtist(Request $request){
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $users = DB::table('bind_box')->join('users', 'users.id', '=', 'bind_box.author')
            ->select('bind_box.author as author_id','users.*')->distinct()->skip($limit*($page-1))->take($limit)->get();
        
        return $this->success($users);
    }
    
    //获取艺术家详情和其艺术品列表
    public function getArtistDetail(Request $request){
        $uid = $request->get('uid');
        $user = DB::table('users')->where('id',$uid)->first();
        
        $user->artworks = DB::table('bind_box')->where('author',$uid)->get();
        $user->collects =  BindBoxCollect::join('bind_box', 'bind_box.code', '=', 'bind_box_collect.code')->where('bind_box_collect.user_id',$uid)->get();
        
        $artworks_codes = array();
        foreach ($user->artworks as $li){
            $artworks_codes[] = $li->code;
        }
        //其作品被添加收藏的个数
        $user->artworks_collect_number = BindBoxCollect::whereIn('code',$artworks_codes)->count();
        
        return $this->success($user);
    }
   
   //添加/取消 收藏
   public function collect(Request $request){
        $user_id = Users::getUserId();
        $code = $request->post('code');
        
        $bind_box = BindBox::where('code',$code)->first();
        if($bind_box->owner == $user_id){
            return $this->error('The author of this NFT is you!');
        }
        if(!$bind_box){
            return $this->success('NFT does not exist!');
        }
        
        if(Cache::has('collect_'.$code.'_'.$user_id)){
            return $this->error('Do not repeat the operation!'); 
        }
        Cache::put('collect_'.$code.'_'.$user_id, 1, Carbon::now()->addSeconds(1));
        
        $bind_box_collect = BindBoxCollect::where('code',$code)->where('user_id',$user_id)->first();
        if($bind_box_collect){
            $bind_box_collect->delete();
        }else{
            
            $bind_box_collect = new BindBoxCollect();
            $bind_box_collect->code = $code;
              
            $bind_box_collect->user_id = $user_id;
            
            $bind_box_collect->created = date('Y-m-d H:i:s');
            
            $bind_box_collect->save();
           
        }
        
        return $this->success('successful');
   }
   
    public function getNftCurrency(){
       $currency = DB::table('bind_box')->join('currency', 'currency.id', '=', 'bind_box.currency_id')->select('currency.*')->distinct()->get();
       return $this->success($currency);
    }
   
   //获取已收藏的列表
   public function getCollection(){
       $user_id = Users::getUserId();
       $list = DB::table('bind_box')
                ->join('currency', 'currency.id', '=', 'bind_box.currency_id')
                ->join('users', 'users.id', '=', 'bind_box.author')
                ->join('bind_box_collect', 'bind_box_collect.code', '=', 'bind_box.code')
                ->where('bind_box_collect.user_id','=',$user_id)->select('bind_box.*','currency.name as currency_name','users.id as author_id','users.head_portrait as author_avatar','users.nickname as author_name')->get();
        
        foreach ($list as $li){
            $li->collect = true;
        }
        return $this->success($list);
   }
   
   //购买NFT
   public function buyNFT(Request $request){
        $redis = RedisService::getInstance(5);
        $user_id = Users::getUserId();
        $code = $request->post('code');
        $bind_box = BindBox::where('code',$code)->first();
        if(!$bind_box){
            return $this->error('NFT does not exist');
        }
        
        $start_time = strtotime($bind_box->start_time);
        $end_time = strtotime($bind_box->end_time);
        $now = time();
        
        if($now < $start_time || $now > $end_time){
            return $this->error('Non-purchase time');
        }
        if($bind_box->pay_type == 2 ){//竞拍模式不能购买
            return $this->error('NFT does not exist!');
        }
        if($bind_box->status == 0){//购买已结束
            return $this->error('Purchase is over');
        }
        $cuurency  =$bind_box->currency_id;
        
        if($bind_box->owner==$user_id){
            return $this->error("Can't buy own products");
        }
        
        $users_wallet = UsersWallet::where('currency', $cuurency)->where('user_id', $user_id)->first();
        if(!$users_wallet){
            return $this->error('UsersWallet does not exist');
        }
        if($users_wallet->change_balance < $bind_box->price){
            return $this->error('Insufficient balance');
        }
        if($redis->get('buy_nft_'.$code)){
            return $this->error('Has been snapped up by others!');
        }
        
        if($redis->get('nft_queue_'.$code)){
            return $this->error('The operation is too fast, please wait!');
        }
        $redis->set('nft_queue_' . $code, 1, 1); //已经有用户进入付款
        
        DB::beginTransaction();
        try{
            change_wallet_balance($users_wallet, 2, -$bind_box->price, AccountLog::USER_BUY_NFT_TYPE_1, '一口价购买NFT');
            $redis->set('buy_nft_' . $code, $user_id);
            
            $bind_box_order = new BindBoxOrder();
            $bind_box_order->code = $code;
            $bind_box_order->buyer_id = $user_id;
            $bind_box_order->sell_id = $bind_box->owner;//卖家
            $bind_box_order->author_id = $bind_box->author;//创作者
            $bind_box_order->status = 1;
            $bind_box_order->order_price = $bind_box->price;
            $bind_box_order->currency_id = $bind_box->currency_id;
            $bind_box_order->created = date('Y-m-d H:i:s',time());
            $bind_box_order->save();
            
            $bind_box->owner = $user_id; //更改NFT拥有者
            $bind_box->resell_nft_status = 0; //转卖的NFT更改为结束
            $bind_box->status = 0;//将状态改为不可购买
            $bind_box->save();
            
            DB::commit();//购买成功
            return $this->success('successful');
        } catch (\Throwable $th) {
            $redis->set('buy_nft_' . $code, null);//失败 清除此商品redis
            DB::rollBack();
            return $this->error('Failed purchase !');
        }
      
   }
   
   
   //NFT拍卖
   public function auctionNFT(Request $request){
        $redis = RedisService::getInstance(5);
        $user_id = Users::getUserId();
        $code = $request->post('code');
        $price = $request->post('price');
        if(!$price){
            return $this->error('error');
        }
        $bind_box = BindBox::where('code',$code)->first();
        if(!$bind_box){
            return $this->error('NFT does not exist');
        }
        $start_time = strtotime($bind_box->start_time);
        $end_time = strtotime($bind_box->end_time);
        $now = time();
        
        if($now < $start_time || $now > $end_time){
            return $this->error('Non-purchase time');
        }
        if($bind_box->pay_type != 2){//拍卖模式
            return $this->error('Non-auction items!');
        }
        if($bind_box->status == 0){//购买已结束
            return $this->error('Purchase is over');
        }
        
        $author = $bind_box->author;
        $per_increase = $bind_box->per_increase; //每次最低加价额度
        
        if($price < $per_increase){
            return $this->error("Increase Must be greater than ".$per_increase);//加价必须大于等于per_increase
        }
        if($bind_box->owner==$user_id){
            return $this->error("Can't buy own products");
        }
        
        $cuurency  =$bind_box->currency_id;
        $users_wallet = UsersWallet::where('currency', $cuurency)->where('user_id', $user_id)->first();
        if(!$users_wallet){
            return $this->error('UsersWallet does not exist');//用户钱包不存在
        }
        
        if($redis->get('auctionNFT_queue_'.$code)){
            return $this->error('The operation is too fast, please wait!');
        }
        $redis->set('auctionNFT_queue_' . $code, 1, 2); //限制出价频率 2秒
        
        $bind_box->refresh(); //取最新数据
        $nft_now_price = $bind_box->price;
        
        $new_price = bcadd($nft_now_price , $price);
        if($new_price == $nft_now_price){
            return $this->error('Invalid markup!'); //价格已失效
        }
        if($users_wallet->change_balance < $new_price){ //余额不足此次拍卖价格
            return $this->error('Insufficient balance');
        }
        
        //扣除保证金
        $bind_box_margin_log = BindBoxMarginLog::where('code',$code)->where('user_id',$user_id)->where('status',1)->where('is_expired',0)->first();
        if(!$bind_box_margin_log){ //未交保证金
            $margin_cuurency = 3; //固定为USDT  
            $number = $bind_box->margin; //保证金数量
            $users_wallet = UsersWallet::where('currency', $margin_cuurency)->where('user_id', $user_id)->first();
            if(!$users_wallet){
                return $this->error('UsersWallet does not exist');
            }
            if($users_wallet->change_balance < $number){//余额不够扣除保证金
                return $this->error('Insufficient margin balance');
            }
            
            DB::beginTransaction();
                try{
                change_wallet_balance($users_wallet, 2, -$number, AccountLog::USER_BUY_MARGIN_NFT, '竞拍扣除保证金');
                $_bind_box_margin_log = new BindBoxMarginLog();
                $_bind_box_margin_log->user_id = $user_id;
                $_bind_box_margin_log->code = $code;
                $_bind_box_margin_log->number = $number;
                $_bind_box_margin_log->currency_id = $cuurency;
                $_bind_box_margin_log->status = 1;
                $_bind_box_margin_log->created = date('Y-m-d H:i:s',time());
                $_bind_box_margin_log->save();
                
                BindBoxQuotationLog::where('code',$code)->update(['status'=>0]);//将其他记录更改为失效
                BindBoxQuotationLog::where('code',$code)->where('buyer_id',$user_id)->update(['is_expired'=>1]);
                $bind_box_quotation_log = new BindBoxQuotationLog();
                $bind_box_quotation_log->code = $code;
                $bind_box_quotation_log->buyer_id = $user_id;
                $bind_box_quotation_log->margin_log_id = $_bind_box_margin_log->id;
                $bind_box_quotation_log->status = 1;
                $bind_box_quotation_log->price = $new_price;
                $bind_box_quotation_log->currency_id = $cuurency;
                $bind_box_quotation_log->created = date('Y-m-d H:i:s',time());
                $bind_box_quotation_log->save();
        
                $bind_box->price = $new_price; //更新NFT最新价格
                $bind_box->save();
        
                DB::commit();
                return $this->success('successful');
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->error($th);
            }
        }else{
                BindBoxQuotationLog::where('code',$code)->update(['status'=>0]);//将其他记录更改为失效
                BindBoxQuotationLog::where('code',$code)->where('buyer_id',$user_id)->update(['is_expired'=>1]);
                $bind_box_quotation_log = new BindBoxQuotationLog();
                $bind_box_quotation_log->code = $code;
                $bind_box_quotation_log->buyer_id = $user_id;
                $bind_box_quotation_log->margin_log_id = $bind_box_margin_log->id;
                $bind_box_quotation_log->status = 1;
                $bind_box_quotation_log->price = $new_price;
                $bind_box_quotation_log->currency_id = $cuurency;
                $bind_box_quotation_log->created = date('Y-m-d H:i:s',time());
                $bind_box_quotation_log->save();
                
                $bind_box->price = $new_price; //更新NFT最新价格
                $bind_box->save();
                return $this->success('successful');
        }
   }
   
   
   //获取商品出价记录
   public function getBindBoxQuotationLog(Request $request){
       $code = $request->post('code');
       if(!$code){
          return $this->error('error'); 
       }
       $bind_box_quotation_logs = BindBoxQuotationLog::where('code',$code)->where('is_expired',0)->orderBy('id','DESC')->limit(10)->get();
       
       return $this->success($bind_box_quotation_logs);
   }
   
   //获取我的NFT
   public function getMyNFTs(Request $request){
       $user_id = Users::getUserId();
       $bind_box = BindBox::where('owner',$user_id)->orderBy('id','DESC')->get();
       return $this->success($bind_box);
   }
   
   //获取我的出价记录
   public function getMyBindBoxQuotationLog(Request $request){
       $user_id = Users::getUserId();
       $limit = $request->post('limit', 10);
       
        $bind_box = new BindBoxQuotationLog();
        $bind_box = $bind_box->where('buyer_id', $user_id)->where('is_expired', 0)->orderBy('id', 'DESC')->paginate($limit);
        return $this->success(['code' => 0, 'data' => $bind_box->items(), 'count' => $bind_box->total()]);
        
   }
   
   
   //开启盲盒
   public function openBlindBox(Request $request){
        $user_id = Users::getUserId();
        $code = $request->post('code');
        
        $bind_box = BindBox::where(['code'=>$code,'pay_type'=>3,'owner'=>$user_id,'rarity_status'=>0])->first();
        
        if(!$bind_box){
            return $this->error('The blind box does not exist or the blind box is opened!');//盲盒不存在或者盲盒已开启
        }
        
        $rarity_house = BindBoxRaityHouse::where('id',$bind_box->rarity_house_id)->first();
        
        $bind_box->rarity_status = 1;//将状态改成已开启
        $bind_box->image = $rarity_house->file; 
        $bind_box->save();
        
        return $this->success($bind_box);
   }
   
   // 转卖
   public function resellNFT(Request $request){
        $redis = RedisService::getInstance(5);
        $user_id = Users::getUserId();
        $code = $request->post('code');
        $bind_box = BindBox::where('code',$code)->first();
        if(!$bind_box){
            return $this->error("NFT does not exist!");
        }
        
        if($bind_box->owner != $user_id){
            return $this->error("Wrong operation!");
        }
        
        if($bind_box->resell_nft_status == 1){//转卖中
            return $this->error("Cannot be modified during auction!");
        }
            
        $price = $request->post('price');
        $start_time = $request->post('start_time');
        $end_time = $request->post('end_time');
        $per_increase = $request->post('per_increase');
        
        if(!$start_time || !$end_time || !$price){
            return $this->error("Can not be empty!");
        }
        if( strtotime($start_time) < time() ){
            return $this->error("The start time cannot be less than the current time!");
        }
        if( strtotime($start_time) < strtotime($end_time) ){
            return $this->error("The start time cannot be less than the end_time!");
        }
        if($redis->get('resellNFT'.$code)){
            return $this->error('The operation is too fast, please wait!');
        }
        $redis->set('resellNFT' . $code, 1, 1); //限制点击频率
        if($bind_box->pay_type == 2 && $bind_box->lock_order == 1){//未超时的前提下 拍到此商品的买家未付款
            return $this->error("Not for resale!");
        }
        
        $bind_box->price = $price;
        $bind_box->start_time = $start_time;
        $bind_box->end_time = $end_time;
        if($bind_box->pay_type == 2 && $per_increase){//竞拍
            $bind_box->per_increase = $per_increase;
        }
        $bind_box->status = 1;
        $bind_box->resell_nft_status = 1; //转卖中
        $bind_box->updated = date('Y-m-d H:i:s',time());
        $bind_box->save();
        
        return $this->success("Successful");
        
   }
   
   //获取需要支付的订单
   public function getNeedPayNFTOrder(Request $request){
       $user_id = Users::getUserId();
       $bind_box_success_order =  BindBoxSuccessOrder::where(['user_id'=>$user_id,])->get(); 
       
       return $this->success($bind_box_success_order);
   }
   
   //支付订单
   public function payNFTOrder(Request $request){
       $user_id = Users::getUserId();
       $id = $request->post('id');
       $order = BindBoxSuccessOrder::where('id',$id)->where('user_id',$user_id)->where('overtime','>',0)->where('is_expired',0)->first();
       
       if(!$order){
           return $this->error("Order does not exist!");
       }
       
            $quotrtion_log = BindBoxQuotationLog::where('id',$order->quotrtion_log_id)->first();
            if(!$quotrtion_log){
                return $this->error("Quotation Log does not exist!");
            }
            $margin_log = BindBoxMarginLog::where('id',$quotrtion_log->margin_log_id)->first();
            $users_wallet = UsersWallet::where('currency', $order->currency_id)->where('user_id', $user_id)->first(); //支付钱包
            if(!$margin_log){
                return $this->error('UsersWallet does not exist!');
            }
            $users_margin_wallet = UsersWallet::where('currency', $margin_log->currency_id)->where('user_id', $user_id)->first(); //保证金钱包
            
            
            if(!$users_wallet){
                return $this->error('UsersWallet does not exist');
            }
            
            if($users_wallet->change_balance < $quotrtion_log->price){
                return $this->error('Insufficient margin balance');
            }
            if(Cache::has('payNFTOrder_'.$user_id)){
                return $this->error('Do not repeat the operation!'); 
            }
            Cache::put('payNFTOrder_'.$user_id, 1, Carbon::now()->addSeconds(1));
            $code = $order->code;
            DB::beginTransaction();
            try{
                change_wallet_balance($users_wallet, 2, -$quotrtion_log->price, AccountLog::USER_ORDER_PAY_NFT, '竞拍支付');
                $order->is_pay =1;
                $order->is_expired =1;
                $order->pay_time = time();
                $order->overtime = 0;
                $order->save();
                
                change_wallet_balance($users_margin_wallet, 2, $margin_log->number, AccountLog::USER_RETURN_MARGIN_NFT, '退还保证金');
                BindBox::where('code',$code)->update(['resell_nft_status'=>0,'status'=>0,'owner'=>$user_id,'lock_order'=>0]); //nft拍卖结束
                $margin_log->status =0;
                $margin_log->save();
                
                DB::commit();
                return $this->success('successful');
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->error('Failed');
        }
       
       
   }
   
   public function readNFTOrderMessage(Request $request){
        $id = $request->post('id');
        
        $order = BindBoxSuccessOrder::where('id',$id)->first();
        
        if(!$order){
          return $this->error('Failed');  
        }
        if($order->is_read == 0){
            $order->is_read=1;
            $order->save();
        }
        return $this->success('successful');
   }
   
    public function test(){
        return;
        $now = Carbon::now();
                // 拍卖已经结束 没有人出价的拍卖品
                $bind_boxs = BindBox::where('end_time','<=',$now)->where('pay_type',2)->get();
                foreach ($bind_boxs as $bind_box){
                    $log = BindBoxQuotationLog::where('is_expired',0)->where('code',$bind_box->code)->first();
                    if(!$log){
                        $bind_box->status = 0;
                        $bind_box->resell_nft_status = 0;
                        $bind_box->save();
                    }
                }
                
            //拍卖结束 拍卖品有人出价
            $bind_box_quotation_logs = DB::table('bind_box_quotation_log')             
                ->leftjoin('bind_box_margin_log', 'bind_box_margin_log.id', '=', 'bind_box_quotation_log.margin_log_id')
                ->leftjoin('bind_box', 'bind_box.code', '=', 'bind_box_quotation_log.code')
                ->where('bind_box.end_time','<=',$now)
                ->where('bind_box.pay_type',2)
                ->where('bind_box_quotation_log.is_expired',0)
                ->select('bind_box_quotation_log.*')->distinct()->get();
                
                foreach ($bind_box_quotation_logs as $bind_box_quotation_log){
                        $quotation_log_id = $bind_box_quotation_log->id;
                        $status = $bind_box_quotation_log->status; //成交状态
                        if($status == 1){ //拍到了
                            echo '生成待支付订单_'.PHP_EOL;
                            $order = new BindBoxSuccessOrder();
                            $order->code = $bind_box_quotation_log->code;
                            $order->quotrtion_log_id = $bind_box_quotation_log->id;
                            $order->user_id = $bind_box_quotation_log->buyer_id;
                            $order->currency_id = $bind_box_quotation_log->currency_id;
                            $order->is_read = 0;
                            $order->is_pay = 0;
                            $order->overtime = time() + 86400;//过期时间24小时
                            $order->created = time();
                            $order->save();
                            
                            Db::table('bind_box_quotation_log')->where('id',$quotation_log_id)->update(['is_expired'=>1]);
                        }else{//未拍到
                            $margin_log = BindBoxMarginLog::where('id',$bind_box_quotation_log->margin_log_id)->where('is_expired',0)->where('status',1)->first();//保证金交了未退
                            if($margin_log){//退保证金
                                $_currency = $margin_log->currency_id;
                                $number = $margin_log->number;
                                $users_wallet = UsersWallet::where('currency', $_currency)->where('user_id', $margin_log->user_id)->first();
                                change_wallet_balance($users_wallet, 2, $number, AccountLog::USER_RETURN_MARGIN_NFT, '退还竞拍保证金');
                                
                                $margin_log->is_expired = 1;
                                $margin_log->status = 0;
                                $margin_log->save();
                                
                                Db::table('bind_box_quotation_log')->where('id',$quotation_log_id)->update(['is_expired'=>1]);
                            }else{ //未拍到 但是推过保证金了
                                Db::table('bind_box_quotation_log')->where('id',$quotation_log_id)->update(['is_expired'=>1]);
                            }
                            
                            
                        }
                        
                }
                
        
        //超时处理
        $bind_box_success_orders = BindBoxSuccessOrder::where('overtime','<',time())->where('overtime','>',0)->where('is_expired',0)->where('is_pay',0)->get();
        foreach ($bind_box_success_orders as $bind_box_success_order){
            Db::table('bind_box')->where('code',$bind_box_success_order->code)->update(['status'=>0,'resell_nft_status'=>0]); //状态失效
            $bind_box_success_order->is_expired = 1; //订单已失效
            $bind_box_success_order->overtime = 0; 
            $bind_box_success_order->save();
        }
        
        echo  '完成';
        
    }
   
}




























