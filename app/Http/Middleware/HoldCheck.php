<?php

namespace App\Http\Middleware;

use Closure;
use App\Users;
use App\LeverTransaction;

class HoldCheck
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
        $lang_arr = ['kr'=>'kor','hk'=>'cht','jp'=>'jp','en'=>'en','spa'=>'spa','zh'=>'zh'];
        $lang = $lang_arr[$request->header('lang')];

        $user_id = Users::getUserId();
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)->whereNotIn('status', [LeverTransaction::CLOSED, LeverTransaction::CANCEL])->count();
        if ($exist_close_trade > 0) {
            return response()->json([
                'type' => 'error',
                'message' => mtranslate('操作失败:您有未平仓的交易,操作禁止',$lang)
            ]);
        }
        return $next($request);
    }
}
