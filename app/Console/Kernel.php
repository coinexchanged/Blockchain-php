<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Locking::class,
        Commands\BonusAlgorithm::class,
        Commands\MonitorEthLog::class,
        Commands\HistoricalDatas::class,
        Commands\AutoCancelLegal::class,
        Commands\UpdateBalance::class,
        Commands\MakeOneWallet::class,
        //Commands\UpdateSortNum::class,
        Commands\AutoCancelC2C::class,
        Commands\AutoCancelC2CDeal::class,
        Commands\ReturnProfit::class,
        Commands\CancelC2ctime::class,
        Commands\OvernightFee::class,
        Commands\UserLevel::class,
        Commands\UpdateFund::class,
        Commands\Test::class,
        Commands\RemoveQueue::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('remove_queue')->hourly()->withoutOverlapping(); //移除积压
        $schedule->command('update_hash_status')->everyMinute()->withoutOverlapping(); //更新哈希值状态
        $schedule->command('lever:overnight')->dailyAt('00:01')->appendOutputTo('./storage/logs/lever_overnight.log'); //收取隔夜费
        $schedule->command('update_user_fund')->daily()->appendOutputTo('./storage/logs/update_user_fund.log'); //更新秒合约资产
        // $schedule->command('update_user_level')->dailyAt('01:00')->appendOutputTo('./storage/logs/update_user_level.log'); //
        // $schedule->command('return:profit')->dailyAt('00:10')->appendOutputTo('./storage/logs/return_profit.log'); //历史盈亏释放 add by tian
        $schedule->command('cancel:c2cdeal')->everyMinute()->appendOutputTo('./storage/logs/cancel_c2cdeal.log'); //c2c取消订单倒计时执行 add by tian
        $schedule->command('auto_cancel_legal')->hourly()->appendOutputTo('./storage/logs/auto_cancel_legal.log');
        // $schedule->command('update_balance')->everyTenMinutes()->withoutOverlapping()->appendOutputTo('./storage/logs/update_balance.log');
       
        $schedule->command('insurance_money')->dailyAt('00:01')->appendOutputTo('./storage/logs/insurance_money.log'); //持币生息
        $schedule->command('return_service_charge')->dailyAt('00:02')->appendOutputTo('./storage/logs/return_service_charge.log'); //返还保险交易手续费

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
