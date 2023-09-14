<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Bank;
use Illuminate\Support\Facades\Validator;
class BankController extends Controller
{
    public function index()
    {
        
        return view('admin.bank.index');
    }
    public function list(Request $request){
        $limit = $request->get('limit', 20);
//        $account_number = $request->get('account_number','');
        $result = new Bank();

        $result = $result->orderBy('sort', 'asc')->orderBy('id', 'asc')->paginate($limit);
        return $this->layuiData($result);
    }
    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new Bank();
        } else {
            $result = Bank::find($id);
        }
        return view('admin.bank.add')->with('result', $result);
    }
    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $name = $request->get('name', '');
        $sort = $request->get('sort', 0);
        $logo = $request->get('logo', '');

        //自定义验证错误信息
        $messages = [
            'required' => ':attribute 为必填字段',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'token' => 'required',
            // 'get_address' => 'required',
            // 'sort' => 'required',
            // 'logo'=>'required',
        ], $messages);

        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
       


        if (empty($id)) {
            $bank = new Bank();
            // $bank->create_time = time();
        } else {
            $bank = Bank::find($id);
        }
        $bank->name = $name;
        $bank->sort = intval($sort);
        $bank->logo = $logo;

        try {
            $bank->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }



    }
    public function del(Request $request){
        $id = $request->get('id',"");
        if(empty($id))return $this->error('参数错误');
        $bank = Bank::find($id);
        try {
            $bank->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

  

}
