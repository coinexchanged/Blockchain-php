<?php

namespace App\Http\Controllers\Agent;

use App\Agent;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AgentIndexController extends Controller
{

    /**
     * 首页获取统计数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request){
        $access_token = $request->get('access_token', 0);
        $agent = Agent::getAgentById(session($access_token));

        $agent_id =$agent['id'];
        $user_id = $agent['user_id'];
        $data['settlement'] = DB::table('lever_transaction')->where('status' , 2)->where('settled' , 1)->count();//结算订单数量
        $data['subordinate_agent_num'] = Agent::where('parent_agent_id', $agent_id)->count();//下级代理商数量
        $data['subordinate_user_num'] = Users::where('parent_id', $user_id)->count();//下级用户数量

        return $this->ajaxReturn($data);
    }
}
