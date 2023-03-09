<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Imports\POBulkImport;
use Excel;
use Auth;
use App\ImportFileHistory;
use Illuminate\Support\Facades\Storage;

class BulkUploadPOJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $fileName = null;
    protected $user_id = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileName, $user_id)
    {
        $this->fileName = $fileName;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = Storage::disk('bulk_uploads')->url('/'.$this->fileName);
        $import = new POBulkImport($this->user_id);
        Excel::import($import, $file);
        ImportFileHistory::insertRecordIntoDb($this->user_id,'Add Bulk POs',$this->fileName);
        Storage::delete($file);
    }
}
