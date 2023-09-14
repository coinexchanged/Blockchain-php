<?php

namespace App\Listeners;

use App\Events\RealNameEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\DAO\UserDAO;
use App\UserProfile;

class RealNameListener
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
     * @param  RealNameEvent  $event
     * @return void
     */
    public function handle(RealNameEvent $event)
    {
        $user = $event->getUser();
        $user_real = $event->getUserReal();
        //UserDAO::checkUpgradeAtelierCondition($user);
        UserProfile::unguarded(function ()  use ($user, $user_real) {
            $user_profile = UserProfile::firstOrNew(['user_id' => $user->id], [
                'name' => $user_real->name,
                'card_id' => $user_real->card_id,
                'front_pic' => $user_real->front_pic,
                'reverse_pic' => $user_real->reverse_pic,
                'hand_pic' => $user_real->hand_pic,
            ]);
            $user->userProfile()->save($user_profile);
        });
        
    }
}
