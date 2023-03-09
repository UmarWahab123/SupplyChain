<?php

namespace App\Mail\Backend;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupplierSuspensionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $supplier       = null;
    public $template     = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($supplier, $template)
    {
        $this->supplier   = $supplier;
        $this->template  = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $supplier        = $this->supplier;
        $template        = $this->template;
        return $this->subject($template->subject) 
                    ->view('users.emails.supplier_account_suspension', compact('supplier', 'template'));
    }
}
