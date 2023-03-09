<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddImportingEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $importing    = null;
    public $password     = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($importing, $password, $template)
    {
        $this->importing    = $importing;
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
        $importing   = $this->importing;
        $password    = $this->password;
        $template    = $this->template;
        return $this->subject(@$template->subject) 
                    ->view('users.emails.add_importing_email', compact('importing', 'password', 'template'));

    }
}
