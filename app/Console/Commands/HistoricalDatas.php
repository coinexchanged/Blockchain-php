<?php
/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use App\AccountLog;
use App\Setting;
use App\Users;
use App\HistoricalData;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HistoricalDatas extends Command
{
    protected $signature = 'historical_data';
    protected $description = '历史数据';


    public function handle()
    {
        DB::beginTransaction();
        try {
            $day = intval(date("d",time()));
            $week = date("w");

            $yesterday_start = date("Y-m-d",strtotime("-1 day"));
            $yesterday_start = strtotime($yesterday_start);
            $yesterday_end = $yesterday_start + 24 * 60 * 60;
            $aaa = HistoricalData::insertData($yesterday_start,$yesterday_end);

            //星期一
            if($week == "1"){
                $week_start = date("Y-m-d",strtotime("last Monday"));
                $week_start = strtotime($week_start);

                HistoricalData::insertData($week_start,time(),"week");
            }
            //每个月的一号
            if($day == 1){
                $month_start = date("Y-m-d",strtotime("last month"));
                $month_start = strtotime($month_start);

                HistoricalData::insertData($month_start,time(),"month");
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }


}
