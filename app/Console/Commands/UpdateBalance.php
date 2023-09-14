<?php

/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\UsersWallet;
use App\Jobs\UpdateBalance as UpdateBalanceJob;

class UpdateBalance extends Command
{
    protected $signature = 'update_balance';
    protected $description = '更新用户余额';

    public function handle()
    {
        $this->comment("开始执行");
        UsersWallet::chunk(100, function ($wallets) {
            $wallets->each(function ($item, $key) {
                UpdateBalanceJob::dispatch($item)->onQueue('update:block:balance');
            });
        });
        $this->comment("执行完成");
    }
}
