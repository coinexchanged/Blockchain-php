<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2018/12/4
 * Time: 19:04
 */

namespace App\Http\Controllers\Agent;


use App\AgentMoneylog;
use App\Users;

class AccountController extends Controller
{

    /**结算流水
     * @return \Illuminate\Http\JsonResponse
     */
    public function moneyLog()
    {
        $start = request()->input('start', '');
        $end   = request()->input('end', '');

        $agentMoneyLog = new AgentMoneylog();

        if ($start && $end) {
            $start         = strtotime($start);
            $end           = strtotime($end);
            $agentMoneyLog = $agentMoneyLog->whereBetween('created_time', [$start, $end]);
        }

        $user = Users::find(Users::getUserId());

        $list = $agentMoneyLog->where('agent_id', $user->agent_id)->paginate();
        return $this->layuiData($list);
    }

}