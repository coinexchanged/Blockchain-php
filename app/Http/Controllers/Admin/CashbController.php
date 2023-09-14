<?php

/**
 * 提币控制器
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\UsersWalletOut;
use App\UsersWallet;
use App\AccountLog;
use App\Currency;
use App\Setting;
use App\Users;
use App\Utils\RPC;
use App\DAO\BlockChain;

class CashbController extends Controller
{
    public function index()
    {
        return view('admin.cashb.index');
    }

    public function cashbList(Request $request)
    {
        $limit = $request->get('limit', 20);
        $account_number = $request->input('account_number', '');
        $userWalletOut = new UsersWalletOut();
        $userWalletOutList = $userWalletOut->whereHas('user', function ($query) use ($account_number) {
            if ($account_number != '') {
                $query->where('phone', $account_number)
                    ->orWhere('account_number', $account_number)
                    ->orWhere('email', $account_number);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($userWalletOutList);
    }

    public function show(Request $request)
    {
        $id = $request->get('id', '');
        if (!$id) {
            return $this->error('参数小错误');
        }
        $walletout = UsersWalletOut::find($id);
        $use_chain_api = Setting::getValueByKey('use_chain_api', 0);
        return view('admin.cashb.edit', ['wallet_out' => $walletout, 'use_chain_api' => $use_chain_api,]);

    }

    //test
    public function done(Request $request)
    {
        set_time_limit(0);
        $id = $request->get('id', '');
        $method = $request->get('method', '');
        $notes = $request->get('notes', '');
        $verificationcode = $request->input('verificationcode', '');
        $txid = $request->input('txid', '');
        if (!$id) {
            return $this->error('参数错误');
        }

        try {

            DB::beginTransaction();
            $wallet_out = UsersWalletOut::where('status', '<=', 1)->lockForUpdate()->findOrFail($id);
            $number = $wallet_out->number;
            $real_number = bc_mul($wallet_out->number, bc_sub(1, bc_div($wallet_out->rate, 100)));
            $user_id = $wallet_out->user_id;
            $currency = $wallet_out->currency;
            $currency_type = $wallet_out->currency_type;

            $currency_model = Currency::find($currency);
            $contract_address = $currency_model->contract_address;
            $total_account = $currency_model->total_account;


            $user_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency)->lockForUpdate()->first();

            if ($method == 'done') {
                $wallet_out->txid = $txid;
                $wallet_out->status = 2;//提币成功状态
                $wallet_out->notes = $notes;//反馈的信息
                $wallet_out->verificationcode = $verificationcode;
                $wallet_out->update_time = time();
                $wallet_out->save();
                $change_result = change_wallet_balance($user_wallet, 2, -$number, AccountLog::WALLETOUTDONE, '提币成功', true);//提币成功
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
            } else {
                $wallet_out->status = 3;//提币失败状态
                $wallet_out->notes = $notes;//反馈的信息
                $wallet_out->verificationcode = $verificationcode;
                $wallet_out->update_time = time();

                $wallet_out->save();
                $change_result = change_wallet_balance($user_wallet, 2, -$number, AccountLog::WALLETOUTBACK, 'failed,lock balance reduced', true);//提币失败,锁定余额减少
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
                $change_result = change_wallet_balance($user_wallet, 2, $number, AccountLog::WALLETOUTBACK, 'failed,lock balance withdrawn');
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
            }
            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    //导出用户列表至excel
    public function csv()
    {
        $data = USersWalletOut::all()->toArray();
        return Excel::create('提币记录', function ($excel) use ($data) {
            $excel->sheet('提币记录', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('账户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('虚拟币');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('提币数量');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('手续费');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('实际提币');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('提币地址');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('反馈信息');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('状态');
                });
                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('提币时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        $i = $key + 2;
                        if ($value['status'] == 1) {
                            $value['status'] = '申请提币';
                        } else if ($value['status'] == 2) {
                            $value['status'] = '提币成功';
                        } else {
                            $value['status'] = '提币失败';
                        }
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['currency_name']);
                        $sheet->cell('D' . $i, $value['number']);
                        $sheet->cell('E' . $i, $value['rate']);
                        $sheet->cell('F' . $i, $value['real_number']);
                        $sheet->cell('G' . $i, $value['address']);
                        $sheet->cell('H' . $i, $value['notes']);
                        $sheet->cell('I' . $i, $value['status']);
                        $sheet->cell('I' . $i, $value['create_time']);
                    }
                }
            });
        })->download('xlsx');
    }
}
