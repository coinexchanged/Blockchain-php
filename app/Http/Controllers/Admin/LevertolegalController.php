<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\LeverMultiple;
use App\CurrencyMatch;
use App\Setting;
use App\UsersWallet;
use App\WalletLog;
use App\UsersWalletOut;
use App\Users;
use App\Currency;
use Illuminate\Support\Facades\Input;
use App\Levertolegal;
use App\AccountLog;


class LevertolegalController extends Controller
{
    public function index()
    {
        return view('admin.levertolegal.index');
    }
    public function add()
    {
        return view('admin.levertolegal.add');
    }

    public function doadd(Request $request)
    {

        $aaaaaaa=new LeverMultiple();
        $aaaaaaa->value= Input::get('value', '');
        $aaaaaaa->type=Input::get('type', '');
//var_dump($aaaaaaa);die;
        try {
            $aaaaaaa->save();
        }catch (\Exception $ex){

        }
        return $this->success('添加成功');
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $name = $request->get('name', '');
        // $token = $request->get('token','');
        // $get_address = $request->get('get_address','');
        $sort = $request->get('sort', 0);
        $logo = $request->get('logo', '');
        $type = $request->get('type', '');
        $is_legal = $request->get('is_legal', '');
        $is_lever = $request->get('is_lever', '');
        $is_match = $request->get('is_match', '');
        $min_number = $request->get('min_number', 0);
        $rate = $request->get('rate', 0);
        $total_account = $request->get('total_account', 0);
        $key = $request->get('key', 0);
        $contract_address = $request->get('contract_address', 0);
        //自定义验证错误信息
        $messages = [
            'required' => ':attribute 为必填字段',
        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sort' => 'required',
            'type' => 'required',
            'is_legal' => 'required',
            'is_lever' => 'required',

            // 'logo'=>'required',
        ], $messages);

        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $has = Currency::where('name', $name)->first();
        if (empty($id) && !empty($has)) {
            return $this->error($name . ' 已存在');
        }
        if (empty($id)) {
            $currency = new Currency();
            $currency->create_time = time();
        } else {
            $currency = Currency::find($id);
        }
        $currency->name = $name;
        // $acceptor->token = $token;
        // $acceptor->get_address = $get_address;
        $currency->sort = intval($sort);
        $currency->logo = $logo;
        $currency->is_legal = $is_legal;
        $currency->is_lever = $is_lever;
        $currency->is_match = $is_match;
        $currency->min_number = $min_number;
        $currency->rate = $rate;
        $currency->total_account = $total_account;
        $currency->key = $key;
        $currency->contract_address = $contract_address;
        $currency->type = $type;
        $currency->is_display = 1;
        DB::beginTransaction();
        try {
            $currency->save();//保存币种
            // if(empty($id)){// 如果是添加新币 //没添加一种交易币，就给用户添加一个交易币钱包
            //     $currency_id = Currency::where('name',$name)->first()->id;
            //     $users = Users::all();
            //     foreach ($users as $key => $value) {
            //         $userWallet = new UsersWallet();
            //         $userWallet->user_id = $value->id;
            //         $userWallet->currency = $currency_id;
            //         $userWallet->create_time = time();
            //         $userWallet->save();
            //     }
            // }
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function lists(Request $request)
    {
//        $limit = $request->get('limit', 10);
//        $account_number = $request->get('account_number','');
        $result = new Levertolegal();
        $count=$result::all()->count();
        $result = $result->leftjoin("users","lever_tolegal.user_id","=","users.id")->where("lever_tolegal.type","=",1)->orderBy("lever_tolegal.add_time","desc")->select("lever_tolegal.*","users.phone")->get()->toArray();
        foreach($result as $key=>$value)
        {
            $result[$key]["add_time"]=date("Y-m-d H:i:s",$value["add_time"]);
            $result[$key]["type"]="杠杆转法币";
        }
//        var_dump($result);die;

        return response()->json(['code' => 0, 'data' => $result, 'count' => $count]);
    }

    //审核
    public function addshow(Request $request)
    {
        $id = $request->get('id',null);
        $res= Levertolegal::find($id);


        $data = $res->toArray();

        return view('admin.levertolegal.update',['result'=>$data]);
    }
    ////审核通过
    public function postAddyes(Request $request)
    {
        $id = $request->post('id',null);
        $user_id = $request->post('user_id',null);
        $number= $request->post('number',null);

        //查询出usdt币的id
        $usdt_id=Currency::where("name","=","USDT")->first()->id;


        //查询出对应的钱包对象
        $usdt_users_wallet=UsersWallet::where("currency","=",$usdt_id)->where("user_id","=",$user_id)->first();
//        var_dump($usdt_users_wallet);;die;



        $data_wallet1 = [
            'balance_type' => 3,//余额类型:1.法币,2.币币,3.杆杠
            'wallet_id' => $usdt_users_wallet->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $usdt_users_wallet->lever_balance,
            'change' => $number,
            'after' => bc_sub($usdt_users_wallet->lever_balance, $number, 5),
        ];
        $data_wallet2 = [
            'balance_type' => 1,//余额类型:1.法币,2.币币,3.杆杠
            'wallet_id' => $usdt_users_wallet->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $usdt_users_wallet->legal_balance,
            'change' => $number,
            'after' => bc_add($usdt_users_wallet->legal_balance, $number, 5),
        ];
//        $usdt_users_wallet->lever_balance = $usdt_users_wallet->lever_balance - $number;

        DB::beginTransaction();
        try {
            $usdt_users_wallet->legal_balance = $usdt_users_wallet->legal_balance + $number;
            //解冻冻结余额
//                var_dump($number);die;
            $usdt_users_wallet->lock_lever_balance = $usdt_users_wallet->lock_lever_balance - $number;
            $usdt_users_wallet->save();

            //更改审核状态为通过
            $status_res=new Levertolegal();
            $status11=$status_res->find($id);
            $status11->status=2;//1:未审核   2：审核通过  3:审核不通过
            $status11->save();

            AccountLog::insertLog([
                'user_id' => $user_id,
                'value' => -$number,
                'currency' => $usdt_id,
                'info' => AccountLog::getTypeInfo(AccountLog::WALLET_LEVEL_LEGAL_OUT),//杠杆转法币审核通过,杠杆减少
                'type' => AccountLog::WALLET_LEVEL_LEGAL_OUT,
            ], $data_wallet1);
            AccountLog::insertLog([
                'user_id' => $user_id,
                'value' => $number,
                'currency' => $usdt_id,
                'info' => AccountLog::getTypeInfo(AccountLog::WALLET_LEVEL_LEGAL_IN),//杠杆转法币审核通过，法币增加
                'type' => AccountLog::WALLET_LEVEL_LEGAL_IN,
            ], $data_wallet2);
            DB::commit();
            return $this->success('划转成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }



    }
//审核不通过
    public function postAddno(Request $request)
    {
        $id = $request->post('id',null);
        $user_id = $request->post('user_id',null);
        $number= $request->post('number',null);

        //查询出usdt币的id
        $usdt_id=Currency::where("name","=","USDT")->first()->id;


        //查询出对应的钱包对象
        $usdt_users_wallet=UsersWallet::where("currency","=",$usdt_id)->where("user_id","=",$user_id)->first();

        try {
            $usdt_users_wallet->lever_balance = $usdt_users_wallet->lever_balance + $number;
            //审核不通过解冻冻结余额
            $usdt_users_wallet->lock_lever_balance = $usdt_users_wallet->lock_lever_balance - $number;
            $usdt_users_wallet->save();

            //更改审核状态为通过
            $status_res=new Levertolegal();
            $status11=$status_res->find($id);
            $status11->status=3;//1:未审核   2：审核通过  3:审核不通过
            $status11->save();

            $usdt_users_wallet->save();
            AccountLog::newinsertLog([
                'user_id' => $user_id,
                'value' => $number,
                'currency' => $usdt_id,
                'info' => AccountLog::getTypeInfo(AccountLog::WALLET_JIEDONGGANGGAN),
                'type' => AccountLog::WALLET_JIEDONGGANGGAN,
            ]);
            DB::commit();

            return $this->success('审核不通过!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function del()
    {
        $admin = LeverMultiple::find(Input::get('id'));
        if($admin == null) {
            abort(404);
        }
        $bool = $admin->delete();
        if($bool){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }

    public function edit(Request $request){

        $id = $request->get('id',0);
        if (empty($id)){
            return $this->error("参数错误");
        }

        $result = LeverMultiple::find($id);
        //
//        $res=UserCashInfo::where('user_id',$id)->first();

        return view('admin.levermultiple.edit',['result'=>$result]);
    }

    //编辑用户信息
    public function doedit(){
        $password = Input::get("value");
        $id = Input::get("id");
        if (empty($id)) return $this->error("参数错误");
        $user = LeverMultiple::find($id);
        $user->value=$password;
        if (empty($user)) return $this->error("数据未找到");
//        DB::beginTransaction();
        try {

            $aa=$user->save();
//            var_dump($aa);die;
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }



    }
 

    /**
     * 交易对显示
     *
     * @return void
     */
    public function match()
    {
        return view('admin.currency.match');
    }

    public function matchList(Request $request)
    {
        $legal_id = $request->route('legal_id');
        $limit = $request->input('limit', 10);
        $legal = Currency::find($legal_id);
        $matchs = $legal->quotation()->paginate($limit);
        return $this->layuiData($matchs);
    }

    public function addMatch($legal_id)
    {
        $is_legal = Currency::where('id', $legal_id)->value('is_legal');
        if (!$is_legal) {
            abort(403, '指定币种不是法币,不能添加交易对');
        }
        $currencies = Currency::where('id', '<>', $legal_id)->get();
        $market_from_names = CurrencyMatch::enumMarketFromNames();
        return view('admin.currency.match_add')->with('currencies', $currencies)
            ->with('market_from_names', $market_from_names);
    }

    public function postAddMatch(Request $request, $legal_id)
    {
        $is_legal = Currency::where('id', $legal_id)->value('is_legal');
        if (!$is_legal) {
            return $this->error('指定币种不是法币,不能添加交易对');
        }
        $currency_id = $request->input('currency_id');
        $is_display = $request->input('is_display', 1);
        $market_from = $request->input('market_from', 0);
        $open_transaction = $request->input('open_transaction', 0);
        $open_lever = $request->input('open_lever', 0);
        $lever_share_num = $request->input('lever_share_num', 1);
        $spread = $request->input('spread', 0);
        $overnight = $request->input('overnight', 0);
        $lever_trade_fee = $request->input('lever_trade_fee', 0);
        //检测交易对是否已存在
        $exist = CurrencyMatch::where('currency_id', $currency_id)
            ->where('legal_id', $legal_id)
            ->first();
        if ($exist) {
            return $this->error('对应交易对已存在');
        }
        CurrencyMatch::unguard();
        $currency_match = CurrencyMatch::create([
            'legal_id' => $legal_id,
            'currency_id' => $currency_id,
            'is_display' => $is_display,
            'market_from' => $market_from,
            'open_transaction' => $open_transaction,
            'open_lever' => $open_lever,
            'lever_share_num' => $lever_share_num,
            'lever_trade_fee' => $lever_trade_fee,
            'spread' => $spread,
            'overnight' => $overnight,
            'create_time' => time(),
        ]);
        CurrencyMatch::reguard();
        return isset($currency_match->id) ? $this->success('添加成功') : $this->error('添加失败');
    }

    public function editMatch($id)
    {
        $currency_match = CurrencyMatch::find($id);
        if (!$currency_match) {
            abort(403, '指定交易对不存在');
        }
        $market_from_names = CurrencyMatch::enumMarketFromNames();
        $currencies = Currency::where('id', '<>', $currency_match->legal_id)->get();
        $var = compact('currency_match', 'currencies', 'market_from_names');
        return view('admin.currency.match_add', $var);
    }

    public function postEditMatch(Request $request, $id)
    {
        $currency_id = $request->input('currency_id');
        $is_display = $request->input('is_display', 1);
        $market_from = $request->input('market_from', 0);
        $open_transaction = $request->input('open_transaction', 0);
        $open_lever = $request->input('open_lever', 0);
        $lever_share_num = $request->input('lever_share_num', 1);
        $spread = $request->input('spread', 0);
        $overnight = $request->input('overnight', 0);
        $lever_trade_fee = $request->input('lever_trade_fee', 0);
        $currency_match = CurrencyMatch::find($id);
        if (!$currency_match) {
            abort(403, '指定交易对不存在');
        }
        CurrencyMatch::unguard();
        $result = $currency_match->fill([
            'currency_id' => $currency_id,
            'is_display' => $is_display,
            'market_from' => $market_from,
            'open_transaction' => $open_transaction,
            'open_lever' => $open_lever,
            'lever_share_num' => $lever_share_num,
            'lever_trade_fee' => $lever_trade_fee,
            'spread' => $spread,
            'overnight' => $overnight,
            'create_time' => time(),
        ])->save();
        CurrencyMatch::reguard();
        return $result ? $this->success('保存成功') : $this->error('保存失败');
    }

    public function delMatch($id)
    {
        $result = CurrencyMatch::destroy($id);
        return $result ? $this->success('删除成功') : $this->error('删除失败');
    }
}
