<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoricalData extends Model
{
    protected $table = 'historical_data';
    public $timestamps = false;

    public function getDataAttribute()
    {
        $data = $this->attributes['data'];
        if(!empty($data)){
            return json_decode($data,true);
        }else{
            return [];
        }
    }
    public static function insertData($start,$end,$type = "day"){
        if(empty($start) || empty($end)) return false;

        $open = 0;
        $hight = 0;
        $low = 0;
        $close = 0;
        $volume = 0;

        $opend_data = TransactionComplete::where("create_time",">",$start)->where("create_time","<",$end)->orderBy('create_time','asc')->first();
        if(!empty($opend_data)) $open = $opend_data->price;

        $hight_data = TransactionComplete::where("create_time",">",$start)->where("create_time","<",$end)->orderBy('price','desc')->first();
        if(!empty($hight_data)) $hight = $hight_data->price;

        $low_data = TransactionComplete::where("create_time",">",$start)->where("create_time","<",$end)->orderBy('price','asc')->first();
        if(!empty($low_data)) $low = $low_data->price;

        $close_data = TransactionComplete::where("create_time",">",$start)->where("create_time","<",$end)->orderBy('create_time','desc')->first();
        if(!empty($close_data)) $close = $close_data->price;

        $volume = TransactionComplete::where("create_time",">",$start)->where("create_time","<",$end)->orderBy('create_time','desc')->count();

        $data = array(
            "timestamp"=>$start,
            "open"=>$open,
            "hight"=>$hight,
            "low"=>$low,
            "close"=>$close,
            "volume"=>$volume,
        );
        $historical_data = new self();
        $historical_data->type = $type;
        $historical_data->start_time = $start;
        $historical_data->end_time = $end;
        $historical_data->data = json_encode($data);
        if($historical_data->save()){
            return true;
        }else{
            return false;
        }
    }
}
