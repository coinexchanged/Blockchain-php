<?php

namespace App\Http\Middleware;
use App\Agent;
use Closure;

class AgentAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // public function handle($request, Closure $next) {
    //     if ($request->session()->has('access_token')) {

    //         $access_token = $request->session()->get('access_token');
    //         if ($request->input('access_token') != $access_token){
    //             return response()->json(['code' => 1001, 'msg' => '登录超时']);
    //         }else{
    //             return $next($request);
    //         }

    //     }else{
    //         return response()->json(['code' => 1001, 'msg' => '登录超时']);
    //     }
    // }
    public function handle($request, Closure $next)
    {
        $agent_id = session()->get('agent_id');

        if (empty($agent_id)) {
            return redirect('/agent');
        }
        $agent = Agent::where('id', $agent_id)->first();

        if (!$agent or $agent['is_lock'] == 1) {
            return redirect('/agent');
        }
        return $next($request);
    }
}
