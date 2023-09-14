<?php

namespace App\Http\Middleware;

use App\Users;
use Closure;
use App\UserReal;
class IsLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user_id = Users::getUserId();
        //判断是否锁定账户 3.12 by t
        $users=Users::where('id',$user_id)->first();
        if ($users->status == 1){//1锁定   0不锁定

            return response()->json(['type' => 'error', 'message' => '您处于锁定状态！']);
        }

        return $next($request);
    }
}
