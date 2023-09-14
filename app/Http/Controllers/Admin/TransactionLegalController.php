<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use App\TransactionLegal;
use App\Currency;

class TransactionLegalController extends Controller
{
    public function index(){
        //$currency = Currency::all();
        $currency = Currency::where('type',1)->get();//获取法币
        //  var_dump($currency);
        return view('admin.transaction.legal',['currency'=> $currency]);
    }
    public function list(Request $request){
        $limit = $request->get('limit', 10);
        $account_number = $request->get('account_number', '');
        $type = $request->get('type', '');
        $currency = $request->get('currency', '');
        $result = new TransactionLegal();
        if(!empty($account_number)){
            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }
        if (!empty($type)) {
            $result = $result->where('type', $type);
        }
        if (!empty($currency)) {
            $result = $result->where('currency', $currency);
        }
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }
    
}