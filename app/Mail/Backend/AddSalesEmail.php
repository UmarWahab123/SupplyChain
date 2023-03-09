<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddSalesEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $sales        = null;
    public $password     = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sales, $password, $template)
    {
        $this->sales        = $sales;
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
        $sales       = $this->sales;
        $password    = $this->password;
        $template    = $this->template;
        return $this->subject($template->subject) 
                    ->view('users.emails.add_sales_email', compact('sales', 'password', 'template'));

    }
}
