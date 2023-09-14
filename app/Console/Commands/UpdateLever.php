<?php

/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\{LeverTransaction};
use App\Jobs\LeverClose;

class UpdateLever extends Command
{
    protected $signature = 'remove_task';
    protected $description = '移除积压任务';

    public function handle()
    {
        $this->comment("开始任务");
        // $lever = LeverTransaction::where('status', LeverTransaction::CLOSING)
        //    ->get();
        // $task_list = $lever->pluck('id')->all();
        // // dd($task_list);
        
        // if (!empty($task_list)) {
        //     LeverClose::dispatch($task_list, true)->onQueue('lever:close');
        // }

        \Illuminate\Support\Facades\Redis::del('queues:lever:update');

        $this->comment("结束任务");
    }

    
}
