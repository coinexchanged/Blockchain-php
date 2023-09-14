<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\FeedBack;
use App\Users;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\CandyTransfer;

class CandyTransferController extends Controller
{
    public function index(){
        return view('admin.candytransfer.index');
    }
    public function candytransfer_List(Request $request){
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $account_number = $request->get('account_number', '');

//        var_dump($account_number);die;
        $feedback = new CandyTransfer();
        if(!empty($account_number)){
            $account_number=Users::where("account_number",$account_number)->first()->id;
            $feedback = $feedback->where("from_user_id", '=', $account_number)->orwhere("to_user_id", '=', $account_number);

        }
        $feedbackList = $feedback->orderBy('id', 'desc')->paginate($limit, ['*'], 'page', $page);
        // dd($result);
        return $this->layuiData($feedbackList);
    }
    public function feedBackDetail(Request $request){
        $id = $request->get('id', '');
        $feedback = FeedBack::find($id);
        return view('admin.candytransfer.detail',['feedback'=> $feedback]);
    }
    public function feedBackDel(Request $request){
        $id = $request->get('id', '');
        $res = FeedBack::deatory($id);
        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('请重试');
        }
    }
    public function reply(Request $request){
        $id = $request->get('id', '');
        $reply_content = $request->get('reply_content', '');
        if(empty($id)||empty($reply_content)){
            return $this->error('参数错误');
        }
        $feedback = FeedBack::find($id);
        $feedback->reply_content = $reply_content;
        $feedback->is_reply = 1;
        $feedback->reply_time = time();
        $feedback->save();
        return $this->success('回复成功');
    }
    //导出用户列表至excel
    public function csv()
    {
        $data = FeedBack::all()->toArray();
        return Excel::create('反馈数据', function ($excel) use ($data) {
            $excel->sheet('反馈数据', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('账户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('反馈内容');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('回复内容');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('反馈时间');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('回复时间');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('状态');
                });

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['content']);
                        $sheet->cell('D' . $i, $value['reply_content']);
                        $sheet->cell('E' . $i, $value['create_time']);
                        $sheet->cell('F' . $i, $value['reply_time']);
                        $sheet->cell('G' . $i, $value['is_reply']);
                    }
                }
            });
        })->download('xlsx');
    }
}