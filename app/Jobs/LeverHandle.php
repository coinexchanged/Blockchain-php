<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\LeverTransaction;

class LeverHandle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        extract($this->params);
        //价格大于0才做更新处理
        if (bc_comp($now_price, '0') > 0) {
            LeverTransaction::tradeHandle($legal_id, $currency_id, $now_price, $now);
        } else {
//            echo '法币id:' . $this->legal_id . ',交易币id:' .$this->currency_id . '当前行情价格异常' . PHP_EOL;
        }
    }
}
