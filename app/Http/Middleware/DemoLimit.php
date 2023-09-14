<?php

namespace App\Http\Middleware;

use Closure;

class DemoLimit
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
        $demo_mode = config('app.demo_mode');
        if ($demo_mode == 1) {
            if ($request->ajax()) {
                return response()->json([
                    'type' => 'error',
                    'message' => '模拟系统禁用此功能',
                ]);
            } else {
                abort(403, '模拟系统禁用此功能');
            }
        }
        return $next($request);
    }
}
