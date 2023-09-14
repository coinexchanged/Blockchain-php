<?php

namespace App\Http\Controllers\Api;

use App\AccountLog;
use App\Currency;
use App\InsuranceClaimApply;
use App\InsuranceType;
use App\MicroOrder;
use App\Setting;
use App\Users;
use App\UsersInsurance;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceController extends Controller
{
    /**
     * 获取保险种类
     */
    public function getInsuranceType()
    {
        $currency_id = request('currency_id',0);
        $currency = Currency::find($currency_id);
        if(!$currency){
            return $this->error('非法参数');
        }
        if($currency->insurancable == 0){
            return $this->error('该币种不支持购买保险');
        }
        $insurance_types = InsuranceType::where('status', 1)
            ->where('currency_id', $currency_id)->get()->toArray();
        return $this->success($insurance_types);
    }


    /**
     * 获取用户币种的保险
     */
    public function getUserCurrencyInsurance()
    {
        $user_id = Users::getUserId();
        $currency_id = request('currency_id',0);
        $user_insurance = UsersInsurance::where('user_id', $user_id)
            ->whereHas('insurance_type', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })
            ->where('status', 1)
            ->first();
        

        $user_wallet = UsersWallet::where('user_id', $user_id)
            ->where('currency', $currency_id)
            ->first();
        
        return $this->success([
            'user_insurance' => $user_insurance,
            'user_wallet' => $user_wallet,
        ]);
    }
    /**
     * 购买保险
     */
    public function buyInsurance()
    {
        $user_id = Users::getUserId();
        $amount = request('amount', 0);
        $insurance_type_id = request('type_id', 0);

        if (!is_numeric($amount) or $amount <= 0) {
            return $this->error('错误的金额！');
        }
        $insurance_type = InsuranceType::find($insurance_type_id);
        if(!$insurance_type){
            return $this->error('不存在的险种！');
        }
        $currency_id = $insurance_type->currency_id;
        $currency = Currency::find($currency_id);
        if(!$currency){
            return $this->error('不存在的币种！');
        }

        if($currency->insurancable == 0){
            return $this->error('该币种不支持购买保险');
        }

        if($amount > $insurance_type->max_amount || $amount < $insurance_type->min_amount){
            return $this->error("购买失败，购买金额必须大于{$insurance_type->min_amount}并且小于{$insurance_type->max_amount}");
        }

        $users_insurance = UsersInsurance::where('user_id',$user_id)
            ->where('status',1)
            ->where('insurance_type_id',$insurance_type_id)
            ->first();

        //该用户存在该币种的险种
        if($users_insurance){
            return $this->error('已经购买了该币种的险种！');
        }

        //保险资产
        $insurance_amount = bcmul($amount, bc_div($insurance_type->insurance_assets, 100),2);
        try {
            DB::beginTransaction();

            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $currency_id)
                ->lockForUpdate()
                ->first();

            if (bc_comp($user_wallet->micro_balance, ($amount+$insurance_amount)) < 0) {
                throw new \Exception('可用余额不足，无法购买！');
            }

            //扣币
            change_wallet_balance($user_wallet, 4, -($amount+$insurance_amount), AccountLog::USER_BUY_INSURANCE, "用户购买保险{$insurance_type->name}", false);

            //生成保险单

            UsersInsurance::create([
                'user_id' => $user_id,
                'insurance_type_id' => $insurance_type_id,
                'amount' => $amount,
                'insurance_amount' => $insurance_amount,
                'status' => 1,
                'claim_status' => 0,
            ]);

            //用户的受保金额
            $user_wallet->insurance_balance = $amount;
            $user_wallet->lock_insurance_balance = $insurance_amount;
            $user_wallet->save();

            DB::commit();
            return $this->success('购买成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('购买失败！原因：'.$e->getMessage());
        }
    }

    /**
     * 索赔
     */
    public function claimApply()
    {
        $user_id = Users::getUserId();
        $user_insurance_id = request('user_insurance_id',0);
        $user_insurance = UsersInsurance::find($user_insurance_id);

        if(!$user_insurance){
            return $this->error('未找到该保险');
        }

        //该保险是否正在处理中？
        $user_insurance_claim = InsuranceClaimApply::where('user_id', $user_id)
            ->where('apply_status', 0)
            ->where('user_insurance_id', $user_insurance_id)
            ->first();
        if($user_insurance_claim){
            return $this->error('该保险正在处理中');
        }

        $can_claim = $this->canClaimApply($user_id, $user_insurance);
        //dd($can_claim);
        if($can_claim !== true){
            return $this->error("申请索赔失败：{$can_claim}");
        }

        $insurance_type = $user_insurance->insurance_type;
        try {
            DB::beginTransaction();

            $make_apply = InsuranceClaimApply::create([
                'user_id' => $user_id,
                'user_insurance_id' => $user_insurance->id,
                'apply_status' => 0,
                'compensate' => bc_mul($user_insurance->amount,bc_div($insurance_type->claim_rate,100)),
                'insurance_type' => $user_insurance->insurance_type_id
            ]);
            $user_insurance->claim_status = 1;
            $user_insurance->save();
            if($insurance_type->auto_claim == 0){
                //不自动处理索赔
            }else{
                //自动处理索赔
                $this->handleClaim($make_apply);
            }
            DB::commit();
            return $this->success('申请索赔成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('申请索赔失败:'.$e->getMessage());
        }
    }

    /**
     * 是否可以申请索赔
     */
    protected function canClaimApply($user_id, $user_insurance)
    {
        //$user = Users::getById($user_id);
        $insurance_type = $user_insurance->insurance_type;

        //该用户该保险的对应的钱包。
        $user_wallet = UsersWallet::where('user_id', $user_id)
            ->where('currency', $insurance_type->currency_id)
            ->first();

        $today_claim_times = $this->getTodayClaimSuccessCount($user_id, $insurance_type->type);

        //超出今日索赔次数
        if($today_claim_times >= $insurance_type->claims_times_daily){
            return '超出今日索赔次数!';
        }

        //此时间段内是否有未平仓的保险
        $count = MicroOrder::where('user_id', $user_id)
            ->where(function($query){
                $query->where('status', 1)->orWhere('status',2);
            })
            ->where('currency_id', $insurance_type->currency_id)
            ->count();
        if($count > 0){
            return '存在未平仓订单';
        }
        switch ($insurance_type->type){
            case 1:
                //受保资产为0不允许索赔申请
                if($user_wallet->insurance_balance == 0){
                    return '受保资产为零';
                }
                //受保金额低于此时可以申请保险索赔。
                $defective_amount = bc_mul($user_insurance->amount ,bc_div($insurance_type->defective_claims_condition, 100));

                //正向险种，受保资产大于【索赔申请条件1额度】，不允许索赔申请
                if($user_wallet->insurance_balance > $defective_amount){
                    return '受保资产不符合可申请索赔条件1';
                }
                break;
            case 2:
                //反向险种，受保资产大于【索赔申请条件2额度】，不允许索赔申请
                if($user_wallet->insurance_balance > $insurance_type->defective_claims_condition2){
                    return '受保资产不符合可申请索赔条件2';
                }
                break;
            default:
                return '未知的险种类型';
        }
        return true;//可以申请索赔
    }

    /**
     * 获取今天用户索赔成功次数
     */
    protected function getTodayClaimSuccessCount($user_id, $insurance_type)
    {
        $now_date = Carbon::now()->toDateString();
        $today_claim_success_count = InsuranceClaimApply::where('user_id', $user_id)
            ->where('insurance_type', $insurance_type)
            ->where('apply_status', 1)//已成功赔付的
            ->whereDate('updated_at', $now_date)//今天的
            ->count();
        return $today_claim_success_count;
    }

    /**
     * 处理索赔
     */
    protected function handleClaim($claim_apply){


        $user_insurance = UsersInsurance::where('id', $claim_apply->user_insurance_id)->first();
        //保险类型
        $insurance_type = $user_insurance->insurance_type;

        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $claim_apply->user_id)
                ->where('currency', $insurance_type->currency_id)
                ->lockForUpdate()
                ->first();


            switch ($insurance_type->claim_direction){
                case 1:
                    //索赔清除用户受保金额
                    change_wallet_balance($user_wallet, 5, -$user_wallet->insurance_balance, AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[清除受保金额]', false);

                    //将保险受保金额给予用户保险账户
                    change_wallet_balance($user_wallet, 5, $claim_apply->compensate, AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[赔偿受保金额]', false);

                    break;
                case 2:
                    //索赔清除用户受保金额
                    change_wallet_balance($user_wallet, 5, -$user_wallet->insurance_balance, AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[清除受保金额]', false);

                    //将保险受保金额给予用户的秒合约账户
                    change_wallet_balance($user_wallet, 4, $claim_apply->compensate, AccountLog::USER_CLAIM_COMPENSATION, '保险赔偿用户[赔偿受保金额]', false);

                    change_wallet_balance($user_wallet, 5, -$user_wallet->lock_insurance_balance,
                        AccountLog::INSURANCE_RESCISSION2, '保险解约，扣除保险金额', true);

                    //将用户的保险状态改变
                    $user_insurance->status = 0;
                    $user_insurance->save();
                    break;
                default:
                    throw  new \Exception('未知受保金额去向状态');
            }
            //更改索赔状态
            $user_insurance->claim_status = 0;
            $user_insurance->save();
            //更改申请状态
            $claim_apply->apply_status = 1;
            $claim_apply->operator = 'auto';
            $claim_apply->save();
            DB::commit();
            return $this->success('处理成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('处理失败：'.$e->getMessage());
        }

    }


    /**
     * 手动解约
     */
    public function manualRescission()
    {
        $user_id = Users::getUserId();
        $user_insurance_id = request('user_insurance_id',0);
        $user_insurance = UsersInsurance::find($user_insurance_id);

        if(!$user_insurance){
            return $this->error('未找到该保险');
        }

        if($user_insurance->status == 0){
            return $this->error('该保险已失效');
        }

        if($user_insurance->claim_status == 1){
            return $this->error('该保险正在索赔处理中');
        }
        //保险类型
        $insurance_type = $user_insurance->insurance_type;

        //用户钱包
        $user_wallet = UsersWallet::where('user_id', $user_id)
            ->where('currency', $insurance_type->currency_id)
            ->first();

        //此时间段内是否有未平仓的保险
        $count = MicroOrder::where('user_id', $user_id)
            ->where(function($query){
                $query->where('status', 1)->orWhere('status',2);
            })
            ->where('currency_id', $insurance_type->currency_id)
            ->count();
        if($count > 0){
            return '解约失败，存在未平仓订单';
        }
        //爆仓盈利条件
        //$rescission_profit = bc_mul($user_insurance->insurance_amount, 1 + bc_div($insurance_type->profit_termination_condition, 100), 2);
        $auto = 1;
        $return_amount =  $user_wallet->insurance_balance;

        try {
            DB::beginTransaction();

            //将用户的保险状态改变
            $user_insurance->status = 0;
            $user_insurance->rescinded_at = \Carbon\Carbon::now()->toDateTimeString();
            $user_insurance->rescinded_type = $auto;//解约类型
            $user_insurance->save();

            //将平仓额度给予用户
            change_wallet_balance($user_wallet, 4, $return_amount, AccountLog::INSURANCE_RESCISSION_ADD,
                '保险解约，赔付金额');

            //扣除用户钱包保险金额
            change_wallet_balance($user_wallet, 5, -$user_wallet->insurance_balance, AccountLog::INSURANCE_RESCISSION1,
                '保险解约，扣除受保金额', false);

            change_wallet_balance($user_wallet, 5, -$user_wallet->lock_insurance_balance,
                AccountLog::INSURANCE_RESCISSION2, '保险解约，扣除保险金额', true);
            DB::commit();
            return $this->success('解约成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($this->returnStr('解约失败:').$e->getMessage());
        }
    }

}
