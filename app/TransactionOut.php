<?php

/**
 * create by vscode
 * @author lion
 */
namespace App;


use Illuminate\Database\Eloquent\Model;
use App\Utils\RPC;

class TransactionOut extends Model
{
    protected $table = 'transaction_out';
    public $timestamps = false;
    protected $appends = ['account_number', 'currency_name', 'legal_name'];

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }

    /**
     * 匹配交易
     *
     * @param App\TransactionIn $in 买入委托模型实例
     * @param float $num 匹配数量
     * @param App\Users $user 用户模型实例
     * @param App\UsersWallet $wallet 钱包模型实例
     * @param integer $legal_id 法币币种id
     * @param integer $currency_id 交易币种id
     * @return void
     */
    public static function transactionBack($in, $num, $user, $wallet, $legal_id, $currency_id)
    {
        if (empty($in) || empty($num) || empty($user) || empty($wallet)) {
            return false;
        }
        //买方法币
        $to_legal = UsersWallet::where("user_id", $in->user_id)
            ->where("currency", $legal_id)
            ->first();
        //卖方法币
        $out_legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $legal_id)
            ->first();
        //买方币
        $to_currency = UsersWallet::where("user_id", $in->user_id)
            ->where("currency", $currency_id)
            ->first();
        //卖方币
        $out_currency = $wallet;

        if (empty($to_legal) || empty($out_legal) || empty($to_currency) || empty($out_currency)) {
            return false;
        }

        if (bc_comp($num, $in->number) > 0) {
            $num = $in->number;
        }

        $data_wallet1['balance_type'] = 2;
        $data_wallet1['wallet_id'] = $wallet->id;
        $data_wallet1['lock_type'] = 0;
        $data_wallet1['create_time'] = time();
        $data_wallet1['before'] = $wallet->change_balance;
        $data_wallet1['change'] = -$num;
        $data_wallet1['after'] = bc_sub($wallet->change_balance, $num, 5);
        //扣除卖方的币
        $out_currency->change_balance = bc_sub($wallet->change_balance, $num, 5);
        $out_currency->save();

        AccountLog::insertLog([
            'user_id' => $out_currency->user_id,
            'value' => -$num,
            'info' => "扣除卖方",
            'type' => AccountLog::TRANSACTIONIN_SELLER,
            "currency" => $currency_id
        ],$data_wallet1);

        $data_wallet2['balance_type'] = 2;
        $data_wallet2['wallet_id'] = $to_currency->id;
        $data_wallet2['lock_type'] = 0;
        $data_wallet2['create_time'] = time();
        $data_wallet2['before'] = $to_currency->change_balance;
        $data_wallet2['change'] = $num;
        $data_wallet2['after'] = bc_add($to_currency->change_balance, $num, 5);
        //增加买方币
        $to_currency->change_balance = bc_add($to_currency->change_balance, $num, 5);
        $to_currency->save();
        AccountLog::insertLog([
            'user_id' => $to_currency->user_id,
            'value' => $num,
            'info' => "买方增加",
            'type' => AccountLog::TRANSACTIONIN_REDUCE_ADD,
            "currency" => $currency_id
        ], $data_wallet2);

        $cny = bc_mul($num, $in->price, 5);
        $data_wallet3['balance_type'] = 1;
        $data_wallet3['wallet_id'] = $to_legal->id;
        $data_wallet3['lock_type'] = 1;
        $data_wallet3['create_time'] = time();
        $data_wallet3['before'] = $to_legal->lock_change_balance;
        $data_wallet3['change'] = -$cny;
        $data_wallet3['after'] = bc_sub($to_legal->lock_change_balance, $cny, 5);
        //扣除买方的法币
        $to_legal->lock_change_balance = bc_sub($to_legal->lock_change_balance, $cny, 5);
        $to_legal->save();

        AccountLog::insertLog([
            'user_id' => $to_legal->user_id,
            'value' => -$cny,
            'info' => "买入扣除法币",
            'type' => AccountLog::TRANSACTIONIN_REDUCE,
            "currency" => $legal_id
        ],$data_wallet3);

        $data_wallet4 = [
            'balance_type' => 1,
            'wallet_id' => $out_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $out_legal->change_balance,
            'change' => $cny,
            'after' => bc_add($out_legal->change_balance, $cny, 5),
        ];
        //增加卖方cny
        $out_legal->change_balance = bc_add($out_legal->change_balance, $cny, 5);
        $out_legal->save();

        AccountLog::insertLog([
            'user_id' => $out_legal->user_id,
            'value' => $cny,
            'info' => "卖方增加法币",
            'type' => AccountLog::TRANSACTIONIN_SELLER_ADD,
            "currency" => $legal_id
        ], $data_wallet4);

        if (bc_comp($num, $in->number) >= 0) {
            //因为价格与委托时有差异，此处应退还神剩余的冻结余额
            $in->delete();
        } else {
            $in->number = bc_sub($in->number, $num, 5);
            $in->save();
        }

        //插入完成记录
        $complete = new TransactionComplete();
        $complete->way = 1;
        $complete->user_id = $in->user_id; //买入
        $complete->from_user_id = $wallet->user_id; //卖出
        $complete->price = $in->price;
        $complete->number = $num;
        $complete->currency = $currency_id;
        $complete->legal = $legal_id;
        $complete->create_time = time();
        $complete->save();
        MarketHour::batchWriteMarketData($currency_id, $legal_id, $num, $in->price, 1);
    }

    public static function transaction($price,$num, $user, $wallet, $legal_id, $currency_id)
    {
        if (empty($price) || empty($num) || empty($user) || empty($wallet)) {
            return false;
        }
        //买方法币
//        $to_legal = UsersWallet::where("user_id", $in->user_id)
//            ->where("currency", $legal_id)
//            ->first();
        //卖方法币
        $out_legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $legal_id)
            ->first();
        //买方币
//        $to_currency = UsersWallet::where("user_id", $in->user_id)
//            ->where("currency", $currency_id)
//            ->first();
        //卖方币
        $out_currency = $wallet;

        if (empty($out_legal)  || empty($out_currency)) {
            return false;
        }

//        if (bc_comp($num, $in->number) > 0) {
//            $num = $in->number;
//        }

        $data_wallet1['balance_type'] = 2;
        $data_wallet1['wallet_id'] = $wallet->id;
        $data_wallet1['lock_type'] = 0;
        $data_wallet1['create_time'] = time();
        $data_wallet1['before'] = $wallet->change_balance;
        $data_wallet1['change'] = -$num;
        $data_wallet1['after'] = bc_sub($wallet->change_balance, $num, 5);
        //扣除卖方的币
        $out_currency->change_balance = bc_sub($wallet->change_balance, $num, 5);
        $out_currency->save();

        AccountLog::insertLog([
            'user_id' => $out_currency->user_id,
            'value' => -$num,
            'info' => "扣除卖方",
            'type' => AccountLog::TRANSACTIONIN_SELLER,
            "currency" => $currency_id
        ],$data_wallet1);

//        $data_wallet2['balance_type'] = 2;
//        $data_wallet2['wallet_id'] = $to_currency->id;
//        $data_wallet2['lock_type'] = 0;
//        $data_wallet2['create_time'] = time();
//        $data_wallet2['before'] = $to_currency->change_balance;
//        $data_wallet2['change'] = $num;
//        $data_wallet2['after'] = bc_add($to_currency->change_balance, $num, 5);
        //增加买方币
//        $to_currency->change_balance = bc_add($to_currency->change_balance, $num, 5);
//        $to_currency->save();
//        AccountLog::insertLog([
//            'user_id' => $to_currency->user_id,
//            'value' => $num,
//            'info' => "买方增加",
//            'type' => AccountLog::TRANSACTIONIN_REDUCE_ADD,
//            "currency" => $currency_id
//        ], $data_wallet2);

        $cny = bc_mul($num, $price, 5);
//        $data_wallet3['balance_type'] = 1;
//        $data_wallet3['wallet_id'] = $to_legal->id;
//        $data_wallet3['lock_type'] = 1;
//        $data_wallet3['create_time'] = time();
//        $data_wallet3['before'] = $to_legal->lock_change_balance;
//        $data_wallet3['change'] = -$cny;
//        $data_wallet3['after'] = bc_sub($to_legal->lock_change_balance, $cny, 5);
//        //扣除买方的法币
//        $to_legal->lock_change_balance = bc_sub($to_legal->lock_change_balance, $cny, 5);
//        $to_legal->save();

//        AccountLog::insertLog([
//            'user_id' => $to_legal->user_id,
//            'value' => -$cny,
//            'info' => "买入扣除法币",
//            'type' => AccountLog::TRANSACTIONIN_REDUCE,
//            "currency" => $legal_id
//        ],$data_wallet3);

        $data_wallet4 = [
            'balance_type' => 1,
            'wallet_id' => $out_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $out_legal->change_balance,
            'change' => $cny,
            'after' => bc_add($out_legal->change_balance, $cny, 5),
        ];
        //增加卖方cny
        $out_legal->change_balance = bc_add($out_legal->change_balance, $cny, 5);
        $out_legal->save();

        AccountLog::insertLog([
            'user_id' => $out_legal->user_id,
            'value' => $cny,
            'info' => "卖方增加法币",
            'type' => AccountLog::TRANSACTIONIN_SELLER_ADD,
            "currency" => $legal_id
        ], $data_wallet4);

//        if (bc_comp($num, $num) >= 0) {
//            //因为价格与委托时有差异，此处应退还神剩余的冻结余额
//            $in->delete();
//        } else {
//            $in->number = bc_sub($in->number, $num, 5);
//            $in->save();
//        }

        //插入完成记录
        $complete = new TransactionComplete();
        $complete->way = 1;
        $complete->user_id = 0; //买入
        $complete->from_user_id = $wallet->user_id; //卖出
        $complete->price = $price;
        $complete->number = $num;
        $complete->currency = $currency_id;
        $complete->legal = $legal_id;
        $complete->create_time = time();
        $complete->save();
//        MarketHour::batchWriteMarketData($currency_id, $legal_id, $num, $in->price, 1);
    }

    public static function pushNews()
    {
        $data = self::orderBy('price', 'desc')->take(5)->get();
        $send = array("type" => "out", "data" => $data, "content" => "");
        return UserChat::sendChat($send);
    }

    /**
     * 币种
     *
     * @return void
     */
    public function currencycoin()
    {
        return $this->belongsTo('App\Currency', 'currency', 'id');
    }

    /**
     * 法币币种
     *
     * @return void
     */
    public function legalcoin()
    {
        return $this->belongsTo('App\Currency', 'legal', 'id');
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currencycoin()->value('name');
    }
    public function getLegalNameAttribute()
    {
        return $this->legalcoin()->value('name');
    }
    
   
    public static function walletTransaction($in, $num, $user, $wallet, $legal_id, $currency_id)
    {
        if (empty($in) || empty($num) || empty($user) || empty($wallet)) {
            return false;
        }
        //买方法币
        $to_legal = UsersWallet::where("user_id", $in->user_id)
            ->where("currency", $legal_id)
            ->first();
        //卖方法币
        $out_legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $legal_id)
            ->first();
        //买方币
        $to_currency = UsersWallet::where("user_id", $in->user_id)
            ->where("currency", $currency_id)
            ->first();
        //卖方币
        $out_currency = $wallet;

        if (empty($to_legal) || empty($out_legal) || empty($to_currency) || empty($out_currency)) {
            return false;
        }

        if (bc_comp($num, $in->number) > 0) {
            $num = $in->number;
        }

        $data_wallet1['balance_type'] = 2;
        $data_wallet1['wallet_id'] = $wallet->id;
        $data_wallet1['lock_type'] = 0;
        $data_wallet1['create_time'] = time();
        $data_wallet1['before'] = $wallet->change_balance;
        $data_wallet1['change'] = -$num;
        $data_wallet1['after'] = bc_sub($wallet->change_balance, $num, 5);
        //扣除卖方的币
        $out_currency->change_balance = bc_sub($wallet->change_balance, $num, 5);
        $out_currency->save();

        AccountLog::insertLog([
            'user_id' => $out_currency->user_id,
            'value' => -$num,
            'info' => "扣除卖方",
            'type' => AccountLog::TRANSACTIONIN_SELLER,
            "currency" => $currency_id
        ],$data_wallet1);

        $data_wallet2['balance_type'] = 2;
        $data_wallet2['wallet_id'] = $to_currency->id;
        $data_wallet2['lock_type'] = 0;
        $data_wallet2['create_time'] = time();
        $data_wallet2['before'] = $to_currency->change_balance;
        $data_wallet2['change'] = $num;
        $data_wallet2['after'] = bc_add($to_currency->change_balance, $num, 5);
        //增加买方币
        $to_currency->change_balance = bc_add($to_currency->change_balance, $num, 5);
        $to_currency->save();
        AccountLog::insertLog([
            'user_id' => $to_currency->user_id,
            'value' => $num,
            'info' => "买方增加",
            'type' => AccountLog::TRANSACTIONIN_REDUCE_ADD,
            "currency" => $currency_id
        ], $data_wallet2);

        $cny = bc_mul($num, $in->price, 5);
        $data_wallet3['balance_type'] = 1;
        $data_wallet3['wallet_id'] = $to_legal->id;
        $data_wallet3['lock_type'] = 1;
        $data_wallet3['create_time'] = time();
        $data_wallet3['before'] = $to_legal->lock_change_balance;
        $data_wallet3['change'] = -$cny;
        $data_wallet3['after'] = bc_sub($to_legal->lock_change_balance, $cny, 5);
        //扣除买方的法币
        $to_legal->lock_change_balance = bc_sub($to_legal->lock_change_balance, $cny, 5);
        $to_legal->save();
       
        AccountLog::insertLog([
            'user_id' => $to_legal->user_id,
            'value' => -$cny,
            'info' => "买入扣除法币",
            'type' => AccountLog::TRANSACTIONIN_REDUCE,
            "currency" => $legal_id
        ],$data_wallet3);

        // Transaction::changeRate($to_legal->user_id,$cny,$legal_id,'in');
        $data_wallet4 = [
            'balance_type' => 1,
            'wallet_id' => $out_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $out_legal->change_balance,
            'change' => $cny,
            'after' => bc_add($out_legal->change_balance, $cny, 5),
        ];
        //增加卖方cny
        $out_legal->change_balance = bc_add($out_legal->change_balance, $cny, 5);
        $out_legal->save();

        AccountLog::insertLog([
            'user_id' => $out_legal->user_id,
            'value' => $cny,
            'info' => "卖方增加法币",
            'type' => AccountLog::TRANSACTIONIN_SELLER_ADD,
            "currency" => $legal_id
        ], $data_wallet4);
        // Transaction::changeRate($out_legal->user_id,$cny,$legal_id,'out');
        if (bc_comp($num, $in->number) >= 0) {
            //因为价格与委托时有差异，此处应退还神剩余的冻结余额
            $in->delete();
        } else {
            $in->number = bc_sub($in->number, $num, 5);
            $in->save();
        }

        //插入完成记录
        $complete = new TransactionComplete();
        $complete->way = 1;
        $complete->user_id = $in->user_id;
        $complete->from_user_id = $wallet->user_id;
        $complete->price = $in->price;
        $complete->number = $num;
        $complete->currency = $currency_id;
        $complete->legal = $legal_id;
        $complete->create_time = time();
        $complete->save();
        $total = TransactionComplete::where('currency', $currency_id)
                                ->where('legal', $legal_id)
                                ->where('create_time', '>=', strtotime(date('Y-m-d')))
                                ->sum('number');
        $data = [
            'legal_id' => $legal_id,
            'currency_id' => $currency_id,
            'volume' => $total,
            'now_price' => $in->price
        ];
        CurrencyQuotation::updateTodayPriceTable($data);//
        MarketHour::batchWriteMarketData($currency_id, $legal_id, $num, $in->price, 1);
    }
}
