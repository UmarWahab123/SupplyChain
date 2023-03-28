<?php

namespace App\Jobs;

use App\ExportStatus;
use App\Exports\ProductSaleReportByMonthExport;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\TableHideColumn;
use App\Variable;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Auth;


class ProductSaleReportByMonthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $auth_id;
    protected $role_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $auth_id, $role_id)
    {
        $this->request = $request;
        $this->auth_id = $auth_id;
        $this->role_id = $role_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $request = $this->request;
            $auth_id = $this->auth_id;
            $role_id = $this->role_id;
            $months = explode(" ", $request['months']);
            $sale_year = $request['year_filter'];

            $products = OrderProduct::from('order_products as op');
            if ($request['order_status'] == 2) {
                $products = $products->select(DB::raw('coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.number_of_pieces
                END),0) AS jan_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.number_of_pieces
                END),0) AS feb_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.number_of_pieces
                END),0) AS mar_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.number_of_pieces
                END),0) AS apr_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.number_of_pieces
                END),0) AS may_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.number_of_pieces
                END),0) AS jun_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.number_of_pieces
                END),0) AS jul_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.number_of_pieces
                END),0) AS aug_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.number_of_pieces
                END),0) AS sep_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.number_of_pieces
                END),0) AS oct_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.number_of_pieces
                END),0) AS nov_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.number_of_pieces
                END),0) AS dec_totalAmount
                '),
                'op.id', 'op.product_id')->where('o.primary_status', 2);
            }
            else if ($request['order_status'] == 3)
            {
                $products = $products->select(DB::raw('coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.pcs_shipped
                END),0) AS jan_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.pcs_shipped
                END),0) AS feb_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.pcs_shipped
                END),0) AS mar_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.pcs_shipped
                END),0) AS apr_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.pcs_shipped
                END),0) AS may_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.pcs_shipped
                END),0) AS jun_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.pcs_shipped
                END),0) AS jul_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.pcs_shipped
                END),0) AS aug_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.pcs_shipped
                END),0) AS sep_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.pcs_shipped
                END),0) AS oct_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.pcs_shipped
                END),0) AS nov_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.pcs_shipped
                END),0) AS dec_totalAmount
                '),
                'op.id', 'op.product_id')->where('o.primary_status', 3);
            }
            else{
                $products = $products->select(DB::raw('coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.number_of_pieces
                END),0)  +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.qty_shipped
                END),0)  +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.pcs_shipped
                END),0) AS jan_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.pcs_shipped
                END),0) AS feb_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.pcs_shipped
                END),0) AS mar_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.pcs_shipped
                END),0) AS apr_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.pcs_shipped
                END),0) AS may_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.pcs_shipped
                END),0) AS jun_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.pcs_shipped
                END),0) AS jul_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.pcs_shipped
                END),0) AS aug_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.pcs_shipped
                END),0) AS sep_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.pcs_shipped
                END),0) AS oct_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.pcs_shipped
                END),0) AS nov_totalAmount,
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.quantity
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.number_of_pieces
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.qty_shipped
                END),0) +
                coalesce(sum(CASE
                WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.pcs_shipped
                END),0) AS dec_totalAmount
                '),
                'op.id', 'op.product_id')->whereIn('o.primary_status', [2,3]);
            }
            // if ($request['order_status'] = 2) {
            //     $products = Product::
            //     select(DB::raw('coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.number_of_pieces
            //     END),0) AS jan_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.number_of_pieces
            //     END),0) AS feb_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.number_of_pieces
            //     END),0) AS mar_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.number_of_pieces
            //     END),0) AS apr_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.number_of_pieces
            //     END),0) AS may_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.number_of_pieces
            //     END),0) AS jun_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.number_of_pieces
            //     END),0) AS jul_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.number_of_pieces
            //     END),0) AS aug_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.number_of_pieces
            //     END),0) AS sep_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.number_of_pieces
            //     END),0) AS oct_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.number_of_pieces
            //     END),0) AS nov_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.number_of_pieces
            //     END),0) AS dec_totalAmount
            //     '),
            //     'products.refrence_code',
            //     'products.brand',
            //     'products.short_desc',
            //     'u.title', 'products.id')->where('o.primary_status', 2);
            // }
            // else if ($request['order_status'] = 3) {
            //     $products = Product::
            //     select(DB::raw('coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.pcs_shipped
            //     END),0) AS jan_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.pcs_shipped
            //     END),0) AS feb_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.pcs_shipped
            //     END),0) AS mar_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.pcs_shipped
            //     END),0) AS apr_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.pcs_shipped
            //     END),0) AS may_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.pcs_shipped
            //     END),0) AS jun_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.pcs_shipped
            //     END),0) AS jul_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.pcs_shipped
            //     END),0) AS aug_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.pcs_shipped
            //     END),0) AS sep_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.pcs_shipped
            //     END),0) AS oct_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.pcs_shipped
            //     END),0) AS nov_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.pcs_shipped
            //     END),0) AS dec_totalAmount
            //     '),
            //     'products.refrence_code',
            //     'products.brand',
            //     'products.short_desc',
            //     'u.title', 'products.id')->where('o.primary_status', 3);
            // }
            // else{
            //     $products = Product::
            //     select(DB::raw('coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.number_of_pieces
            //     END),0)  +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.qty_shipped
            //     END),0)  +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.pcs_shipped
            //     END),0) AS jan_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.pcs_shipped
            //     END),0) AS feb_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.pcs_shipped
            //     END),0) AS mar_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.pcs_shipped
            //     END),0) AS apr_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.pcs_shipped
            //     END),0) AS may_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.pcs_shipped
            //     END),0) AS jun_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.pcs_shipped
            //     END),0) AS jul_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.pcs_shipped
            //     END),0) AS aug_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.pcs_shipped
            //     END),0) AS sep_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.pcs_shipped
            //     END),0) AS oct_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.pcs_shipped
            //     END),0) AS nov_totalAmount,
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.quantity
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.number_of_pieces
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.qty_shipped
            //     END),0) +
            //     coalesce(sum(CASE
            //     WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.pcs_shipped
            //     END),0) AS dec_totalAmount
            //     '),
            //     'products.refrence_code',
            //     'products.brand',
            //     'products.short_desc',
            //     'u.title', 'products.id')->whereIn('o.primary_status', [2,3]);
            // }

            $products = $products->with('product:id,refrence_code,brand,short_desc,selling_unit,supplier_id', 'product.sellingUnits:id,title');
            $products = $products->leftJoin('orders as o', 'op.order_id', '=', 'o.id');

            // $products = $products->whereYear('o.converted_to_invoice_on',$sale_year)->groupBy('products.id');
            // $products->leftJoin('order_products AS op','products.id','=','op.product_id')->join('orders as o', 'op.order_id', '=', 'o.id')->join('customers as c', 'o.customer_id', '=', 'c.id')->join('units as u', 'products.selling_unit', '=', 'u.id');
            // dd($products->toSql());

            if($request['customer_categories'] != null)
            {
                $id_split = explode('-', $request['customer_categories']);
                if ($id_split[0] == 'pri') {
                    $products = $products->join('customers as c', 'o.customer_id', '=', 'c.id');
                    $products = $products->where('c.category_id',$id_split[1]);
                }
                else{
                    $products = $products->where('o.customer_id',$id_split[1]);
                }
            }


            // if($request['customer_categories'] != null)
            // {
            //     $id_split = explode('-', $request['customer_categories']);
            //     if ($id_split[0] == 'pri') {
            //         $products = $products->where('c.category_id',$id_split[1]);
            //     }
            //     else{
            //         $products = $products->where('o.customer_id',$id_split[1]);
            //     }
            // }

            if($request['supplier'] != null)
            {
                // $products = $products->where('op.supplier_id',$request['supplier']);
                $products = $products->leftJoin('products as prod', 'op.product_id', '=', 'prod.id')->where('prod.supplier_id', $request['supplier']);
            }

            if($request['product'] != null)
            {
                // $products = $products->where('products.id',$request['product']);
                $products = $products->where('op.product_id',$request['product']);
            }

            if($request['product_category'] != null)
            {
                $id_split = explode('-', $request['product_category']);
                $products = $products->join('products as p', 'p.id', '=', 'op.product_id');
                if ($id_split[0] == 'pri') {
                    $products = $products->where('p.primary_category',$id_split[1]);
                }
                else{
                    $products = $products->where('p.category_id',$id_split[1]);
                }
            }

            // if($request['product_category'] != null)
            // {
            //     $id_split = explode('-', $request['product_category']);
            //     if ($id_split[0] == 'pri') {
            //         $products = $products->where('products.primary_category',$id_split[1]);
            //     }
            //     else{
            //         $products = $products->where('products.category_id',$id_split[1]);
            //     }
            // }

            if($request['sale_person'] != null)
            {
              // $products->where('o.user_id',$request['sale_person']);
                $products->where('o.user_id',$request->sale_person);
            }
            else
            {
              $products->where('o.dont_show',0);
            }

            if($request['sale_person_filter'] != null )
            {
              if($auth_id != $request['sale_person_filter'])
              {
                $products = $products->where('o.user_id',$request['sale_person_filter']);
              }
              else
              {
                $products = $products->where(function($q){
                  $q->where('o.user_id', $auth_id);
                });
              }
            }
            elseif($role_id == 3)
            {
              $user_i = $auth_id;
              $products = $products->where(function($q){
                $q->where('o.user_id',$auth_id);
              });
            }
            
            // if($request['sale_person_filter'] != null )
            // {
            //   if($auth_id != $request['sale_person_filter'])
            //   {
            //     $products = $products->where('o.user_id',$request['sale_person_filter']);
            //   }
            //   else
            //   {
            //     $products = $products->where(function($q) use ($auth_id){
            //       $q->where('o.user_id',$auth_id);
            //     });
            //   }
            // }
            // elseif($role_id == 3)
            // {
            //   $products = $products->where(function($q) use ($auth_id){
            //     $q->where('o.user_id',$auth_id);
            //   });
            // }

            $products = $products->where('is_billed', 'Product')->whereYear('o.converted_to_invoice_on',$sale_year)->groupBy('op.product_id');

            /*********************  Sorting code ************************/
            // if($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'p.refrence_code';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'p.refrence_code';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 2 && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'p.brand';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 2 && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'p.brand';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'p.short_desc';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'p.short_desc';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'u.title';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'u.title';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Jan' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'jan_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Jan' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'jan_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Feb' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'feb_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Feb' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'feb_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Mar' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'mar_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Mar' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'mar_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Apr' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'apr_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Apr' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'apr_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'May' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'may_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'May' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'may_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Jun' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'jun_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Jun' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'jun_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Jul' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'jul_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Jul' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'jul_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Aug' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'aug_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Aug' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'aug_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Sep' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'sep_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Sep' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'sep_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Oct' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'oct_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Oct' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'oct_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Nov' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'nov_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Nov' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'nov_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'] == 'Dec' && $request['sortbyvalue'] == 1)
            // {
            //   $sort_variable  = 'dec_totalAmount';
            //   $sort_order     = 'DESC';
            // }
            // elseif($request['sortbyparam'] == 'Dec' && $request['sortbyvalue'] == 2)
            // {
            //   $sort_variable  = 'dec_totalAmount';
            //   $sort_order     = 'ASC';
            // }

            // if($request['sortbyparam'])
            // {
            //   $products->orderBy($sort_variable, $sort_order);
            // }
            // else{
            //     $products->orderBy('products.id', 'asc');
            // }

            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $sort_variable = null;
            $product_sort = null;
            if($request['sortbyparam'] == 1)
            {
                $product_sort  = 'p.refrence_code';
            }
            else if($request['sortbyparam'] == 2)
            {
                $product_sort  = 'p.brand';
            }
            else if($request['sortbyparam'] == 3)
            {
                $product_sort  = 'p.short_desc';
            }
            else if($request['sortbyparam'] == 4)
            {
                $product_sort  = 'u.title';
            }
            else if($request['sortbyparam'] == 'Jan')
            {
                $sort_variable  = 'jan_totalAmount';
            }
            else if($request['sortbyparam'] == 'Feb')
            {
                $sort_variable  = 'feb_totalAmount';
            }
            else if($request['sortbyparam'] == 'Mar')
            {
                $sort_variable  = 'mar_totalAmount';
            }
            else if($request['sortbyparam'] == 'Apr')
            {
                $sort_variable  = 'apr_totalAmount';
            }
            else if($request['sortbyparam'] == 'May')
            {
                $sort_variable  = 'may_totalAmount';
            }
            else if($request['sortbyparam'] == 'Jun')
            {
                $sort_variable  = 'jun_totalAmount';
            }
            else if($request['sortbyparam'] == 'Jul')
            {
                $sort_variable  = 'jul_totalAmount';
            }
            else if($request['sortbyparam'] == 'Aug')
            {
                $sort_variable  = 'aug_totalAmount';
            }
            else if($request['sortbyparam'] == 'Sep')
            {
                $sort_variable  = 'sep_totalAmount';
            }
            else if($request['sortbyparam'] == 'Oct')
            {
                $sort_variable  = 'oct_totalAmount';
            }
            else if($request['sortbyparam'] == 'Nov')
            {
                $sort_variable  = 'nov_totalAmount';
            }
            else if($request['sortbyparam'] == 'Dec')
            {
                $sort_variable  = 'dec_totalAmount';
            }
            else
            {
                $products->orderBy('op.product_id', 'asc');
            }

            if($product_sort != null)
            {
                $products = $products->join('products as p', 'p.id', '=', 'op.product_id');
                if ($product_sort == 'u.title') {
                    $products = $products->join('units as u', 'p.selling_unit', '=', 'u.id');
                }
                $products = $products->orderBy($product_sort, $sort_order);
            }
            if($sort_variable)
            {
                $products->orderBy($sort_variable, $sort_order);
            }
            
            $vairables       = Variable::select('slug','standard_name','terminology')->get();

            $global_terminologies = [];
            foreach($vairables as $variable)
            {
                if($variable->terminology != null)
                {
                  $global_terminologies[$variable->slug]=$variable->terminology;
                }
                else
                {
                  $global_terminologies[$variable->slug]=$variable->standard_name;
                }
            }
             $current_date = date("Y-m-d");
            $filename='Product-Sale-Report-By-Month-'.$auth_id.'-'.$current_date.'.xlsx';
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','product_sales_report_by_month')->where('user_id',$auth_id)->first();
            if($not_visible_columns!=null)
            {
              $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
            }
            else
            {
              $not_visible_arr=[];
            }

            $return= \Excel::store(new ProductSaleReportByMonthExport($products, $months,$not_visible_arr, $global_terminologies), $filename);
            if($return)
            {
                ExportStatus::where('user_id',$auth_id)->where('type','product_sales_report_by_month')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s'),'file_name'=>$filename]);
               return response()->json(['msg'=>'File Saved']);
            } 
          }      
        catch(Exception $e) {
            $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }
    public function failed($exception)
    {
       
        ExportStatus::where('type','product_sales_report_by_month')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Product Sale Report By Month";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
       
    }
}
