<?php

namespace App\Http\Controllers\Admin;


use App\AdminModule;
use App\AdminModuleAction;
use App\AdminRole;
use App\AdminRolePermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class AdminRolePermissionController extends Controller{

    public function update()
    {
        if(session()->get('admin_is_super') != '1') {
            abort(403);
        }
        $id = Input::get('id', '');
        $adminRole = AdminRole::find($id);
        if($adminRole == null) {
            abort(404);
        }

        $modules = AdminModule::all();
        $modules_data = [];
        foreach ($modules as $index=>$m)
        {
            $action = AdminModuleAction::where("admin_module_id",$m->id)->where("level",">=",0)->get();
            if (count($action)){
                $m->actions = $action;
                $modules_data[$index] = $m;
            }
        }
        $results = AdminRolePermission::where('role_id', $adminRole->id)->get();
        if(empty($results)){
            $permissions = [];
        }else{
            $results = $results->toArray();
            $permissions = [];
            foreach($results as $row) {
                $permissions[$row['module']][] = $row['action'];
            }
        }
        return view('admin.manager.role_permission', [
            'admin_role' => $adminRole,
            'modules' => $modules_data,
            'permissions' => $permissions,
            'success' => Session::get('success', null)
        ]);
    }

    public function postUpdate()
    {
        if(session()->get('admin_is_super') != '1') {
            abort(403);
        }
        $roleID = Input::get('id', null);

        $role = AdminRole::find($roleID);
        if($role == null) {
            abort(404);
        }

        AdminRolePermission::where('role_id', $roleID)->delete();
        foreach(Input::get('permission') as $module => $actions)
        {
            foreach($actions as $action)
            {
                $adminRolePermission = new AdminRolePermission();
                $adminRolePermission->role_id = $roleID;
                $adminRolePermission->module = $module;
                $adminRolePermission->action = $action;
                $adminRolePermission->save();
            }
        }
        return $this->success('修改成功');
    }
}
?>