<?php

namespace App\Jobs;

use App\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use function GuzzleHttp\json_encode;

class UpdateCurrencyPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $kline_data;

    /**
     * Create a new job instance.
     *
     *
     * @return void
     */
    public function __construct($kline_data)
    {
        //
        $this->kline_data = $kline_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        if (empty($this->kline_data)) {
            return 0;
        }
        $currency_id = $this->kline_data['currency_id'];
        $currency = Currency::find($currency_id);

        if ($currency) {
            $currency->price = $this->kline_data['close'];
            $currency->save();
        }
    }
}
