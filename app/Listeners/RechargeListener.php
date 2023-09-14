<?php

namespace App\Listeners;

use App\Events\RechargeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\DAO\UserDAO;
use App\AccountLog;
use App\Users;

class RechargeListener
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
     * @param  RechargeEvent  $event
     * @return void
     */
    public function handle(RechargeEvent $event)
    {
        list('user_id' => $user_id, 'scene' => $scene, 'number' => $number) =  $event->getEventParam();
        //只有从商家购买法币和链上充值才累计团队充值金额,并且充值金额只累加不减少
        if (in_array($scene, [
                AccountLog::LEGAL_USER_BUY,
                AccountLog::ETH_EXCHANGE,
            ]) && bc_comp($number, 0) > 0) {
            UserDAO::updateTopUpnumber($user_id, $number);
        }
        $user = Users::find($user_id);
        UserDAO::checkUpgradeAtelierCondition($user);
    }
}
