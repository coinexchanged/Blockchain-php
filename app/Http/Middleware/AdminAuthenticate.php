<?php

namespace App\Http\Middleware;

use App\Admin;
use App\AdminModuleAction;
use App\AdminRole;
use App\AdminRolePermission;
use Closure;
use Illuminate\Support\Facades\Route;


class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $admin = session()->get('admin_username');

        if (empty($admin)) {
            //return response()->json(['error' => '999', 'message' => '请先登录']);
            return redirect('/login');
        }
        $admin_user = Admin::where('username', $admin)->select()->first();

        $admin_role = AdminRole::where('id', $admin_user->role_id)->first();
        $admin_permit = AdminRolePermission::where('role_id', $admin_user->role_id)->get();

        $arr = [];
        foreach ($admin_permit as $v) {
            $arr[] = $v['action'];

        }

        $name = Route::getCurrentRoute()->uri();

        if ($admin_role['is_super'] != 1 && (!in_array($name, $arr))) {
            //return response()->json(['type'=>'997','message'=>'权限不够！']);
            if($request->ajax()){
                return response()->json(['type'=>'997','message'=>'权限不够！']);
            }
            abort(403, '权限不够');
        }
        //dd($name);
        return $next($request);
    }
}
