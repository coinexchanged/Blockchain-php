<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\MarketHour;

class EsearchMarket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $marketData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($market_data)
    {
        $this->marketData = $market_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            MarketHour::getAndSetEsearchMarket($this->marketData);
        } catch (\Exception $e) {
            var_dump($e->getFile());
            var_dump($e->getLine());
            var_dump($e->getMessage());
        }
    }
}
