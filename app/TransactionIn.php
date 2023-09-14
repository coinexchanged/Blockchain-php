<?php

/**
 * create by vscode
 * @author lion
 */
namespace App;


use Illuminate\Database\Eloquent\Model;
use App\Utils\RPC;

class TransactionIn extends Model
{
    protected $table = 'transaction_in';
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
     * @param App\TransactionOut $out 卖出委托模型实例
     * @param float $num 匹配数量
     * @param App\Users $user 买入用户模型实例
     * @param integer $legal_id 法币币种id
     * @param integer $currency_id 交易币种id
     * @return void
     */
    public static function transactionBack($out, $num, $user, $legal_id, $currency_id)
    {
        if (empty($out) || empty($num) || empty($user)) {
            return false;
        }
        //买方法币
        $to_legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $legal_id)
            ->first();
        //卖方法币
        $out_legal = UsersWallet::where("user_id", $out->user_id)
            ->where("currency", $legal_id)
            ->first();
        //买方币
        $to_currency = UsersWallet::where("user_id", $user->id)
            ->where("currency", $currency_id)
            ->first();
        //卖方币
        $out_currency = UsersWallet::where("user_id", $out->user_id)   
            ->where("currency", $currency_id)
            ->first();
        $out_user = Users::find($out->user_id);

        if (empty($to_currency) || empty($out_currency) || empty($out_user) || empty($to_legal) || empty($out_legal)) {
            return false;
        }

        if (bc_comp($num, $out->number) > 0) {
            $num = $out->number;
        }

        $cny = bc_mul($num, $out->price, 5);
        $data_wallet1 = [
            'balance_type' => 1,
            'wallet_id' => $to_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $to_legal->change_balance,
            'change' => -$cny,
            'after' => bc_sub($to_legal->change_balance, $cny, 5),
        ];
        //扣除买方的法币

        $to_legal->change_balance = bc_sub($to_legal->change_balance, $cny, 5);
        $to_legal->save();
        AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => -$cny,
            'info' => "买方扣除币币余额",
            'type' => AccountLog::TRANSACTIONIN_REDUCE,
            'currency_id' => $legal_id
        ],$data_wallet1);


        $data_wallet2 = [
            'balance_type' => 1,
            'wallet_id' => $out_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $out_legal->change_balance,
            'change' => $cny,
            'after' => bc_add($out_legal->change_balance, $cny, 5),
        ];
        //增加卖方币币
        $out_legal->change_balance = bc_add($out_legal->change_balance, $cny, 5);
        $out_legal->save();
        AccountLog::insertLog([
            'user_id' => $out->user_id,
            'value' => $cny,
            'info' => "卖方增加币币余额",
            'type' => AccountLog::TRANSACTIONIN_SELLER_ADD,
            'currency' => $legal_id
        ],$data_wallet2);

        $data_wallet3 = [
            'balance_type' => 2,
            'wallet_id' => $out_currency->id,
            'lock_type' => 1,
            'create_time' => time(),
            'before' => $out_currency->lock_change_balance,
            'change' => -$num,
            'after' => bc_sub($out_currency->lock_change_balance, $num, 5),
        ];
        //扣除卖方的币
        if (bc_comp($out_currency->lock_change_balance, $num) < 0) {
            abort(403, '匹配到的卖方委托存在异常,委托编号:' . $out->id);
            return false;
        }
        $out_currency->lock_change_balance = bc_sub($out_currency->lock_change_balance, $num, 5);
        $out_currency->save();
        AccountLog::insertLog([
            'user_id' => $out->user_id,
            'value' => -$num,
            'info' => "扣除卖方",
            'type' => AccountLog::TRANSACTIONIN_SELLER,
            'currency' => $currency_id
        ],$data_wallet3);


        $data_wallet4 = [
            'balance_type' =>  2,
            'wallet_id' => $to_currency->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $to_currency->change_balance,
            'change' => $num,
            'after' => bc_add($to_currency->change_balance, $num, 5),
        ];
        //增加买方的币
        $to_currency->change_balance = bc_add($to_currency->change_balance, $num, 5);
        $to_currency->save();

        AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => $num,
            'info' => "买方增加",
            'type' => AccountLog::TRANSACTIONIN_REDUCE_ADD,
            'currency' => $currency_id
        ],$data_wallet4);

        if ($num >= $out->number) {
            $out->delete();
        } else {
            $out->number = bc_sub($out->number, $num, 5);
            $out->save();
        }

        //插入完成记录
        $complete = new TransactionComplete();
        $complete->way = 2;
        $complete->user_id = $user->id;
        $complete->from_user_id = $out->user_id;
        $complete->price = $out->price;
        $complete->number = $num;
        $complete->currency = $currency_id;
        $complete->legal = $legal_id;
        $complete->create_time = time();
        $complete->save();
        MarketHour::batchWriteMarketData($currency_id, $legal_id, $num, $out->price, 1);
    }

    public static function transaction($price, $num, $user, $legal_id, $currency_id)
    {
        if (empty($price) || empty($num) || empty($user)) {
            return false;
        }
        //买方法币
        $to_legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $legal_id)
            ->first();
        //卖方法币
//        $out_legal = UsersWallet::where("user_id", $out->user_id)
//            ->where("currency", $legal_id)
//            ->first();
        //买方币
        $to_currency = UsersWallet::where("user_id", $user->id)
            ->where("currency", $currency_id)
            ->first();
        //卖方币
//        $out_currency = UsersWallet::where("user_id", $out->user_id)
//            ->where("currency", $currency_id)
//            ->first();
//        $out_user = Users::find($out->user_id);

        if (empty($to_currency) || empty($to_legal)) {
            return false;
        }

//        if (bc_comp($num, $out->number) > 0) {
//            $num = $out->number;
//        }

        $cny = bc_mul($num, $price, 5);
        $data_wallet1 = [
            'balance_type' => 1,
            'wallet_id' => $to_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $to_legal->change_balance,
            'change' => -$cny,
            'after' => bc_sub($to_legal->change_balance, $cny, 5),
        ];
        //扣除买方的法币

        $to_legal->change_balance = bc_sub($to_legal->change_balance, $cny, 5);
        $to_legal->save();
        AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => -$cny,
            'info' => "买方扣除币币余额",
            'type' => AccountLog::TRANSACTIONIN_REDUCE,
            'currency_id' => $legal_id
        ],$data_wallet1);



        $data_wallet4 = [
            'balance_type' =>  2,
            'wallet_id' => $to_currency->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $to_currency->change_balance,
            'change' => $num,
            'after' => bc_add($to_currency->change_balance, $num, 5),
        ];
        //增加买方的币
        $to_currency->change_balance = bc_add($to_currency->change_balance, $num, 5);
        $to_currency->save();

        AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => $num,
            'info' => "买方增加",
            'type' => AccountLog::TRANSACTIONIN_REDUCE_ADD,
            'currency' => $currency_id
        ],$data_wallet4);

        //插入完成记录
        $complete = new TransactionComplete();
        $complete->way = 2;
        $complete->user_id = $user->id;
        $complete->from_user_id = 0;
        $complete->price = $price;
        $complete->number = $num;
        $complete->currency = $currency_id;
        $complete->legal = $legal_id;
        $complete->create_time = time();
        $complete->save();
//        MarketHour::batchWriteMarketData($currency_id, $legal_id, $num, $out->price, 1);
    }

    public static function pushNews()
    {
        $data = self::orderBy('price', 'asc')->take(5)->get();
        $send = array("type" => "in", "data" => $data, "content" => "");
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
    
    //钱包的撮合交易 买入
    public static function walletTransaction($out, $num, $user, $legal_id, $currency_id)
    {
        if (empty($out) || empty($num) || empty($user)) {
            return false;
        }
        //买方法币
        $to_legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $legal_id)
            ->first();
        //卖方法币
        $out_legal = UsersWallet::where("user_id", $out->user_id)
            ->where("currency", $legal_id)
            ->first();
        //买方币
        $to_currency = UsersWallet::where("user_id", $user->id)
            ->where("currency", $currency_id)
            ->first();
        //卖方币
        $out_currency = UsersWallet::where("user_id", $out->user_id)   
            ->where("currency", $currency_id)
            ->first();
        $out_user = Users::find($out->user_id);

        if (empty($to_currency) || empty($out_currency) || empty($out_user) || empty($to_legal) || empty($out_legal)) {
            return false;
        }

        if (bc_comp($num, $out->number) > 0) {
            $num = $out->number;
        }

        $cny = bc_mul($num, $out->price, 5);
        $data_wallet1 = [
            'balance_type' => 1,
            'wallet_id' => $to_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $to_legal->change_balance,
            'change' => -$cny,
            'after' => bc_sub($to_legal->change_balance, $cny, 5),
        ];
        //扣除买方的交易币

        $to_legal->change_balance = bc_sub($to_legal->change_balance, $cny, 5);
        $to_legal->save();
        AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => -$cny,
            'info' => "买入扣除法币",
            'type' => AccountLog::TRANSACTIONIN_REDUCE,
            'currency' => $legal_id
        ],$data_wallet1);
        //扣除买方的法币的同事。扣除手续费
        
        // Transaction::changeRate($user->id,$cny,$legal_id,'in');

        $data_wallet2 = [
            'balance_type' => 1,
            'wallet_id' => $out_legal->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $out_legal->change_balance,
            'change' => $cny,
            'after' => bc_add($out_legal->change_balance, $cny, 5),
        ];
        //增加卖方法币
        $out_legal->change_balance = bc_add($out_legal->change_balance, $cny, 5);
        $out_legal->save();
        AccountLog::insertLog([
            'user_id' => $out->user_id,
            'value' => $cny,
            'info' => "卖方增加法币",
            'type' => AccountLog::TRANSACTIONIN_SELLER_ADD,
            'currency' => $legal_id
        ],$data_wallet2);
        //卖出的同事，扣除手续费
        // Transaction::changeRate($out->user_id,$cny,$legal_id,'out');
        $data_wallet3 = [
            'balance_type' => 2,
            'wallet_id' => $out_currency->id,
            'lock_type' => 1,
            'create_time' => time(),
            'before' => $out_currency->lock_change_balance,
            'change' => -$num,
            'after' => bc_sub($out_currency->lock_change_balance, $num, 5),
        ];
        //扣除卖方的币
        if (bc_comp($out_currency->lock_change_balance, $num) < 0) {
            abort(403, '匹配到的卖方委托存在异常,委托编号:' . $out->id);
            return false;
        }
        $out_currency->lock_change_balance = bc_sub($out_currency->lock_change_balance, $num, 5);
        $out_currency->save();
        AccountLog::insertLog([
            'user_id' => $out->user_id,
            'value' => -$num,
            'info' => "扣除卖方",
            'type' => AccountLog::TRANSACTIONIN_SELLER,
            'currency' => $currency_id
        ],$data_wallet3);


        $data_wallet4 = [
            'balance_type' =>  2,
            'wallet_id' => $to_currency->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $to_currency->change_balance,
            'change' => $num,
            'after' => bc_add($to_currency->change_balance, $num, 5),
        ];
        //增加买方的币
        $to_currency->change_balance = bc_add($to_currency->change_balance, $num, 5);
        $to_currency->save();

        AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => $num,
            'info' => "买方增加",
            'type' => AccountLog::TRANSACTIONIN_REDUCE_ADD,
            'currency' => $currency_id
        ],$data_wallet4);

        if ($num >= $out->number) {
            $out->delete();
        } else {
            $out->number = bc_sub($out->number, $num, 5);
            $out->save();
        }

        //插入完成记录
        $complete = new TransactionComplete();
        $complete->way = 2;
        $complete->user_id = $user->id;
        $complete->from_user_id = $out->user_id;
        $complete->price = $out->price;
        $complete->number = $num;
        $complete->currency = $currency_id;
        $complete->legal = $legal_id;
        $complete->create_time = time();
        $complete->save();
        //插入交易行情数据表
        $total = TransactionComplete::where('currency', $currency_id)
                                ->where('legal', $legal_id)
                                ->where('create_time', '>=', strtotime(date('Y-m-d')))
                                ->sum('number');
        $data = [
            'legal_id' => $legal_id,
            'currency_id' => $currency_id,
            'volume' => $total,
            'now_price' => $out->price
        ];
        CurrencyQuotation::updateTodayPriceTable($data);//
        MarketHour::batchWriteMarketData($currency_id, $legal_id, $num, $out->price, 1);
    }
}
