<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Imports\CustomerPOBulkImport;
use Excel;
use Auth;
use App\ImportFileHistory;
use Illuminate\Support\Facades\Storage;

class CustomerBulkUploadPOJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileName = null;
    protected $user_id = null;
    protected $user = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileName, $user_id, $user)
    {
        $this->fileName = $fileName;
        $this->user_id = $user_id;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = Storage::disk('temp')->url('/'.$this->fileName);
        $import = new CustomerPOBulkImport($this->user_id, $this->user);
        Excel::import($import, $file);
        ImportFileHistory::insertRecordIntoDb($this->user_id,'Add Bulk POs',$this->fileName);
        return true;
    }
}
