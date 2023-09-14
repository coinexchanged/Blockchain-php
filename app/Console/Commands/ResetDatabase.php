<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset_database {force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理数据库';

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

        $force = $this->argument('force');
        if($force === 'yes'){
            $needs_delete_table_arr = [
                'account_log','address','agent_log','agent_money_log','c2c_deal','c2c_deal_send',
                'candy_transfer','conversion','failed_jobs','false_data','feedback','flash_against',
                'historical_data','insurance_claim_applies','jobs','legal_deal','legal_deal_send','lever_transaction',
                'micro_orders','seller','transaction','transaction_complete','transaction_in','transaction_out',
                'user_algebra','user_cash_info','user_profiles','user_real','users_insurances','users_transfer_to_change',
                'users_wallet_out','wallet_log'
            ];
            $retain_table = [
                'agent' => ['select_field' => 'user_id', 'retain_id' => [1]],
                'users' => ['select_field' => 'id', 'retain_id' => [1]],
                'users_wallet' => ['select_field' => 'user_id', 'retain_id' => [1]]
            ];


            foreach ($needs_delete_table_arr as $table){
                \Schema::hasTable($table)?DB::table($table)->delete():'';
            }
            foreach ($retain_table as $table_name => $table){
                DB::table($table_name)->whereNotIn($table['select_field'] ,$table['retain_id'])->delete();
            }

            $this->info('清理成功！');
        }else{
            $this->error('命令错误！');
        }

    }
}
