<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\DAO\BlockChain;

class UpdateBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $wallet;
    protected $noBalanceContinue = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($wallet, $no_balance_continue = false)
    {
        $this->wallet = $wallet;
        $this->noBalanceContinue = $no_balance_continue;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BlockChain::updateWalletBalance($this->wallet, $this->noBalanceContinue);
    }
}
