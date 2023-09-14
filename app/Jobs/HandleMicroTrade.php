<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use App\Logic\MicroTradeLogic;

class HandleMicroTrade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $klineData = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($kline_data)
    {
        $this->klineData = $kline_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        echo Carbon::now()->toDateTimeString() . PHP_EOL;
       

        $match_id = $this->klineData['match_id'];
        $now_price = $this->klineData['close'];
        
        MicroTradeLogic::newPrice($match_id, $now_price);
        MicroTradeLogic::close($match_id);

    }
}
