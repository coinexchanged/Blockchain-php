<?php

namespace App\Http\Middleware;

use Closure;
use App\Users;

class ValidateUserLocked
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
        $user = Users::find($user_id);
        if ($user) {
            if ($user->status == 1 && $user->lock_time > time()) {
                return response()->json(['type' => 'error', 'message' => '账号锁定中,' . date('Y-m-d H:i:s', $user->lock_time) . '前不能进行此操作']);
            }
        } else {
            return response()->json(['type'=>'999','message'=>'请登录']);
        }
        return $next($request);
    }
}
