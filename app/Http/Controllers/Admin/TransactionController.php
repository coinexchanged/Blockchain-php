<?php

namespace App\Http\Controllers\Admin;

use App\Agent;
use App\AgentMoneylog;
use App\Setting;
use App\AccountLog;
use App\Transaction;
use App\TransactionComplete;
use App\TransactionIn;
use App\TransactionOut;
use App\Users;
use App\Currency;
use App\LeverTransaction;
use App\TransactionOrder;
use App\TransactionOrdercopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Facades\Excel;
class TransactionController extends Controller{

    public function index(){
        $currency = Currency::all();
        return view("admin.transaction.index",['currency'=> $currency]);
    }

    public function lists(){
        $limit = Input::get('limit',10);
        $account_number = Input::get('account_number','');//用户交易账号
        $type = Input::get('type', '');
        $currency = Input::get('currency', '');
        $status = Input::get('status', '');

        $result = new Transaction();
        if (!empty($account_number)){

            $users = Users::where('account_number','like','%'.$account_number.'%')->get()->pluck('id');
            $result = $result->where(function($query) use ($users){
                $query->whereIn('from_user_id',$users);
            } );
            // ->orWhere(function($query) use ($users){
            //     $query->whereIn('to_user_id',$users);
            // });
        }
        
        if (!empty($type)) {
            $result = $result->where('type','=', $type);
        }
        if (!empty($currency)) {
            $result = $result->where('currency', $currency);
        }
        if (!empty($status)) {
            $result = $result->where('status', $status);
        }
        

        $list = $result->orderBy('id','desc')->paginate($limit);
        return response()->json(['code'=>0,'data'=>$list->items(),'count'=>$list->total()]);
    }

    public function completeIndex(){
        return view("admin.transaction.complete");
    }
    public function inIndex(){
        return view("admin.transaction.in");
    }
    public function outIndex(){
        return view("admin.transaction.out");
    }
    public function cnyIndex(){
        return view("admin.transaction.cny");
    }

    public function completeList(Request $request){
        $limit = $request->get('limit',10);
        $account_number = $request->get('account_number','');
        $result = new TransactionComplete();
        if (!empty($account_number)){
            $users = Users::where('account_number','like','%'.$account_number.'%')->get()->pluck('id');
            $result = $result->whereIn('user_id',$users);
        }
        $result = $result->orderBy('id','desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function inList(Request $request){
        $limit = $request->get('limit',10);
        $account_number = $request->get('account_number','');
        $result = new TransactionIn();
        if (!empty($account_number)){
            $users = Users::where('account_number','like','%'.$account_number.'%')->get()->pluck('id');
            $result = $result->whereIn('user_id',$users);
        }
        $result = $result->orderBy('id','desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function outList(Request $request){
        $limit = $request->get('limit',10);
        $account_number = $request->get('account_number','');
        $result = new TransactionOut();
        if (!empty($account_number)){
            $users = Users::where('account_number','like','%'.$account_number.'%')->get()->pluck('id');
            $result = $result->whereIn('user_id',$users);
        }
        
        $result = $result->orderBy('id','desc')->paginate($limit);
       
        return $this->layuiData($result);
    }

    public function cnyList(Request $request){
        $limit = $request->get('limit',10);
        $account_number = $request->get('account_number','');
        $result = new AccountLog();
        if (!empty($account_number)){
            $users = Users::where('account_number','like','%'.$account_number.'%')->get()->pluck('id');
            $result = $result->whereIn('user_id',$users);
        }
        $types = array(13,14,15,20,22,24);
        $result = $result->whereIn('type',$types)->orderBy('id','desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function Leverdeals_show(){
//        $currency = Currency::all();
        return view("admin.leverdeals.list");
    }
    /*
    //后台杠杆交易订单管理 by tian
    public function Leverdeals(Request $request){

        $limit = $request->input("limit", 10);
        $id = $request->input("id", 0);
        $username = $request->input("phone", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
//        var_dump($id);die;
        $where = [];
        if ($id > 0){
            $where[] = ['lever_transaction.id' , '=' , $id];
        }
//        var_dump($where);die;
        if (!empty($username)){
            $s = DB::table('users')->where('account_number' , $username)->first();
            if ($s !== null){
                $where[] = ['lever_transaction.user_id' , '=' , $s->id];
            }
        }

        if ($status  != 10   && in_array($status , [LeverTransaction::ENTRUST,LeverTransaction::BUY,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])){
            $where[] = ['lever_transaction.status' , '=' , $status];
        }

        if ($type > 0 && in_array($type , [1,2])){
            $where[] = ['type' , '=' , $type];
        }
        if (!empty($start) && !empty($end)) {
            $where[] = ['lever_transaction.create_time' , '>' , strtotime($start . ' 0:0:0')];
            $where[] = ['lever_transaction.create_time' , '<' , strtotime($end . ' 23:59:59')];
        }

        $order_list = TransactionOrdercopy::leftjoin("users","lever_transaction.user_id","=","users.id")->select("lever_transaction.*","users.phone")->whereIn('lever_transaction.status' , [LeverTransaction::ENTRUST,LeverTransaction::BUY,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])->where($where)->paginate($limit);

        foreach($order_list as $key =>$value )
        {
            $order_list[$key]["create_time"]=date("Y-m-d H:i:s",$value->create_time);
            $order_list[$key]["transaction_time"]=date("Y-m-d H:i:s",substr($value->transaction_time,0,strpos($value->transaction_time, '.')));
            $order_list[$key]["update_time"]=date("Y-m-d H:i:s",substr($value->update_time,0,strpos($value->update_time, '.')));
            $order_list[$key]["handle_time"]=date("Y-m-d H:i:s",substr($value->handle_time,0,strpos($value->handle_time, '.')));
            $order_list[$key]["complete_time"]=date("Y-m-d H:i:s",substr($value->complete_time,0,strpos($value->complete_time, '.')));
        }

        return $this->layuiData($order_list);
    }*/

    public function Leverdeals(Request $request)
    {

        $limit = $request->input("limit", 20);
        $id = $request->input("id", 0);
        $username = $request->input("phone", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);
        // $real_name = $request->input("real_name",'');
        // $parent_name = $request->input("parent_name",'');
        // $parent_account = $request->input("parent_account",'');
        $legal_id = $request->input('legal_id',-1);

        $start = $request->input("start", '');
        $end = $request->input("end", '');




            $query =LeverTransaction::whereHas('user', function ($query) use ($username) {
                
                $username != '' && $query->where('account_number', $username)->orWhere('phone', $username);

                
            })->where(function ($query) use ($id,$status,$type,$legal_id) {
             
                $id !=0 && $query->where('id', $id);
                $legal_id !=-1 && $query->where('legal', $legal_id);

                $status != 10 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::BUY, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING]) && $query->where('status', $status);

                $type > 0 && in_array($type, [1, 2]) && $query->where('type',$type);
            })->where(function($query) use ($start,$end){

                !empty($start) && $query->where('create_time','>=',strtotime($start . ' 0:0:0'));
                
                !empty($end) && $query->where('create_time','<=',strtotime($end . ' 23:59:59'));

            });
            


            $query_total = clone $query;
            $total = $query_total->select([
                //DB::raw('sum(fact_profits) as balance1'),
                 DB::raw('SUM((CASE `type` WHEN 1 THEN `update_price` - `price` WHEN 2 THEN `price` - `update_price` END) * `number`) AS `balance1`'),
                DB::raw('sum(origin_caution_money) as balance2'),
                DB::raw('sum(trade_fee) as balance3'),
                
            ])->first();
            $total && $total = $total->setAppends([]);

            $order_list =$query->orderBy('id', 'desc')->paginate($limit);
            $items = $order_list->getCollection();
            $items->transform(function ($item, $key){
            
            $item->setAppends(['symbol','account_number','profits','time']);
            
            return $item;
        });

        return $this->layuiData($order_list, ['total' => $total]);
    }



    //导出杠杆交易 团队所有订单excel
    public function csv(Request $request){

//        $limit = $request->input("limit", "");
        $id = $request->input("id", 0);
        $username = $request->input("phone", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
//        var_dump($id);die;
        $where = [];
        if ($id > 0){
            $where[] = ['lever_transaction.id' , '=' , $id];
        }
//        var_dump($where);die;
        if (!empty($username)){
            $s = DB::table('users')->where('account_number' , $username)->first();
            if ($s !== null){
                $where[] = ['lever_transaction.user_id' , '=' , $s->id];
            }
        }

        if ($status  != 10   && in_array($status , [LeverTransaction::ENTRUST,LeverTransaction::BUY,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])){
            $where[] = ['lever_transaction.status' , '=' , $status];
        }

        if ($type > 0 && in_array($type , [1,2])){
            $where[] = ['type' , '=' , $type];
        }
        if (!empty($start) && !empty($end)) {
            $where[] = ['lever_transaction.create_time' , '>' , strtotime($start . ' 0:0:0')];
            $where[] = ['lever_transaction.create_time' , '<' , strtotime($end . ' 23:59:59')];
        }

        $order_list = TransactionOrdercopy::leftjoin("users","lever_transaction.user_id","=","users.id")->select("lever_transaction.*","users.phone")->whereIn('lever_transaction.status' , [LeverTransaction::ENTRUST,LeverTransaction::BUY,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])->where($where)->get();

        foreach($order_list as $key =>$value )
        {
            $order_list[$key]["create_time"]=date("Y-m-d H:i:s",$value->create_time);
            $order_list[$key]["transaction_time"]=date("Y-m-d H:i:s",substr($value->transaction_time,0,strpos($value->transaction_time, '.')));
            $order_list[$key]["update_time"]=date("Y-m-d H:i:s",substr($value->update_time,0,strpos($value->update_time, '.')));
            $order_list[$key]["handle_time"]=date("Y-m-d H:i:s",substr($value->handle_time,0,strpos($value->handle_time, '.')));
            $order_list[$key]["complete_time"]=date("Y-m-d H:i:s",substr($value->complete_time,0,strpos($value->complete_time, '.')));
        }

        $data = $order_list;

        return Excel::create('杠杆交易', function ($excel) use ($data) {
            $excel->sheet('杠杆交易', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('用户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('交易手续费');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('隔夜费金额');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('交易类型');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('当前状态');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('原始价格');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('开仓价格');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('当前价格');
                });



                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('手数');
                });
                $sheet->cell('K1', function ($cell) {
                    $cell->setValue('倍数');
                });
                $sheet->cell('L1', function ($cell) {
                    $cell->setValue('初始保证金');
                });
                $sheet->cell('M1', function ($cell) {
                    $cell->setValue('当前可用保证金');
                });
                $sheet->cell('N1', function ($cell) {
                    $cell->setValue('创建时间');
                });
                $sheet->cell('O1', function ($cell) {
                    $cell->setValue('价格刷新时间');
                });
                $sheet->cell('P1', function ($cell) {
                    $cell->setValue('平仓时间');
                });
                $sheet->cell('Q1', function ($cell) {
                    $cell->setValue('完成时间');
                });

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if($value['type']==1)
                        {
                            $value['type']="买入";
                        }
                        else{
                            $value['type']="卖出";
                        }
                        if($value['status']==0)
                        {
                            $value['status']="挂单中";
                        }elseif($value['status']==1)
                        {
                            $value['status']="交易中";
                        }
                        elseif($value['status']==2)
                        {
                            $value['status']="平仓中";
                        }
                        elseif($value['status']==3)
                        {
                            $value['status']="已平仓";
                        }
                        elseif($value['status']==4)
                        {
                            $value['status']="已撤单";
                        }

                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['phone']);
                        $sheet->cell('C' . $i, $value['trade_fee']);
                        $sheet->cell('D' . $i, $value['overnight_money']);
                        $sheet->cell('E' . $i, $value['type']);
                        $sheet->cell('F' . $i, $value['status']);
                        $sheet->cell('G' . $i, $value['origin_price']);
                        $sheet->cell('H' . $i, $value['price']);
                        $sheet->cell('I' . $i, $value['update_price']);

                        $sheet->cell('J' . $i, $value['share']);
                        $sheet->cell('K' . $i, $value['multiple']);
                        $sheet->cell('L' . $i, $value['origin_caution_money']);
                        $sheet->cell('M' . $i, $value['caution_money']);
                        $sheet->cell('N' . $i, $value['create_time']);
                        $sheet->cell('O' . $i, $value['update_time']);
                        $sheet->cell('P' . $i, $value['handle_time']);
                        $sheet->cell('Q' . $i, $value['complete_time']);
                    }
                }
            });
        })->download('xlsx');
    }

}
?>