<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddSalesCoordinatorEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $sales_co     = null;
    public $password     = null;
    public $template     = null;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sales_co, $password, $template)
    {
        $this->sales_co     = $sales_co;
        $this->password     = $password;
        $this->template     = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $sales_co    = $this->sales_co;
        $password    = $this->password;
        $template    = $this->template;
        return $this->subject(@$template->subject) 
                    ->view('users.emails.add_sales_coordinator_email', compact('sales_co', 'password', 'template'));

    }
}
