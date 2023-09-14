<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use App\Admin;
use App\AdminRole;
use App\AdminRolePermission;
use App\Users;

class DefaultController extends Controller
{

    public function login()
    {
        $username = Input::get('username', '');
        $password = Input::get('password', '');
        if (empty($username)) {
            return $this->error('用户名必须填写');
        }
        if (empty($password)) {
            return $this->error('密码必须填写');
        }
        $password = Users::MakePassword($password);
        $admin = Admin::where('username', $username)->where('password', $password)->first();
        if (empty($admin)) {
            return $this->error('用户名密码错误');
        } else {
            $role = AdminRole::find($admin->role_id);
            if (empty($role)) {
                return $this->error('账号异常');
            } else {
                session()->put('admin_username', $admin->username);
                session()->put('admin_id', $admin->id);
                session()->put('admin_role_id', $admin->role_id);
                session()->put('admin_is_super', $role->is_super);
                return $this->success('登陆成功');
            }
        }
    }

    public function login1()
    {
        return view('admin.login1');
    }

    public function index()
    {
        $admin_role = AdminRolePermission::where("role_id", session()->get('admin_role_id'))->get();
        $admin_role_data = array();
        foreach ($admin_role as $r) {
            array_push($admin_role_data, $r->action);
        }
        return view('admin.indexnew')->with("admin_role_data", $admin_role_data);;
    }

    public function indexnew()
    {
        $admin_role = AdminRolePermission::where("role_id", session()->get('admin_role_id'))->get();
        $admin_role_data = array();
        foreach ($admin_role as $r) {
            array_push($admin_role_data, $r->action);
        }
        return view('admin.index')->with("admin_role_data", $admin_role_data);;
    }



    public function getVerificationCode(Request $request)
    {
        $http_client = app('LbxChainServer');

        $uri = '/v3/wallet/verification';

        $response = $http_client->request('post', $uri, [
            'form_params' => [
                'projectname' => config('app.name'),
            ],
        ]);
        $result = json_decode($response->getBody()->getContents(), true); 
        if (isset($result['code']) && $result['code'] == 0) {
            return $this->success('发送成功');
        } else {
            return $this->error($result['msg']);
        }
    }
}
