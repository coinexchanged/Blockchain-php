<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\DAO\BlockChain;
use Maatwebsite\Excel\Facades\Excel;
use App\Utils\RPC;
use App\{Address,
    AccountLog,
    Currency,
    CurrencyMatch,
    IdCardIdentit,
    LegalStore,
    MarketHour,
    MyQuotation,
    Setting,
    Users,
    UserCashInfo,
    UserReal,
    UsersWallet,
    Wallet};

class UserController extends Controller
{
    public function index()
    {
        $stores = LegalStore::all();
        return view("admin.user.index",[
            'stores'=>$stores
        ]);
    }

    //导出用户列表至excel
    public function csv(Request $request)
    {
        // $limit = $request->get('limit', 10);
        $account = $request->get('account', '');

        $list = new Users();
        $list = $list->leftjoin("user_real", "users.id", "=", "user_real.user_id");
        //var_dump($n);die;
        if (!empty($account)) {
            $list = $list->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        }
        $list = $list->select("users.*", "user_real.card_id")->orderBy('users.id', 'desc')->get();
        $data = $list;

        return Excel::create('用户数据', function ($excel) use ($data) {
            $excel->sheet('用户数据', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('账户名');
                });
                /*
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('团队充值业绩');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('直推实名人数');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('团队实名人数');
                });
                */
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('邀请码');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('用户状态');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('头像');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('注册时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['top_upnumber']);
                        $sheet->cell('D' . $i, $value['zhitui_real_number']);
                        $sheet->cell('E' . $i, $value['real_teamnumber']);
                        $sheet->cell('F' . $i, $value['extension_code']);
                        $sheet->cell('G' . $i, $value['status']);
                        $sheet->cell('H' . $i, $value['head_portrait']);
                        $sheet->cell('I' . $i, $value['time']);
                    }
                }
            });
        })->download('xlsx');
    }

    //用户列表
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $name = $request->get('name', '');
        $risk = $request->get('risk', -2);

        $list = new Users();
        $list = $list->leftjoin("user_real", "users.id", "=", "user_real.user_id");

        if (!empty($account)) {
            $list = $list->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        }

        $list = $list->when($name != '', function ($query) use ($name) {
            $query->whereHas('userReal', function ($query) use ($name) {
                $query->where('name', $name);
            });
        });

        if ($risk != -2) {
            $list = $list->where('risk', $risk);
        }

        $list = $list->select("users.*", "user_real.card_id")
            ->orderBy('users.id', 'desc')
            ->paginate($limit);

        $items = $list->getCollection();
        $items->transform(function ($item, $key) {
            return $item->append('risk_name');
        });
        $list->setCollection($items);

        $USDT_id = Currency::where('name', 'USDT')->first()->id;
//        var_dump($list);

        $ulist = $list->items();
        $total = $list->total();
        array_walk($ulist, function (&$item) {

            $puser = \app\Users::find($item['parent_id']);
//            if($item['id']=='125')
//            {
//                var_dump($item->toArray());
//                var_dump($puser->toArray());
//            }
            $item['pname'] = $puser ? $puser->account_number : '根用户';
        });

        return response()->json(['code' => 0, 'data' => $ulist, 'count' => $total]);
    }

    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = new Users();
        $result = $result->leftjoin("user_real", "users.id", "=", "user_real.user_id")->select("users.*", "user_real.card_id")->find($id);
        //var_dump($result->toArray());die;
        $res = UserCashInfo::where('user_id', $id)->first();

        return view('admin.user.edit', ['result' => $result, 'res' => $res]);
    }

    //编辑用户信息
    public function doedit()
    {
        $phone = Input::get("phone");
        $email = Input::get("email");
        $account_number = Input::get("account_number");
        $card_id = Input::get("card_id");
        $password = Input::get("password");
        $withdraw_password = Input::get("withdraw_password");
        $account_number = Input::get("account_number");
        $pay_password = Input::get("pay_password");
        $bank_account = Input::get("bank_account");
        $bank_name = Input::get("bank_name");
        $alipay_account = Input::get("alipay_account");
        $wechat_nickname = Input::get("wechat_nickname");
        $wechat_account = Input::get("wechat_account");
        $is_service = Input::get("is_service", 0) ?? 0;
        $risk = Input::get('risk', 0);

        $id = Input::get("id");
        if (empty($id)) return $this->error("参数错误");

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error("数据未找到");
        }

        $user->account_number = $account_number;
        $user->email = $email;
        $user->phone = $phone;
        $user->withdraw_password = $withdraw_password;
        
        if (!empty($password)) {
            $user->password = Users::MakePassword($password);
        }
        if (!empty($pay_password)) {
            $user->pay_password = $pay_password;
        }
        if (!empty($is_service)) {
            $has_service = Users::where('is_service', 1)->first();
            if ($has_service) {
                return $this->error("只允许设置一个客服,当前客服账号:{$has_service->account_number}");
            }
            $user->is_service = $is_service;
        }
        $user->risk = $risk;
        DB::beginTransaction();

        try {
            $user->save();
            $cashinfo = UserCashInfo::where('user_id', $id)->first();
            if (empty($cashinfo)) {
                $cashinfo = new UserCashInfo();
                $cashinfo->user_id = $id;
            }

            $cashinfo->bank_name = $bank_name ?? '';
            $cashinfo->bank_account = $bank_account ?? '';
            $cashinfo->alipay_account = $alipay_account ?? '';
            $cashinfo->wechat_account = $wechat_account ?? '';
            $cashinfo->wechat_nickname = $wechat_nickname ?? '';
            $cashinfo->save();
            //更改身份证号
            if (!empty($card_id)) {
                $real = UserReal::where("user_id", "=", $id)->first();
                $real->card_id = $card_id;
                $real->save();
            }
            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function lockUser(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        $result = Users::find($id);
        //
        // $res=UserCashInfo::where('user_id',$id)->first();
        return view('admin.user.lock', ['result' => $result]);
    }

    public function doLock(Request $request)
    {
        $id = $request->get('id', 0);
        $date = $request->get('date', 0);
        $status = $request->get('status', 0);

        if (empty($id)) {
            return $this->error('参数错误');
        }
        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if (empty($date)) {
            return $this->error('缺少时间！');
        }
        $users = new Users();
        $result = $users->lockUser($user, $status, $date);
        if (!$result) {
            return $this->error('锁定失败');
        }
        return $this->success('操作成功');
    }

    public function del(Request $request)
    {
        return $this->error('禁止删除用户,将会造成系统崩溃');
        $id = $request->get('id');
        $user = Users::getById($id);
        if (empty($user)) {
            $this->error("用户未找到");
        }
        try {
            $user->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function lock(Request $request)
    {
        $id = $request->get('id', 0);

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->status == 1) {
            $user->status = 0;
        } else {
            $user->status = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function wallet(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        return view("admin.user.user_wallet", ['user_id' => $id]);
    }

    public function walletList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = $request->get('user_id', null);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $list = new UsersWallet();
        $list = $list->where('user_id', $user_id)->orderBy('id', 'desc')->paginate($limit);

        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

//钱包锁定状态
    public function walletLock(Request $request)
    {
        $id = $request->get('id', 0);

        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('参数错误');
        }
        if ($wallet->status == 1) {
            $wallet->status = 0;
        } else {
            $wallet->status = 1;
        }
        try {
            $wallet->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /*
     * 调节账户
     * */
    public function conf(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }
        $account = Users::where('id', $result->user_id)->value('phone');
        if (empty($account)) {
            $account = Users::where('id', $result->user_id)->value('email');
        }
        $result['account'] = $account;
        return view('admin.user.conf', ['results' => $result]);
    }

    //调节账号  type  1法币交易余额  2法币交易锁定余额 3币币交易余额 4币币交易锁定余额  5杠杆交易余额 6杠杆交易锁定余额
    public function postConf(Request $request)
    {
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($request->all(), [
            'way' => 'required',   //增加 increment；减少 decrement
            'type' => 'required',       //原生余额1；消费余额2；增值余额3；可增加其他账户调节字段
            'conf_value' => 'required',       //值
        ], $message);

        //以上验证通过后 继续验证
        $validator->after(function ($validator) use ($request) {

            $wallet = UsersWallet::find($request->get('id'));
            if (empty($wallet)) {
                return $validator->errors()->add('isUser', '没有此钱包');
            }
            $user = Users::getById($wallet->user_id);
            if (empty($user)) {
                return $validator->errors()->add('isUser', '没有此用户');
            }
            $way = $request->get('way', 'increment');
            $type = $request->get('type', 1);
            $conf_value = $request->get('conf_value', 0);
            if ($type == 1 && $way == 'decrement') {
                if ($wallet->legal_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包法币交易余额不足' . $conf_value . '元');
                }
            } elseif ($type == 2 && $way == 'decrement') {
                if ($wallet->lock_legal_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包法币交易锁定余额不足' . $conf_value . '元');
                }
            } elseif ($type == 3 && $way == 'decrement') {
                if ($wallet->change_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包币币交易余额不足' . $conf_value . '元');
                }
            } elseif ($type == 4 && $way == 'decrement') {
                if ($wallet->lock_change_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包币币交易锁定余额不足' . $conf_value . '元');
                }
            } elseif ($type == 5 && $way == 'decrement') {
                if ($wallet->lever_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包闪兑交易余额不足' . $conf_value . '元');
                }
            } elseif ($type == 6 && $way == 'decrement') {
                if ($wallet->lock_lever_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包闪兑交易锁定余额不足' . $conf_value . '元');
                }
            } elseif ($type == 7 && $way == 'decrement') {
                if ($wallet->micro_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包秒合约余额不足' . $conf_value . '元');
                }
            } elseif ($type == 8 && $way == 'decrement') {
                if ($wallet->lock_micro_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包秒合约锁定余额不足' . $conf_value . '元');
                }
            }


        });
        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $id = $request->get('id', null);
        $way = $request->get('way', 'increment');
        $type = $request->get('type', 1);
        $conf_value = $request->get('conf_value', 0);
        $info = $request->get('info', ':');
        $wallet = UsersWallet::find($id);
        $user = Users::getById($wallet->user_id);


        $data_wallet['wallet_id'] = $id;
        $data_wallet['create_time'] = time();
        DB::beginTransaction();
        try {
            if ($type == 1) {
                $data_wallet['balance_type'] = 1;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->legal_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->legal_balance, $conf_value, 5);
                    $wallet->increment('legal_balance', $conf_value);
                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);
                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->legal_balance, $conf_value, 5);
                    $wallet->decrement('legal_balance', $conf_value);
                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);
                }
            } elseif ($type == 2) {
                $data_wallet['balance_type'] = 1;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_legal_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_legal_balance, $conf_value, 5);
                    $wallet->increment('lock_legal_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_legal_balance, $conf_value, 5);
                    $wallet->decrement('lock_legal_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 3) {
                $data_wallet['balance_type'] = 2;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->change_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->change_balance, $conf_value, 5);
                    $wallet->increment('change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->change_balance, $conf_value, 5);
                    $wallet->decrement('change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 4) {
                $data_wallet['balance_type'] = 2;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_change_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_change_balance, $conf_value, 5);
                    $wallet->increment('lock_change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_change_balance, $conf_value, 5);
                    $wallet->decrement('lock_change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 5) {
                $data_wallet['balance_type'] = 3;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->lever_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lever_balance, $conf_value, 5);
                    $wallet->increment('lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lever_balance, $conf_value, 5);
                    $wallet->decrement('lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 6) {
                $data_wallet['balance_type'] = 3;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_lever_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_lever_balance, $conf_value, 5);
                    $wallet->increment('lock_lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_lever_balance, $conf_value, 5);
                    $wallet->decrement('lock_lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 7) {
                $data_wallet['balance_type'] = 4;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->micro_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->micro_balance, $conf_value, 5);
                    $wallet->increment('micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->micro_balance, $conf_value, 5);
                    $wallet->decrement('micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 8) {
                $data_wallet['balance_type'] = 4;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_micro_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_micro_balance, $conf_value, 5);
                    $wallet->increment('lock_micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_micro_balance, $conf_value, 5);
                    $wallet->decrement('lock_micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            }
            //$wallet->save();
            //$user->save();
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    //删除钱包
    public function delw(Request $request)
    {
        $id = $request->get('id');
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            $this->error("钱包未找到");
        }
        try {
            $wallet->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    /*
     * 提币地址信息
     * */
    public function address(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }


        $list = Address::where('user_id', $result->user_id)->where('currency', $result->currency)->get()->toArray();

        return view('admin.user.address', ['results' => $result, 'list' => $list]);
    }

    /*
   * 修改提币地址信息
   * */
    public function addressEdit(Request $request)
    {
        $user_id = $request->get('user_id', 0);
        $currency = $request->get('currency', 0);
        $total_arr = $request->get('total_arr', '');
        if (empty($user_id) || empty($currency)) {
            return $this->error('参数错误');
        }
        DB::beginTransaction();
        try {
            Address::where('user_id', $user_id)->where('currency', $currency)->delete();
            if (!empty($total_arr)) {
                foreach ($total_arr as $key => $val) {
                    $ads = new Address();
                    $ads->user_id = $user_id;
                    $ads->currency = $currency;
                    $ads->address = $val['address'];
                    $ads->notes = $val['notes'];
                    $ads->save();
                }
            }
            DB::commit();
            return $this->success('修改提币地址成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }

    }

    //加入黑名单
    public function blacklist(Request $request)
    {
        $id = $request->get('id', 0);

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->is_blacklist == 1) {
            $user->is_blacklist = 0;
        } else {
            $user->is_blacklist = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function candyConf(Request $request, $id)
    {
        $user = Users::find($id);
        return view('admin.user.candy_conf')->with('user', $user);
    }

    public function postCandyConf(Request $request, $id)
    {
        $user = Users::find($id);
        $way = $request->input('way', 0);
        $change = $request->input('change', 0);
        $memo = $request->input('memo', '');
        if (!in_array($way, [1, 2])) {
            return $this->error('调整方式传参错误');
        }
        if ($change <= 0) {
            return $this->error('调整金额必须大于0');
        }
        if ($way == 2) {
            $change = bc_mul($change, -1);
        }
        $result = change_user_candy($user, $change, AccountLog::ADMIN_CANDY_BALANCE, '后台调整' . ($way == 2 ? '减少' : '增加') . '通证 ' . $memo);
        return $result === true ? $this->success('调整成功') : $this->error('调整失败:' . $result);
    }

    //链上余额归集到总账号
    public function balance(Request $request)
    {
        exit('功能被禁用');
        set_time_limit(0);
        $id = $request->get('id', 0);//钱包id
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('钱包不存在');
        }
        $btc_chain_balance = 0;
        $currency = Currency::find($wallet->currency);
        $total_account = $currency->total_account;
        $user_address = $wallet->address;
        $number = $wallet->old_balance;
        $lessen = bc_pow(10, 8);
        //$origin_number = $number = $wallet->old_balance;
        $btc_key = decrypt($wallet->private);
        $btc_id = Currency::where('name', 'BTC')->first()->id;
        $fee = bc_mul($currency->chain_fee, bc_pow(10, $currency->decimal_scale ?? 8));
        if ($id != $btc_id) {
            $btc_wallet = UsersWallet::where('address', $user_address)
                ->where('currency', $btc_id)
                ->first();
            $usdt_balance_url = 'http://47.92.148.83:82/wallet/usdt/balance?address=' . $user_address;
            $content = RPC::apihttp($usdt_balance_url);
            $content = json_decode($content, true);
            if (isset($content['code']) && $content['code'] == 0 && isset($content['data'])) {
                $origin_number = $content['data']['balance'];
            } else {
                return $this->error('获取USDT链上余额失败:' . var_export($content, true));
            }
        } else {
            $btc_wallet = $wallet;
        }
        $btc_balance_url = 'http://47.92.148.83:82/wallet/btc/balance?address=' . $user_address;
        $btc_content = RPC::apihttp($btc_balance_url);
        $btc_content = json_decode($btc_content, true);
        if (isset($btc_content["code"]) && $btc_content["code"] == 0) {
            $btc_chain_balance = $btc_content['data']['balance'];
            if ($id == $btc_id) {
                $origin_number = bc_sub($btc_chain_balance, $fee);
            }
            $btc_chain_balance = bc_div($btc_chain_balance, $lessen, 8);
        } else {
            return $this->error('获BTC取链上余额失败');
        }

        if (bc_comp($btc_chain_balance, $currency->chain_fee) < 0) {
            return $this->error('当前账户BTC余额不足,请充值之后再归拢');
        }

        $origin_number = bc_div($origin_number, $lessen);

        $old_balance = 0;
        if (empty($total_account)) {
            return $this->error('usdt币种设置错误');
        }
        if ($currency->type == 'usdt') {
//            var_dump($currency->type);var_dump($total_account);var_dump($origin_number);var_dump($user_address);var_dump($btc_key);var_dump($fee);die;
            $content = BlockChain::transfer($currency->type, $currency->type, $total_account, $origin_number, $user_address, $btc_key, 1, $fee);
        } elseif ($currency->type == 'btc') {
            $content = BlockChain::transfer($currency->type, $currency->type, $total_account, $origin_number, $user_address, $btc_key, 1, $fee);
        } else {
            return $this->error('只支持usdt、btc归拢');
        }
        //记录错误日志
        Log::useDailyFiles(base_path('storage/logs/blockchain/collect'), 7);
        Log::critical('用户id:' . $wallet->user_id . ',币种:' . $currency->type . ',归拢:' . $origin_number, $content);
        if (isset($content["errcode"]) && $content["errcode"] == "0" && isset($content['txid'])) {
            try {
                DB::beginTransaction();
                $wallet = UsersWallet::lockForUpdate()->find($id);
                //如果转的是usdt，需要扣btc
                $btc_wallet->refresh();
                if ($currency->type == 'usdt') {
                    //扣手续费不用更新old_balance，这样直接扣除可避免原打入的手续费被当作充值到账
                    $btc_wallet->old_balance = $btc_wallet->old_balance - $currency->chain_fee - 0.00000546;
                    $btc_wallet->save();
                }
                AccountLog::insertLog([
                    'user_id' => 0,
                    'value' => 0,
                    'info' => '对用户id' . $wallet->user_id . ',币种:' . $currency->type . ',归拢:' . $origin_number . ',交易哈希值:' . $content['txid'],
                    'type' => 881
                ]);
                $wallet->refresh();
                $wallet->old_balance = $old_balance;;
                $wallet->gl_time = time();
                $wallet->txid = $content['txid'];
                $wallet->save();
                DB::commit();
                return $this->success('归拢成功，请在30分钟后刷新余额');
            } catch (\Exception $ex) {
                DB::rollback();
                return $this->error($ex->getMessage());
            }
        } else {
            return $this->error(var_export($content, true));
        }
    }


    //向账户充btc手续费0.00006
    public function sendBtc(Request $request)
    {
        exit('功能被禁用');
        set_time_limit(0);
        $id = $request->get('id', 0);//钱包id
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('钱包不存在');
        }
        $user_address = $wallet->address;
        $currency = Currency::find($wallet->currency);
        $btc_id = Currency::where('name', 'BTC')->first()->id;
        // return $currency->id.'+'.$btc_id;
        if ($currency->id != $btc_id) {
            return $this->error('只支持btc账户');
        }
        $total_account = $currency->total_account;
        $key = $currency->key;
        $account = bc_mul($currency->chain_fee, 100000000, 0);
        $url = "http://47.92.148.83:82/wallet/btc/sendto?fromaddress=" . $total_account . "&toaddress=" . $user_address . "&privkey=" . $key . "&amount=" . $account;
        $content = file_get_contents($url);
        $content = @json_decode($content, true);
        if ($content['code'] == 0) {
            AccountLog::insertLog([
                'user_id' => 0,
                'value' => $currency->chain_fee,
                'info' => '向' . $wallet->user_id . '打手续费',
                'type' => 8888888
            ]);
            $wallet->old_balance = $wallet->old_balance + $currency->chain_fee;
            $wallet->gl_time = time();
            $wallet->save();
            return $this->success('转入手续费成功');
        } else {
            return $this->error('转入错误' . $content['msg']);
        }
    }

    public function batchRisk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $risk = $request->input('risk', 0);
            if (empty($ids)) {
                throw new \Exception('请先选择用户');
            }
            if (!in_array($risk, [-1, 0, 1])) {
                throw new \Exception('风控类型不正确');
            }
            $affect_rows = Users::whereIn('id', $ids)
                ->update([
                    'risk' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function chargeList(Request $request)
    {
        $limit = $request->get('limit', 20);
        $list = DB::table('charge_req')
            ->join('users', 'users.id', '=', 'charge_req.uid')
            ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            ->select('charge_req.*', 'users.account_number', 'currency.name')
            ->orderBy('charge_req.id', 'desc')->paginate($limit);
        // $userWalletOut = new UsersWalletOut();
        // $userWalletOutList = $userWalletOut->orderBy('id', 'desc')->paginate($limit);
        // var_dump($list);exit;

        return $this->layuiData($list);
    }

    public function quotation(Request $request)
    {


        $last = MyQuotation::orderBy('id', 'desc')->first();
        $currencys = CurrencyMatch::where('market_from', 0)->get();
        return view('admin.quotation.index', ['data' => $last, 'currencys' => $currencys->toArray()]);
    }

    public function saveQuotation(Request $request)
    {

        $con = $request->post('kline');
        $date = $request->post('date');
        $currency = strtoupper($request->post('currency'));
        $obj = json_decode($con, true);
        $timestamp = strtotime($date);
        $esclient = MarketHour::getEsearchClient();
        for ($i = 0; $i < count($obj); $i++) {
            $timeed = strtotime('+ ' . ($i * 60) . ' seconds', $timestamp);
            $timefor = date('Y-m-d H:i:s', $timeed);
//            $needle = MyQuotation::where('itime', $timeed)->where('base', Setting::getValueByKey("platform_currenys"))->first();
            $needle = new MyQuotation();
            $needle->open = $obj[$i][1];
            $needle->high = $obj[$i][2];
            $needle->low = $obj[$i][3];
            $needle->close = $obj[$i][4];
            $needle->vol = $obj[$i][5];
            $needle->base = $currency;
            $needle->target = 'USDT';
            $needle->symbol = "{$needle->base}/{$needle->target}";
            $needle->itime = $timeed;

            $id = strtolower($needle->base . $needle->target) . '.1min.' . ($timeed * 1000);
            $ob = json_decode(json_encode($needle),true);
            $ob['id'] = $id;
            $ob['itime'] = $timeed;


            $params = [
                'index' => 'market.quotation',
                'type' => 'doc',
                'id' => $id,
                'body' => json_decode(json_encode($ob)),
            ];
            $delete_param = [
                'index' => 'market.quotation',
                'type' => 'doc',
                'id' => $id,
            ];
            try {
                $esclient->get($delete_param) && $esclient->delete($delete_param);
            } catch (\Exception $exception)
            {

            }
            $response = $esclient->index($params);
        }

        return $response;
    }

    public function passReq(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $req = Db::table('charge_req')->where(['id' => $id, 'status' => 1])->first();
        if (!$req) {
            return $this->error('充值记录错误');
        }

        DB::beginTransaction();
        DB::table('charge_req')->where('id', $id)->update(['status' => 2, 'updated_at' => date('Y-m-d H:i:s')]);


        $wallet = UsersWallet::where('user_id', $req->uid)->where('currency', $req->currency_id)->lockForUpdate()->first();

        $res = change_wallet_balance($wallet, 2, $req->amount, AccountLog::WALLET_CURRENCY_IN, "后台审核充值通过");
        if ($res) {
            DB::commit();
            return $this->success('充值成功');


        } else {
            DB::rollBack();
            return $this->error('变更余额失败');
        }
//        DB::table('users_wallet')->where(['currency' => $req->currency_id, 'user_id' => $req->uid])->increment('change_balance', $req->amount);
//
//        Db::table('account_log')->insert(['user_id' => $req->uid,
//            'value' => $req->amount,
//            'created_time' => time(),
//            'type' => AccountLog::ADMIN_CHANGE_BALANCE,
//            'currency' => 3,
//            'info' => "审核通过，充值成功"
//        ]);
//        return $this->success('充值成功');
    }

    public function refuseReq(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $req = Db::table('charge_req')->where(['id' => $id, 'status' => 1])->first();
        if (!$req) {
            return $this->error('充值记录错误');
        }

        DB::table('charge_req')->where('id', $id)->update(['status' => 3, 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->success('充值申请已驳回');
    }

    public function chargeReq(Request $request)
    {

        return view('admin.user.charge');
    }

}
