<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class CoinTrade extends Model
{
    public $table = 'coin_trade';
    const TRADE_TYPE_SELL = 2;
    const TRADE_TYPE_BUY = 1;
    public static function newTrade($userId,$type,$currencyId,$legalId,$amount,$tradePrice,$targetPrice){
        $fee = Setting::getValueByKey('COIN_TRADE_FEE');
        $tmp = new self();
        $tmp->u_id = $userId;
        $tmp->currency_id = $currencyId;
        $tmp->legal_id = $legalId;
        $tmp->type = $type;
        $tmp->target_price = $targetPrice;
        $tmp->trade_price = $tradePrice;
        $tmp->trade_amount = $amount;
        $tmp->charge_fee = $fee;
        $tmp->save();
        return $tmp;
    }
}
