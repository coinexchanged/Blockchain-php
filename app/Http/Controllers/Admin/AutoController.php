<?php

namespace App\Http\Controllers\Admin;

use App\AutoList;
use App\Currency;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

class AutoController extends Controller
{
    public function index(){
        return view('admin.auto.index');
    }

    public function add(Request $request){
        $id = $request->get('id',null);
        if (empty($id)){
            $result = new AutoList();
        }else{
            $result = AutoList::find($id);
        }
        $currencies = Currency::where('is_legal',0)->orderBy('id','desc')->get();
        $legals = Currency::where('is_legal',1)->orderBy('id','desc')->get();
        return view('admin.auto.add')->with(['currencies'=>$currencies,'legals'=>$legals,'result'=>$result]);
    }

    public function postAdd(Request $request){
        $id = $request->get('id',null);
        $sell_account = $request->get('sell_account',null);
        $buy_account = $request->get('buy_account',null);
        $currency_id = $request->get('currency_id',null);
        $legal_id = $request->get('legal_id',null);
        $min_price = $request->get('min_price',null);
        $max_price = $request->get('max_price',null);
        $min_number = $request->get('min_number',null);
        $max_number = $request->get('max_number',null);
        $need_second = $request->get('need_second',null);

        $messages  = [
            'sell_account.required'       => '卖家账号必填',
            'buy_account.required'           => '买家账号必填',
            'currency_id.required' => '请选择交易币',
            'currency_id.integer' => '交易币值必须为整型',
            'legal_id.required' => '请选择法币',
            'legal_id.integer' => '法币值必须为整型',
            'min_price.required' => '请填写最低价格区间',
            'min_price.numeric' => '最低价格区间必须为数字',
            'max_price.required' => '请填写最高价格区间',
            'max_price.numeric' => '最高价格区间必须为数字',
            'min_number.required' => '请填写最低随机购买数量',
            'min_number.numeric' => '最低随机购买数量必须为数字',
            'max_number.required' => '请填写最高随机购买数量',
            'max_number.numeric' => '最高随机购买数量必须为数字',
            'need_second.required' => '请填写生成频率',
            'need_second.integer' => '生成频率必须为整型',
        ];

        //验证
        $validator = Validator::make($request->all(), [
            'sell_account' => 'required', //正则验证 如有多条不能用| 必须是数组 ['required','regex:/^[a-zA-Z0-9]$/']
            'buy_account'   => 'required',
            'currency_id' => 'required|integer',
            'legal_id' => 'required|integer',
            'min_price' => 'required|numeric',
            'max_price' => 'required|numeric',
            'min_number' => 'required|numeric',
            'max_number' => 'required|numeric',
            'need_second' => 'required|integer',
        ], $messages);

        if ($validator->fails()){
            return $this->error($validator->errors()->first());
        }

        $sell_user = Users::where('account_number',$sell_account)->first();
        if (empty($sell_user)) return $this->error('卖家用户不存在');
        $buy_user = Users::where('account_number',$buy_account)->first();
        if (empty($buy_user)) return $this->error('卖家账号不存在');
        $currency = Currency::find($currency_id);
        if (empty($currency)) return $this->error('交易币不存在');
        $legal = Currency::find($legal_id);
        if (empty($legal) || empty($legal->is_legal)) return $this->error('该币不是法币');
        if ($min_price >= $max_price) return $this->error('请设置正确的价格区间');
        if ($min_number >= $max_number) return $this->error('请填写正确的随机数量');
        if ($need_second <= 0) return $this->error('请填写正确的生成秒数');

        $is = AutoList::where('currency_id',$currency_id)->where('legal_id',$legal_id)->first();

        if (!empty($is) && empty($id)){
            return $this->error('该交易对已经有机器人了');
        }

        if (empty($id)){
            $auto_list = new AutoList();
            $auto_list->create_time = time();

        }else{
            $auto_list = AutoList::find($id);
        }
        try{
            $auto_list->buy_user_id = $buy_user->id;
            $auto_list->sell_user_id = $sell_user->id;
            $auto_list->currency_id = $currency_id;
            $auto_list->legal_id = $legal_id;
            $auto_list->min_price = $min_price;
            $auto_list->max_price = $max_price;
            $auto_list->min_number = $min_number;
            $auto_list->max_number = $max_number;
            $auto_list->need_second = $need_second;
            $auto_list->save();
            return $this->success('添加成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }


    }


    public function lists(Request $request){
        $limit = $request->get('limit');
        $results = AutoList::orderBy('id','desc')->paginate($limit);
        return $this->layuiData($results);
    }


 
}
