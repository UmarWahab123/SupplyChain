<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserAccountSuspensionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user       = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $template)
    {
        $this->user   = $user;
        $this->template  = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user        = $this->user;
        $template    = $this->template;
        return $this->subject($template->subject) 
                    ->view('users.emails.user_account_suspension_email', compact('user', 'template'));
    }
}
