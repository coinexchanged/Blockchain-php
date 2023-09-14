<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DAO\FactprofitsDAO;
use Illuminate\Support\Facades\DB;

class ReturnProfit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'return:profit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '返还杠杆交易亏损';

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
        $aaa=new FactprofitsDAO();
        $all = DB::table('lever_transaction')->select("user_id")->groupBy('user_id')->get();
        foreach($all as $key=>$value)
        {
            var_dump($value->user_id);
            var_dump($aaa::Profit_loss_release($value->user_id));
        }
    }
}
