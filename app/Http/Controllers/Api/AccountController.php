<?php

namespace App\Http\Controllers\Api;

use App\AccountLog;
use App\LegalOrder;
use App\Transaction;
use App\Users;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends Controller
{

    public function list()
    {
        $address = Users::getUserId(Input::get('address', ''));
        $limit = Input::get('limit', '12');
        $page = Input::get('page', '1');
        if (empty($address)) return $this->error("参数错误");

        $user = Users::where("id", $address)->first();
        if (empty($user)) return $this->error("数据未找到");


        $data = AccountLog::where("user_id", $user->id)->orderBy('id', 'DESC')->paginate($limit);

        $rlist = $data->items();
        $lang = $this->language;
        $key = $lang === 'zh' ? 'info' : 'info_' . $lang;
        array_walk($rlist, function (&$entry) use ($lang, $key) {

            $info = $entry->$key;
            $entry->info = $info ?: $entry->info;
//           sleep(1);
        });

        return $this->success(array(
            "user_id" => $user->id,
            "data" => $rlist,
            "limit" => $limit,
            "page" => $page,
        ));
    }

    public function show_profits(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->input('limit', 10);
        $prize_pool = AccountLog::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number');
            if ($account_number) {
                $query->where('account_number', $account_number);
            }
            //            $scene = $request->input('scene', -1);
            $start_time = strtotime($request->input('start_time', null));
            $end_time = strtotime($request->input('end_time', null));
            //            $scene != -1 && $query->where('scene', $scene);
            $start_time && $query->where('created_time', '>=', $start_time);
            $end_time && $query->where('created_time', '<=', $end_time);
        })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->where("user_id", "=", $user_id)->orderBy('id', 'desc')->paginate($limit);

        return $this->success($prize_pool);
    }


    public function chargeMentionMoney(Request $request)
    {
        $limit = $request->get('limit', 5);
        $user_id = Users::getUserId();
        $arr = [AccountLog::ETH_EXCHANGE, AccountLog::WALLETOUT, AccountLog::WALLET_CURRENCY_IN, AccountLog::WALLETOUTDONE, AccountLog::WALLETOUTBACK];
        $currency = $request->get('currency', -1);
        $list = AccountLog::where(function ($query) use ($currency) {
            $currency != -1 && $query->where('currency', $currency);
        })->whereIn('type', $arr)
            ->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        $rlist = $list->toArray();
//        var_dump(count($list->toArray()));

//        $rlist = $list->toArray();

//        var_dump($rlist);

        $key = $request->header('lang') === 'zh' ? 'info' : 'info_' . $request->header('lang');
        array_walk($rlist['data'], function (&$entry) use ($key) {
//var_dump($entry);
//die;
            $arr = ['币币' => 'Change', '币币[锁定]' => 'Change[locked]','法币' => 'Legal', '法币[锁定]' => 'Legal[locked]'];
            $info = $entry[$key];
//            var_dump($info);
            $entry['info'] = $info ?: $entry['info'];
            $entry['transaction_info'] = $arr[$entry['transaction_info']] ?: $entry['transaction_info'];
        });

        return $this->success($rlist);
    }

    public function LegalBuy(Request $request)
    {
        $limit = $request->get('limit', 5);
        $user_id = Users::getUserId();
        $arr = [AccountLog::ETH_EXCHANGE, AccountLog::WALLETOUT, AccountLog::WALLET_CURRENCY_IN, AccountLog::WALLETOUTDONE, AccountLog::WALLETOUTBACK];
        $currency = $request->get('currency', -1);
        $list = LegalOrder::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        $rlist = $list->toArray();

        return $this->success($rlist);
    }

    public function LegalBuyInfo(Request $request)
    {
        $id = Input::get('id');
        $user_id = Users::getUserId();
        $record = LegalOrder::where(['id' => $id, 'user_id' => $user_id])->first();
        if ($record) {
            return $this->success($record);
        }else{
            return $this->error('订单不存在');
        }
    }

    public function CancelBuyInfo(Request $request)
    {
        $id = Input::post('id');
        $user_id = Users::getUserId();
        $record = LegalOrder::where(['id' => $id, 'user_id' => $user_id])->first();
        if($record->status==0)
        {
            $record->status=-1;
            $record->save();
            return $this->success('订单状态已更新');
        }
    }

    public function PayBuyInfo(Request $request)
    {
        $id = Input::post('id');
        $user_id = Users::getUserId();
        $record = LegalOrder::where(['id' => $id, 'user_id' => $user_id])->first();
        if($record->status==0)
        {
            $record->status=1;
            $record->url = Input::post('pic');
            $record->pay_time = date('Y-m-d H:i:s');
            $record->save();
            return $this->success('订单状态已更新');
        }else{
            return $this->error('订单状态异常');
        }
    }
}
