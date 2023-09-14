<?php

/**
 * create by vscode
 * @author lion
 */
namespace App;


use Illuminate\Database\Eloquent\Model;
use App\Utils\RPC;
use App\MarketHour;
use App\MarketDay;

class TransactionComplete extends Model
{
    protected $table = 'transaction_complete';
    public $timestamps = false;

    protected $appends = [
        'account_number',
        'time',
        'from_number',
        'currency_name',
        'legal_name'
    ];

    public function getTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getFromNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'from_user_id')->value('account_number');
    }

    public static function getQuotation($legal_id, $currency_id, $start_time, $end_time)
    {
        $return = array(
            "start_time" => "",
            "end_time" => "",
            "start_price" => 0.00,
            "end_price" => 0.00,
            "highest" => 0.00,
            "mmminimum" => 0.00,
            "number" => 0.00,
        );
        if (empty($legal_id) || empty($currency_id) || empty($start_time) || empty($end_time)) {
            return $return;
        }

        //时间段内数量查询、
        $numbers = self::where("create_time", ">", $start_time)
            ->where("create_time", "<", $end_time)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->get();
        $number = 0;
        foreach ($numbers as $key => $value) {
            $number = $number + $value->number;
        }
        $return['number'] = $number;

        $return["start_time"] = date("Y-m-d H:i:s", $start_time);
        $return["end_time"] = date("Y-m-d H:i:s", $end_time);

        $start_price = self::orderBy('create_time', 'asc')
            ->where("create_time", ">", $start_time)
            ->where("create_time", "<", $end_time)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->first();
        // dump( $start_price);
        $start_price && $return["start_price"] = $start_price->price;

        $end_price = self::orderBy('create_time', 'desc')
            ->where("create_time", ">", $start_time)
            ->where("create_time", "<", $end_time)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->first();
        $end_price && $return["end_price"] = $end_price->price;

        $highest = self::orderBy('price', 'desc')
            ->where("create_time", ">", $start_time)
            ->where("create_time", "<", $end_time)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->first();
        $highest && $return["highest"] = $highest->price;

        $mmminimum = self::orderBy('price', 'asc')
            ->where("create_time", ">", $start_time)
            ->where("create_time", "<", $end_time)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->first();
        $mmminimum && $return["mmminimum"] = $mmminimum->price;
        return $return;
    }


    //通过新的表设计生产k线图
    public static function getQuotation_market_k($legal_id, $currency_id, $start_time, $end_time,$type)
    {
        $return = array(
            "start_time" => "",
            "end_time" => "",
            "start_price" => 0.00,
            "end_price" => 0.00,
            "highest" => 0.00,
            "mmminimum" => 0.00,
            "number" => 0.00,
        );
        //var_dump($return);
        if (empty($legal_id) || empty($currency_id) || empty($start_time) || empty($end_time)) {
            return $return;
        }

        //时间段内数量查询、
        $numbers = MarketHour::where("day_time",">",$start_time)
            ->where("day_time","<",$end_time)
            ->where("currency_id",$currency_id)
            ->where("legal_id",$legal_id)
            ->first();
        if($type ==5){
             $numbers = MarketDay::where("currency_id", $currency_id)->where("legal_id", $legal_id)->get();
         }else{
            
         }
        //var_dump($numbers);
       // dump($numbers);
        // foreach ($numbers as $key => $value) {
        //     $number = $number + $value['number'];
        // }
        $return['number'] = $numbers->number;
        $return["start_time"] = date("Y-m-d H:i:s", $start_time);
        $return["end_time"] = date("Y-m-d H:i:s", $end_time);
        $return['highest']=$numbers->highest;
        $return['mminimum']=$numbers->mminimum;
        return $return;
    }


    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('name');
    }
    public function getLegalNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'legal')->value('name');
    }

    public static function getOpenPrice($currency, $legal, $time = null)
    {
        empty($time) && $time = time();
        $time = strtotime(date('Y-m-d', $time));

        do{
            $openPrice = self::where([
            ['create_time','>=',$time],
            ['currency','=',$currency],
            ['legal','=',$legal]
        ])->orderBy('create_time','asc')->first();
            $time -= 3600*24;
        }while(!$openPrice);

       if($openPrice){
           return $openPrice['price'];
       }else{
           return 1;
       }
    }


}
