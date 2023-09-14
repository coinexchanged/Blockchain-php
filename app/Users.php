<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Session;
use App\Token;

class Users extends Model
{
    protected $table = 'users';
    public $timestamps = false;

    protected $hidden = [
        'password',
        'pay_password',
        'memorizing_words',
        'is_blacklist',
        'gesture_password',
        'risk',
    ];

    protected $appends = [
        'account',
        'is_seller',
        'create_date',
        'usdt',
        'caution_money',
        'parent_name',
        'my_agent_level',
        // 'lever_balance',
        // 'lock_lever_balance',
        // 'legal_balance',
        // 'lock_legal_balance',
        'userreal_name', //tian add
        'usdt_mic',
        'legal_store'
    ];

    protected static $roleList = [
        MicroOrder::RESULT_LOSS => '亏损',
        MicroOrder::RESULT_BALANCE => '无',
        MicroOrder::RESULT_PROFIT => '盈利',
    ];

    public function getUserrealNameAttribute()
    {
        $user_profile = $this->userReal()->first();
        if($user_profile){
            return $user_profile->name ?? '--';
        }else{
            return '--';
        }

    }

    public function getLegalStoreAttribute()
    {
        return $this->hasOne(LegalStore::class,'id','store_id')->first();
    }

    public function userReal()
    {
        return $this->hasMany(UserReal::class, 'user_id')->where('review_status', 2);
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function getLeverBalanceAttribute()
    {
        $id = $this->attributes['id'];
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->lever_balance;
    }
    public function getLockLeverBalanceAttribute()
    {
        $id = $this->attributes['id'];
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->lock_lever_balance;
    }
    public function getLegalBalanceAttribute()
    {
        $id = $this->attributes['id'];
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->legal_balance;
    }
    public function getLockLegalBalanceAttribute()
    {
        $id = $this->attributes['id'];
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->lock_legal_balance;
    }
    public function getUsdtMicAttribute()
    {
        $value = $this->attributes['id'];

        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $value)->first();

        return isset($wal->micro_balance) ? $wal->micro_balance : '0.00000';
    }
    //秒合约账号
    public function getUsdtAttribute()
    {
        $value = $this->attributes['id'];

        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $value)->first();

        return isset($wal->lever_balance) ? $wal->lever_balance : '0.00000';
    }

    public function getCautionMoneyAttribute()
    {
        $value = $this->attributes['id'];

        return DB::table('lever_transaction')->where('user_id', $value)->whereIn('status', [0, 1])->sum('caution_money');
    }

    public function getParentNameAttribute()
    {
        $value = $this->getAttribute('agent_note_id');
        $p = Agent::where('id', $value)->first();
        return isset($p->username) ? $p->username : '-/-';
    }

    public function getMyAgentLevelAttribute()
    {
        $value = $this->attributes['agent_id'] ?? 0;
        if ($value == 0) {
            return '普通用户';
        } else {
            $m = DB::table('agent')->where('id', $value)->first();
            $name = '';
            if (empty($m)) {
                $name = '';
            } else {
                if ($m->level == 0) {
                    $name = '超管';
                } else if ($m->level > 0) {
                    $name = $m->level . '级代理商';
                }
            }

            return $name;
        }
    }

    public function getCreateDateAttribute()
    {
        $value = $this->getAttribute('time');
        return $value;
        return date('Y-m-d H:i:s', $value);
    }

    //密码加密
    public static function MakePassword($password, $type = 0)
    {
        if ($type == 0) {
            $salt = 'ABCDEFG';
            $passwordChars = str_split($password);
            foreach ($passwordChars as $char) {
                $salt .= md5($char);
            }
        } else {
            $salt = 'TPSHOP' . $password;
        }
        return md5($salt);
    }

    public static function getByAccountNumber($account_number)
    {
        return self::where('account_number', $account_number)->first();
    }

    public static function getByString($string)
    {
        if (empty($string)) {
            return "";
        }
        return self::where("phone", $string)
            ->orwhere('email', $string)
            ->orWhere('account_number', $string)
            ->first();
    }

    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }
    //生成邀请码
    public static function getExtensionCode()
    {
        $code = self::generate_password(4);
        if (self::where("extension_code", $code)->first()) {
            //如果生成的邀请码存在，继续生成，直到不存在
            $code = self::getExtensionCode();
        }
        return $code;
    }
    public static function generate_password($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public static function getUserId()
    {
        // return session('user_id');
        $token = Token::getToken();
        $user_id = Token::getUserIdByToken($token);
        return $user_id;
    }

    public static function getAuthUser()
    {
        return self::find(self::getUserId());
    }


    public function getTimeAttribute()
    {
        if (isset($this->attributes['time'])) {
            $value = $this->attributes['time'];
            return $value ? date('Y-m-d H:i:s', $value) : '';
        } else {
            return "";
        }
    }

    //获取用户的账号  手机号或邮箱
    public function getAccountAttribute()
    {
        //$value = $this->attributes['phone'];
        $value = $this->getAttribute('phone');
//        var_dump($this);
//        die;
        if (empty($value)) {
            $value = $this->attributes['email']??'123@123.com';
            $n = strripos($value, '@');
            $value = mb_substr($value, 0, 2) . '******' . mb_substr($value, $n);
        } else {
            $value = mb_substr($value, 0, 3) . '******' . mb_substr($value, -3, 3);
        }
        return $value;
    }

    /*
    //手势密码序列化
    public function setGesturePassword($value) {
        $this->attributes['gesture_password'] = serialize($value);
    }
    //取出数据时反序列化
    public function getGesturePassword($value) {
        return unserialize($value);
    }
    */

    public function getIsSellerAttribute()
    {
        try{
        $seller = Seller::where('user_id', $this->attributes['id'])->first();
        if (!empty($seller)) {
            return 1;
        }
        return 0;
        }catch (\Exception $exception)
        {
            return 1;
        }
    }

    public function cashinfo()
    {
        return $this->belongsTo('App\UserCashInfo', 'id', 'user_id');
    }

    public function legalDeal()
    {
        return $this->hasOne('App\C2cDeal', 'seller_id', 'id');
    }

    /**
     *
     * @param  $model 用户模型实例
     * @param  $status 锁定开关
     * @param  $time 锁定结束时间
     * @return bool
     */
    public function lockUser($model, $status, $time)
    {
        if (!empty($time)) {
            $time = strtotime($time);
        }
        if ($status == 1) {
            $model->status = 1;
            $model->lock_time = $time;
        } else {
            $model->status = 0;
            $model->lock_time = 0;
        }
        $result = $model->save();
        if ($result) {
            return true;
        }
        return false;
    }

    public function getRiskNameAttribute()
    {
        $risk = $this->attributes['risk'] ?? 0;
        return self::$roleList[$risk];
    }

    /*
     * count 当前几代
     * $algebra 总共几代
     * user_id 用户id
     * touch_user_id 触发者id
     * currency 币种id
     * price 金额
     * */
    public static function rebate($user_id,$touch_user_id,$currency,$price,$count=1,$algebra=0)
    {
        $user=self::where('id', $user_id)->first();
        $touch_user = self::getById($touch_user_id);
        if (empty($user)) {
            return true;
        }

        if ($user->parent_id==0) {
            return true;
        }
        $wallet = UsersWallet::where('currency', $currency)
            ->where('user_id', $user->parent_id)
            ->first();

        $u_algebra=Algebra::where('algebra', $count)->first();
        if (empty($u_algebra)||empty($wallet)) {
            $count+=1;
            $algebra-=1;
            $result=self::rebate($user->parent_id, $touch_user_id, $currency, $price, $count, $algebra);
            return $result;
        }

        $totle_price=$price*$u_algebra->rate/100;
        $info='第'.$count."代用户{$touch_user->account_number}返手续费：".$totle_price;
        $result = change_wallet_balance($wallet, 4, $totle_price, AccountLog::MICRO_TRADE_CLOSE_SETTLE, $info);
        $algebra-=1;
        $user_algebra=new UserAlgebra();
        $user_algebra->user_id=$user->parent_id;
        $user_algebra->touch_user_id=$touch_user_id;
        $user_algebra->algebra=$count;
        $user_algebra->info=$info;
        $user_algebra->value=$totle_price;
        $user_algebra->save();
        $count+=1;
        if ($algebra==0) {
            return true;
        }else{
            $result=self::rebate($user->parent_id, $touch_user_id, $currency, $price, $count, $algebra);
            return $result;
        }

    }

    public function belongAgent()
    {
        return $this->belongsTo(Agent::class, 'agent_note_id', 'id');
    }
}
