<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Users;

class UpdateParent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:parent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新用户上级';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Users::where('type', 1)->chunk(10000, function ($users) {
            foreach ($users as $key => $user) {
                $parent = Users::where('origin_user_id', $user->origin_parent_id)->first();
                if (!$parent) {
                    continue;
                }
                $user->parent_id = $parent->id;
                $user->save();
                $this->info('更新用户' . $user->id . '的上级完成');
            }
        });
        $this->info('全部执行完成');
    }
}
