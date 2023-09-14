<?php

namespace App\Http\Controllers\Admin;

use App\AccountLog;
use App\LegalStore;
use App\Robot;
use App\UsersWallet;
use Illuminate\Http\Request;
use App\LegalOrder as LegalOrderModel;
use Illuminate\Support\Facades\DB;

class LegalOrder extends Controller
{
    //
    public function index()
    {
        $news = self::needleList(0, 20);
        $count = count($news);
        $data = [
            'count' => $count,
            'news' => $news
        ];
        return view('admin.needle.index', [
            'data' => $data,
        ]);
    }

    public function confirm(Request $request)
    {
        $id = $request->post('id');
        $order = LegalOrderModel::where('id', $id)->first();
        if ($order) {
            if ($order->type == 'buy') {
                if (intval($order->status) !== 1) {
                    return json_encode(['code' => -1, 'msg' => '订单状态异常']);
                }
                $order->status = 2;
                $order->save();

                DB::beginTransaction();
                $wallet = UsersWallet::where('user_id', $order->user_id)->where('currency', 3)->lockForUpdate()->first();
                $res = change_wallet_balance($wallet, 1, $order->usdt_amount, AccountLog::WALLET_CHANGE_IN, "购入法币");
                DB::commit();
                return json_encode(['code' => 1, 'msg' => '更新完成']);
            } else {
                if (intval($order->status) !== 0) {
                    return json_encode(['code' => -1, 'msg' => '订单状态异常']);
                }
                $order->pay_time==time();
                $order->status = 2;
                $order->save();
                DB::beginTransaction();
                $wallet = UsersWallet::where('user_id', $order->user_id)->where('currency', 3)->lockForUpdate()->first();
                $res = change_wallet_balance($wallet, 1, -$order->usdt_amount, AccountLog::WALLET_CHANGE_IN, "卖出法币",true);
                DB::commit();
                return json_encode(['code' => 1, 'msg' => '更新完成']);
            }
        } else {
            return json_encode(['code' => -1, 'msg' => '订单不存在']);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');
        $order = LegalOrderModel::where('id', $id)->first();
        if ($order) {
            $order->delete();
            return json_encode(['code' => 1, 'msg' => '更新完成']);
        } else {
            return json_encode(['code' => -1, 'msg' => '订单不存在']);
        }
    }

    public function cancel(Request $request)
    {
        $id = $request->post('id');
        $order = LegalOrderModel::where('id', $id)->first();
        if ($order) {
            DB::beginTransaction();
            if (intval($order->status) !== 0) {
                return json_encode(['code' => -1, 'msg' => '订单状态异常']);
            }
            $order->status = -1;
            $order->save();
            if($order->type=='sell')
            {
                $wallet = UsersWallet::where('user_id', $order->user_id)->where('currency', 3)->lockForUpdate()->first();
                $res = change_wallet_balance($wallet, 1, -$order->usdt_amount, AccountLog::WALLET_CHANGE_IN, "订单被关闭，退回锁定法币",true);
                $res = change_wallet_balance($wallet, 1, $order->usdt_amount, AccountLog::WALLET_CHANGE_IN, "法币交易被取消，退回余额");

            }
            DB::commit();
            return json_encode(['code' => 1, 'msg' => '更新完成']);
        } else {
            return json_encode(['code' => -1, 'msg' => '订单不存在']);
        }
    }

    public function store()
    {
        return view('admin.legalstore.list');
    }

    public function storeList()
    {
        $limit = request()->input('limit', 10);
        $list = LegalStore::paginate($limit);
        return $this->layuiData($list);
    }

    public function storeAdd()
    {
        if (request()->isMethod('GET')) {

            $id = request()->input('id', 0);
            if (empty($id)) {
                $result = new LegalStore();
            } else {
                $result = LegalStore::find($id);
            }
            return view('admin.legalstore.add')->with(['result' => $result]);
        }

        if (request()->isMethod('POST')) {

            $data = request()->post();
            $id = request()->input('id', 0);


            if ($id) {
                $robot = LegalStore::find($id);
            } else {
                $robot = new LegalStore();
            }
            DB::beginTransaction();

            try {

                $robot->name = $data['name'];
                $robot->bank_name = $data['bank_name'];
                $robot->bank_user = $data['bank_user'];
                $robot->bank_account = $data['bank_account'];
                $robot->bank_subname = $data['bank_subname'];

                $robot->rate = $data['rate'];
                $robot->rate_sell=$data['rate_sell'];
                $robot->min_num = $data['min_num'];
                $robot->max_num = $data['max_num'];
                $robot->min_num_wid = $data['min_num_wid'];
                $robot->max_num_wid = $data['max_num_wid'];
                $robot->alipay_account = $data['alipay_account'];
                $robot->wechat_account = $data['wechat_account'];
                $robot->alipay_qrcode = $data['alipay_qrcode'];
                $robot->wechat_qrcode = $data['wechat_qrcode'];
                $info = $robot->save();
                if (!$info) throw new \Exception('保存失败');

                DB::commit();
                return $this->success('保存成功');
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error($e->getMessage());
            }
        }
    }

    public function order(Request $request)
    {

        if ($request->ajax()) {
            $limit = $request->get('limit', 20);
            $account_number = $request->input('account_number', '');
            $userWalletOut = new LegalOrderModel();
            $status = $request->input('status', '1');
            $userWalletOutList = $userWalletOut->whereHas('users', function ($query) use ($account_number) {
                if ($account_number != '') {
                    $query->where('phone', $account_number)
                        ->orWhere('account_number', $account_number)
                        ->orWhere('email', $account_number);
                }
            })->where(function ($query) use ($status) {
                $query->where('status', $status);

            })->where('type', $request->input('type'))->orderBy('id', 'desc')->paginate($limit);
            return $this->layuiData($userWalletOutList);
        }

        return view('admin.legalorder.index');
    }

    public static function orderList($cId = 0, $num = 0)
    {
        $news_query = LegalOrderModel::where(function ($query) use ($cId) {
            $cId > 0 && $query->where('id', $cId);
        })->orderBy('id', 'desc');
        $news = $num != 0 ? $news_query->paginate($num) : $news_query->get();
        return $news;
    }
}
