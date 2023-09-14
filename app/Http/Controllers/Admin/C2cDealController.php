<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\C2cDeal;
use App\Currency;
use Maatwebsite\Excel\Facades\Excel;

class C2cDealController extends Controller
{
    public function index()
    {
        //买入交易总额
        $aaaa=C2cDeal::leftJoin("c2c_deal_send","c2c_deal.legal_deal_send_id","=","c2c_deal_send.id")->where("c2c_deal_send.type","=","sell")->select("c2c_deal.*","c2c_deal_send.type")->get();
        $buy_all_number=0;
        foreach($aaaa as $key=>$value)
        {
            $buy_all_number=$buy_all_number+$value->number;
        }

        //卖出交易总额
        $bbbb=C2cDeal::leftJoin("c2c_deal_send","c2c_deal.legal_deal_send_id","=","c2c_deal_send.id")->where("c2c_deal_send.type","=","buy")->select("c2c_deal.*","c2c_deal_send.type")->get();
        $sell_all_number=0;
        foreach($bbbb as $key=>$value)
        {
            $sell_all_number=$sell_all_number+$value->number;
        }

        // $currency = Currency::where('is_legal', 1)->orderBy('id', 'desc')->get();//获取法币
        //return view('admin.legal.deal', ['currency' => $currency]);
        return view('admin.c2c.deal',['sell_all_number' => $sell_all_number,'buy_all_number'=>$buy_all_number]);
    }

    public function list(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account_number = $request->get('account_number', '');
        $seller_number = $request->get('seller_number', '');
        $type = $request->get('type', '');
        // $currency_id = $request->get('currency_id', 0);
        $result = new C2cDeal();
        if (!empty($account_number)) {
            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }
        if (!empty($seller_number)) {

            $result = $result->whereHas('seller', function ($query) use ($seller_number) {
                $query->where('account_number', 'like', '%' . $seller_number . '%');
            });
        }

        if (!empty($type)) {
            $result = $result->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });

        }
        // if (!empty($currency_id)) {
        //     $result = $result->whereHas('legalDealSend', function ($query) use ($currency_id) {
        //         $query->where('currency_id', $currency_id);
        //     });
        // }

        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }
    //导出c2c交易信息
    public function csv(Request $request)
    {

//        $limit = $request->get('limit', 10);
        $account_number = $request->get('account_number', '');
        $seller_number = $request->get('seller_number', '');
        $type = $request->get('type', '');
//    var_dump($account_number);var_dump($seller_number);var_dump($type);
        // $currency_id = $request->get('currency_id', 0);
        $result = new C2cDeal();
        if (!empty($account_number)) {
            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }
        if (!empty($seller_number)) {

            $result = $result->whereHas('seller', function ($query) use ($seller_number) {
                $query->where('account_number', 'like', '%' . $seller_number . '%');
            });
        }

        if (!empty($type)) {
            $result = $result->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });

        }
        // if (!empty($currency_id)) {
        //     $result = $result->whereHas('legalDealSend', function ($query) use ($currency_id) {
        //         $query->where('currency_id', $currency_id);
        //     });
        // }

        $data = $result->orderBy('id', 'desc')->get();
//    var_dump($data->toArray());die;


//        $data = C2cDeal::all()->toArray();
        return Excel::create('交易信息', function ($excel) use ($data) {
            $excel->sheet('交易信息', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('交易需求id');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('用户交易账号');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('真实姓名');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('买入/卖出');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('支付方式');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('单价');
                });


                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('交易量');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('交易币');
                });
                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('交易总金额');
                });
                $sheet->cell('K1', function ($cell) {
                    $cell->setValue('交易状态');
                });
                $sheet->cell('L1', function ($cell) {
                    $cell->setValue('交易时间');
                });
                $sheet->cell('M1', function ($cell) {
                    $cell->setValue('确认时间');
                });

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if($value["type"]=="buy")
                        {
                            $value["type"]="卖出";
                        }
                        elseif($value["type"]=="sell")
                        {
                            $value["type"]="买入";
                        }
                        if($value["is_sure"]==0)
                        {
                            $value["is_sure"]="未完成";
                        }elseif($value["is_sure"]==1)
                        {
                            $value["is_sure"]="已完成";
                        }
                        elseif($value["is_sure"]==2)
                        {
                            $value["is_sure"]="取消";
                        }
                        elseif($value["is_sure"]==3)
                        {
                            $value["is_sure"]="已付款";
                        }
//                        var_dump($value["type"]);die;
                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['legal_deal_send_id']);
                        $sheet->cell('C' . $i, $value['account_number']);
                        $sheet->cell('D' . $i, $value['user_realname']);
                        $sheet->cell('E' . $i, $value['type']);
                        $sheet->cell('F' . $i, $value['way_name']);
                        $sheet->cell('G' . $i, $value['price']);
                        $sheet->cell('H' . $i, $value['number']);
                        $sheet->cell('I' . $i, $value['currency_name']);
                        $sheet->cell('J' . $i, $value['deal_money']);
                        $sheet->cell('K' . $i, $value['is_sure']);
                        $sheet->cell('L' . $i, $value['format_create_time']);
                        $sheet->cell('M' . $i, $value['format_update_time']);
                    }
                }
            });
        })->download('xlsx');
    }

}