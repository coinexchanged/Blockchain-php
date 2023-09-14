<?php

namespace App\Listeners;

use App\Events\LeverSubmitOrder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\DAO\PrizePool\CandySender;
use App\PrizePool;
use App\DAO\RewardDAO;

class RewardCandy
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LeverSubmitOrder  $event
     * @return void
     */
    public function handle(LeverSubmitOrder $event)
    {
        $lever_order = $event->getLeverOrder();
        if ($lever_order->status >= 1 || $lever_order->status <= 3) {
            //执行手续费返给上级任务
            RewardDAO::rewardLeverTransationFee($lever_order);
            //执行手续费返给工作室任务
            RewardDAO::rewardLeverFeeToAtelier($lever_order);
        }
    }
}
