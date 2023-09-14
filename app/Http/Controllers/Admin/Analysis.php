<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Analysis extends Controller
{
    public function index()
    {
        return view('admin.analysis.index');
    }
    
    public function getlist(Request $request)
    {
        $param = $request->input();
        $limit = $request->get('limit', 20);
        $where = [];
        if (isset($param['uid']) && $param['uid']) $where[] = ['uid', '=', $param['uid']];
        if (isset($param['status']) && $param['status'] > 0) $where[] = ['status', '=', $param['status']];
        $first_card = DB::table("analyst")->where($where)->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($first_card);
    }
    
    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        $first_card = DB::table("analyst")->where("id",$id)->first();
        return view('admin.analysis.edit', ['result' => $first_card]);
    }
    
    public function postedit(Request $request)
    {
        $id = $request->get('id', 0);
        $data = $request->input();
        DB::table("analyst")->where('id',$id)->update($data);
        return $this->success('修改成功');
    }
    
    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        DB::table("analyst")->where("id",$id)->delete();
        DB::table("history_record")->where("cid",$id)->delete();
        return $this->success('删除成功');
    }
    
    public function add(Request $request)
    {
        return view('admin.analysis.add');
    }
    
    public function post_add(Request $request)
    {
        $data = $request->input();
        $data = $this->array_remove($data,'file');
        DB::table("analyst")->insert($data);
        return $this->success('添加成功');
    }
    
    
    
    public function jilu(Request $request)
    {
        $id = $request->get('id', 0);
        return view('admin.analysis.jilu',['cid' => $id]);
    }
    
    public function jilu_list(Request $request)
    {
        $limit = $request->get('limit', 20);
        $id = $request->get('id', 0);
        $first_card = DB::table("history_record")->where("cid",$id)->paginate($limit);
        return $this->layuiData($first_card);
    }
    
    public function jilu_edit(Request $request)
    {
        $id = $request->get('id', 0);
        $first_card = DB::table("history_record")->where("id",$id)->first();
        return view('admin.analysis.jiluedit', ['result' => $first_card]);
    }
    
    public function post_jilu_edit(Request $request)
    {
        $id = $request->get('id', 0);
        $data = $request->input();
        DB::table("history_record")->where('id',$id)->update($data);
        return $this->success('修改成功');
    }
    
    public function jilu_delete(Request $request)
    {
        $id = $request->get('id', 0);
        DB::table("history_record")->where("id",$id)->delete();
        return $this->success('删除成功');
    }
    
    public function jilu_add(Request $request)
    {
        $id = $request->get('cid', 0);
        return view('admin.analysis.jiluadd',['cid' => $id]);
    }
    
    public function post_jilu_add(Request $request)
    {
        $data = $request->input();
        DB::table("history_record")->insert($data);
        return $this->success('添加成功');
    }
    
    public function array_remove($arr, $key){
        if(!array_key_exists($key, $arr)){
            return $arr;
        }
        $keys = array_keys($arr);
        $index = array_search($key, $keys);
        if($index !== FALSE){
            array_splice($arr, $index, 1);
        }
        return $arr;
    }
}