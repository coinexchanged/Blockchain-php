<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\{Agent,
    AgentMoneylog,
    Currency,
    LeverTransaction,
    TransactionComplete,
    TransactionOrder,
    Users,
    UsersWallet,
    CurrencyMatch,
    MicroOrder};

/**
 * 该类处理所有的订单与结算。
 * Class ReportController
 * @package App\Http\Controllers\Agent
 */
class OrderController extends Controller
{

    //杠杆订单管理
    public function leverIndex()
    {
        $legal_currencies = Currency::where('is_legal', 1)->get();
        return view("agent.order.leverlist", [
            'legal_currencies' => $legal_currencies,

        ]);
    }

    //撮合单
    public function transactionIndex()
    {
        $legal_currencies = Currency::where('is_legal', 1)->get();
        $currencies = Currency::get();
        return view("agent.order.transaction", [
            'legal_currencies' => $legal_currencies,
            'currencies' => $currencies,
        ]);
    }

    //撮合订单
    public function transactionList(Request $request)
    {
        $limit = $request->get('limit', 10);
        //当前代理商信息
        $agent_id = Agent::getAgentId();
        $node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();

        $account_number = $request->get('account_number', '');


        $result = TransactionComplete::when($account_number != '', function ($query) use ($account_number) {

            $query->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            })->orWhereHas('fromUser', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        })->where(function ($query) use ($request) {
            $legal = $request->input('legal', -1);
            $currency = $request->input('currency', -1);
            $legal != -1 && $query->where('legal', $legal);
            $currency != -1 && $query->where('currency', $currency);
            $start_time = $request->input('start_time', '');
            $end_time = $request->input('end_time', '');
            if (!empty($start_time)) {
                $start_time = strtotime($start_time);
                $query->where('create_time', '>=', $start_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $query->where('create_time', '<=', $end_time);
            }
        })->where(function ($query) use ($node_users) {
            $query->where(function ($query) use ($node_users) {
                $query->whereIn('user_id', $node_users);
            })->orwhere(function ($query) use ($node_users) {
                $query->whereIn('from_user_id', $node_users);
            });
            //})->orderBy('id', 'desc')->toSql();
            //dd($result);
        })->orderBy('id', 'desc')->paginate($limit);
        $sum = $result->sum('number');
        return $this->layuiData($result, $sum);
    }


    //杠杆
    public function order_list(Request $request)
    {

        $limit = $request->input("limit", 10);
        $id = $request->input("id", 0);
        $username = $request->input("username", '');
        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $legal_id = $request->input("legal_id", -1);


        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $query = TransactionOrder::whereHas('user', function ($query) use ($username) {

            $username != '' && $query->where('account_number', $username)->orWhere('phone', $username);
        })->where(function ($query) use ($id, $status, $type) {

            $id != 0 && $query->where('id', $id);

            $status != 10 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        })->where(function ($query) use ($now_agent_id) {

            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        })->when($legal_id > 0, function ($query) use ($legal_id) {

            $query->where('legal', $legal_id);

        });

        $order_list = $query->orderBy('id', 'desc')->paginate($limit);


        return $this->layuiData($order_list);
    }


    /**
     *获取杠杆统计数据
     */
    public function get_order_account(Request $request)
    {

        $id = $request->input("id", 0);
        $username = $request->input("username", '');
        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');

        $legal_id = $request->input("legal_id", -1);

        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $query1 = TransactionOrder::whereHas('user', function ($query) use ($username) {

            $username != '' && $query->where('account_number', $username)->orWhere('phone', $username);
        })->where(function ($query) use ($id, $status, $type) {

            $id != 0 && $query->where('id', $id);

            $status != 10 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        })->where(function ($query) use ($now_agent_id) {

            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        })->when($legal_id > 0, function ($query) use ($legal_id) {

            $query->where('legal', $legal_id);

        });

        //可用保证金 未平仓
        $_lock = $query1->selectRaw('sum(if(status <= 2,caution_money,0)) as caution_money')->value('caution_money') ?? 0;

        //总订单数
        $_count = $query1->count();

        //头寸收益（平仓最终盈亏） 
        $_toucun = $query1->whereIn('status', [LeverTransaction::CLOSED])->sum('fact_profits');
        //手续费收益（已平仓手续费）
        $_shouxu = $query1->whereIn('status', [LeverTransaction::CLOSED])->sum('trade_fee');

        //查询当前代理商的头寸  手续费百分比
        $now_agent = Agent::getAgentById($now_agent_id);

        $data = [];
        $data['_num'] = $_count;
        $data['_toucun'] = bc_mul(bc_mul($_toucun, $now_agent->pro_loss / 100), -1);// 乘以代理商头寸百分比 取负数
        $data['_shouxu'] = bc_mul($_shouxu, $now_agent->pro_ser / 100);// 乘以代理商手续费百分比

        $_all = bc_add($data['_toucun'], $data['_shouxu']);

        $data['_all'] = $_all;
        //可用保证金
        $data['_lock'] = $_lock;


        return $this->ajaxReturn($data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    //导出订单记录Excel 
    public function order_excel(Request $request)
    {
        $limit = $request->input("limit", 10);
        $id = $request->input("id", 0);
        $username = $request->input("username", '');
        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $legal_id = $request->input("legal_id", -1);
        //echo $id.'-'.$username.'-'.$agentusername.'-'.$status.'-'. $type.'-'.$start.'-'.$end;exit;

        /*
        $where = [];
        if ($id > 0){
            $where[] = ['id' , '=' , $id];
        }
        if (!empty($username)){
            $s = DB::table('users')->where('account_number' , $username)->first();
            if ($s !== null){
                $where[] = ['user_id' , '=' , $s->id];
            }
        }
        if ($status  != 10   && in_array($status , [LeverTransaction::ENTRUST,LeverTransaction::TRANSACTION,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])){
            $where[] = ['status' , '=' , $status];
        }
        if ($type > 0 && in_array($type , [1,2])){
            $where[] = ['type' , '=' , $type];
        }
        if (!empty($start) && !empty($end)) {
            $where[] = ['create_time' , '>' , strtotime($start . ' 0:0:0')];
            $where[] = ['create_time' , '<' , strtotime($end . ' 23:59:59')];
        }

        $_self = Agent::getAgent();
        if ($_self === null){
            return $this->outmsg('发生错误！请重新登录');
        }
        $sons = $this->get_my_sons();

        if (!empty($agentusername)){
            $s = DB::table('agent')->where('username' , $agentusername)->first();

            if (!in_array($s->id , $sons['all'])){
                return $this->error('该代理商并不属于您的团队');
            }else{

                $p_s_s = $this->get_my_sons($s->id);

                if (!empty($p_s_s)){
                    $order_list = TransactionOrder::whereIn('status' , [LeverTransaction::ENTRUST,LeverTransaction::TRANSACTION,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])->whereIn('user_id' , $p_s_s['all'])->where($where)->get()->toArray();
                }else{

                    $order_list = TransactionOrder::whereIn('status' , [LeverTransaction::ENTRUST,LeverTransaction::TRANSACTION,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])->whereIn('user_id' , $sons['all'])->where($where)->get()->toArray();

                }
            }
        }else{

            $order_list = TransactionOrder::whereIn('status' , [LeverTransaction::ENTRUST,LeverTransaction::TRANSACTION,LeverTransaction::CLOSED,LeverTransaction::CANCEL,LeverTransaction::CLOSING])->whereIn('user_id' , $sons['all'])->where($where)->get()->toArray();


        }
        */

        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $query = TransactionOrder::whereHas('user', function ($query) use ($username) {

            $username != '' && $query->where('account_number', $username)->orWhere('phone', $username);
        })->where(function ($query) use ($id, $status, $type) {

            $id != 0 && $query->where('id', $id);

            $status != 10 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        })->where(function ($query) use ($now_agent_id) {

            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        })->when($legal_id > 0, function ($query) use ($legal_id) {

            $query->where('legal', $legal_id);

        });


        $order_list = $query->orderBy('id', 'desc')->get()->toArray();

        $data = $order_list;
        //dd($data);
        return Excel::create('订单数据', function ($excel) use ($data) {
            $excel->sheet('订单数据', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('用户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('所属代理商');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('用户等级');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('交易类型');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('交易对');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('当前状态');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('原始价格');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('开仓价格');
                });
                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('当前价格');
                });
                $sheet->cell('K1', function ($cell) {
                    $cell->setValue('最终盈亏');
                });
                $sheet->cell('L1', function ($cell) {
                    $cell->setValue('手数');
                });
                $sheet->cell('M1', function ($cell) {
                    $cell->setValue('倍数');
                });
                $sheet->cell('N1', function ($cell) {
                    $cell->setValue('初始保证金');
                });
                $sheet->cell('O1', function ($cell) {
                    $cell->setValue('当前可用保证金');
                });
                $sheet->cell('P1', function ($cell) {
                    $cell->setValue('创建时间');
                });
                $sheet->cell('Q1', function ($cell) {
                    $cell->setValue('完成时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if ($value['type'] == 1) {
                            $value['type'] = "买入";
                        } else {
                            $value['type'] = "卖出";
                        }
                        if ($value['status'] == 0) {
                            $value['status'] = "挂单中";
                        } elseif ($value['status'] == 1) {
                            $value['status'] = "交易中";
                        } elseif ($value['status'] == 2) {
                            $value['status'] = "平仓中";
                        } elseif ($value['status'] == 3) {
                            $value['status'] = "已平仓";
                        } elseif ($value['status'] == 4) {
                            $value['status'] = "已撤单";
                        }

                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['user_name']);
                        $sheet->cell('C' . $i, $value['parent_agent_name']);
                        $sheet->cell('D' . $i, $value['agent_level']);
                        $sheet->cell('E' . $i, $value['type']);
                        $sheet->cell('F' . $i, $value['symbol']);

                        $sheet->cell('G' . $i, $value['status']);
                        $sheet->cell('H' . $i, $value['origin_price']);
                        $sheet->cell('I' . $i, $value['price']);
                        $sheet->cell('J' . $i, $value['update_price']); //当前价格
                        $sheet->cell('K' . $i, $value['fact_profits']);

                        $sheet->cell('L' . $i, $value['share']); //手数
                        $sheet->cell('M' . $i, $value['multiple']);
                        $sheet->cell('N' . $i, $value['origin_caution_money']); //初始保证金
                        $sheet->cell('O' . $i, $value['caution_money']);
                        $sheet->cell('P' . $i, $value['create_time']); //创建时间

                        $sheet->cell('Q' . $i, $value['complete_time']);
                    }
                }
                ob_end_clean();
            });
        })->download('xlsx');
    }


    //导出用户记录Excel
    public function user_excel(Request $request)
    {

        $id = request()->input('id', 0);
        $parent_id = request()->input('parent_id', 0);
        $account_number = request()->input('account_number', '');
        $start = request()->input('start', '');
        $end = request()->input('end', '');

        $users = new Users();

        if ($id) {
            $users = $users->where('id', $id);
        }
        if ($parent_id > 0) {
            $users = $users->where('agent_note_id', $parent_id);
        }
        if ($account_number) {
            $users = $users->where('account_number', $account_number);
        }
        if (!empty($start) && !empty($end)) {
            $users->whereBetween('time', [strtotime($start . ' 0:0:0'), strtotime($end . ' 23:59:59')]);
        }

        // $my_agent_list = Agent::getLevel4AgentId(Agent::getAgentId(), [Agent::getAgentId()]);

        // $users = $users->whereIn('agent_note_id', $my_agent_list);

        $agent_id = Agent::getAgentId();
        $users = $users->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");

        $data = $users->get()->toArray();
        //dd($data);
        return Excel::create('用户列表', function ($excel) use ($data) {
            $excel->sheet('用户列表', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('用户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('用户身份');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('上级代理商');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('USDT余额');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('邮箱');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('邀请码');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('加入时间');
                });


                if (!empty($data)) {
                    foreach ($data as $key => $value) {

                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['my_agent_level']);
                        $sheet->cell('D' . $i, $value['parent_name']);
                        $sheet->cell('E' . $i, $value['usdt']);
                        $sheet->cell('F' . $i, $value['email']);
                        $sheet->cell('G' . $i, $value['extension_code']);
                        $sheet->cell('H' . $i, $value['create_date']);


                    }
                }
                ob_end_clean();
            });
        })->download('xlsx');
    }

    /**
     * 结算列表首页
     */
    public function jieIndex(Request $request)
    {
        //法币
        $legal_currencies = Currency::where('is_legal', 1)->get();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        $self = Agent::getAgent();
        $is_admin = $self ? $self->is_admin : 0;
        return view('agent.order.jie_index', [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
            'is_admin' => $is_admin
        ]);
    }

    public function jie_list(Request $request)
    {

        $limit = $request->input("limit", 10);
        $start = $request->input("start", '');
        $end = $request->input("end", '');

        $agent_id = Agent::getAgentId();
        //$node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $child_agents = Agent::getAllChildAgent($agent_id);
        $son_agents = $child_agents->pluck('id')->all();

        $lists = AgentMoneylog::whereIn('agent_id', $son_agents)
            ->where(function ($query) use ($request) {

                $id = $request->input("id", 0);
                $username = $request->input("username", '');
                $belong_agent = $request->input('belong_agent', '');
                $legal_id = $request->input('legal_id', -1);

                $type = $request->input("type", -1);//1 头寸  2手续费

                $query->when($id > 0, function ($query) use ($id) {
                    $query->where('id', $id);
                })->when($username != '', function ($query) use ($username) {
                    $query->whereHas('user', function ($query) use ($username) {
                        $query->where('account_number', $username);
                    });
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('agent', function ($query) use ($belong_agent) {
                        $query->where('username', $belong_agent);
                    });
                })->when($legal_id > 0, function ($query) use ($legal_id) {
                    $query->where('legal_id', $legal_id);
                })->when($type > 0, function ($query) use ($type) {
                    $query->where('type', $type);
                });
            })->where(function ($query) use ($start, $end) {

                !empty($start) && $query->where('created_time', '>=', strtotime($start . ' 0:0:0'));

                !empty($end) && $query->where('created_time', '<=', strtotime($end . ' 23:59:59'));
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        return $this->layuiData($lists);
    }

    public function jie_export(Request $request)
    {
        $limit = $request->input("limit", 100000000);
        $start = $request->input("start", '');
        $end = $request->input("end", '');

        $agent_id = Agent::getAgentId();
        //$node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $child_agents = Agent::getAllChildAgent($agent_id);
        $son_agents = $child_agents->pluck('id')->all();

        $lists = AgentMoneylog::whereIn('agent_id', $son_agents)
            ->where(function ($query) use ($request) {

                $id = $request->input("id", 0);
                $username = $request->input("username", '');
                $belong_agent = $request->input('belong_agent', '');
                $legal_id = $request->input('legal_id', -1);

                $type = $request->input("type", -1);//1 头寸  2手续费

                $query->when($id > 0, function ($query) use ($id) {
                    $query->where('id', $id);
                })->when($username != '', function ($query) use ($username) {
                    $query->whereHas('user', function ($query) use ($username) {
                        $query->where('account_number', $username);
                    });
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('agent', function ($query) use ($belong_agent) {
                        $query->where('username', $belong_agent);
                    });
                })->when($legal_id > 0, function ($query) use ($legal_id) {
                    $query->where('legal_id', $legal_id);
                })->when($type > 0, function ($query) use ($type) {
                    $query->where('type', $type);
                });
            })->where(function ($query) use ($start, $end) {

                !empty($start) && $query->where('created_time', '>=', strtotime($start . ' 0:0:0'));

                !empty($end) && $query->where('created_time', '<=', strtotime($end . ' 23:59:59'));
            })
            ->orderBy('id', 'desc')->get();

        $title = [];
        $title[] = "代理商,代理商等级,用户名,杠杆订单id,结算类型,是否到账,结算币种,结算收益,交易时间,备注";
        foreach ($lists->toArray() as $item) {
            $type = $item['type'] == 1 ? '头寸收益' : '手续费收益';
            $is_daozhang = $item['status'] == 0 ? '未提现' : '已提现';
            $title[] = "{$item['jie_agent_name']},{$item['jie_agent_level']},{$item['user_name']},{$item['relate_id']},{$type},{$is_daozhang},{$item['legal_name']},{$item['change']},{$item['created_time']},{$item['memo']}";
        }
        $con = implode("\r\n",$title);

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=结算信息.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $con;
        exit;

    }


    /**
     * 订单详情
     */
    public function order_info(Request $request)
    {
        $order_id = $request->input("order_id", 0);

        if ($order_id > 0) {
            // $sons = $this->get_my_sons();

            //$orderinfo = TransactionOrder::where('id', $order_id)->whereIn('user_id', $sons['all'])->first();
            $orderinfo = LeverTransaction::where('id', $order_id)->first();

            if (empty($orderinfo)) {
                return $this->error('订单编号错误或者您无权查看订单详情');
            } else {
                //dd($orderinfo);
                return view("agent.order.info", ['info' => $orderinfo]);
                // $data['info'] = $orderinfo;
                // return $this->ajaxReturn($data);
            }
        } else {
            return $this->error('非法参数');
        }


    }


    /**
     * @param $san_user
     *
     */
    public function sel_arr($arr = array())
    {
        if (!empty($arr)) {
            $new_arr = [];
            foreach ($arr as $k => $val) {
                $new_arr[] = $val->user_id;
            }
            return $new_arr;
        } else {
            return [];
        }
    }

    /**
     * @param $san_user
     *
     */
    public function sel_agent_arr($arr = array())
    {
        if (!empty($arr)) {
            $new_arr = [];
            foreach ($arr as $k => $val) {
                $new_arr[] = $val->id;
            }
            return $new_arr;
        } else {
            return [];
        }
    }

    //我的杠杆订单
    public function userLeverIndex(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        return view("agent.user.leverlist", ['user_id' => $id]);
    }

    public function userLeverList(Request $request)
    {

        $limit = $request->input("limit", 10);

        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $user_id = $request->input("user_id", '');


        $query = TransactionOrder::where('user_id', $user_id)->where(function ($query) use ($status, $type) {


            $status != 10 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        });


        $order_list = $query->orderBy('id', 'desc')->paginate($limit);

        return $this->layuiData($order_list);
    }

    public function microIndex()
    {
        $currencies = Currency::where('is_micro', 1)->get();
        $currency_matches = CurrencyMatch::where('open_microtrade', 1)->get();

        return view("agent.order.microlist", [
            'currencies' => $currencies,
            'currency_matches' => $currency_matches

        ]);
    }

    //获取交易对和支付币种
    public function microCurrency()
    {
        $currencies = Currency::where('is_micro', 1)->get();
        $currency_matches = CurrencyMatch::where('open_microtrade', 1)->get();
        $data = [];
        $data['currencies'] = $currencies;
        $data['currency_matches'] = $currency_matches;

        return $this->ajaxReturn($data);
    }

    //秒合约订单
    public function microList(Request $request)
    {
        $currency_id = $request->input('currency_id', -1);
        $match_id = $request->input('match_id', -1);

        $type = $request->input('type', -1);
        $account = $request->input('account', '');
        $name = $request->input('name', '');
        $limit = $request->input('limit', 10);
        $status = $request->input('status', -1);
        $start = $request->input("start_time", '');
        $end = $request->input("end_time", '');
        $pre_profit_result = $request->input('pre_profit_result', -2);
        $profit_result = $request->input('profit_result', -2);
        $id = $request->input('id', '');
        $agentusername = $request->input('agentusername', '');

        $_self = Agent::getAgent(); //获取当前登录代理商平台用户
        $agent_id = Agent::getAgentId();
        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }


        $querys = MicroOrder::with(['currency', 'currencyMatch', 'user'])
            ->when($currency_id != -1, function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })->when($match_id != -1, function ($query) use ($match_id) {
                $query->where('match_id', $match_id);
            })->when($type != -1, function ($query) use ($type) {
                $query->where('type', $type);
            })->when($status != -1, function ($query) use ($status) {
                $query->where('status', $status);
            })->when($pre_profit_result != -2, function ($query) use ($pre_profit_result) {
                $query->where('pre_profit_result', $pre_profit_result);
            })->when($profit_result != -2, function ($query) use ($profit_result) {
                $query->where('profit_result', $profit_result);
            })->when($account != '' || $name != '', function ($query) use ($account, $name) {
                $query->whereHas('user', function ($query) use ($account, $name) {
                    $account != '' && $query->where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%');
                    $query->when($name != '', function ($query) use ($name) {
                        $query->whereHas('userProfile', function ($query) use ($name) {
                            $query->where("name", 'like', '%' . $name . '%');
                        });
                    });
                });
            })->when($start != '', function ($query) use ($start) {
                $query->where('created_at', '>=', $start);
            })->when($end != '', function ($query) use ($end) {
                $query->where('created_at', '<=', $end);
            })->when($id != '', function ($query) use ($id) {
                $query->where('id', $id);
            })->where(function ($query) use ($now_agent_id) {

                $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
            });

        $query_total = clone $querys;
        $total = $query_total->select([
            DB::raw('*'),
            DB::raw('sum(fee) as total_fee'),
            DB::raw('sum(fact_profits) as total_fact_profits'),

        ])->first()->setVisible(['total_fee', 'total_fact_profits']);

        //$total='';

        $results = $querys->orderBy('id', 'desc')->paginate($limit);
        $items = $results->getCollection();
        $items->transform(function ($item, $key) {
            return $item->append('pre_profit_result_name')->makeVisible('pre_profit_result');
        });
        $results->setCollection($items);
        return $this->layuiData($results, ['total' => $total]);
    }
}
