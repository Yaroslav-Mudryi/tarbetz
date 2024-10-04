<?php

namespace App\Console;

use App\Console\Commands\BinanceGetStatus;
use App\Console\Commands\BlockIoIPN;
use App\Console\Commands\Cron;
use App\Console\Commands\FetchMatch;
use App\Console\Commands\PayoutCryptoCurrencyUpdateCron;
use App\Console\Commands\PayoutCurrencyUpdateCron;
use App\Console\Commands\CronAPI;  // Add your new command here
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
        BlockIoIPN::class,
        Cron::class,
        FetchMatch::class,
        BinanceGetStatus::class,
        PayoutCryptoCurrencyUpdateCron::class,
        PayoutCurrencyUpdateCron::class,
        CronAPI::class,  // Register your new command here
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $basicControl = basicControl();
        if ($basicControl->currency_layer_auto_update == 1) {
            $schedule->command('payout-currency:update')
                ->{basicControl()->currency_layer_auto_update_at}();
        }
        if ($basicControl->coin_market_cap_auto_update == 1) {
            $schedule->command('payout-crypto-currency:update')->{basicControl()->coin_market_cap_auto_update_at}();
        }

        $blockIoGateway = Gateway::where(['code' => 'blockio', 'status' => 1])->count();
        if ($blockIoGateway == 1) {
            $schedule->command('blockIo:ipn')->everyThirtyMinutes();
        }

        $schedule->command('cron:status')->hourly();
        $schedule->command('payout-status:update')->everyFiveMinutes();
        $schedule->command('cron:fetch');
        $schedule->command('cron:match-result');

        // Schedule your new command here, for example to run daily
        $schedule->command('cronapi:run')->everyTwoMinutes();  // Adjust the schedule as needed
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
