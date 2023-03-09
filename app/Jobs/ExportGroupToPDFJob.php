<?php

namespace App\Jobs;

use App\FailedJobException;
use App\Helpers\POGroupSortingHelper;
use App\Models\Common\Company;
use App\Models\Common\Configuration;
use App\Models\Common\PoGroupProductDetail;
use App\PdfsStatus;
use App\User;
use App\Variable;
use Auth;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use PDF;

class ExportGroupToPDFJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    public $timeout=3600;
    protected $user_id;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id)
    {
        $this->data=$data;
        $this->user_id=$user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

        $vairables=Variable::select('slug','standard_name','terminology')->get();
        $global_terminologies=[];
        foreach($vairables as $variable)
        {
            if($variable->terminology != null)
            {
                $global_terminologies[$variable->slug]=$variable->terminology;
            }else{
                $global_terminologies[$variable->slug]=$variable->standard_name;
            }
        }
        $data=$this->data;
        $user_id=$this->user_id;
        $user = User::select('id','company_id')->where('id',$this->user_id)->first();
        $company_info = Company::where('id',$user->company_id)->first();

        $group_detail = PoGroupProductDetail::where('po_group_product_details.status',1)->where('po_group_product_details.po_group_id',$data['po_group_id']);
        if($data['po_group_supplier_id'] != null)
        {
            $group_detail = $group_detail->where('po_group_product_details.supplier_id',$data['po_group_supplier_id']);
        }
        if($data['po_group_product_id'] != null)
        {
            $group_detail = $group_detail->where('po_group_product_details.product_id',$data['po_group_product_id']);
        }
        if ($data['blade'] == 'receiving_queue_details' && isset($data['sort_order']))
        {
            $group_detail = POGroupSortingHelper::WarehouseProductRecevingRecordsSorting($data, $group_detail);
        }
        else if(isset($data['sort_order']))
        {
            $group_detail = POGroupSortingHelper::ProductReceivingRecordsSorting($data, $group_detail);
        }
        $group_detail = $group_detail->get();
        $configuration = Configuration::first();
		$pdf = PDF::loadView('warehouse.products.completed_group_print_pdf',compact('group_detail','global_terminologies','company_info','configuration'))->setPaper('a4', 'landscape');
              $pdf->getDomPDF()->set_option("enable_php", true);
        // making pdf name starts

        $makePdfName = 'Group-No-'.$data['group_ref_id'];
        $path=public_path('uploads/system_pdfs');
        if(!is_dir($path))
        {
            mkdir($path,0755,true);
        }
        $pdf->save($path.'/'.$makePdfName.'.pdf');
        PdfsStatus::where('user_id',$user_id)->where('group_id',$data['po_group_id'])->delete();
        } catch(Exception $e) {
            dd($e);
            $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }
    public function failed($exception)
    {
        PdfsStatus::where('user_id',$this->user_id)->where('group_id',$this->data['po_group_id'])->update(['status'=>2]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Products Receiving Importing";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
