<?php

/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use App\AccountLog;
use App\Currency;
use App\Level;
use App\Users;
use App\UsersWallet;
use App\Setting;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    protected $signature = 'Testtest';
    protected $description = '测试';


    public function handle()
    {

        $this->comment("start");

        Users::rebate(357,357,3,100,1,2);
        $this->comment("end");
    }



}
