<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ExportStatus;
use App\FailedJobException;
use App\User;
use App\Exports\BulkReceivedIntoStockExport;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;
use MaxAttemptsExceededException;


class BulkReceivedIntoStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $selectedIds;
    protected $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($selectedIds,$user_id)
    {
        $this->selectedIds = $selectedIds;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $selectedIds = $this->selectedIds;
        $user_id = $this->user_id;

        try {
            $query = PurchaseOrderDetail::with('customer','product','getOrder', 'PurchaseOrder:id,ref_id')->whereIn('purchase_order_details.po_id',$selectedIds)->select('purchase_order_details.*')->orderBy('po_id', 'desc');
            $filename = 'Purchase_orders_details.xlsx';

            $return=\Excel::store(new bulkReceivedIntoStockExport($query), $filename);
            if($return)
            {
                ExportStatus::where('type','waiting_confirmation_po_details')->where('user_id', $user_id)->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
        } catch(Exception $e) {
            $this->failed($e);
        }
        catch(QueueMaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
        ExportStatus::where('type', 'waiting_confirmation_po_details')->where('user_id', $this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type='waiting_confirmation_po_details';
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
