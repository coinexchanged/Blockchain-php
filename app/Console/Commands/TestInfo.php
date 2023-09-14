<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:My {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '我的测试项目';

    protected $arg='';
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
        //
        echo 'hehe'.$this->argument('name');;
    }
}
