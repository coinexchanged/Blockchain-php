<?php

namespace App\Http\Controllers\Admin;


use App\AdminRole;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\Request;
use Validator;

class AdminRoleController extends Controller{

    public function users()
    {
        if(session()->get('admin_is_super') != '1') {
            abort(403);
        }
        $adminUses = AdminRole::all();
        return response()->json(['code'=>0,'data'=>$adminUses]);
    }

    public function add(){

        if(session()->get('admin_is_super') != '1') {
            abort(404);
        }
        $id = Input::get('id',null);

        if(empty($id)){
            $adminRole = New AdminRole();
            $adminRole -> is_super = 0;
        }else{
            $adminRole = AdminRole::find($id);
            if($adminRole == null) {
                abort(404);
            }
        }
        return view('admin.manager.role_add', ['admin_role' => $adminRole]);
    }

    public function postAdd(){
        if(session()->get('admin_is_super') != '1') {
            abort(403);
        }
        $id = Input::get('id', null);

        $validator = Validator::make(Input::all(), [
            'name' => 'required',
        ], [
            'name.required' => '角色名称必须填写',
        ]);

        if(empty($id)) {
            $adminRole = new AdminRole();
        }else{
            $adminRole = AdminRole::find($id);
            if($adminRole == null) {
                return redirect()->back();
            }
        }

        $adminRole->name = Input::get('name', '');
        $adminRole->is_super = Input::get('is_super', '');

        if($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        try {
            $adminRole->save();
        }catch (\Exception $ex){
            $validator->errors()->add('error', $ex->getMessage());
            return $this->error($validator->errors()->first());
        }
        return $this->success('添加成功');
    }

    public function del(Request $request){
        $id = $request->get('id');
        $admin_role = AdminRole::find($id);
        $bool = $admin_role->delete();
        if($bool){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }

    }
}
?>