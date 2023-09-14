<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\{AccountLog, Agent, ChargeReq, Users, UsersWalletOut, Currency, LeverTransaction, UsersWallet, AgentMoneylog};

class CapitalController extends Controller
{

    //充币
    public function rechargeIndex()
    {
        //币币
        $legal_currencies = Currency::get();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.recharge", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    //充币申请
    public function rechargeApply()
    {
        //币币
        $legal_currencies = Currency::get();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.rechargeapply", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    //提币
    public function withdrawIndex()
    {
        //币币
        $legal_currencies = Currency::get();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.withdraw", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    public function rechargeList(Request $request)
    {
        $limit = $request->input('limit', 20);
        // $agent = Agent::getAgent();
        // $child_agents = Agent::getAllChildAgent($agent->id);
        // $agents = $child_agents->pluck('id')->all();
        // $child_users = Users::whereIn('agent_note_id', $agents)->get();
        $agent_id = Agent::getAgentId();
        $node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $lists = AccountLog::where('type', AccountLog::WALLET_CURRENCY_IN)
            //->whereIn('user_id', $child_users->pluck('id')->all())
            ->whereIn('user_id', $node_users)
            ->where(function ($query) use ($request) {

                $account_number = $request->input('account_number', '');
                $belong_agent = $request->input('belong_agent', '');
                $currency_id = $request->input('currency_id', -1);

                $query->when($account_number != '', function ($query) use ($account_number) {
                    $query->whereHas('user', function ($query) use ($account_number) {
                        $query->where('account_number', $account_number);
                    });
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('user', function ($query) use ($belong_agent) {
                        $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
                            $query->where('username', $belong_agent);
                        });
                    });
                })->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency', $currency_id);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $lists->getCollection();
//        var_dump($items->toArray());
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
//            var_dump($item->toArray());
//            die;
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            return $item;
        });
        $lists->setCollection($items);
        return $this->layuiData($lists);
    }

    public function applyList(Request $request)
    {
        $limit = $request->input('limit', 20);

        $agent_id = Agent::getAgentId();
        $node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $lists = ChargeReq::whereIn('uid', $node_users)
            ->where(function ($query) use ($request) {

                $account_number = $request->input('account_number', '');
                $belong_agent = $request->input('belong_agent', '');
                $currency_id = $request->input('currency_id', -1);
                $status = $request->input('status', 0);
                $query->when($account_number != '', function ($query) use ($account_number) {
                    $query->whereHas('user', function ($query) use ($account_number) {
                        $query->where('account_number', $account_number);
                    });
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('user', function ($query) use ($belong_agent) {
                        $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
                            $query->where('username', $belong_agent);
                        });
                    });
                })->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })->when($status > 0, function ($query) use ($status) {
                    $query->where('status', $status);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $lists->getCollection();
//        var_dump($items->toArray());
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            return $item;
        });
        $lists->setCollection($items);
        return $this->layuiData($lists);
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
        DB::table('charge_req')->where('id', $id)->update(['status' => 2, 'passman_uid' => Agent::getAgentId(), 'updated_at' => date('Y-m-d H:i:s')]);


        $wallet = UsersWallet::where('user_id', $req->uid)->where('currency', $req->currency_id)->lockForUpdate()->first();

        $res = change_wallet_balance($wallet, 2, $req->amount, AccountLog::WALLET_CURRENCY_IN, "代理审核充值通过");
        if ($res) {
            DB::commit();
            return $this->success('充值成功');


        } else {
            DB::rollBack();
            return $this->error('变更余额失败');
        }
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

        DB::table('charge_req')->where('id', $id)->update(['status' => 3, 'passman_uid' => Agent::getAgentId(), 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->success('充值申请已被驳回');
    }

    //提币
    public function withdrawList(Request $request)
    {
        $limit = $request->input('limit', 20);
        // $agent = Agent::getAgent();
        // $child_agents = Agent::getAllChildAgent($agent->id);
        // $agents = $child_agents->pluck('id')->all();
        // $child_users = Users::whereIn('agent_note_id', $agents)->get();
        $agent_id = Agent::getAgentId();
        $node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $lists = UsersWalletOut::where('status', 2)
            //->whereIn('user_id', $child_users->pluck('id')->all())
            ->whereIn('user_id', $node_users)
            ->where(function ($query) use ($request) {

                $account_number = $request->input('account_number', '');
                $belong_agent = $request->input('belong_agent', '');
                $currency_id = $request->input('currency_id', -1);

                $query->when($account_number != '', function ($query) use ($account_number) {
                    $query->whereHas('user', function ($query) use ($account_number) {
                        $query->where('account_number', $account_number);
                    });
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('user', function ($query) use ($belong_agent) {
                        $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
                            $query->where('username', $belong_agent);
                        });
                    });
                })->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency', $currency_id);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
            if ($item->notes == '') {
                $item->notes = '用户提币';
            }
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            return $item;
        });
        $lists->setCollection($items);
        return $this->layuiData($lists);
    }

    //用户资金
    public function wallet(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        return view("agent.capital.wallet", ['user_id' => $id]);
    }

    public function wallettotalList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = $request->get('user_id', null);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }

        $list = Currency::orderBy('id', 'asc')->select(['id', 'name'])->paginate($limit);

        foreach ($list->items() as &$value) {
            $value->_ru = AccountLog::where('type', AccountLog::CHAIN_RECHARGE)
                ->where('user_id', $user_id)
                ->where('currency', $value->id)
                ->sum('value');

            $value->_chu = UsersWalletOut::where('status', 2)
                ->where('user_id', $user_id)
                ->where('currency', $value->id)
                ->sum('real_number');

            $value->_caution_money = LeverTransaction::where('user_id', $user_id)->whereIn('status', [0, 1, 2])->where('legal', $value->id)->sum('caution_money');
        }

        return $this->layuiData($list);
    }

    //结算 提现到账
    public function walletOut(Request $request)
    {
        $id = $request->get('id', '');

        if (!$id) {
            return $this->error('参数错误');
        }

        try {
            DB::beginTransaction();
            $agent_log = AgentMoneylog::lockForUpdate()->find($id);
            if (empty($agent_log)) {
                throw new \Exception('操作失败:信息有误');
            }
            if ($agent_log->status != 0) {
                throw new \Exception('操作失败:该账单已提现,请勿重复操作或刷新后重试');
            }
            $agent = Agent::find($agent_log->agent_id);
            if ($agent->is_admin != 1) {
                $wallet = UsersWallet::where('user_id', $agent->user_id)->where('currency', $agent_log->legal_id)->first();
                if (empty($wallet)) {
                    throw new \Exception('用户钱包不存在');
                }
                if ($agent_log->type == 1) {

                    $account_type = AccountLog::AGENT_JIE_TC_MONEY;
                    $account_info = '代理商结算头寸收益 划转到账';
                } else {
                    $account_type = AccountLog::AGENT_JIE_SX_MONEY;
                    $account_info = '代理商结算手续费收益 划转到账';
                }
                $change_result = change_wallet_balance($wallet, 1, $agent_log->change, $account_type, $account_info);
                if ($change_result !== true) {
                    throw new \Exception($change_result);
                }
            } else {
                throw new \Exception('超级代理商无法提现');
            }


            $agent_log->status = 1; //
            $agent_log->updated_time = time(); //

            $agent_log->save();

            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
}
