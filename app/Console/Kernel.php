<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Commands\CheckStatusForSoldProductReportCommand;

use App\Commands\CheckStatusForCompleteProductCommand;
use App\Commands\CheckStatusForImportingProductReceivingCommand;
use App\Commands\UpdateProductCurrency;
use App\Commands\UpdateProductMargin;
use App\Commands\AnnualMonthlyEmailCommand;
use App\Commands\WooComOrdersFetchCommand;
use App\BillingConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CheckStatusForSoldProductReportCommand::class,
        Commands\CheckStatusForCompleteProductCommand::class,
        Commands\CheckStatusForImportingProductReceivingCommand::class,
        Commands\UpdateProductCurrency::class,
        Commands\UpdateProductMargin::class,
        Commands\AnnualMonthlyEmailCommand::class,
        Commands\WooComOrdersFetchCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {


        //$schedule->command('CSVExport:CheckStatusForSoldProductReportCommand')
        // ->withoutOverlapping()
        // ->everyMinute()
        // ->appendOutputTo('storage/logs/csv_for_sold_product_report.log');

        // $schedule->command('CSVExport:CheckStatusForCompleteProductCommand')
        // ->withoutOverlapping()
        // ->everyMinute()
        // ->appendOutputTo('storage/logs/csv_for_complete_products.log');

        // $schedule->command('demo:CheckStatusForImportingProductReceivingCommand')
        // ->withoutOverlapping()
        // ->everyMinute()
        // ->appendOutputTo('storage/logs/csv_for_importing_product_receiving.log');
        $schedule->command('product_margin:UpdateProductMargin')
        ->withoutOverlapping()
        ->everyMinute()
        ->appendOutputTo('storage/logs/update_products_margin.log');

        $schedule->command('product_currency:UpdateProductCurrency')
        ->withoutOverlapping()
        ->everyMinute()
        ->appendOutputTo('storage/logs/update_product_from_currencies.log');
        if (Schema::hasTable('billing_configurations')) {
            $config = BillingConfiguration::select(['official_launch_date', 'type', 'mail_date'])->where('status', 1)->first();

        if ($config) {
            if ($config->type == 'annual') {
                $schedule->command('billing:email')
                ->withoutOverlapping()
                ->yearly()->when(function () use ($config) {
                    $date = Carbon::now();
                    return ($date->diffInMonths($config->official_launch_date) -1 == 11 || $date->diffInMonths($config->mail_date) -1 == 11) ? true : false;
                })
                ->appendOutputTo('storage/logs/billing_emails.log');
               
            }
            else{
                $day = Carbon::parse($config->official_launch_date)->format('d');
                $time = (string)Carbon::parse($config->official_launch_date)->format('H:i');
                $schedule->command('billing:email')
                ->withoutOverlapping()
                ->monthlyOn($day, $time)
                ->appendOutputTo('storage/logs/billing_emails.log'); 
            }
        }
        }


        // Woo Commerce Orders Fetch Schedular
        // $schedule->command('woocom_orders:fetch')
        // ->withoutOverlapping()
        // ->everyMinute()
        // ->appendOutputTo('storage/logs/woocom_orders.log');
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
