<?php

namespace App\Http\Controllers\Admin;

use App\LegalDeal;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\LegalDealSend;
use App\Currency;

class LegalDealSendController extends Controller
{
    public function index(){

        $currency = Currency::where('is_legal',1)->orderBy('id','desc')->get();//获取法币
        return view('admin.legal.index',['currency'=> $currency]);
    }
    public function list(Request $request){
        $limit = $request->get('limit', 10);
        //$account_number = $request->get('account_number', '');
        $seller_name = $request->get('seller_name', '');
        $type = $request->get('type', '');
        $currency_id = $request->get('currency_id', 0);
        $result = new LegalDealSend();

        if(!empty($seller_name)){

            $result = $result->whereHas('seller', function ($query) use ($seller_name) {
                $query->where('name', 'like', '%' . $seller_name . '%');
            });
        }

        if (!empty($type)) {

            $result = $result->where('type', $type);
        }
        if (!empty($currency_id)) {

            $result = $result->where('currency_id', $currency_id);
        }
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

}