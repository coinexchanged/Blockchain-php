<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\Currency;
use App\CurrencyMatch;
use App\Setting;
use App\UsersWallet;
use App\Users;

class CurrencyController extends Controller
{
    public function index()
    {
        return view('admin.currency.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new Currency();
        } else {
            $result = Currency::find($id);
        }
        return view('admin.currency.add')->with('result', $result);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $name = $request->get('name', '');
        $sort = $request->get('sort', 0) ?? 0;
        $logo = $request->get('logo', '') ?? '';
        $type = $request->get('type', '') ?? '';
        $is_legal = $request->get('is_legal', 0) ?? 0;
        $is_lever = $request->get('is_lever', 0) ?? 0;
        $is_match = $request->get('is_match', 0) ?? 0;
        $is_micro = $request->get('is_micro', 0);
        $micro_min = $request->get('micro_min', 0);
        $micro_max = $request->get('micro_max', 0);
        $micro_holdtrade_max = $request->input('micro_holdtrade_max', 0); //最大下单笔数
        $price = $request->get('price', 0);
        $min_number = $request->get('min_number',0);
        $max_number = $request->get('max_number', 0);
        $rate = $request->get('rate', 0) ?? 0;
        $rmb_relation = $request->get('rmb_relation', 0) ?? 0;
        //$total_account = $request->get('total_account', '') ?? '';
        $micro_trade_fee = $request->get('micro_trade_fee', 0);
        //$key = $request->get('key', '') ?? '';
        $contract_address = $request->get('contract_address', '') ?? '';
        $decimal_scale = $request->get('decimal_scale', 18);
        $chain_fee = $request->get('chain_fee', 0);
        $insurancable = $request->get('insurancable', 0);
        $verificationcode = $request->input('verificationcode', '');
        //自定义验证错误信息
        $messages = [
            'required' => ':attribute 为必填字段',
        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sort' => 'required',
            'type' => 'required',
        ], $messages);

        try {
            DB::beginTransaction();
            $projectname = config('app.name');
            //如果验证不通过
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }
            $currency = Currency::findOrNew($id);

            $has = Currency::where('name', $name)->first();

            if (empty($id) && !empty($has)) {
                throw new \Exception('币种 ' . $name . ' 已存在');
            }

            // $chain_client = app('LbxChainServer');

            // if ($currency->total_account != $total_account) {
            //     if ($verificationcode == '') {
            //         throw new \Exception('请先填写验证码再操作');
            //     } else {
            //         $uri = '/v3/wallet/changeaddress';
            //         $response = $chain_client->request('post', $uri, [
            //             'form_params' => [
            //                 'projectname' => $projectname,
            //                 'coin' => strtoupper($currency->type),
            //                 'address' => $total_account,
            //                 'verificationcode' => $verificationcode,
            //             ],
            //         ]);
            //         $result = json_decode($response->getBody()->getContents(), true);
            //         if (!isset($result['code']) || $result['code'] != 0) {
            //             throw new \Exception($result['msg'] ?? '请求发生错误');
            //         }
            //     }
            // }
            $currency->name = $name;
            $currency->sort = intval($sort);
            $currency->logo = $logo;
            $currency->is_legal = $is_legal;
            $currency->is_lever = $is_lever;
            $currency->is_match = $is_match;
            $currency->is_micro = $is_micro;
            $currency->min_number = $min_number;
            $currency->max_number = $max_number;
            $currency->micro_holdtrade_max = $micro_holdtrade_max;
            $currency->rate = $rate;
            $currency->price = $price;
            $currency->micro_min = $micro_min;
            $currency->micro_max = $micro_max;
            $currency->rmb_relation = $rmb_relation;
            //$currency->total_account = $total_account;
            $currency->decimal_scale = $decimal_scale;
            $currency->insurancable = $insurancable;
            $currency->chain_fee = $chain_fee; //链上手续费
            // if ($key != '********' ) {
            //     $uri = '/v3/wallet/encrypt';
            //     $response = $chain_client->request('post', $uri, [
            //         'form_params' => [
            //             'projectname' => $projectname,
            //             'p' => $key,
            //         ],
            //     ]);
            //     $result = json_decode($response->getBody()->getContents(), true);
            //     if (!isset($result['code']) || $result['code'] != 0) {
            //         throw new \Exception($result['msg'] ?? '请求发生错误');
            //     }
            //     $currency->key = $result['data']['k'];
            // }
            $currency->contract_address = $contract_address;
            $currency->type = $type;
            $currency->is_display = 1;
            $currency->save();//保存币种
           
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error('操作失败:' . $exception->getMessage());
        }
    }
    public function setInAddress(Request $request)
    {
        $id = $request->route('id', 0);
        $currency = Currency::findOrFail($id);
        return view('admin.currency.set_in_address', [
            'currency' => $currency,
        ]);
    }

    public function setOutAddress(Request $request)
    {
        $id = $request->route('id', 0);
        $currency = Currency::findOrFail($id);
        return view('admin.currency.set_out_address', [
            'currency' => $currency,
        ]);
    }

    /**
     * 设置转入钱包地址
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postSetInAddress(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $verificationcode = $request->input('verificationcode', '');
            $collect_account = $request->input('collect_account', '');
            $currency = Currency::findOrFail($id);
            if ($verificationcode == '') {
                throw new \Exception('请先填写验证码再操作');
            }
            if ($collect_account == '' || $collect_account == $currency->total_account) {
                throw new \Exception('转入地址不能为空或与转出地址相同');
            }
            $projectname = config('app.name');
            $chain_client = app('LbxChainServer');
            // 更改转入地址
            $uri = '/v3/wallet/changeinaddress';
            $response = $chain_client->request('post', $uri, [
                'form_params' => [
                    'projectname' => $projectname,
                    'coin' => strtoupper($currency->type),
                    'address' => $collect_account,
                    'verificationcode' => $verificationcode,
                ],
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            if (!isset($result['code']) || $result['code'] != 0) {
                throw new \Exception($result['msg'] ?? '请求发生错误');
            }
            $currency->collect_account = $collect_account;
            $currency->save();
            return $this->success('操作完成');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 设置转出钱包地址
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postSetOutAddress(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $verificationcode = $request->input('verificationcode', '');
            $total_account = $request->input('total_account', '');
            $key = $request->input('key', '');
            $encrypt_key = '';
            $currency = Currency::findOrFail($id);
            if ($verificationcode == '') {
                throw new \Exception('请先填写验证码再操作');
            }
            if ($total_account == '' || $total_account == $currency->collect_account) {
                throw new \Exception('转出地址不能为空或与归拢地址不能相同');
            }
            $projectname = config('app.name');
            $chain_client = app('LbxChainServer');
            // 更改转出地址
            $uri = '/v3/wallet/changeoutaddress';
            $response = $chain_client->request('post', $uri, [
                'form_params' => [
                    'projectname' => $projectname,
                    'coin' => strtoupper($currency->type),
                    'address' => $total_account,
                    'verificationcode' => $verificationcode,
                ],
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            if (!isset($result['code']) || $result['code'] != 0) {
                throw new \Exception($result['msg'] ?? '请求发生错误');
            }
            $auto_encrypt_private = Setting::getValueByKey('auto_encrypt_private', 1);
            if ($key != '********' && $key != '') {
                if ($auto_encrypt_private) {
                    $uri = '/v3/wallet/encrypt';
                    $response = $chain_client->request('post', $uri, [
                        'form_params' => [
                            'projectname' => $projectname,
                            'p' => $key,
                        ],
                    ]);
                    $result = json_decode($response->getBody()->getContents(), true);
                    if (!isset($result['code']) || $result['code'] != 0) {
                        throw new \Exception($result['msg'] ?? '请求发生错误');
                    }
                    $encrypt_key = $result['data']['k'];     
                } else {
                    $encrypt_key = '';
                }
            }
            $currency->key = $encrypt_key;
            $currency->total_account = $total_account;
            $currency->save();
            return $this->success('操作完成');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $insurable = $request->get('insurancable', null);
        $result = new Currency();
        if($insurable){
            $result->where('insurancable',1);
        }
//        $account_number = $request->get('account_number','');

        $result = $result->orderBy('sort', 'asc')->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        $acceptor = Currency::find($id);
        if (empty($acceptor)) {
            return $this->error('无此币种');
        }
        try {
            $acceptor->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function isDisplay(Request $request)
    {
        $id = $request->get('id', 0);
        $currency = Currency::find($id);
        if (empty($currency)) {
            return $this->error('参数错误');
        }
        if ($currency->is_display == 1) {
            $currency->is_display = 0;
        } else {
            $currency->is_display = 1;
        }
        try {
            $currency->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    public function isInsurancable(Request $request)
    {
        $id = $request->get('id', 0);
        $currency = Currency::find($id);
        if (empty($currency)) {
            return $this->error('参数错误');
        }
        if ($currency->insurancable == 1) {
            $currency->insurancable = 0;
        } else {
            $currency->insurancable = 1;
        }
        try {
            $currency->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function executeCurrency(Request $request)
    {
        $id = intval($request->get('id', 0));
        $is_execute = Setting::getValueByKey('currency_' . $id, 0);
        if ($is_execute == 1) {
            return $this->error('该币上币程序正在后台执行中');
        } elseif ($is_execute == 2) {
            return $this->error('该币已经运行过上币程序');
        } else {
            $path = base_path();
            $process = new Process('nohup php artisan execute_currency ' . $id . ' >./execute_currency.log 2>&1 &', $path); //第一个参数是运行的命令,命令方式跟 Linux 一致，第二个参数是可以执行此条命令的路径
            //上边那个是交易所的上币逻辑，下边这个是钱包的上币逻辑
            //$process = new Process('nohup php artisan make_wallet ' . $id . ' >./execute_currency.log 2>&1 &', $path); //第一个参数是运行的命令,命令方式跟 Linux 一致，第二个参数是可以执行此条命令的路径
            $process->run();
            return $this->success('开始在后台执行上币脚本');
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
        $currency_from_names = CurrencyMatch::enumCurrencyFromNames();
        return view('admin.currency.match_add')->with('currencies', $currencies)
            ->with('market_from_names', $market_from_names)->with('currency_from_names', $currency_from_names);
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
        $open_microtrade = $request->input('open_microtrade', 0);
        $lever_share_num = $request->input('lever_share_num', 1);
        $spread = $request->input('spread', 0);
        $overnight = $request->input('overnight', 0);
        $lever_trade_fee = $request->input('lever_trade_fee', 0);
        $lever_min_share = $request->input('lever_min_share', 0);
        $lever_max_share = $request->input('lever_max_share', 0);
        $fluctuate_min = $request->input('fluctuate_min', 0);
        $fluctuate_max = $request->input('fluctuate_max', 0);
        $risk_group_result = $request->input('risk_group_result', 0);

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
            'open_microtrade' => $open_microtrade,
            'lever_share_num' => $lever_share_num,
            'lever_trade_fee' => $lever_trade_fee,
            'fluctuate_min' => $fluctuate_min,
            'fluctuate_max' => $fluctuate_max,
            'risk_group_result' => $risk_group_result,
            'spread' => $spread,
            'overnight' => $overnight,
            'lever_min_share' => $lever_min_share,
            'lever_max_share' => $lever_max_share,
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
        $currency_from_names = CurrencyMatch::enumCurrencyFromNames();
        $currencies = Currency::where('id', '<>', $currency_match->legal_id)->get();
        $var = compact('currency_match', 'currencies', 'market_from_names','currency_from_names');
        return view('admin.currency.match_add', $var);
    }

    public function postEditMatch(Request $request, $id)
    {
        $currency_id = $request->input('currency_id');
        $is_display = $request->input('is_display', 1);
        $market_from = $request->input('market_from', 0);
        $open_transaction = $request->input('open_transaction', 0);
        $open_lever = $request->input('open_lever', 0);
        $open_microtrade = $request->input('open_microtrade', 0);
        $lever_share_num = $request->input('lever_share_num', 1);
        $spread = $request->input('spread', 0);
        $overnight = $request->input('overnight', 0);
        $lever_trade_fee = $request->input('lever_trade_fee', 0);
        $lever_min_share = $request->input('lever_min_share', 0);
        $lever_max_share = $request->input('lever_max_share', 0);
        $fluctuate_min = $request->input('fluctuate_min', 0);
        $fluctuate_max = $request->input('fluctuate_max', 0);
        $risk_group_result = $request->input('risk_group_result', 0);
        $currency_type = $request->input('currency_type',1);
        $currency_code = $request->input('currency_code','');
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
            'open_microtrade' => $open_microtrade,
            'lever_share_num' => $lever_share_num,
            'lever_trade_fee' => $lever_trade_fee,
            'fluctuate_min' => $fluctuate_min,
            'fluctuate_max' => $fluctuate_max,
            'risk_group_result' => $risk_group_result,
            'spread' => $spread,
            'overnight' => $overnight,
            'lever_min_share' => $lever_min_share,
            'lever_max_share' => $lever_max_share,
            'create_time' => time(),
            'currency_type' => $currency_type,
            'currency_code' => $currency_code,
        ])->save();
        CurrencyMatch::reguard();
        return $result ? $this->success('保存成功') : $this->error('保存失败');
    }

    public function delMatch($id)
    {
        $result = CurrencyMatch::destroy($id);
        return $result ? $this->success('删除成功') : $this->error('删除失败');
    }

    public function microMatch(Request $request)
    {
        return view('admin.setting.currency_risk');
    }

    public function microMatchList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $risk = $request->input('risk', -2);
        $currency_match = CurrencyMatch::where('open_microtrade', 1)
            ->when($risk != -2, function ($query) use ($risk) {
                $query->where('risk_group_result', $risk);
            })
            ->paginate($limit);
        $items = $currency_match->getCollection();
        $items->transform(function ($item, $key) {
            return $item->append('risk_group_result_name');
        });
        $currency_match->setCollection($items);
        return $this->layuiData($currency_match);
    }

    public function microRisk(Request $request)
    {
        $ids = $request->input('ids', []);
        $risk = $request->input('risk', 0);
        $affect_rows = CurrencyMatch::whereIn('id', $ids)->update(['risk_group_result' => $risk]);
        return $this->success($affect_rows . '个交易对设置成功');
    }
}
