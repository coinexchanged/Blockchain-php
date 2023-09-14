<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FaKa extends Controller
{
    public function index()
    {
        return view('admin.faka.index');
    }
    
    public function getlist(Request $request)
    {
        $param = $request->input();
        $limit = $request->get('limit', 20);
        $where = [];
        if (isset($param['uid']) && $param['uid']) $where[] = ['uid', '=', $param['uid']];
        if (isset($param['status']) && $param['status'] > 0) $where[] = ['status', '=', $param['status']];
        $first_card = DB::table("card_issuance")->where($where)->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($first_card);
    }
    
    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        $first_card = DB::table("card_issuance")->where("id",$id)->first();
        return view('admin.faka.edit', ['result' => $first_card]);
    }
    
    public function postedit(Request $request)
    {
        $data = $request->input();
        $data["status"] = 2;
        DB::table("card_issuance")->update($data);
        return $this->success('发放成功');
    }
    
    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        DB::table("card_issuance")->where("id",$id)->delete();
        return $this->success('删除成功');
    }
}