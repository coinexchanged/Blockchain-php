<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Session;
use App\Agent;
use App\UserCashInfo;
use App\UserChat;
use App\Users;
use App\Token;
use App\AccountLog;
use App\UsersWallet;
use App\Currency;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\UserProfile;

class MiningMachine extends Controller
{
    //查询期货列表
    public function get_mining_machine_list()
    {
        $futures_market = DB::table('futures_market')->get();
        return $this->success($futures_market);
    }

    //查询期货信息
    public function get_mining_machine_info()
    {
        $id = Input::get("id",0);
        $mining_machine_s = DB::table('futures_market')->where('id', $id)->first();
        if(!$mining_machine_s) return $this->error('该型号不存在');
        return $this->success($mining_machine_s);
    }

    //购买期货
    public function buy_mining_machine()
    {
        $id = Input::get("id",0);
        $money = Input::get("money",0,'floatval');
        $user_id = Users::getUserId();

        if($id < 1) return $this->error('请选择一款矿机');
        //获取矿机信息
        $mining_machine_info = DB::table('futures_market')->where('id',$id)->first();
        if(!$mining_machine_info) return $this->error('该型号不存在');
        if($money < $mining_machine_info->pricemin) return $this->error('购买数量不能少于'.$mining_machine_info->pricemin);
        //获取币币账户 USDT
        $users_wallet_info = DB::table('users_wallet')->where([
            ['user_id','=',$user_id],
            ['currency','=',3]
        ])->first();
        //币币账户 USDT
        $u_USDT = $users_wallet_info->micro_balance;
        if($money > $u_USDT) return $this->error('余额不足');
        //减去 币币账户 USDT 余额
        DB::table('users_wallet')->where([
            ['user_id','=',$user_id],
            ['currency','=',3]
        ])->decrement('micro_balance', $money);

        $totime = strtotime('+'.$mining_machine_info->days.' day');
        //生成订单
        DB::table('futures_market_buy')->insert(['user_id' => $user_id, 'currency_id'=>$mining_machine_info->currency_id, 'currency_name'=>$mining_machine_info->currency_name, 'days'=>$mining_machine_info->days, 'money' => $money, 'interest'=>sprintf("%.8f",$mining_machine_info->rates * $money / 100), 'time' => time(), 'totime' => $totime]);
        $info = '购买矿机，扣除USDT:'.$money;
        //写入 account_log
        $get_id = DB::table('account_log')->insertGetId(['user_id' => $user_id, 'value' => -$money, 'created_time' => time(), 'info' => $info,'type' => 666, 'currency' => 3,'info_en' => $info,'info_jp' => $info,'info_hk' => $info,'info_spa' => $info,'info_kr' => $info,'transfered' => 1]);
        return $this->success(get_lang_config("buy_mining_machine"));
    }

    //购买期货订单
    public function my_mining_machine()
    {
        $limit = Input::get('limit', 10);
        $page = Input::get('page', 1);
        $user_id = Users::getUserId();
        $futures_market_buy = DB::table('futures_market_buy')->where([
            ['user_id', $user_id]
        ])->orderBy('state','asc')->orderBy('time','desc')->paginate($limit, ['*'], 'page', $page);
        foreach ($futures_market_buy as $k => $v){
            $futures_market_buy[$k]->time = date("Y-m-d H:i:s",$futures_market_buy[$k]->time);
            $futures_market_buy[$k]->totime = date("Y-m-d H:i:s",$futures_market_buy[$k]->totime);
        }
        return $this->success($futures_market_buy);
    }
    
    //期货收益
    public function get_mining_incomes()
    {
        $user_id = Users::getUserId();
        $futures_market_buy = DB::table('futures_market_buy')
            ->where([
                ['user_id','=',$user_id],
                ['state','=',1]
            ])->get();
        
        $data['num'] = count($futures_market_buy);
        $data['money'] = 0;
        $data['incomesing'] = 0;
        foreach ($futures_market_buy as $key => $item){
            $data['money'] += $item->money;
            $data['incomesing'] += $item->interest;
        }

        //获取累计收益
        $data['incomesed'] = DB::table('futures_market_buy')
            ->where([
                ['user_id','=',$user_id],
                ['state','=',2]
            ])->sum("interest");
        return $this->success($data);
    }

    //矿机用户日收益-服务器定时任务
    public function mining_machine_daily_income()
    {
        $futures_market_buy = DB::table('futures_market_buy')
        ->where([
            ['state','=',1],
            ['totime','<=',time()]
        ])->get();
        if($futures_market_buy){
            foreach ($futures_market_buy as $key => $item){
                DB::beginTransaction();
                $users_wallet = DB::table('users_wallet')->where([
                    ['user_id','=',$item->uid],
                    ['currency','=',3]
                ])->increment('micro_balance', $item->interest);
                if($users_wallet){
                    $futures_update = DB::table('futures_market_buy')->where(['id'=>$item->id])->update(['state'=>2]);
                    if($futures_update){
                        DB::commit();
                    } else {
                        DB::rollBack();
                    }
                }
            }
        }
    }
    
    //到期自动返还押金-服务器定时任务
    public function mining_machine_refund_deposit()
    {
        $mining_machine_s = DB::table('futures_market_buy')
        ->where([
            ['deposit','=',1],
            ['e_time','<=',time()]
        ])->get();
        foreach ($mining_machine_s as $k => $v){
            DB::table('users_wallet')->where([
                ['user_id','=',$mining_machine_s[$k]->uid],
                ['currency','=',3]
            ])->increment('micro_balance', $mining_machine_s[$k]->money);
            DB::table('futures_market_buy')->where('id',$mining_machine_s[$k]->id)->update(['status' => 2,'deposit' => 2]);
        }
    }
    
    //注册送矿机福利
    public function mining_machine_new_benefits()
    {
        $user_id = Users::getUserId();
        $num = DB::table('futures_market_buy')->where('uid',$user_id)->count();
        if($num == 0){
            $inserts = [
                'uid' => $user_id,
                'mid' => 7,
                'type' => 3,
                'num' => 1,
                'status' => 1,
                'deposit' => 2,
                'b_time' => time(),
                'e_time' => time()+(7*86400)
            ];
            DB::table('mining_machine_buy')->insert($inserts);
        }else{
            return $this->error('不满足赠送条件');
        }
        return $this->success(get_lang_config("mining_machine_new_benefits"));
    }
}
?>