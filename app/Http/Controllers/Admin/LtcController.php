<?php

namespace App\Http\Controllers\Admin;

use App\{Ltc,LtcBuy,Currency, CurrencyMatch};
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;

class LtcController extends Controller{

    public function index(){
        return view("admin.ltc.index");
    }

    public function lists(Request $request){
        $limit = $request->get('limit',10);
        $list = Ltc::orderBy('id','desc')->paginate($limit);
        return response()->json(['code'=>0,'data'=>$list->items(),'count'=>$list->total()]);
    }

    public function add()
    {
        $currency = Currency::get();
        return view('admin.ltc.add',['currencies'=>$currency]);
    }

    public function postAdd(Request $request){
        $currency_id = $request->get('currency_id','');
        $days = $request->get('days','');
        $rates = $request->get('rates','');
        $pricemin = $request->get('pricemin','');
        $state = $request->get('state','');
        if(empty($currency_id)){
            return $this->error('请选择币种');
        }
        if(empty($days)){
            return $this->error('请输入产品期限');
        }
        if(empty($rates)){
            return $this->error('请输入产品利率');
        }
        if(empty($pricemin)){
            return $this->error('请输入起投金额');
        }
        $currency_name = Currency::where('id',$currency_id)->value('name');

        $id = $request->get('id','');
        if(empty($id)){
            $result = new Ltc();
        }else{
            $result = Ltc::find($id);
        }
        try{
            $result->currency_id = $currency_id;
            $result->currency_name = $currency_name;
            $result->days = $days;
            $result->rates = $rates;
            $result->pricemin = $pricemin;
            $result->state = $state;
            $result->save();
            return $this->success('添加成功');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        $id = Input::get('id',null);
        if(empty($id)){
            return $this->error('参数错误');
        }
        $result = DB::table('futures_market')->where('id', $id)->first();
        if(empty($result)){
            return $this->error('无此数据');
        }
        $currency = Currency::get();
        return view('admin.ltc.add', ['result'=>$result, 'currencies'=>$currency]);
    }

    public function del(Request $request){
        $id = $request->get('id','');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $result = Ltc::find($id);
        try{
            $result->delete();
            return $this->success('删除成功');
        }catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
    
    public function ltcBuy(){
        return view("admin.ltc.ltcBuy");
    }

    public function buyList(Request $request){
        $limit = $request->get('limit',10);
        $result = new LtcBuy();
        $result = $result->orderBy('id','desc')->paginate($limit);
        return response()->json(['code'=>0,'data'=>$result->items(),'count'=>$result->total()]);
    }
}
?>