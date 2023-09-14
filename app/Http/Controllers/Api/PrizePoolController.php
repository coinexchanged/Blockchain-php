<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\PrizePoolcopy;
use App\Users;
use App\AccountLog;
use Illuminate\Support\Facades\Input;
use App\DAO\FactprofitsDAO;

class PrizePoolController extends Controller
{
    public function test555()
    {
        $aa=new FactprofitsDAO();
        $aa::Profit_loss_release(1);
    }
    public function candyhistory()//通证奖励记录
    {
        $user_id = Users::getUserId();
        $prize_pool = PrizePoolcopy::where("to_user_id","=",$user_id)->orderBy("create_time","desc")->get()->toArray();
        foreach($prize_pool as $key =>$value)
        {
            $prize_pool[$key]["create_time"]=date("Y-m-d H:i:s",$value["create_time"]);
        }
//        var_dump($prize_pool);die;
        return $this->success($prize_pool);
    }

    public function candy_tousdthistory()//通证兑换usdt记录
    {
        $limit = Input::get('limit','10');
        $page = Input::get('page','1');
        $user_id = Users::getUserId();
        $type=AccountLog::CANDY_TOUSDT_CANDY;
        $prize_pool = AccountLog::where("user_id","=",$user_id)->where("type","=",$type)->orderBy("created_time","desc")->paginate($limit);
        return $this->success(array(
            "data"=>$prize_pool->items(),
            "limit"=>$limit,
            "page"=>$page,
        ));

//        return $this->success($prize_pool);
    }
}
