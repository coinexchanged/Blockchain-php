<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Users;
use App\UsersWallet;

class MakeWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:wallet {user_id? : user_id} {--operate=single : the operation type:all,single}';//operate = single

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成钱包';

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
        $userId = $this->argument('user_id');
        $operateName = $this->option('operate');
        if($operateName == 'all'){
            $this->info("给全部用户生成钱包");
            Users::chunk(1000, function ($users) {
                foreach ($users as $key => $user) {
                    UsersWallet::makeWallet($user->id);
                    $this->info('用户id' . $user->id . '生成钱包完成');
                }
            });
        }else if($operateName == 'single'){
            $this->info("给单个用户生成钱包");
            $user = Users::getById($userId);
            if(empty($user)){
                $this->info("错误的用户id");
            }else{
                $res = UsersWallet::makeWallet($userId);
                if($res){
                    $this->info("用户{$user->id},生成成功！");
                }else{
                    $this->error("用户{$user->id},生成失败！");
                }

            }
        }else{
            $this->error("参数错误");
            return;
        }
        $this->info('全部生成完成');
    }
}
