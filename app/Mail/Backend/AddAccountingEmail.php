<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddAccountingEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $accounting   = null;
    public $password     = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($accounting, $password, $template)
    {
        $this->accounting   = $accounting;
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
        $accounting  = $this->accounting;
        $password    = $this->password;
        $template    = $this->template;
        return $this->subject(@$template->subject) 
                    ->view('users.emails.add_accounting_email', compact('accounting', 'password', 'template'));

    }
}
