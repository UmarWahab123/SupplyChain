<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddWarehouseEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $warehouse    = null;
    public $password     = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($warehouse, $password, $template)
    {
        $this->warehouse    = $warehouse;
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
        $warehouse   = $this->warehouse;
        $password    = $this->password;
        $template    = $this->template;
        return $this->subject(@$template->subject) 
                    ->view('users.emails.add_warehouse_email', compact('warehouse', 'password', 'template'));
    }
}
