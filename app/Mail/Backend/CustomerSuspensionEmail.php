<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomerSuspensionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer       = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer, $template)
    {
        $this->customer   = $customer;
        $this->template  = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $customer        = $this->customer;
        $template        = $this->template;
        return $this->subject($template->subject) 
                    ->view('users.emails.customer_suspension_email', compact('customer', 'template'));
    }
}
