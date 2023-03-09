<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Common\Configuration;
use Illuminate\Support\Facades\Mail;
use App\UserLoginHistory;
use App\Exports\UserLoginHistoryExport;
use App\Mail\BillingMail;
use App\BillingConfiguration;
use Carbon\Carbon;
use App\User;


class AnnualMonthlyEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command send emails according to billing configurations';

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
        $billing = BillingConfiguration::where('status', 1)->first();
        $active_users = User::where('status', 1)->whereNull('parent_id')->count();
        if($billing->official_launch_date != null)
        {
            $from = Carbon::parse($billing->official_launch_date);
            $to = Carbon::now();
            if ($billing->type == 'annual' && $to->diffInYears($from) > 1) {
               $from = date("Y-m-d", strtotime ( '-1 year'));
               $this->sendMail($from, $to);
            }
            else if ($billing->type == 'monthly' && $to->diffInMonths($from) > 1 && $billing->no_of_free_users != null && $active_users > $billing->no_of_free_users) {
                $from = date("Y-m-d", strtotime ( '-1 month'));
               $this->sendMail($from, $to);
            }
        }

    }

    public function sendMail($from, $to)
    {
        $query = UserLoginHistory::with('user_detail')->where('last_login', '>=', $from)->where('last_login', '<=', $to)->orderBy('id', 'DESC')->get();

        $configurations = Configuration::select('billing_email')->first();

        \Excel::store(new UserLoginHistoryExport($query), 'users-login-history-export.xlsx');
        Mail::to($configurations->billing_email)->send(new BillingMail());
    }
}
    