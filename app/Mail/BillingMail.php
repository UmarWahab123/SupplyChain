<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\BillingConfiguration;
use App\User;
use App\Models\Common\Configuration;
use Carbon\Carbon;


class BillingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = config('app.mail_username');
        $company_info = Configuration::select(['company_name', 'logo'])->first();
        $billing = BillingConfiguration::where('status', 1)->first();
        if ($billing->type == 'annual') {
            $billing->mail_date = Carbon::now();
            $billing->save();
        }

        $subject = '';
        $ammountDue = '';
        $active_users = '';
        if ($billing->type == 'annual') {
            $subject = 'Annual User Fee Notification';
        }
        else{
            $active_users = User::where('status', 1)->count();
            // $active_users = User::count();
            $ammountDue = ($active_users - $billing->no_of_free_users) * $billing->monthly_price_per_user;
            $subject = 'Monthly User Price Notification';
        }

        return $this->from($email)
                ->subject($subject)
                ->view('emails.billing_email', compact('billing', 'ammountDue', 'company_info', 'active_users'))
                ->attach(storage_path('app/users-login-history-export.xlsx'), [
                    'as' => 'UserReport.xlsx',
                    'mime' => 'application/xlsx',
                ]);
    }
}
