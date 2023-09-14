<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transaction';
    public $timestamps = false;
    const CREATED_AT = 'time';
    protected $appends = [
        'from_address',
        'to_address',
        'from_account',
        'to_account',
        'total',
        'currency_name'
    ];

    public static function createData($data)
    {
        if (empty($data)) {
            return false;
        }
        if (empty($data["from_user_id"]) || empty($data["to_user_id"]) || empty($data["number"])) {
            return false;
        }

        $transaction = new self();
        $transaction->from_user_id = $data["from_user_id"];
        $transaction->to_user_id = $data["to_user_id"];
        $transaction->type = empty($data["type"]) ? 1 : $data["type"];
        $transaction->number = $data["number"];
        $transaction->remarks = $data["remarks"];
        $transaction->time = time();
        $transaction->status = 1;

        if ($transaction->save()) {
            return true;
        } else {
            return false;
        }
    }

    public static function pushNews($currency_id, $legal_id)
    {
        $in = TransactionIn::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'desc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit(5)
            ->get()
            ->toArray();
        $out = TransactionOut::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'asc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit(5)
            ->get()
            ->toArray();

        krsort($out);
        $out_data = array();
        foreach ($out as $o) {
            array_push($out_data, $o);
        }

        $last_price = 0;
        $last = TransactionComplete::orderBy('id', 'desc')->where("currency", $currency_id)->where("legal", $legal_id)->first();
        if (!empty($last)) {
            $last_price = $last->price;
        }

        $send = array(
            "type" => "transaction",
            "in" => json_encode($in),
            "out" => json_encode($out_data),
            "last_price" => $last_price,
            "currency_id" => $currency_id,
            "legal_id" => $legal_id
        );
        return UserChat::sendChat($send);
    }

    public function getFromAddressAttribute()
    {
        return $this->hasOne('App\UsersWallet', 'user_id', 'from_user_id')->value('address');
    }
    public function getToAddressAttribute()
    {
        return $this->hasOne('App\UsersWallet', 'user_id', 'to_user_id')->value('address');
    }
    public function getFromAccountAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'from_user_id')->value('account_number');
    }
    public function getToAccountAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'to_user_id')->value('account_number');
    }
    public function getTimeAttribute()
    {
        $value = $this->attributes['time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('name');
    }
    
   //获取交易总额
    public function getTotalAttribute()
    {
        $number = $this->attributes['number'];
        $price = $this->attributes['price'];
        $tol = $number * $price;
        return $tol;
    }
}
