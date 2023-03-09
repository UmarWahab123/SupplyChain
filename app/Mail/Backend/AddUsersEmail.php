<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddUsersEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $user_role   = null;
    public $password     = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_role, $password, $template)
    {
        $this->user_role   = $user_role;
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
        $user_role  = $this->user_role;
        $password    = $this->password;
        $template    = $this->template;
        return $this->subject(@$template->subject) 
                    ->view('users.emails.add_users_email', compact('user_role', 'password', 'template'));
    }
}
