<?php

use App\ExportStatus;
use App\Events\ProductCreated;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sales\ProductController;
use App\Http\Controllers\SystemConfigurationController;
use App\Http\Controllers\Backend\POSIntegrationController;
use App\Http\Controllers\Backend\SpoilageReportController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/welcome', function () {
    echo 'Welcome';
});


Route::get('/logout-user', function () {
    if (Auth::check()) {
        Auth::logout();
        $status = 'true';
        return response()->json($status);
    }
})->name('logout-user');

Route::get('/processing-status', function () {
    if (Auth::check()) {
        $status = 'true';
    } else {
        $status = url('');
    }
    return response()->json($status);
})->name('processing-status');
require_once "scripts.php";
require_once "ecom.php";

Route::get('/logout', function () {
    if (!session()->has('url.intended')) {
        session(['url.intended' => url()->previous()]);
    }
    return view('auth.login');
});


Route::get('recursive-old-data-status', function () {
    $status = ExportStatus::where('user_id', 1)->where('type', 'update_old_record')->first();
    return response()->json(['status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name, 'last_downloaded' => $status->last_downloaded]);
})->name('recursive-old-data-status');

Route::get('get-download-xslx/{file_name}', function ($file_name) {
    $file    = storage_path('app/' . $file_name);
    $headers = array(
        'Content-Type: application/xlsx',
        'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
    );
    ob_end_clean();
    return \Response::download($file, $file_name, $headers);
});

Route::get('/execution-time', function () {
    return view('execution');
});
Auth::routes(['verify' => true, 'register' => false]);

Route::group(['namespace' => 'Auth'], function () {
    Route::post('login', 'LoginController@postLogin');
    Route::get('/send-password-change-request-to-admin', 'ResetPasswordController@sendRequestToAdmin')->name('password.admin-request');
});

Route::get('/home', 'HomeController@index')->name('home')->middleware('verified');

Route::group(['namespace' => 'Purchasing', 'middleware' => 'purchasing'], function () {

    //Route to update the exchange rate column of all po orders
    Route::get('/update_pos_exchange_rate', function () {
        $orders = App\Models\Common\PurchaseOrders\PurchaseOrder::all();
        // dd($orders);

        foreach ($orders as $order) {
            # code...

            $query = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::where('po_id', $order->id)->first();
            $order->exchange_rate = $query->currency_conversion_rate;

            $order->save();
        }

        return '<h1> Purchase orders updated successfully! </h1>';
    });

    //Route to update the customer type product margins
    Route::get('/update_customer_type_product_margins', function () {
        $privateMargins = App\Models\Common\CustomerTypeProductMargin::where('customer_type_id', 4)->where('is_mkt', 0)->orderBy('product_id', 'ASC')->get();

        foreach ($privateMargins as $pm) {
            $ecommerceMargins = App\Models\Common\CustomerTypeProductMargin::where('customer_type_id', 6)->where('product_id', $pm->product_id)->first();
            if ($ecommerceMargins) {
                $ecommerceMargins->default_value = $pm->default_value;
                $ecommerceMargins->is_mkt        = 1;
                $ecommerceMargins->save();
            }

            $pm->is_mkt = 1;
            $pm->save();
        }

        return '<h1> Updated successfully! </h1>';
    });

    //Route to update the cogs of all imported items
    Route::get('/update_cogs_of_products', function () {

        $html = '';
        $products = App\Models\Common\Order\OrderProduct::select('id', 'product_id', 'locked_actual_cost', 'actual_cost', 'is_cogs_updated', 'status', 'manual_cogs_shipment')->whereNotNull('product_id')->where('is_cogs_updated', 1)->groupBy('product_id')->where('status', '>', 8)->get();

        $html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Product ID</th>
		      			<th>Product CODE</th>
		      			<th>Product cogs</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
        foreach ($products as $value) {
            $id = [];
            $checkImport = App\Models\Common\PoGroupProductDetail::where('status', 1)->with('po_group')->select('unit_price_in_thb', 'freight', 'landing', 'total_extra_cost', 'total_extra_tax', 'actual_tax_percent', 'quantity_inv', 'po_group_id', 'product_id', 'id')->where('quantity_inv', '!=', 0)->where('product_id', $value->product_id)->orderBy('id', 'ASC')->first();

            if ($checkImport) {
                $productData = App\Models\Common\Product::select('id', 'status', 'unit_conversion_rate', 'refrence_code')->find($value->product_id);

                $unit_price_in_thb = $checkImport->unit_price_in_thb;

                $calculations = (($checkImport->actual_tax_percent / 100) * $unit_price_in_thb) + $unit_price_in_thb;

                $calculations = ($calculations) + ($checkImport->freight) + ($checkImport->landing) + ($checkImport->total_extra_cost / $checkImport->quantity_inv) + ($checkImport->total_extra_tax / $checkImport->quantity_inv);

                $final_cogs    = $calculations * $productData->unit_conversion_rate;

                for ($i = 1; $i > 0; $i++) {
                    if ($final_cogs == 0) {
                        array_push($id, $checkImport->id);
                        $checkImport = App\Models\Common\PoGroupProductDetail::where('status', 1)->with('po_group')->select('unit_price_in_thb', 'freight', 'landing', 'total_extra_cost', 'total_extra_tax', 'actual_tax_percent', 'quantity_inv', 'po_group_id', 'product_id', 'id')->where('quantity_inv', '!=', 0)->where('product_id', $value->product_id)->whereNotIn('id', $id)->orderBy('id', 'ASC')->first();

                        if ($checkImport) {
                            $productData = App\Models\Common\Product::select('id', 'status', 'unit_conversion_rate', 'refrence_code')->find($value->product_id);

                            $unit_price_in_thb = $checkImport->unit_price_in_thb;

                            $calculations = (($checkImport->actual_tax_percent / 100) * $unit_price_in_thb) + $unit_price_in_thb;

                            $calculations = ($calculations) + ($checkImport->freight) + ($checkImport->landing) + ($checkImport->total_extra_cost / $checkImport->quantity_inv) + ($checkImport->total_extra_tax / $checkImport->quantity_inv);

                            $final_cogs   = $calculations * $productData->unit_conversion_rate;
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }

                if ($final_cogs != 0) {
                    $is_cogs_updated = 2;
                    DB::table('order_products')
                        ->where('product_id', $value->product_id)
                        ->where('status', '>', 8)
                        ->where('is_cogs_updated', 1)
                        ->update(['is_cogs_updated' => DB::raw($is_cogs_updated), 'actual_cost' => DB::raw($final_cogs), 'locked_actual_cost' => DB::raw($final_cogs), 'manual_cogs_shipment' => DB::raw($checkImport->po_group_id)]);
                }

                $html .= '<tr><td>' . $productData->id . '</td><td>' . $productData->refrence_code . '</td><td>' . $final_cogs . '</td></tr>';
            } else {
                $productData = App\Models\Common\Product::select('id', 'status', 'unit_conversion_rate', 'refrence_code', 'selling_price')->find($value->product_id);
                $is_cogs_updated = 2;
                DB::table('order_products')
                    ->where('product_id', $value->product_id)
                    ->where('status', '>', 8)
                    ->where('is_cogs_updated', 1)
                    ->update(['is_cogs_updated' => DB::raw($is_cogs_updated), 'actual_cost' => DB::raw($productData->selling_price), 'locked_actual_cost' => DB::raw($productData->selling_price)]);
            }
        }

        $html .= '</tbody></table>';
        return $html;
    });
    Route::get('check-ecommerce-enabled', 'ProductController@enableEcommerce')->name('check-ecommerce-enabled');

    Route::post('update-fixed-prices-check', 'ProductController@updateFixedPricesCheck')->name('update-fixed-prices-check');

    Route::post('upload-bulk-product-in-pos', 'PurchaseOrderController@uploadBulkProductsInPos')->name('upload-bulk-product-in-pos');
    Route::post('upload-bulk-product-in-pos-detail', 'PurchaseOrderController@uploadBulkProductsInPosDetail')->name('upload-bulk-product-in-pos-detail');

    Route::get('recursive-export-status-supplier-bulk-products', 'ProductController@recursiveExportStatusSupplierBulkProducts')->name('recursive-export-status-supplier-bulk-products');
    Route::get('recursive-export-status-supplier-bulk-prices', 'ProductController@recursiveExportStatusSupplierBulkPrices')->name('recursive-export-status-supplier-bulk-prices');
    Route::get('recursive-export-status-move-supplier-bulk-products', 'ProductController@recursiveExportStatusMoveSupplierBulkProducts')->name('recursive-export-status-move-supplier-bulk-products');
    Route::post('upload-supplier-bulk-product-job-status', 'ProductController@uploadSupplierBulkProductJobStatus')->name('upload-supplier-bulk-product-job-status');
    Route::post('upload-supplier-bulk-prices-job-status', 'ProductController@uploadSupplierBulkPricesJobStatus')->name('upload-supplier-bulk-prices-job-status');
    Route::post('move-supplier-bulk-product-job-status', 'ProductController@moveSupplierBulkProductJobStatus')->name('move-supplier-bulk-product-job-status');

    Route::get('export-bulk-products-file-download', 'ProductController@bulkProductUploadFileDownload')->name('export-bulk-products-file-download');

    Route::get('/supplier-transaction-detail/{id}', 'SupplierController@getSupplierTransactionDetail')->name('supplier-transaction-detail');
    Route::get('/get-payment-ref-invoices-for-payable', 'SupplierController@getPaymentRefInvoicesForPayable')->name('get-payment-ref-invoices-for-payable');
    Route::get('/get-invoices-for-payable', 'SupplierController@getInvoicesForPayable')->name('get-invoices-for-payable');
    Route::get('/get-invoices-for-payable-last-five', 'SupplierController@getInvoicesForPayableLastFive')->name('get-invoices-for-payable-last-five');

    Route::get('/', 'HomeController@getHome')->name('purchasing-dashboard');
    Route::get('transfer-document-dashboard', 'HomeController@getTransferDashboard')->name('transfer-document-dashboard');

    Route::get('/waiting-shipping-info', 'HomeController@getWaitingShippingInfo')->name('waiting-shipping-info');
    Route::get('/dispatch-from-supplier', 'HomeController@getDispatchFromSupplier')->name('dispatch-from-supplier');
    Route::get('/received-into-stock', 'HomeController@getReceivedintoStock')->name('received-into-stock');
    Route::get('/all-pos', 'HomeController@allPos')->name('all-pos');

    Route::get('complete-profile', 'HomeController@completeProfile');
    Route::post('complete-profile', 'HomeController@completeProfileProcess');
    Route::post('/add-courier-purchase', 'HomeController@addCourier')->name('add-courier-purchase');

    Route::get('general-settings', 'HomeController@generalSettings')->name('general-settings');
    Route::post('save-column-toggle', 'HomeController@saveTableColumnDisplay')->name('save-column-toggle');
    Route::post('save-column-toggle-purchase-list', 'HomeController@saveTableColumnDisplayPurchaseList')->name('save-column-toggle-purchase-list');
    Route::post('save-purchase-list-column-toggle', 'HomeController@savePurchaseListTableColumnDisplay')->name('save-purchase-list-column-toggle');

    Route::get('/export-received-into-stock', 'HomeController@exportReceivedIntoStock')->name('export-received-into-stock');
    Route::get('recursive-export-status-recieved-into-stock', 'HomeController@recursiveExportStatusReceivedIntoStock')->name('recursive-export-status-recieved-into-stock');
    Route::get('check-status-for-first-time-received-into-stock', 'HomeController@checkStatusForFirstTimeReceivedIntoStock')->name('check-status-for-first-time-received-into-stock');

    // Route::post('check-old-password','HomeController@checkOldPassword');

    // Route::group(['middleware' => 'incomplete-profile'], function(){
    // 	Route::get('/','HomeController@getHome')->name('purchasing-dashboard');
    // });

    /****************** Products ROUTES**************************/

    Route::get('/purchase-account-payable', 'ProductController@purchaseAccountPayable')->name('purchase-account-payable');
    Route::get('/get-account-payable-purchase-orders', 'ProductController@getAccountPayablePurchaseOrders')->name('get-account-payable-purchase-orders');
    Route::get('/get-purchase-order-received-amount', 'ProductController@getPurchaseOrderReceivedAmount')->name('get-purchase-order-received-amount');

    Route::post('/delete_po_transaction', 'ProductController@deletePoTransaction')->name('delete_po_transaction');

    Route::get('get-po-transaction-history', 'ProductController@getPoTransactionHistory')->name('get-po-transaction-history');
    Route::get('get-po-transaction-del-history', 'ProductController@getPoTransactionDelHistory')->name('get-po-transaction-del-history');
    Route::post('/save-account-payable-data', 'ProductController@saveAccountPayableData')->name('save-account-payable-data');
    Route::post('/save-account-payable-tran-data', 'export-account-payable-table@saveAccountPayableTranData')->name('save-account-payable-tran-data');
    Route::get('/get-supplier-orders', 'ProductController@getSupplierOrders')->name('get-supplier-orders');
    Route::get('/recursive-export-status-account-payable-table', 'ProductController@recursiveStatusCheckAccountPayableTable')->name('recursive-export-status-account-payable-table');
    Route::get('/check-status-for-first-time-account-payable-table', 'ProductController@checkStatusFirstTimeForAccountPayableTable')->name('check-status-for-first-time-account-payable-table');

    Route::post('export-account-payable-table', 'ProductController@exportAccountPayableTable')->name('export-account-payable-table');


    Route::post('purchasing-add-brands', 'ProductController@purchaseAddBrand')->name('purchasing-add-brands');
    Route::get('get-sale-price-for-selected-customer', 'ProductController@getSalePriceForSelectedCustomer')->name('get-sale-price-for-selected-customer');
    Route::get('complete-list-product', 'ProductController@index')->name('complete-list-product');
    Route::get('print-barcode/{ids}', 'ProductController@printBarcode')->name('print-barcode');

    // Route::get('complete-list-product','ProductController@index')->name('setProductDetailFilters');


    Route::get('sold-products-report', 'ProductController@soldProductsReport')->name('sold-products-report');

    Route::get('stock-report-with-params/{prod_id?}/{order_id?}/{is_type?}', function ($prod_id, $order_id, $is_type) {
        // dd($prod_id,$order_id);
        return redirect('stock-detail-report-wpm')->with(['product_id' => $prod_id, 'order_id' => $order_id, 'is_type' => $is_type]);
    })->name('stock-report-with-params');

    Route::get('sold-product-report-with-param/{prod_id?}/{from_date?}/{to_date?}/{mg_report?}/{w_id?}/{cat_id?}/{cust_id?}/{c_ty_id?}/{saleid?}', function ($prod_id, $from_date, $to_date, $mg_report, $w_id, $cat_id, $cust_id, $c_ty_id, $saleid) {
        // dd($saleid);
        return redirect('sold-products-report')->with(['prod_id' => $prod_id, 'from_date' => $from_date, 'to_date' => $to_date, 'mg_report' => $mg_report, 'w_id' => $w_id, 'cat_id' => $cat_id, 'cust_id' => $cust_id, 'c_ty_id' => $c_ty_id, 'saleid' => $saleid]);
    })->name('sold-product-report-with-param');

    Route::get('purchasing-report-with-param/{prod_id?}/{ordr_id?}/{from_date?}/{to_date?}', function ($prod_id, $from_date, $to_date, $mg_report, $w_id, $cat_id, $cust_id, $c_ty_id, $saleid) {
        // dd($saleid);
        return redirect('sold-products-report')->with(['prod_id' => $prod_id, 'from_date' => $from_date, 'to_date' => $to_date, 'mg_report' => $mg_report, 'w_id' => $w_id, 'cat_id' => $cat_id, 'cust_id' => $cust_id, 'c_ty_id' => $c_ty_id, 'saleid' => $saleid]);
    })->name('sold-product-report-with-param');

    Route::get('/export-status-sold-products', 'ProductController@exportSoldProductReportStatus')->name('export-status-sold-products');
    Route::get('/export-status-stock-movement-report', 'ProductController@exportStockMovementReportStatus')->name('export-status-stock-movement-report');
    Route::get('/check-status-for-first-time-sold-products', 'ProductController@checkStatusFirstTimeForSoldProducts')->name('check-status-for-first-time-sold-products');

    Route::get('/recursive-export-status-sold-products', 'ProductController@recursiveStatusCheckSoldProduct')->name('recursive-export-status-sold-products');
    Route::get('/recursive-export-status-stock-movement-report', 'ProductController@recursiveStatusCheckStockMovementReport')->name('recursive-export-status-stock-movement-report');

    Route::post('get-sold-product-data-for-report', 'ProductController@getsoldProdDataForReport')->name('get-sold-product-data-for-report');
    Route::get('get-sold-product-data-for-supplier-report', 'ProductController@getsoldProdDataForSupplierReport')->name('get-sold-product-data-for-supplier-report');
    Route::post('update-cogs-from-report', 'ProductController@UpdateCOGSFromPSReport')->name('update-cogs-from-report');
    Route::post('get-product-sale-report-detail-history', 'ProductController@getPSRDHistoryData')->name('get-product-sale-report-detail-history');
    Route::get('get-sold-product-data-for-report-transfer', 'ProductController@getsoldProdDataForReportTransfer')->name('get-sold-product-data-for-report-transfer');
    Route::post('/export-sold-products-report', 'ProductController@exportSoldProductReport')->name('export-sold-products-report');
    Route::post('get-sold-product-report-footer-values', 'ProductController@getsoldProductReportFooterValues')->name('get-sold-product-report-footer-values');
    Route::post('get-sold-product-report-transfer-footer-values', 'ProductController@getsoldProductReportTransferFooterValues')->name('get-sold-product-report-transfer-footer-values');

    /****************** Product Sales Report********************/
    Route::get('product-sales-report', 'ProductController@productSalesReport')->name('product-sales-report');
    Route::get('get-product-sales-report-data', 'ProductController@getProductSalesReportData')->name('get-product-sales-report-data');
    Route::get('get-product-sales-report-footer-data', 'ProductController@getProductSalesReportFooterData')->name('get-product-sales-report-footer-data');


    Route::post('export-product-sales-report', 'ProductController@exportProductSalesReport')->name('export-product-sales-report');

    Route::get('/check-status-for-first-time-product-report', 'ProductController@checkStatusFirstTimeForProductReport')->name('check-status-for-first-time-product-report');
    Route::get('/recursive-export-status-product-report', 'ProductController@recursiveStatusCheckProductReport')->name('recursive-export-status-product-report');

    Route::get('get-product-sales-report-detail/{customer_id}/{supplier_id}/{product_id}/{from_date}/{to_date}/{date_type}', function ($customer_id, $supplier_id, $product_id, $from_date, $to_date, $date_type) {
        $customer_detail = App\Models\Sales\Customer::find($customer_id);
        // dd($customer_detail);
        return redirect('sold-products-report')->with(['find' => 'Yes', 'customer_id' => $customer_id, 'supplier_id' => $supplier_id, 'product_id' => $product_id, 'from_date' => $from_date, 'to_date' => $to_date, 'date_type' => $date_type, 'customer_name' => ($customer_detail != null ? $customer_detail->reference_name : '')]);
    })->name('get-product-sales-report-detail');

    /********************* Margin Report Routes Start ***************************/
    Route::get('margin-report', 'ProductController@MarginReport')->name('margin-report');
    Route::get('get-margin-report', 'ProductController@getMarginReport')->name('get-margin-report');
    Route::get('/export-status-margin-report-by-office', 'ProductController@ExportMarginReportByOffice')->name('export-status-margin-report-by-office');
    Route::get('/recursive-export-status-margin-reports', 'ProductController@recursiveStatusCheckForMarginReports')->name('recursive-export-status-margin-reports');


    Route::get('margin-report-2/{from_dashboard?}', 'ProductController@MarginReport2')->name('margin-report-2');
    Route::get('get-margin-report-2', 'ProductController@getMarginReport2')->name('get-margin-report-2');
    Route::get('get-margin-report-2-footer', 'ProductController@getMarginReport2Footer')->name('get-margin-report-2-footer');
    Route::get('export-status-margin-report-by-product-name', 'ProductController@ExportMarginReportByProductName')->name('export-status-margin-report-by-product-name');


    Route::get('margin-report-3', 'ProductController@MarginReport3')->name('margin-report-3');
    Route::get('get-margin-report-3', 'ProductController@getMarginReport3')->name('get-margin-report-3');
    Route::get('get-margin-report-3-footer', 'ProductController@getMarginReport3Footer')->name('get-margin-report-3-footer');
    Route::get('export-status-margin-report-by-sales', 'ProductController@ExportMarginReportBySales')->name('export-status-margin-report-by-sales');

    Route::get('margin-report-4', 'ProductController@MarginReport4')->name('margin-report-4');
    Route::get('get-margin-report-4', 'ProductController@getMarginReport4')->name('get-margin-report-4');
    Route::get('get-margin-report-4-footer', 'ProductController@getMarginReport4Footer')->name('get-margin-report-4-footer');
    Route::get('export-status-margin-report-by-product-category', 'ProductController@ExportMarginReportByProductCategory')->name('export-status-margin-report-by-product-category');


    Route::get('margin-report-5', 'ProductController@MarginReport5')->name('margin-report-5');
    Route::get('get-margin-report-5', 'ProductController@getMarginReport5')->name('get-margin-report-5');
    Route::get('get-margin-report-5-footer', 'ProductController@getMarginReport5Footer')->name('get-margin-report-5-footer');
    Route::get('export-status-margin-report-by-customer', 'ProductController@ExportMarginReportByCustomer')->name('export-status-margin-report-by-customer');

    Route::get('margin-report-6', 'ProductController@MarginReport6')->name('margin-report-6');
    Route::get('get-margin-report-6', 'ProductController@getMarginReport6')->name('get-margin-report-6');
    Route::get('get-margin-report-6-footer', 'ProductController@getMarginReport6Footer')->name('get-margin-report-6-footer');
    Route::get('export-status-margin-report-by-customer-type', 'ProductController@ExportMarginReportByCustomerType')->name('export-status-margin-report-by-customer-type');

    Route::get('margin-report-7', 'ProductController@MarginReport7')->name('margin-report-7');
    Route::get('get-margin-report-7', 'ProductController@getMarginReport7')->name('get-margin-report-7');

    Route::get('margin-report-8', 'ProductController@MarginReport8')->name('margin-report-8');
    Route::get('get-margin-report-8', 'ProductController@getMarginReport8')->name('get-margin-report-8');

    Route::get('margin-report-9', 'ProductController@MarginReport9')->name('margin-report-9');
    Route::get('get-margin-report-9', 'ProductController@getMarginReport9')->name('get-margin-report-9');
    Route::get('get-margin-report-9-footer', 'ProductController@getMarginReport9Footer')->name('get-margin-report-9-footer');
    Route::get('export-status-margin-report-by-product-type', 'ProductController@ExportMarginReportByProductType')->name('export-status-margin-report-by-product-type');

    Route::get('margin-report-11', 'ProductController@MarginReport11')->name('margin-report-11');
    Route::get('get-margin-report-11', 'ProductController@getMarginReport11')->name('get-margin-report-11');
    Route::get('get-margin-report-11-footer', 'ProductController@getMarginReport11Footer')->name('get-margin-report-11-footer');
    Route::get('export-status-margin-report-by-product-type-2', 'ProductController@ExportMarginReportByProductType2')->name('export-status-margin-report-by-product-type-2');

    Route::get('margin-report-10', 'ProductController@MarginReport10')->name('margin-report-10');
    Route::get('get-margin-report-10', 'ProductController@getMarginReport10')->name('get-margin-report-10');
    Route::get('export-status-margin-report-by-supplier', 'ProductController@ExportMarginReportByupplier')->name('export-status-margin-report-by-supplier');

    Route::get('margin-report-12/{supplier_id}', 'ProductController@MarginReport12')->name('margin-report-12');
    Route::get('get-margin-report-12', 'ProductController@getMarginReport12')->name('get-margin-report-12');

    Route::get('margin-report-product-type-3', 'ProductController@MarginReportProductType3')->name('margin-report-product-type-3');
    Route::get('get-margin-report-product-type-3', 'ProductController@getMarginReportProductType3')->name('get-margin-report-product-type-3');
    Route::get('get-margin-report-product-type-3-footer', 'ProductController@getMarginReportProductType3Footer')->name('get-margin-report-product-type-3-footer');
    Route::get('export-status-margin-report-by-product-type-3', 'ProductController@ExportMarginReportByProductType3')->name('export-status-margin-report-by-product-type-3');

    /********************* Margin Report Routes End ***************************/
    Route::get('remove-multi-products', 'ProductController@removeMultipleProducts')->name('remove-multi-products');
    Route::get('deactivate-products', 'ProductController@deactivateProducts')->name('deactivate-products');

    Route::get('products-enable-ecommerce', 'ProductController@ecommerceproductsenabled')->name('products-enable-ecommerce');
    Route::get('products-enable-woocommerce', 'ProductController@woocommerceproductsenabled')->name('products-enable-woocommerce');
    Route::get('products-disable-ecommerce', 'ProductController@ecommerceProductDisabled')->name('products-disable-ecommerce');
    Route::get('refresh-stock', 'ProductController@refreshStock')->name('refresh-stock');

    Route::get('activate-products', 'ProductController@activateProducts')->name('activate-products');
    Route::get('delete-products', 'ProductController@deleteProducts')->name('delete-products');

    Route::get('deactivate-list-product', 'ProductController@deactivatedProducts')->name('deactivate-list-product');
    // Product search of Purchasing
    Route::post('purchase-fetch-product', 'ProductController@purchaseFetchProduct')->name('purchase-fetch-product');
    Route::post('purchase-fetch-product-spr', 'ProductController@purchaseFetchProductSpr')->name('purchase-fetch-product-spr');
    Route::post('purchase-fetch-customer', 'ProductController@purchaseFetchCustomer')->name('purchase-fetch-customer');
    Route::post('purchase-fetch-product-category', 'ProductController@purchaseFetchProductCategory')->name('purchase-fetch-product-category');
    Route::post('fetch-product-with-category', 'ProductController@FetchProductWithCategory')->name('fetch-product-with-category');
    Route::post('purchase-fetch-orders', 'ProductController@purchaseFetchOrders')->name('purchase-fetch-orders');
    Route::post('purchase-fetch-purchase-orders', 'ProductController@purchaseFetchPurchaseOrders')->name('purchase-fetch-purchase-orders');
    Route::get('bulk-products-upload-form/{id?}', 'ProductController@bulkUploadProudcts')->name('bulk-products-upload-form');
    Route::get('bulk-prices-upload-form', 'ProductController@bulkUploadPrices')->name('bulk-prices-upload-form');
    Route::post('get-temp-product-data', 'ProductController@getTempProductData')->name('get-temp-product-data');
    Route::get('discard-temp-data', 'ProductController@discardTemp')->name('discard-temp-data');
    Route::get('delete-selected-temp', 'ProductController@discardSelectedTempData')->name('delete-selected-temp');
    Route::post('save-selected-temp-bulk', 'ProductController@saveBulkTempProduct')->name('save-selected-temp-bulk');

    Route::get('bulk-quantity-upload-form', 'PurchasingController@bulkUploadQuantity')->name('bulk-quantity-upload-form');
    Route::post('get-filtered-stock-prod-excel', 'PurchasingController@getFilteredStockProdExcel')->name('get-filtered-stock-prod-excel');
    Route::get('recursive-call-for-status', 'PurchasingController@recursiveStatusCheck')->name('recursive-call-for-status');
    Route::get('check-status-for-first-time-stock-adjustments', 'PurchasingController@checkStatusFirstTimeForStockAdjustment')->name('check-status-for-first-time-stock-adjustments');
    Route::get('recursive-call-for-import-status', 'PurchasingController@recursiveImportStatusCheck')->name('recursive-call-for-import-status');


    // Sup-874 Bulk import product from different supliers at a time
    Route::get('bulk-upload-products', 'BulkUploadProductsController@index')->name('bulk-upload-products.index');



    Route::post('get-all-prod-qty-excel', 'PurchasingController@getFilteredStockProdExcel')->name('get-all-prod-qty-excel');
    Route::post('bulk-upload-prod-qty', 'PurchasingController@bulkUploadProdQty')->name('bulk-upload-prod-qty');


    Route::post('get-all-prod-excel', 'ProductController@getAllProdExcel')->name('get-all-prod-excel');
    Route::post('get-filtered-prod-excel', 'ProductController@getFilteredProdExcel')->name('get-filtered-prod-excel');

    Route::get('/check-status-for-first-time-bulk-price', 'ProductController@checkStatusFirstTimeForBulkPrice')->name('check-status-for-first-time-bulk-price');
    Route::get('/recursive-export-status-bulk-price', 'ProductController@recursiveStatusCheckBulkPrice')->name('recursive-export-status-bulk-price');

    Route::post('download-supplier-all-products', 'ProductController@downloadSupplierAllProducts')->name('download-supplier-all-products');

    Route::get('/check-status-for-first-time-bulk-product', 'ProductController@checkStatusFirstTimeForBulkProduct')->name('check-status-for-first-time-bulk-product');
    Route::get('/recursive-export-status-bulk-product', 'ProductController@recursiveStatusCheckBulkProduct')->name('recursive-export-status-bulk-product');



    Route::get('incomplete-list-product', 'ProductController@incomplete')->name('incomplete-list-product');
    Route::get('get-datatables-for-incomplete-product', 'ProductController@getInCompleteData')->name('get-purchase-incomplete-product');
    Route::get('move-selected-temp-incomplete', 'ProductController@moveTempToIncomplete')->name('move-selected-temp-incomplete');

    Route::get('/inquiry-products', 'ProductController@indexForInquiry')->name('inquiry-products-to-purchasing');
    Route::get('/get-datatables-for-inquiry-products', 'ProductController@getDataForInquiry')->name('get-inquiry-products-to-purchasing');

    Route::post('move-to-inventory', 'ProductController@MoveToInventory')->name('move-to-inventory');
    Route::post('delete-inquiry-products', 'ProductController@deleteInquiryProducts')->name('delete-inquiry-products');
    // script for adding rows on click of view detail of product
    Route::post('temp_add-rows-to-tables', 'ProductController@tempRowsAdd')->name('temp_add-rows-to-tables');
    Route::get('adding-product', 'ProductController@addProduct')->name('adding-product');
    Route::post('add-product', 'ProductController@add')->name('add-product');
    Route::post('get-datatables-for-product', 'ProductController@getData')->name('get-product');
    Route::get('/export-complete-products', 'ProductController@exportCompleteProducts')->name('export-complete-products');
    Route::get('/export-status-complete-products', 'ProductController@exportCompleteProductsStatus')->name('export-status-complete-products');
    Route::get('/recursive-export-status-complete-products', 'ProductController@recursiveStatusCheck')->name('recursive-export-status-complete-products');
    Route::get('/check-status-for-first-time-complete-products', 'ProductController@checkStatusFirstTime')->name('check-status-for-first-time-complete-products');

    Route::get('/export-status-for-ecom-products-excel', 'ProductController@exportProductsForEcom')->name('export-status-for-ecom-products-excel');
    Route::get('/recursive-export-status-for-ecom-products-excel', 'ProductController@exportProductsForEcomStatus')->name('recursive-export-status-for-ecom-products-excel');

    Route::get('get-datatables-for-deactivated-product', 'ProductController@getdeactivatedData')->name('get-deactivated-product');
    Route::get('edit-product', 'ProductController@edit')->name('product-edit');
    Route::post('update-product', 'ProductController@update')->name('update-product');
    //adding new unit from purchasing side
    Route::post('adding-unit', 'ProductController@addUnit')->name('adding-unit');
    //adding new product type from purchasing side
    Route::post('adding-product-type', 'ProductController@addProdType')->name('adding-product-type');
    //adding new product category from purchasing side
    Route::post('adding-product-cat', 'ProductController@addProdCat')->name('adding-product-cat');
    //getting supplier by ID
    Route::get('get-supplier-by-id/{id}', 'ProductController@getSupplierById')->name('get-supplier-by-id');
    // product detail page
    Route::get('get-product-detail/{id}', 'ProductController@getProductDetail')->name('get-product-detail');
    Route::post('crop-image', 'ProductController@cropImage')->name('crop-image');
    Route::post('set-default-image-ecom', 'ProductController@setDefaultImage')->name('set-default-image-ecom');
    //
    // save default supplier in product detail
    Route::post('set-default-supplier', 'ProductController@setDefaultSupplier')->name('setDefaultSupplier');
    Route::get('make-manual-stock-adjustment', 'ProductController@makeManualStockAdjustment')->name('make-manual-stock-adjustment');
    Route::post('update-stock-record', 'ProductController@updateStockRecord')->name('update-stock-record');
    Route::post('delete-stock-record', 'ProductController@deleteStockRecord')->name('delete-stock-record');
    Route::post('update-stock-card-cogs', 'ProductController@updateStockRecordCost')->name('update-stock-card-cogs');

    // saving product details page fields route
    Route::post('save-prod-data-prod-detail-page', 'ProductController@saveProdDataProdDetailPage')->name('save-prod-data-prod-detail-page');
    Route::get('get-product-history', 'ProductController@getProductHistory')->name('get-product-history');

    // saving product data through incomplete route
    Route::post('save-prod-data-incomplete-to-complete', 'ProductController@saveProdDataIncomplete')->name('save-prod-data-incomplete-to-complete');
    Route::post('save-temp-products-data', 'ProductController@saveTempProductData')->name('save-temp-products-data');
    Route::post('save-temp-products-data-fp', 'ProductController@saveTempProductDataFixedPrice')->name('save-temp-products-data-fp');
    // saving product default supplier page fields route
    Route::post('save-prod-supp-data-prod-detail-page', 'ProductController@saveProdSuppDataProdDetailPage')->name('save-prod-supp-data-prod-detail-page');
    // getting product Image
    Route::get('get-prod-image', 'ProductController@getProdImages')->name('get-prod-image');
    // adding product Images
    Route::post('add-product-image', 'ProductController@addProductImages')->name('add-product-image');
    // delete prod imaged
    Route::get('remove-prod-image', 'ProductController@removeProdImage')->name('remove-prod-image');
    Route::get('delete-prod-img-from-detail', 'ProductController@delProdImgFromDetail')->name('delete-prod-img-from-detail');
    // getting product category childs
    Route::get('getting-product-category-childs', 'ProductController@getProductCategoryChilds')->name('getting-product-category-childs');
    Route::post('edit-prod-supp-data', 'ProductController@editProdSuppData')->name('edit-prod-supp-data');
    Route::post('edit-prod-margin-data', 'ProductController@editProdMarginData')->name('edit-prod-margin-data');
    Route::post('edit-prod-fixed-price-data', 'ProductController@editProdFixedPriceData')->name('edit-prod-fixed-price-data');
    Route::get('show-single-supplier-record', 'ProductController@showSingleSupplierRecord')->name('show-single-supplier-record');
    Route::get('get-product-suppliers-record/{id}', 'ProductController@getProductSuppliersRecord')->name('get-product-suppliers-record');
    Route::get('delete-prod-fixed-price-row', 'ProductController@deleteProdFixedPrice')->name('delete-prod-fixed-price-row');
    Route::post('upload-bulk-product', 'ProductController@uploadBulkProducts')->name('upload-bulk-product');
    Route::post('upload-prices-bulk-product', 'ProductController@uploadPricesBulkProducts')->name('upload-prices-bulk-product');
    Route::post('upload-bulk-product-suppliers', 'ProductController@uploadBulkProductSuppliers')->name('upload-bulk-product-suppliers');
    Route::post('save-inquiry-prod-data', 'ProductController@saveInquiryProductData')->name('save-inquiry-prod-data');

    Route::get('check-mkt-status', 'ProductController@checkMktStatus')->name('check-mkt-status');
    Route::get('update-single-product-price', 'ProductController@updateSingleProductPrice')->name('update-single-product-price');
    Route::get('getting-product-incorrect-prices', 'ProductController@getiingIncorrectProductPrice')->name('getting-product-incorrect-prices');
    Route::get('update-billed-qty-script', 'ProductController@updateBilledQty')->name('update-billed-qty-script');
    // Route::get('get-product-suppliers-details/{id}', 'ProductController@getProductSupplierDetails')->name('get-product-suppliers-details/{id}');
    /****************** Supplier ROUTES**************************/
    Route::post('add-supplier-cats', 'SupplierController@addSupplierCats')->name('add-supplier-cats');
    Route::get('supplier', 'SupplierController@index')->name('list-of-suppliers');

    // first this was post method
    Route::get('adding-supplier', 'SupplierController@add')->name('adding-supplier');
    Route::get('save-incom-supplier', 'SupplierController@saveIncompSupplier')->name('save-incom-supplier');
    Route::get('get-datatables-for-supplier', 'SupplierController@getData')->name('getting-supplier');
    Route::post('/update-supplier-profile-pic/{id}', 'SupplierController@updateSupplierProfile')->name('update-supplier-profile-pic');

    // Supplier Export Routes
    Route::get('/export-suplier-data', 'SupplierController@exportSupplierData')->name('export-suplier-data');
    Route::get('/recursive-export-status-supplier-list', 'SupplierController@recursiveExportStatusSupplierList')->name('recursive-export-status-supplier-list');
    // End

    Route::get('bulk-upload-suppliers-form', 'SupplierController@bulkUploadSuppliersForm')->name('bulk-upload-suppliers-form');
    Route::post('bulk-upload-suppliers', 'SupplierController@bulkUploadSuppliers')->name('bulk-upload-suppliers');
    Route::post('bulk-upload-pos', 'SupplierController@bulkUploadPO')->name('bulk-upload-pos');
    Route::get('recursive-import-status-bulk-pos', 'SupplierController@RecursiveCallForBulkPos')->name('recursive-import-status-bulk-pos');
    Route::get('check-status-for-first-time-bulk-pos', 'SupplierController@CheckStatusFirstTimeForBulkPos')->name('check-status-for-first-time-bulk-pos');

    Route::get('get-temp-suppliers-data', 'SupplierController@getTempSuppliersData')->name('get-temp-suppliers-data');
    Route::get('discard-temp-suppliers-data', 'SupplierController@discardTempSuppliers')->name('discard-temp-suppliers-data');
    Route::post('save-temp-supplier-data', 'SupplierController@saveTempSupplierData')->name('save-temp-supplier-data');

    Route::get('redirect-to-products/{id}', 'SupplierController@redirectToProducts');
    Route::get('check-supp-exist-in-prod-suppliers', 'ProductController@checkSuppExistInProdSupp')->name('check-supp-exist-in-prod-suppliers');
    // getting supplier complete details
    Route::get('get-supplier-detail/{id}', 'SupplierController@getSupplierDetailByID')->name('get-supplier-detail');

    // active or suspend account
    Route::get('/supplier-suspension', 'SupplierController@suspendSupplier')->name('supplier-suspension');
    Route::get('/supplier-activation', 'SupplierController@activateSupplier')->name('supplier-activation');
    // delete supplier from detail page
    Route::get('deleting-supplier', 'ProductController@deleteSupplier')->name('deleting-supplier');
    // getting product category childs for supplier
    Route::get('getting-product-category-childs-for-supplier', 'SupplierController@getProductCategoryChilds')->name('getting-product-category-childs-for-supplier');

    Route::post('add-supplier-notes', 'SupplierController@addSupplierNote')->name('add-supplier-notes');
    Route::post('edit-supplier-notes', 'SupplierController@editSupplierNote')->name('edit-supplier-notes');
    Route::post('get-supplier-notes', 'SupplierController@getSupplierNote')->name('get-supplier-notes');
    Route::post('delete-supplier-note', 'SupplierController@deleteSupplierNote')->name('delete-supplier-note');
    Route::post('save-supp-data-supp-detail-page', 'SupplierController@saveSuppDataSuppDetail')->name('save-supp-data-supp-detail-page');
    Route::get('getting-country-states', 'SupplierController@getCountryStates')->name('getting-country-states');
    // wednesday added
    Route::post('get-product-suppliers-exist', 'ProductController@getSupplierExist')->name('get-product-suppliers-exist');
    Route::post('get-customer-fixed-price-data', 'ProductController@getCustFixedPriceData')->name('get-customer-fixed-price-data');
    Route::get('get-supplier-contacts', 'SupplierController@getSupplierContact')->name('get-supplier-contacts');
    Route::get('delete-supplier-contact', 'SupplierController@deleteSupplierContact')->name('delete-supplier-contact');
    Route::get('get-supplier-accounts', 'SupplierController@getSupplierAccount')->name('get-supplier-accounts');
    Route::post('add-supplier-accounts', 'SupplierController@addSupplierAccount')->name('add-supplier-accounts');
    Route::post('save-supp-account-data', 'SupplierController@saveSuppAccountData')->name('save-supp-account-data');
    Route::get('delete-supplier-account', 'SupplierController@deleteSupplierAccount')->name('delete-supplier-account');
    Route::post('add-supplier-contacts', 'SupplierController@addSupplierContact')->name('add-supplier-contacts');
    Route::post('save-supp-contacts-data', 'SupplierController@saveSuppContactsData')->name('save-supp-contacts-data');
    Route::get('delete-supplier-note', 'SupplierController@deleteSupplierNoteDetailPage')->name('delete-supplier-note');
    Route::get('delete-supplier-docs', 'SupplierController@deleteSupplierdocs')->name('delete-supplier-docs');
    Route::get('discard-supplier-from-detail', 'SupplierController@discradSupplierFromDP')->name('discard-supplier-from-detail');
    Route::get('get-supplier-general-docs', 'SupplierController@getSupplierGeneralDocuments')->name('get-supplier-general-docs');
    Route::post('add-supplier-general-document', 'SupplierController@addSupplierGeneralDocuments')->name('add-supplier-general-document');

    /****************** Supplier Credit Note ROUTES**************************/
    Route::get('supplier-credit-note', 'SupplierCreditNoteController@index')->name('supplier-credit-note.index');
    Route::get('get-supplier-credit-note-detail/{id}', 'SupplierCreditNoteController@getSupplierNoteDetail')->name('get-supplier-credit-note-detail');
    Route::get('add-supplier-to-credit-note', 'SupplierCreditNoteController@addSupplierToCreditNote')->name('add-supplier-to-credit-note');

    /****************** Supplier Debit Note ROUTES**************************/
    Route::get('supplier-debit-note', 'SupplierDebitNoteController@index')->name('supplier-debit-note.index');
    Route::get('get-supplier-debit-note-detail/{id}', 'SupplierDebitNoteController@getSupplierNoteDetail')->name('get-supplier-debit-note-detail');
    Route::get('add-supplier-to-debit-note', 'SupplierDebitNoteController@addSupplierToCreditNote')->name('add-supplier-to-debit-note');


    /****************** draft invoices ROUTES**************************/
    Route::get('list-draft-invoices', 'DraftInvoicesController@index')->name('list-draft-invoices');
    Route::get('pending-list-draft-invoices', 'DraftInvoicesController@pendingDraftInvoices')->name('pending-list-draft-invoices');
    Route::get('get-all-draft-invoices', 'DraftInvoicesController@getDraftInvoicesData')->name('get-all-draft-invoices');
    Route::get('get-all-pending-draft-invoices', 'DraftInvoicesController@getPendingDraftInvoicesData')->name('get-all-pending-draft-invoices');
    Route::get('get-draft-invoices-product-details/{id}', 'DraftInvoicesController@getDraftInvoicesProductsDetails')->name('get-draft-invoices-product-details');
    Route::get('get-search-draft-invoices-details/{id}', 'ProductController@getSearchDraftInvoicesProductsDetails')->name('get-search-draft-invoices-details');

    Route::get('get-draft-invoices-pending-product-details/{id}', 'DraftInvoicesController@getPendingDraftInvoicesProductsDetails')->name('get-draft-invoices-pending-product-details');
    Route::get('get-completed-quotation-products-to-list/{id}', 'DraftInvoicesController@getProductsData')->name('get-completed-quotation-products-to-list');
    Route::get('get-completed-quotation-pending-products-to-list/{id}', 'DraftInvoicesController@getPendingInvoiceProductsData')->name('get-completed-quotation-pending-products-to-list');

    /****************** Purchase list ROUTES**************************/
    Route::get('list-purchasing', 'PurchasingController@index')->name('list-purchasing');
    Route::get('get-all-purchase-list-data', 'PurchasingController@getPurchaseListData')->name('get-all-purchase-list-data');
    Route::post('/export-purchase-list', 'PurchasingController@exportPurchaseList')->name('export-purchase-list');

    Route::get('/check-status-for-first-time-purchase-list', 'PurchasingController@checkStatusFirstTimeForPurchaseList')->name('check-status-for-first-time-purchase-list');
    Route::get('/recursive-export-status-purchase-list', 'PurchasingController@recursiveStatusCheckPurchaseList')->name('recursive-export-status-purchase-list');

    //Get Po of selected supplier in Purchase list
    Route::get('/get-pos-of-seleced-supplier', 'PurchasingController@getPosOfSelecedSupplier')->name('get-pos-of-seleced-supplier');



    // add product supplier from product detail page
    Route::post('add-product-suppliers', 'ProductController@addProductSupplier')->name('add-product-suppliers');
    Route::post('add-product-suppliers-dropdown', 'ProductController@addProductSupplierDropdown')->name('add-product-suppliers-dropdown');
    // delete supplier from detail page
    Route::get('delete-prod-supplier', 'ProductController@deleteProdSupplier')->name('delete-prod-supplier');
    Route::get('check-product-suppliers-exist-in-po', 'ProductController@checkSupplierProductExistInPo')->name('check-product-suppliers-exist-in-po');

    // product margins
    Route::post('add-product-margins', 'ProductController@addProductMargin')->name('add-product-margins');
    // Product fixed prices based on customer
    Route::post('add-product-fixed-price', 'ProductController@addProductCustomerFixedPrice')->name('add-product-fixed-price');
    /****************** Purchase Order ROUTES**************************/
    Route::post('save-draft-po-dates', 'PurchaseOrderController@SaveDraftPoDates')->name('save-draft-po-dates');
    Route::post('save-po-note', 'PurchaseOrderController@SavePoNote')->name('save-po-note');
    Route::post('save-po-product-quantity', 'PurchaseOrderController@SavePoProductQuantity')->name('save-po-product-quantity');
    Route::post('get_pogpd__id', 'PurchaseOrderController@getpogpd_id')->name('get_pogpd__id');
    Route::post('save-po-pod-vat-actual-quantity', 'PurchaseOrderController@SavePoProductVatActual')->name('save-po-pod-vat-actual-quantity');
    Route::post('clear-revert-po-purchasing-vat', 'PurchaseOrderController@ClearReevrtPurchasingVat')->name('clear-revert-po-purchasing-vat');
    Route::post('save-po-product-desc', 'PurchaseOrderController@SavePoProductDesc')->name('save-po-product-desc');

    Route::post('save-dpo-product-desc', 'PurchaseOrderController@SaveDpoProductDesc')->name('save-dpo-product-desc');
    Route::post('save-po-product-discount', 'PurchaseOrderController@SavePoProductDiscount')->name('save-po-product-discount');
    Route::post('save-draft-po-product-quantity', 'PurchaseOrderController@SaveDraftPoProductQuantity')->name('save-draft-po-product-quantity');
    Route::post('save-draft-po-pod-vat-actual-quantity', 'PurchaseOrderController@SaveDraftPoVatActual')->name('save-draft-po-pod-vat-actual-quantity');
    Route::post('update-draft-po-billed-unit-per-package', 'PurchaseOrderController@updateDraftPoBilledUnitPerPackage')->name('update-draft-po-billed-unit-per-package');
    Route::post('update-draft-po-desired-qty', 'PurchaseOrderController@updateDraftPoDesiredQuantity')->name('update-draft-po-desired-qty', '');
    Route::post('save-draft-po-product-discount', 'PurchaseOrderController@SaveDraftPoProductDiscount')->name('save-draft-po-product-discount');
    Route::post('save-po-product-warhouse', 'PurchaseOrderController@SavePoProductWarehouse')->name('save-po-product-warhouse');
    Route::post('payment-term-save-in-po', 'PurchaseOrderController@paymentTermSaveInPo')->name('payment-term-save-in-po');

    Route::post('create_purchase_order', 'PurchaseOrderController@createPurchaseOrder')->name('create_purchase_order');
    Route::post('add-billed-item-in-po', 'PurchaseOrderController@addBilledItemInPo')->name('add-billed-item-in-po');
    Route::post('add-billed-item-in-dpo', 'PurchaseOrderController@addBilledItemInDpo')->name('add-billed-item-in-dpo');
    Route::get('check-existing-pos', 'PurchaseOrderController@checkExistingPos')->name('check-existing-pos');

    Route::post('order-product-supplier-save', 'PurchasingController@orderProductSupplierSave')->name('order-product-supplier-save');
    Route::post('order-product-warehouse-save', 'PurchasingController@orderProductWarehouseSave')->name('order-product-warehouse-save');
    Route::post('update-unit-price', 'PurchaseOrderController@UpdateUnitPrice')->name('update-unit-price');
    Route::post('update-unit-price-with-vat', 'PurchaseOrderController@UpdateUnitPriceWithVat')->name('update-unit-price-with-vat');
    Route::post('update-pod-gross-weight-price', 'PurchaseOrderController@updateUnitGrossWeight')->name('update-pod-gross-weight-price');
    Route::post('update-desired-qty', 'PurchaseOrderController@UpdateDesireQty')->name('update-desired-qty');
    Route::post('update-billed-unit-per-package', 'PurchaseOrderController@UpdateBilledUnitPerPackage')->name('update-billed-unit-per-package');
    Route::post('update-draft-po-unit-price', 'PurchaseOrderController@UpdateDraftPoUnitPrice')->name('update-draft-po-unit-price');
    Route::post('update-draft-po-unit-price-vat', 'PurchaseOrderController@UpdateDraftPoUnitPriceVat')->name('update-draft-po-unit-price-vat');
    Route::post('update-draft-po-unit-gross-weight', 'PurchaseOrderController@UpdateDraftPoUnitGrossWeight')->name('update-draft-po-unit-gross-weight');
    Route::get('get-purchase-list-prod-note', 'PurchasingController@getPurchaseListProdNote')->name('get-purchase-list-prod-note');
    Route::get('get-draft-po-detail-note', 'PurchaseOrderController@getDraftPoDetailNote')->name('get-draft-po-detail-note');
    Route::post('save-remarks-to-order-Products', 'PurchasingController@saveRemarksInOrderProd')->name('save-remarks-to-order-Products');
    Route::post('add-purchase-list-prod-note', 'PurchasingController@addPurchaseListProdNote')->name('add-purchase-list-prod-note');
    Route::post('add-draft-po-item-note', 'PurchaseOrderController@addDraftPoItemNote')->name('add-draft-po-item-note');
    Route::get('delete-purchase-list-prod-note', 'PurchasingController@deletePurchaseListProdNote')->name('delete-purchase-list-prod-note');
    Route::post('set-target-receive-date', 'PurchaseOrderController@setTargetReceiveDate')->name('set-target-receive-date');
    //Group to po
    Route::post('create-po-group', 'PurchaseOrderController@createPoGroup')->name('create-po-group');
    // delete produc data
    Route::get('delete-product-data', 'ProductController@deleteProdData')->name('delete-product-data');
    Route::get('check-existing-po-groups', 'PurchaseOrderController@checkExistingPoGroups')->name('check-existing-po-groups');
    Route::get('update-group', 'PurchaseOrderController@UpdateGroup')->name('update-group');
    Route::get('remove-from-existing-po-groups', 'PurchaseOrderController@removeFromExistingGroup')->name('remove-from-existing-po-groups');

    Route::get('remove-from-existing-po-groups-r-i-s', 'PurchaseOrderController@removeFromExistingGroupReceived')->name('remove-from-existing-po-groups-r-i-s');
    Route::get('purchase-orders', 'PurchaseOrderController@purchaseOrders')->name('purchase-orders');

    Route::get('get-purchase-orders-data', 'PurchaseOrderController@getData')->name('get-purchase-orders-data');


    Route::get('purchasing-report/{redirection?}/{type?}', 'PurchasingController@purchasingReport')->name('purchasing-report');
    Route::get('purchasing-report-grouped', 'PurchasingController@purchasingReportGrouped')->name('purchasing-report-grouped');

    // Route::post('/export-purchasing-report','PurchasingController@exportPurchasingReport')->name('export-purchasing-report');
    Route::get('/export-purchasing-report', 'PurchasingController@exportPurchasingReport')->name('export-purchasing-report');
    Route::get('/export-purchasing-report-grouped', 'PurchasingController@exportPurchasingReportGrouped')->name('export-purchasing-report-grouped');
    Route::get('recursive-export-status-purchasing-report', 'PurchasingController@recursiveExportStatusPurchasingReport')->name('recursive-export-status-purchasing-report');
    Route::get('recursive-export-status-purchasing-report-grouped', 'PurchasingController@recursiveExportStatusPurchasingReportGrouped')->name('recursive-export-status-purchasing-report-grouped');
    Route::get('check-status-for-first-time-purchasing-report', 'PurchasingController@checkStatusForFirstTimePurchasingReport')->name('check-status-for-first-time-purchasing-report');
    Route::get('check-status-for-first-time-purchasing-report-grouped', 'PurchasingController@checkStatusForFirstTimePurchasingReportGrouped')->name('check-status-for-first-time-purchasing-report-grouped');
    Route::post('get-purchase-orders-data-for-report', 'PurchasingController@getPoDataForReport')->name('get-purchase-orders-data-for-report');
    Route::get('get-purchase-orders-data-for-grouped-report', 'PurchasingController@getPoDataForGroupedReport')->name('get-purchase-orders-data-for-grouped-report');


    Route::get('get-purchase-orders-data-for-report-footer-values', 'PurchasingController@getPoDataForReportFooter')->name('get-purchase-orders-data-for-report-footer-values');

    Route::get('purchasing-report-main', 'PurchasingController@purchasingReportMain')->name('purchasing-report-main');
    Route::post('/export-purchasing-report-main', 'PurchasingController@exportPurchasingReportMain')->name('export-purchasing-report-main');
    Route::get('get-purchase-orders-main-data-for-report', 'PurchasingController@getPoDataMainForReport')->name('get-purchase-orders-main-data-for-report');

    Route::get('get-transfer-document-data', 'PurchaseOrderController@getTransferDocumentData')->name('get-transfer-document-data');
    Route::get('delete-transfer-documents', 'PurchaseOrderController@deleteTransferDocuments')->name('delete-transfer-documents');

    Route::get('get-all-pos-data', 'PurchaseOrderController@getAllPoData')->name('get-all-pos-data');
    Route::get('get-po-w_s_info-data', 'PurchaseOrderController@get_wsInfoData')->name('get-po-w_s_info-data');
    Route::get('get-po-d_f_supplier-data', 'PurchaseOrderController@get_dfSupplierData')->name('get-po-d_f_supplier-data');
    Route::get('get-po-r_i_stock-data', 'PurchaseOrderController@get_riStockData')->name('get-po-r_i_stock-data');
    // draft po data
    Route::get('get-draft-purchase-orders-data', 'PurchaseOrderController@getDraftPoData')->name('get-draft-purchase-orders-data');
    Route::get('get-draft-transfer-data', 'PurchaseOrderController@getDraftTdData')->name('get-draft-transfer-data');

    Route::get('get-purchase-order-detail/{id}', 'PurchaseOrderController@getPurchaseOrderDetail')->name('get-purchase-order-detail');
    Route::post('export-waiting-conf-td', 'PurchaseOrderController@exportWaitingConformationTD')->name('export-waiting-conf-td');
    Route::post('export-waiting-conformation-po', 'PurchaseOrderController@exportWaitingConformationPO')->name('export-waiting-conformation-po');
    Route::get('get-purchase-order-history', 'PurchaseOrderController@getPurchaseOrderHistory')->name('get-purchase-order-history');

    Route::get('get-draft-purchase-order-history', 'PurchaseOrderController@getDraftPurchaseOrderHistory')->name('get-draft-purchase-order-history');

    Route::get('get-purchase-order-status-history', 'PurchaseOrderController@getPurchaseOrderStatusHistory')->name('get-purchase-order-status-history');

    // getting purchase order product details
    Route::get('get-purchase-order-product-detail/{id}', 'PurchaseOrderController@getPurchaseOrderProdDetail')->name('get-purchase-order-product-detail');
    Route::get('get-purchase-order-product-detail-td/{id}', 'PurchaseOrderController@getPurchaseOrderProdDetailTD')->name('get-purchase-order-product-detail-td');
    Route::post('add-purchase-order-document', 'PurchaseOrderController@uploadPurchaseOrderDocuments')->name('add-purchase-order-document');
    Route::post('add-draft-purchase-order-document', 'PurchaseOrderController@uploadDraftPurchaseOrderDocuments')->name('add-draft-purchase-order-document');
    Route::post('get-draft-order-files', 'PurchaseOrderController@getDraftOrderFiles')->name('get-draft-order-files');

    Route::get('remove-draft-order-file', 'PurchaseOrderController@removeDraftOrderFile')->name('remove-draft-order-file');

    Route::get('revert-po-status-to-wc', 'PurchaseOrderController@revertPoStatusToWc')->name('revert-po-status-to-wc');
    Route::get('delete-product-from-po', 'PurchaseOrderController@deleteProdFromPo')->name('delete-product-from-po');
    Route::get('delete-product-from-po-detail', 'PurchaseOrderController@deleteProdFromPoDetail')->name('delete-product-from-po-detail');
    Route::get('check-po-product-numbers', 'PurchaseOrderController@checkPoProductNumbers')->name('check-po-product-numbers');
    Route::post('export-po-to-pdf/{id}', 'PurchaseOrderController@exportToPDF')->name('export-po-to-pdf');
    //Draft Po print button route
    Route::get('export-draft-po-to-pdf/{id}/{show_price_input}/{column_name}/{sort_order}/{pf_logo?}', 'PurchaseOrderController@exportDraftPOToPDF')->name('export-draft-po-to-pdf');
    Route::post('export-draft-to-pdf/{id}', 'DraftInvoicesController@exportDraftToPDF')->name('export-draft-to-pdf');
    Route::get('create-purchase-order-direct', 'PurchaseOrderController@createDirectPurchaseOrder')->name('create-purchase-order-direct');
    Route::get('create-transfer-document', 'PurchaseOrderController@createTransferDoc')->name('create-transfer-document');
    Route::get('get-draft-po/{id}', 'PurchaseOrderController@getDraftPo')->name('get-draft-po');
    Route::post('export-draft-po', 'PurchaseOrderController@exportDraftPo')->name('export-draft-po');
    Route::post('check-qty-export-draft-po', 'PurchaseOrderController@checkQtyExportDraftPo')->name('check-qty-export-draft-po');
    Route::get('get-draft-td/{id}', 'PurchaseOrderController@getDraftTd')->name('get-draft-td');
    Route::post('update-transfer-document', 'PurchaseOrderController@updateTransferDocument')->name('update-transfer-document');
    Route::post('update-po-transfer-document', 'PurchaseOrderController@updatePoTransferDocument')->name('update-po-transfer-document');
    Route::post('update-transfer-document-detail', 'PurchaseOrderController@updateTransferDocumentDetail')->name('update-transfer-document-detail');
    Route::post('update-po-transfer-document-detail', 'PurchaseOrderController@updatePoTransferDocumentDetail')->name('update-po-transfer-document-detail');
    Route::post('update-po-transfer-document-detail-for-reserving', 'PurchaseOrderController@updatePoTransferDocumentDetailForReserving')->name('update-po-transfer-document-detail-for-reserving');
    Route::post('update-draft-po-transfer-document-detail-for-reserving', 'PurchaseOrderController@updateDraftPoTransferDocumentDetailForReserving')->name('update-draft-po-transfer-document-detail-for-reserving');
    Route::post('export-draft-td', 'PurchaseOrderController@exportDraftTd')->name('export-draft-td');
    Route::post('add-supplier-to-draft-po', 'PurchaseOrderController@AddSupplierToDraftPo')->name('add-supplier-to-draft-po');
    Route::post('autocomplete-fetching-products', 'PurchaseOrderController@autocompleteFetchProduct')->name('autocomplete-fetching-products');

    Route::post('autocomplete-fetching-products-for-transfer', 'PurchaseOrderController@autocompleteFetchProductForTD')->name('autocomplete-fetching-products-for-transfer');

    Route::post('autocomplete-fetching-products-for-po', 'PurchaseOrderController@autocompleteFetchProductsForPurchaseOrder')->name('autocomplete-fetching-products-for-po');
    Route::post('autocomplete-fetching-products-for-td', 'PurchaseOrderController@autocompleteFetchProductsForTransferDoc')->name('autocomplete-fetching-products-for-td');
    Route::post('add-prod-to-draft-po', 'PurchaseOrderController@addProdToPo')->name('add-prod-to-draft-po');

    Route::post('add-prod-to-po-detail', 'PurchaseOrderController@addProdToPoDetail')->name('add-prod-to-po-detail');

    Route::get('get-product-to-list-draft-po/{id}', 'PurchaseOrderController@getDataFromPoDetail')->name('get-product-to-list-draft-po');
    Route::get('check-if-products-exist-on-dpo', 'PurchaseOrderController@checkIfProductExistOnDpo')->name('check-if-products-exist-on-dpo');

    Route::post('warehouse-save-in-draft-po', 'PurchaseOrderController@warehouseSaveInDraftPo')->name('warehouse-save-in-draft-po');
    Route::post('payment-term-save-in-dpo', 'PurchaseOrderController@paymentTermSaveInDpo')->name('payment-term-save-in-dpo');
    Route::post('add-prod-by-refrence-number', 'PurchaseOrderController@addProdByRefrenceNumber')->name('add-prod-by-refrence-number');

    Route::post('add-prod-by-refrence-number-in-po-detail', 'PurchaseOrderController@addProdByRefrenceNumberInPoDetail')->name('add-prod-by-refrence-number-in-po-detail');
    Route::get('remove-draft-po-product', 'PurchaseOrderController@removeProductFromDraftPo')->name('remove-draft-po-product');
    Route::get('delete-draft-po-note', 'PurchaseOrderController@deleteDraftPoNote')->name('delete-draft-po-note');
    Route::get('delete-po-detail-note', 'PurchaseOrderController@deletePoDetailNote')->name('delete-po-detail-note');
    Route::post('action-draft-po', 'PurchaseOrderController@doActionDraftPo')->name('action-draft-po');
    Route::post('action-draft-td', 'PurchaseOrderController@doActionDraftTd')->name('action-draft-td');
    Route::get('getting-draft-po-docs-for-download/{id}', 'PurchaseOrderController@downloadDraftPoDocuments')->name('getting-draft-po-docs-for-download');
    Route::get('getting-docs-for-download/{id}', 'PurchaseOrderController@downloadDocuments')->name('getting-docs-for-download');
    Route::post('confirm-purchase-order', 'PurchaseOrderController@confirmPurchaseOrder')->name('confirm-purchase-order');
    Route::post('cancel-purchase-order', 'PurchaseOrderController@cancelPurchaseOrder')->name('cancel-purchase-order');
    Route::post('confirm-transfer-document', 'PurchaseOrderController@confirmTransferDocument')->name('confirm-transfer-document');

    Route::post('get-purchase-order-files', 'PurchaseOrderController@getPurchaseOrderFiles')->name('get-purchase-order-files');
    Route::get('remove-purchase-order-file', 'PurchaseOrderController@removePurchaseOrderFile')->name('remove-purchase-order-file');

    Route::get('get-po-customers', 'PurchaseOrderController@getPoCustomers')->name('get-po-customers');
    Route::get('get-po-notes', 'PurchaseOrderController@getPoNotes')->name('get-po-notes');
    Route::get('changing-status-of-po', 'PurchaseOrderController@changeStatusOfPo')->name('changing-status-of-po');
    /****************** Purchase Profile Setting ROUTES**************************/
    Route::get('change-password', 'HomeController@changePassword')->name('change-password');
    Route::post('change-password-process', 'HomeController@changePasswordProcess')->name('change-password-process');
    Route::post('check-old-password', 'HomeController@checkOldPassword');

    Route::get('profile-setting', 'HomeController@profile');
    Route::post('profile-setting', 'HomeController@updateProfile');
    // getting product sub categoty
    Route::get('get-product-sub-cat', 'ProductController@getProdSubCat')->name('get-product-sub-cat');

    //Delete draft po
    Route::get('delete-draft-po', 'PurchaseOrderController@deleteDraftPo')->name('delete-draft-po');
    Route::get('actions_for_selected_pos', 'PurchaseOrderController@actionsForSelectedPos')->name('actions_for_selected_pos');

    //new routes
    Route::get('table-po-pod-list', 'PurchaseOrderController@getPoInOutBalance')->name('table-po-pod-list');
    Route::get('get-html-of-stock-data', 'PurchaseOrderController@getStockData')->name('get-html-of-stock-data');

    Route::get('get-html-of-stock-data-card', 'PurchaseOrderController@getStockDataCard')->name('get-html-of-stock-data-card');

    Route::get('recursive_call_for_stock_card_job', 'PurchaseOrderController@recursiveCallForStockCardJob')->name('recursive_call_for_stock_card_job');
    // Route::get('get-prod-types', 'ProductController@getProductTypes')->name('get-prod-types');
    // Route::get('get-prod-categories', 'ProductController@getProductCategories')->name('get-prod-categories');
    // Route::get('get-prod-units', 'ProductController@getProductUnits')->name('get-prod-units');
    Route::get('get-prod-dropdowns', 'ProductController@getProductsDropDowns')->name('get-prod-dropdowns');
    Route::get('get-supplier-dropdowns', 'ProductController@getSupplierDropDowns')->name('get-supplier-dropdowns');
    Route::get('stock-report', 'HomeController@stockReportDashboard')->name('stock-report');
    Route::get('/get-datatables-for-stock-report', 'HomeController@getStockReport')->name('get-stock-report');
    Route::get('/get-stock-report-from-product-detail', 'HomeController@getStockReportFromProductDetail')->name('get-stock-report-from-product-detail');

    Route::get('stock-detail-report', 'HomeController@stockDetailReport')->name('stock-detail-report');
    Route::get('stock-detail-report-wpm', 'HomeController@stockDetailReportWithPm')->name('stock-detail-report-wpm');
    Route::get('get-stock-detail-report', 'HomeController@getStockDetailReport')->name('get-stock-detail-report');
    Route::post('get-custom-invoice-number', 'HomeController@getCustomInvoiceNumber')->name('get-custom-invoice-number');
    Route::post('update-stock-invoice-number', 'HomeController@updateCustomInvoiceNumber')->name('update-stock-invoice-number');


    /*************************** Routes for Version Number ************************/
    Route::get('version', 'VersionController@index')->name('version');
    Route::get('view-version', 'VersionController@viewVersions')->name('view-version');
    Route::get('/get-version', 'VersionController@getVersionData')->name('get-version');
    Route::get('/create-version', 'VersionController@createVersion')->name('create-version');
    Route::get('/delete-version', 'VersionController@deleteVersion')->name('delete-version');
    Route::post('/add-version', 'VersionController@addVersion')->name('add-version');
    Route::get('/publish-version', 'VersionController@publishVersion')->name('publish-version');
    Route::get('/edit-version/{id}', 'VersionController@editVersion')->name('edit-version');
    Route::post('update-version', 'VersionController@updateVersion')->name('update-version');
    Route::get('/view-version-detail/{id}', 'VersionController@viewVersion')->name('view-version-detail');
    Route::get('/show-version-detail', 'VersionController@showVersionDetail')->name('show-version-detail');
});

Route::get('user-login-from-admin/{token_for_admin_login}/{user_id}', 'UserController@userLoginFromAdmin');

Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'middleware' => 'admin'], function () {

    // Spoilage Report Routes
    Route::get('/spoilage-report', 'SpoilageReportController@index')->name('spoilage-report');
    Route::get('/get-spoilage-report', 'SpoilageReportController@getSpoilageReport')->name('get-spoilage-report');

    Route::get('/update_products', function () {
        $orders = App\Models\Common\Order\Order::where('primary_status', 3)->get();
        // dd($orders);

        foreach ($orders as $order) {
            # code...
            $vat = 0;
            $sub_total = 0;
            $sub_total_w_w = 0;
            $query = App\Models\Common\Order\OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                // $sub_total += $value->total_price;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $sub_total += $value->total_price;
                $sub_total_w_w += $value->total_price_with_vat;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $vat += @$value->total_price * (@$value->vat / 100);
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            $order->total_amount = $grand_total;
            $order->save();
        }
    });

    //to update the total price item level on 2 decimal numbers
    Route::get('/update_items_total', function () {
        $orders = App\Models\Common\order\Order::where('primary_status', 1)->get();
        // dd($orders);

        foreach ($orders as $order) {
            # code...
            $vat = 0;
            $sub_total = 0;
            $sub_total_w_w = 0;
            $query = App\Models\Common\Order\OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                // $sub_total += $value->total_price;
                // $vat += $value->total_price_with_vat-$value->total_price;

                if ($value->is_retail == 'qty') {
                    $result = round($value->unit_price, 2) * $value->quantity;
                    $value->total_price = round($value->unit_price, 2) * $value->quantity;
                    $total_price_with_vat = round((($value->vat / 100) * round($value->unit_price, 2)) + round($value->unit_price, 2), 2);
                    $value->total_price_with_vat = $total_price_with_vat * $value->quantity;
                    $value->save();
                } else if ($value->is_retail == 'pieces') {
                    $result = round($value->unit_price, 2) * $value->number_of_pieces;
                    $value->total_price = round($value->unit_price, 2) * $value->number_of_pieces;
                    $total_price_with_vat = round((($value->vat / 100) * round($value->unit_price, 2)) + round($value->unit_price, 2), 2);
                    $value->total_price_with_vat = $total_price_with_vat * $value->number_of_pieces;
                    $value->save();
                }
                $discount = $value->discount;
                if ($discount != null) {
                    $dis = $discount / 100;
                    $discount_value = $dis * $value->total_price;
                    $discount_value2 = $dis * $value->total_price_with_vat;
                    $result = $value->total_price - $discount_value;
                    $result2 = $value->total_price_with_vat - $discount_value2;
                } else {
                    $result = $value->total_price;
                    $result2 = $value->total_price_with_vat;
                }

                $value->total_price = $result;
                $value->total_price_with_vat = $result2;
                $value->save();
                $sub_total += $value->total_price;
                $sub_total_w_w += $value->total_price_with_vat;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $vat += @$value->total_price * (@$value->vat / 100);
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            $order->total_amount = $grand_total;
            $order->save();
        }
    });

    //to update the total price item level on 2 decimal numbers
    Route::get('/update_items_total_draft', function () {
        $orders = App\Models\Common\order\Order::where('primary_status', 2)->get();
        // dd($orders);

        foreach ($orders as $order) {
            # code...
            $vat = 0;
            $sub_total = 0;
            $sub_total_w_w = 0;
            $query = App\Models\Common\Order\OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                // $sub_total += $value->total_price;
                // $vat += $value->total_price_with_vat-$value->total_price;
                if ($value->is_retail == 'qty') {
                    $result = round($value->unit_price, 2) * $value->quantity;
                    $value->total_price = round($value->unit_price, 2) * $value->quantity;
                    $total_price_with_vat = round((($value->vat / 100) * round($value->unit_price, 2)) + round($value->unit_price, 2), 2);
                    $value->total_price_with_vat = $total_price_with_vat * $value->quantity;
                    $value->save();
                } else if ($value->is_retail == 'pieces') {
                    $result = round($value->unit_price, 2) * $value->number_of_pieces;
                    $value->total_price = round($value->unit_price, 2) * $value->number_of_pieces;
                    $total_price_with_vat = round((($value->vat / 100) * round($value->unit_price, 2)) + round($value->unit_price, 2), 2);
                    $value->total_price_with_vat = $total_price_with_vat * $value->number_of_pieces;
                    $value->save();
                }
                $discount = $value->discount;
                if ($discount != null) {
                    $dis = $discount / 100;
                    $discount_value = $dis * $value->total_price;
                    $discount_value2 = $dis * $value->total_price_with_vat;
                    $result = $value->total_price - $discount_value;
                    $result2 = $value->total_price_with_vat - $discount_value2;
                } else {
                    $result = $value->total_price;
                    $result2 = $value->total_price_with_vat;
                }

                $value->total_price = $result;
                $value->total_price_with_vat = $result2;
                $value->save();
                $sub_total += $value->total_price;
                $sub_total_w_w += $value->total_price_with_vat;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $vat += @$value->total_price * (@$value->vat / 100);
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            $order->total_amount = $grand_total;
            $order->save();
        }
    });

    Route::get('/update_items_total_invoice', function () {
        $orders = App\Models\Common\order\Order::where('primary_status', 3)->get();
        // dd($orders);

        foreach ($orders as $order) {
            # code...
            $vat = 0;
            $sub_total = 0;
            $sub_total_w_w = 0;
            $query = App\Models\Common\Order\OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                // $sub_total += $value->total_price;
                // $vat += $value->total_price_with_vat-$value->total_price;
                if ($value->is_retail == 'qty') {
                    $result = round($value->unit_price, 2) * $value->qty_shipped;
                    $value->total_price = round($value->unit_price, 2) * $value->qty_shipped;
                    $total_price_with_vat = round((($value->vat / 100) * round($value->unit_price, 2)) + round($value->unit_price, 2), 2);
                    $value->total_price_with_vat = $total_price_with_vat * $value->qty_shipped;
                    $value->save();
                } else if ($value->is_retail == 'pieces') {
                    $result = round($value->unit_price, 2) * $value->pcs_shipped;
                    $value->total_price = round($value->unit_price, 2) * $value->pcs_shipped;
                    $total_price_with_vat = round((($value->vat / 100) * round($value->unit_price, 2)) + round($value->unit_price, 2), 2);
                    $value->total_price_with_vat = $total_price_with_vat * $value->pcs_shipped;
                    $value->save();
                }
                $discount = $value->discount;
                if ($discount != null) {
                    $dis = $discount / 100;
                    $discount_value = $dis * $value->total_price;
                    $discount_value2 = $dis * $value->total_price_with_vat;
                    $result = $value->total_price - $discount_value;
                    $result2 = $value->total_price_with_vat - $discount_value2;
                } else {
                    $result = $value->total_price;
                    $result2 = $value->total_price_with_vat;
                }

                $value->total_price = $result;
                $value->total_price_with_vat = $result2;
                $value->save();
                $sub_total += $value->total_price;
                $sub_total_w_w += $value->total_price_with_vat;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $vat += @$value->total_price * (@$value->vat / 100);
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            $order->total_amount = $grand_total;
            $order->save();
        }
    });

    Route::get('/check_order_total', function () {
        $orders = App\Models\Common\order\Order::all();
        // dd($orders);
        $html = '';
        $html2 = '';

        $table = '
		      	<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Order ID</th>
		      			<th>ref_id</th>
		      			<th>No. of items </th>
		      			<th> Vat items </th>
		      			<th> Non Vat items </th>
		      			<th> No of Billed Items </th>
		      			<th>Item Total </th>
		      			<th> Order Total </th>
		      			<th> Status </th>
		      		</tr>
		      	</thead>
		      	<tbody>';

        foreach ($orders as $order) {
            # code...
            $vat = 0;
            $sub_total = 0;
            $sub_total_w_w = 0;
            $query = App\Models\Common\Order\OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                // $sub_total += $value->total_price;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $sub_total += $value->total_price;
                $sub_total_w_w += $value->total_price_with_vat;
                // $vat += $value->total_price_with_vat-$value->total_price;
                $vat += @$value->total_price * (@$value->vat / 100);
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            // $order->total_amount = $grand_total;
            // $order->save();

            $total = $order->total_amount;
            $total = (int)$total;
            $grand_total = (int)$grand_total;
            if ($total != $grand_total) {
                $html .= ' ' . $order->id;
                $html2 .= '(' . $grand_total . ' ' . $total . '),';

                $table .= '
		      <tr>';
                $table .= '<td>' . $order->id . '</td>';
                if ($order->primary_status == 3) {
                    $table .= '<td>' . $order->in_ref_id . '</td>';
                } else {
                    $table .= '<td>' . $order->ref_id . '</td>';
                }
                $table .= '<td>' . $order->order_products()->count() . '</td>';
                $table .= '<td>' . $order->order_products()->where('vat', '>', '0')->count() . '</td>';
                $table .= '<td>' . $order->order_products()->whereNull('vat')->count() . '</td>';
                $table .= '<td>' . $order->order_products()->where('is_billed', 'Billed')->count() . '</td>';
                $table .= '<td>' . $grand_total . '</td>';
                $table .= '<td>' . $total . '</td>';
                $table .= '<td>' . $order->statuses->title . '</td>';
                $table .= '</tr>
		      ';
            }
        }

        $table .= '</tbody>
		      	</table>
		      ';

        return $table;
    });

    Route::get('/check_deleted_supplier_products', function () {
        $pos = App\Models\Common\PurchaseOrders\PurchaseOrder::whereNotNull('supplier_id')->get();
        // dd($pos);
        $html_string = '
			<table class="table dot-dash text-center">
            <thead class="dot-dash">
            <tr>
            	<th>PO Id</th>
                <th></th>
                <th></th>
                <th>PO.Ref</th>
                <th></th>
                <th></th>
                <th>Status</th>
                <th></th>
                <th></th>
                <th>Product</th>
            </tr>
            </thead>
            <tbody>';
        foreach ($pos as $po) {
            $query = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::where('po_id', $po->id)->where('is_billed', 'Product')->whereNotNull('product_id')->get();
            foreach ($query as  $value) {
                $sup_prod = App\Models\Common\SupplierProducts::where('product_id', $value->product_id)->where('supplier_id', $value->PurchaseOrder->supplier_id)->first();

                if ($sup_prod == NULL) {
                    $html_string .=
                        '<tr>
		        		<td>' . $value->PurchaseOrder->id . '</td>
		                <td></td>
		                <td></td>
		                <td>' . $value->PurchaseOrder->ref_id . '</td>
		                <td></td>
		                <td></td>
		                <td>' . $value->PurchaseOrder->p_o_statuses->title . '</td>
		                <td></td>
		                <td></td>
		                <td>' . $value->product->refrence_code . '</td>
		            </tr>';
                } else {
                    // do nothing
                }
            }
        }
        $html_string .= '</tbody></table>';

        return $html_string;
    });

    Route::get('/check_po_prices', function () {
        $pos = App\Models\Common\PurchaseOrders\PurchaseOrder::whereNotNull('supplier_id')->get();
        // dd($pos);
        $po_ids = '';
        $po_total = '';
        $i = 0;
        $html_string = '
			<table class="table dot-dash text-center">
            <thead class="dot-dash">
            <tr>
                <th>PO.ID</th>
                <th></th>
                <th></th>
                <th>PO.Ref</th>
                <th></th>
                <th></th>
                <th>Status</th>
                <th></th>
                <th></th>
                <th>PO Total</th>
                <th></th>
                <th></th>
                <th>New Total</th>
            </tr>
            </thead>
            <tbody>';
        foreach ($pos as $po) {
            $i++;
            $system_total = 0;
            $sub_total    = 0;
            $query = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::where('po_id', $po->id)->get();
            foreach ($query as  $value) {
                $unit_price = $value->pod_unit_price;
                $sub = $value->quantity * $unit_price - (($value->quantity * $unit_price) * ($value->discount / 100));
                // $sub = $value->pod_total_unit_price - (($value->pod_total_unit_price) * ($value->discount / 100));
                $sub_total += $sub;
            }

            $system_total += $po->total;
            if ((int)$sub_total !== (int)$system_total) {
                $html_string .=
                    '<tr>
	                <td>' . $po->id . '</td>
	                <td></td>
	                <td></td>
	                <td>' . $po->ref_id . '</td>
	                <td></td>
	                <td></td>
	                <td>' . $po->p_o_statuses->title . '</td>
	                <td></td>
	                <td></td>
	                <td>' . (int)$system_total . '</td>
	                <td></td>
	                <td></td>
	                <td>' . (int)$sub_total . '</td>
	            </tr>';
            }
        }
        $html_string .= '</tbody></table>';

        return $html_string;
    });

    Route::get('/update_order_products', function () {
        $queryy = App\Models\Common\Order\OrderProduct::where('is_retail', 'pieces')->whereNull('pcs_shipped')->where('status', 11)->count();
        $query = App\Models\Common\Order\OrderProduct::where('is_retail', 'pieces')->whereNull('pcs_shipped')->where('status', 11)->get();
        foreach ($query as  $product) {
            $product->pcs_shipped = $product->number_of_pieces;
            $product->save();
        }



        return 'Total order products founds are ' . $queryy . ' and pcs shipped updated successfully';
    });

    /************   User Login History Routes  ************/
    Route::get('/users-login-history', 'HomeController@historyView')->name('users-login-history');
    Route::get('/superadmin-as-other-user', 'HomeController@superAdminAsUser')->name('superadmin-as-other-user');
    Route::get('/get-user-login-details', 'HomeController@getUserLoginDetails')->name('get-user-login-details');

    Route::post('/export-users-login-history-list', 'HomeController@exportUserLoginHistoryList')->name('export-users-login-history-list');
    Route::get('/check-status-for-first-time-user-login-list', 'HomeController@checkStatusFirstTimeForUserLogin')->name('check-status-for-first-time-user-login-list');
    Route::get('/recursive-export-status-user-login-list', 'HomeController@recursiveStatusCheckUserLogin')->name('recursive-export-status-user-login-list');

    /************   User Login History Routes  ************/

    Route::get('/', 'HomeController@getHome');
    Route::get('admin-complete-profile', 'HomeController@completeProfile');
    Route::post('admin-complete-profile', 'HomeController@completeProfileProcess');
    Route::get('suspend-user', 'HomeController@suspend')->name('suspend-user');
    Route::get('activate-user', 'HomeController@activate')->name('activate-user');
    // Route::post('check-old-password','HomeController@checkOldPassword');
    // Route::group(['middleware' => 'incomplete-profile'], function(){
    // 	Route::get('/','HomeController@getHome');
    // });

    /************   Bank Routes  ************/
    Route::get('/banks-list', 'BankController@index')->name('banks-list');
    Route::get('/get-bank', 'BankController@getData')->name('get-bank');
    Route::post('/add-bank', 'BankController@add')->name('add-bank');
    Route::post('/edit-bank', 'BankController@edit')->name('edit-bank');
    Route::get('/delete-bank', 'BankController@delete')->name('delete-bank');
    Route::get('/get-bank-data', 'BankController@getBankData')->name('get-bank-data');
    /************   Purchasing Routes  ************/
    Route::get('/purchasing', 'PurchasingController@index')->name('purchasing-list');
    Route::get('/get-datatables-for-purchasing', 'PurchasingController@getData')->name('get-purchasing');
    Route::post('/add-purchasing', 'PurchasingController@add')->name('add-purchasing');

    /************ All users Routes ****************/

    Route::get('/all_users', 'UsersController@index')->name('all-users-list');
    Route::get('export-bulk-users-file-download', 'UsersController@bulkUserUploadFileDownload')->name('export-bulk-users-file-download');
    Route::get('/get-datatables-for-users', 'UsersController@getData')->name('get-users');
    Route::get('/get-datatables-for-users-histories', 'UsersController@getUserHistory')->name('get-users-histories');
    Route::post('/add-users', 'UsersController@add')->name('add-users');
    Route::get('/user_detail/{id}', 'UsersController@detail')->name('user_detail');
    Route::post('change-user-password-by-admin', 'UsersController@changeUserPassword')->name('change-user-password-by-admin');
    Route::post('/save-user-data-user-detail-page', 'UsersController@saveUserDataUserDetailPage')->name('save-user-data-user-detail-page');
    Route::post('/add-bulk-users', 'UsersController@uploadUserBulk')->name('add-bulk-users');

    /************ Admin User dashboard access Routes ********/
    Route::get('create-token-of-user-for-admin-login', 'UsersController@createTokenOfUserForAdminLogin');

    /************ For Ecom Holidays Configuration ********/
    Route::get('/get-all-holidays-data', 'EcommerceConfigController@getAllHolidays')->name('get-all-holidays-data');
    Route::post('/add-ecom-holidays', 'EcommerceConfigController@addEcomHolidays')->name('add-ecom-holidays');

    /************   Sales Routes  ************/
    Route::get('/sales', 'SalesController@index')->name('sales-list');
    Route::get('/get-datatables-for-sales', 'SalesController@getData')->name('get-sales');
    Route::post('/add-sales', 'SalesController@add')->name('add-sales');
    Route::post('/get-sales-warehouse', 'SalesController@getSalesWarehouse')->name('get-sales-warehouse');

    /************   Sales Coordinators Routes  ************/
    Route::get('/sales-coordinators', 'SalesCoordinatorsController@index')->name('sales-coordinators-list');
    Route::get('/get-datatables-for-sales-coordinators', 'SalesCoordinatorsController@getData')->name('get-sales-coordinators');
    Route::post('/add-sales-coordinators', 'SalesCoordinatorsController@add')->name('add-sales-coordinators');

    /************    Importing Routes  ************/
    Route::get('/importing', 'ImportingController@index')->name('importing-list');
    Route::get('/get-datatables-for-importing', 'ImportingController@getData')->name('get-importing');
    Route::post('/add-importing', 'ImportingController@add')->name('add-importing');

    /************    Warehouse Routes  ************/
    Route::get('/warehouse', 'WarehouseController@index')->name('warehouse-list');
    Route::get('/get-datatables-for-warehouse', 'WarehouseController@getData')->name('get-warehouse');
    Route::get('/get-datatables-for-all-warehouses', 'WarehouseController@getAll')->name('get-all-warehouse');
    Route::get('update-warehouse', 'WarehouseController@updateWarehouse')->name('update-warehouse');
    Route::post('/add-warehouse', 'WarehouseController@add')->name('add-warehouse');
    Route::get('reset-default', 'WarehouseController@resetDefault')->name('reset-default');
    Route::get('set-default', 'WarehouseController@setDefault')->name('set-default');
    Route::post('save-warehouse-data', 'WarehouseController@saveWarehouseData')->name('save-warehouse-data');
    Route::get('suspend-selected-warehouse', 'WarehouseController@suspendSelectedWarehouse')->name('suspend-selected-warehouse');
    Route::get('activate-selected-warehouse', 'WarehouseController@activateSelectedWarehouse')->name('activate-selected-warehouse');
    Route::post('change-users-warehouse', 'WarehouseController@changeUsersWarehouse')->name('change-users-warehouse');

    /************    Accounting Routes  ************/
    Route::get('/accounting', 'AccountingController@index')->name('accounting-list');
    Route::get('/get-datatables-for-accounting', 'AccountingController@getData')->name('get-accounting');
    Route::post('/add-accounting', 'AccountingController@add')->name('add-accounting');

    /************   Product Category Routes  ************/
    Route::get('export-categories-data', 'ProductCategoryController@exportCatData')->name('export-categories-data');
    // bulk upload route below one
    Route::post('/upload-bulk-categories', 'ProductCategoryController@uploadBulkCategories')->name('upload-bulk-categories');
    Route::get('/product-categories-list', 'ProductCategoryController@index')->name('product-categories-list');
    Route::get('/get-product-categories', 'ProductCategoryController@getData')->name('get-product-categories');
    Route::post('/add-product-category', 'ProductCategoryController@add')->name('add-product-category');
    Route::post('/edit-product-category', 'ProductCategoryController@edit')->name('edit-product-category');
    Route::post('/edit-product-parent-category', 'ProductCategoryController@editParent')->name('edit-product-parent-category');
    Route::get('delete-category', 'ProductCategoryController@deleteCategory')->name('delete-category');
    Route::post('/update-products-margins', 'ProductCategoryController@updateProductMargins')->name('update-products-margins');
    Route::post('/update-products-margins-by-cat', 'ProductCategoryController@updateProductMarginsByCat')->name('update-products-margins-by-cat');
    /*job created for products margins*/
    Route::get('margins-update-job-status', 'ProductCategoryController@marginUpdateJobStatus')->name('margins-update-job-status');
    Route::get('check-status-for-first-time-margins-update', 'ProductCategoryController@checkStatusForFirstTimeMargins')->name('check-status-for-first-time-margins-update');
    Route::get('recursive-job-status-margins-update', 'ProductCategoryController@recursiveJobStatusMarginsUpdate')->name('recursive-job-status-margins-update');
    Route::post('/sync-product-category', 'ProductCategoryController@syncProductCategory')->name('sync-product-category');

    /************   Admin Report routes  ************/
    Route::get('customer-sales-report/{year?}', 'HomeController@customerSalesReport')->name('customer-sales-report');
    Route::get('export-customer-sales-report', 'HomeController@exportCustomerSalesReportWithJob')->name('export-customer-sale-report');
    Route::get('check-status-for-first-time-sales-customer-report', 'HomeController@checkStatusFirstTimeForCustomerSalesReport')->name('check-status-for-first-time-sales-customer-report');
    Route::get('recursive-export-status-customer-sales-report', 'HomeController@recursiveStatusCheck')->name('recursive-export-status-customer-sales-report');
    Route::get('recursive-export-status-account-receivable', 'HomeController@recursiveStatusCheckAccountReceivable')->name('recursive-export-status-account-receivable');
    Route::get('check-status-for-first-time-account-receivable-export', 'HomeController@checkStatusFirstTimeForAccountReceivableExport')->name('check-status-for-first-time-account-receivable-export');

    /************   Product Sale Report By month Routes  ************/
    Route::get('product-sales-report-by-month/{year?}', 'ProductSaleReportByMonthController@ProductSalesReportByMonth')->name('product-sales-report-by-month');
    Route::get('get-product-sale-report-by-month', 'ProductSaleReportByMonthController@getProductSalesReportByMonth')->name('get-product-sale-report-by-month');
    Route::get('export-product-sale-report-by-month', 'ProductSaleReportByMonthController@ExportProductSalesReportByMonth')->name('export-product-sale-report-by-month');
    Route::get('check-status-for-first-time-product-sales-report-by-month', 'ProductSaleReportByMonthController@CheckStatusProductSalesReportByMonth')->name('check-status-for-first-time-product-sales-report-by-month');
    Route::get('recursive-export-status-product-sales-report-by-month', 'ProductSaleReportByMonthController@RecursiveExportStatusProductSalesReportByMonth')->name('recursive-export-status-product-sales-report-by-month');




    Route::get('/get-datatables-for-customers-sale-report', 'HomeController@getCustomerSalesReport')->name('get-customers-sale-report');
    Route::post('/get-customer-sale-report-footer-values', 'HomeController@getCustomerSalesReportFooter')->name('get-customer-sale-report-footer-values');
    Route::post('/get-customer-sale-report-general-footer-values', 'HomeController@getCustomerSalesReportGeneralFooter')->name('get-customer-sale-report-general-footer-values');
    Route::get('get_customer_invoices_from_report/{customer_id}/{year}', function ($customer_id, $year) {
        // dd($customer_id,$year);
        return redirect('sales/invoices')->with(['year' => 'Yes', 'customer_id' => $customer_id, 'year' => $year]);
    })->name('get_customer_invoices_from_report');


    /************   Product sub Category Routes  ************/
    Route::get('/product-sub-categories-list/{id}', 'ProductCategoryController@subCategories')->name('product-sub-categories-list');
    Route::get('/get-product-sub-categories/{id}', 'ProductCategoryController@getSubData')->name('get-product-sub-categories');

    Route::post('/add-product-sub-category', 'ProductCategoryController@addProductSubCategory')->name('add-product-sub-category');
    Route::get('get-sub-cat-margin-detail', 'ProductCategoryController@getSubCatMarginDetail')->name('get-sub-cat-margin-detail');
    Route::get('validate-unique-prefix', 'ProductCategoryController@validateUniquePrefix')->name('validate-unique-prefix');
    Route::get('get-sub-cat-detail-for-edit', 'ProductCategoryController@getSubCatDetailForEdit')->name('get-sub-cat-detail-for-edit');
    Route::get('delete-sub-category', 'ProductCategoryController@deleteSubCat')->name('delete-sub-category');
    Route::get('get-prod-cat-name-for-edit', 'ProductCategoryController@getPrdCatNameForEdit')->name('get-prod-cat-name-for-edit');
    Route::get('get-dynamic-category-fields', 'ProductCategoryController@getDynamicCategoryFields')->name('get-dynamic-category-fields');
    Route::get('get-sub-category-history', 'ProductCategoryController@getSubCategoryHistory')->name('get-sub-category-history');

    /************   Unit Routes  ************/
    Route::get('/unit-list', 'UnitController@index')->name('unit-list');
    Route::get('/get-unit', 'UnitController@getData')->name('get-unit');
    Route::post('/add-unit', 'UnitController@add')->name('add-unit');
    Route::post('/edit-unit', 'UnitController@edit')->name('edit-unit');
    Route::get('/delete-unit', 'UnitController@delete')->name('delete-unit');
    Route::get('/unit-history', 'UnitController@getUnitHistory')->name('unit-history');

    /************   Country Routes  ************/
    Route::get('/country-list', 'CountryController@index')->name('country-list');
    Route::get('/get-country', 'CountryController@getData')->name('get-country');
    Route::post('/add-country', 'CountryController@add')->name('add-country');
    Route::post('/edit-country', 'CountryController@edit')->name('edit-country');
    Route::get('/delete-country', 'CountryController@delete')->name('delete-country');

    /************   Billing Configuration Routes  ************/
    Route::get('/billing-configuration', 'BillingConfigurationController@index')->name('billing-configuration');
    Route::post('/billing-configuration/save', 'BillingConfigurationController@saveData')->name('save-billing-configuration');
    Route::post('/billing-configuration/save-type', 'BillingConfigurationController@saveConfigType')->name('save-config-type');


    /************   Page Setting Routes  ************/
    Route::post('/add-page-setting', 'QuotationConfigController@addPageSetting')->name('add-page-setting');
    /************   Distrct Routes  ************/
    Route::get('/district-list', 'DistrcitController@index')->name('district-list');
    Route::get('/get-district', 'DistrcitController@getData')->name('get-district');
    Route::post('/add-district', 'DistrcitController@add')->name('add-district');
    Route::get('/edit-district', 'DistrcitController@getedit')->name('edit-district');
    Route::post('/edit-district', 'DistrcitController@edit')->name('edit-district');
    Route::get('/delete-district', 'DistrcitController@delete')->name('delete-district');


    /****************** Admin Profile Setting ROUTES**************************/
    Route::get('change-password-admin', 'HomeController@changePassword')->name('change-password-admin');
    Route::post('change-admin-password-process', 'HomeController@changePasswordProcess')->name('change-admin-password-process');
    Route::post('check-admin-old-password', 'HomeController@checkOldPassword')->name('check-admin-old-password');

    Route::get('admin-profile-setting', 'HomeController@profile')->name('admin-profile-setting');
    Route::post('admin-update-profile-setting', 'HomeController@updateProfile')->name('admin-update-profile-setting');

    /************  Menu Routes  ************/
    Route::get('/menu-list', 'MenuController@index')->name('menu-list');
    Route::post('/edit-menu', 'MenuController@edit')->name('edit-menu');
    Route::get('/get-menus', 'MenuController@getData')->name('get-menus');
    Route::post('/add-menu', 'MenuController@store')->name('add-menu');
    Route::get('/sub-menus-list/{id}', 'MenuController@subMenus')->name('sub-menus-list');
    Route::get('/get-sub-menus/{id}', 'MenuController@getSubData')->name('get-sub-menus');
    Route::post('/delete-menu', 'MenuController@delete')->name('delete-menu');
    Route::get('sorting-menus', 'MenuController@sortingMenu')->name('sorting-menus');


    /************  Variables Routes  ************/
    Route::get('/variables-list', 'VariableController@index')->name('variables-list');
    Route::post('/edit-variable', 'VariableController@edit')->name('edit-variable');
    Route::get('/get-variables', 'VariableController@getData')->name('get-variables');
    Route::post('/add-variable', 'VariableController@store')->name('add-variable');
    Route::post('/delete-variable', 'VariableController@delete')->name('delete-variable');

    /************  common search configuration  ************/
    Route::get('/search-config', 'SearchConfiguration@index')->name('search-config');
    Route::get('/update-search-config', 'SearchConfiguration@updateSearchConfig')->name('update-search-config');
    Route::get('/update-search-config-columns', 'SearchConfiguration@updateSearchConfigColumns')->name('update-search-config-columns');

    /************  Ecommerce search configuration  ************/
    Route::get('/ecommerce-config', 'EcommerceConfigController@index')->name('ecommerce-config');
    Route::get('/update-ecommerce-config', 'EcommerceConfigController@updateSearchConfig')->name('update-ecommerce-config');
    Route::get('/update-ecommerce-config-columns', 'EcommerceConfigController@updateSearchConfigColumns')->name('update-ecommerce-config-columns');

    /********************************Groups Config********************/
    Route::get('/groups-config', 'GroupConfigController@index')->name('groups-config');
    Route::post('/add-groups-setting', 'GroupConfigController@addProductPageSetting')->name('add-groups-setting');
    Route::get('update-groups-config', 'GroupConfigController@updateGroupsConfig')->name('update-groups-config');

    /************** Warehouse Config *********************************/
    Route::post('/add-warehouse-setting', 'GroupConfigController@addWarehouseConfig')->name('add-warehouse-setting');
    Route::get('update-warehouse-config', 'GroupConfigController@updateWarehouseConfig')->name('update-warehouse-config');

    /************  Quotation  ************/
    Route::get('/quotation-config', 'QuotationConfigController@index')->name('quotation-config');
    Route::get('/products-config', 'ProductConfigController@index')->name('products-config');
    Route::post('/update-customer-category-config', 'ProductConfigController@UpdateCustomerCategoryConfig')->name('update-customer-category-config');
    Route::get('/product-detail-config', 'ProductConfigController@ProductDetailConfig')->name('product-detail-config');
    Route::post('/add-product-detail-config', 'ProductConfigController@addProductDetailPageSetting')->name('add-product-detail-config');
    Route::get('update-product-detail-config', 'ProductConfigController@updateProductDetailConfig')->name('update-product-detail-config');
    Route::get('update-supplier-detail-config', 'ProductConfigController@updateSupplierDetailConfig')->name('update-supplier-detail-config');
    Route::get('get_selected_section_config', 'ProductConfigController@getSelectedSectionConfig')->name('get_selected_section_config');
    Route::post('delete-product-detail-config', 'ProductConfigController@deleteProductDetailConfig')->name('delete-product-detail-config');


    // Route::post('/edit-variable','VariableController@edit')->name('edit-variable');
    // Route::get('/get-variables','VariableController@getData')->name('get-variables');
    Route::get('/qutotaion-config-order', 'QuotationConfigController@addConfiugration')->name('qutotaion-config-order');
    // Route::get('/qutotaion-config-print','QuotationConfigController@addConfiugration')->name('qutotaion-config-print');
    Route::post('/add-products-setting', 'ProductConfigController@addProductPageSetting')->name('add-products-setting');
    // Route::post('/delete-variable','VariableController@delete')->name('delete-variable');

     /************  Customer  ************/
     Route::get('/customer-detail-config', 'CustomerConfigController@CustomerDetailConfig')->name('customer-detail-config');
     Route::get('/add-customer-config', 'CustomerConfigController@addConfiugration')->name('add-customer-config');
     Route::get('update-customer-detail-config', 'CustomerConfigController@updateCustomerDetailConfig')->name('update-customer-detail-config');

    /************ Po Confiugration */
    Route::get('/po-config', 'POConfigController@index')->name('po-config');
    Route::get('/po-config-add', 'POConfigController@addConfiugration')->name('po-config-add');
    Route::post('/save-po-vat-configuration', 'POConfigController@savePoVatConfiguration')->name('save-po-vat-configuration');
    /************  PickInstruction Config Routes  ************/
    Route::get('/pick-instruction-config', 'PickInstructionConfigController@index')->name('pick-instruction-config');
    Route::get('/pi-add-config', 'PickInstructionConfigController@addConfig')->name('pi-add-config');
    Route::get('/partial-pi-add-config', 'PickInstructionConfigController@partialPickConfig')->name('partial-pi-add-config');
    Route::get('/pi-redirection-add-config', 'PickInstructionConfigController@RedirectionPickConfig')->name('pi-redirection-add-config');

    /************  Statuses Routes  ************/
    Route::get('/status-list', 'StatusesController@index')->name('status-list');
    Route::get('/get-status', 'StatusesController@getData')->name('get-status');
    Route::post('/add-status', 'StatusesController@add')->name('add-status');
    Route::post('/edit-status', 'StatusesController@edit')->name('edit-status');

    Route::get('/sub-statuses-list/{id}', 'StatusesController@subStatuses')->name('sub-statuses-list');
    Route::get('/get-sub-statuses/{id}', 'StatusesController@getSubData')->name('get-sub-statuses');


    /************   Payment Types Routes  ************/
    Route::get('/payment-type-list', 'PaymentTypesController@index')->name('payment-type-list');
    Route::get('/get-payment-type', 'PaymentTypesController@getData')->name('get-payment-type');
    Route::post('/add-payment-type', 'PaymentTypesController@add')->name('add-payment-type');
    Route::post('/edit-payment-type', 'PaymentTypesController@edit')->name('edit-payment-type');
    Route::post('/payment-visible-in-customer', 'PaymentTypesController@paymentVisibleInCustomer')->name('payment-visible-in-customer');
    Route::get('/check-payment-type-of-customer', 'PaymentTypesController@checkPaymenttTypeOfCustomer')->name('check-payment-type-of-customer');
    Route::get('/delete-payment-type', 'PaymentTypesController@deletePaymentType')->name('delete-payment-type');

    /************   Payment Terms Routes  ************/
    Route::get('/payment-term-list', 'PaymentTermsController@index')->name('payment-term-list');
    Route::get('/get-payment-term', 'PaymentTermsController@getData')->name('get-payment-term');
    Route::post('/add-payment-term', 'PaymentTermsController@add')->name('add-payment-term');
    Route::post('/edit-payment-term', 'PaymentTermsController@edit')->name('edit-payment-term');
    Route::get('/delete-payment-term', 'PaymentTermsController@deletePaymentTerm')->name('delete-payment-term');


    /************* Currencies routes **************** */
    Route::get('/currency-list', 'CurrencyController@index')->name('currency-list');
    Route::post('/add-currency', 'CurrencyController@add')->name('add-currency');
    Route::get('/get-currency', 'CurrencyController@getData')->name('get-currency');
    Route::get('get-currency-history', 'CurrencyController@getCurrencyHistory')->name('get-currency-history');
    Route::post('/edit-currency', 'CurrencyController@edit')->name('edit-currency');
    Route::post('/update-currency-exchange-rates', 'CurrencyController@UpdateRates')->name('update-currency-exchange-rates');
    Route::post('/save-currency-data', 'CurrencyController@saveCurrencyData')->name('save-currency-data');
    Route::post('/update-prices-on-product-level', 'CurrencyController@updatePricesOnProductLevel')->name('update-prices-on-product-level');

    Route::get('/check-status-for-first-time-currencies-update', 'CurrencyController@checkStatusForFirstTimeCurrencies')->name('check-status-for-first-time-currencies-update');
    Route::get('/recursive-job-status-currency-update', 'CurrencyController@recursiveCurrencyStatusCheck')->name('recursive-job-status-currency-update');
    Route::get('/currency-update-job-status', 'CurrencyController@currencyUpdateJobStatus')->name('currency-update-job-status');
    /************   Product Type Routes  ************/
    Route::get('/product-type-list', 'ProductTypeController@index')->name('product-type-list');
    Route::get('/get-product-types', 'ProductTypeController@getData')->name('get-product-types');
    Route::get('/get-product-secondary-types', 'ProductTypeController@getSecondaryTypes')->name('get-product-secondary-types');
    Route::get('/get-product-types-3', 'ProductTypeController@getTertiaryTypes')->name('get-product-types-3');
    Route::post('/add-product-type', 'ProductTypeController@add')->name('add-product-type');
    Route::post('/add-prod-secondary-type', 'ProductTypeController@addSecondaryType')->name('add-prod-secondary-type');
    Route::post('/edit-product-type', 'ProductTypeController@edit')->name('edit-product-type');
    Route::post('/add-prod-type-3', 'ProductTypeController@addProductType3')->name('add-prod-type-3');

    /************   Brand Routes  ************/
    Route::get('/brand-list', 'BrandController@index')->name('brand-list');
    Route::get('/get-brands', 'BrandController@getData')->name('get-brands');
    Route::post('/add-brand', 'BrandController@add')->name('add-brand');
    Route::post('/edit-brand', 'BrandController@edit')->name('edit-brand');

    // ***************   Global Configuration Routes Start  *******************

    // *********   Roles Routes Start  ********/
    Route::get('roles', 'HomeController@viewRoles')->name('roles-list');
    Route::post('add-roles', 'HomeController@addRole')->name('add-role');
    Route::get('role-menus', 'HomeController@viewRoleDetails')->name('role-menus');
    Route::get('add-role-menu', 'HomeController@storeRoleMenu')->name('add-role-menu');
    Route::get('add-role-access', 'HomeController@storeRoleAccess')->name('add-role-access');
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('logs');

    //**************** Quote Config ************************** */
    Route::get('update-quote-config', 'HomeController@updateQuoteConfig')->name('update-quote-config');
    //**************** Products Config ************************** */
    Route::get('update-products-config', 'HomeController@updateProductsConfig')->name('update-products-config');
    Route::get('update-print-config', 'HomeController@updatePrintConfig')->name('update-print-config');

    //**************** Accounting Config ************************** */
    Route::get('accounting-config', 'HomeController@accountingConfig')->name('accounting-config');
    Route::get('save-auto-run-payment-ref-no', 'HomeController@saveAutoRunPaymentRefNo')->name('save-auto-run-payment-ref-no');

    //**************** Company ************************** */
    Route::get('getting-country-states-backend', 'HomeController@getCountryStatesBackend')->name('getting-country-states-backend');
    Route::get('show-company', 'HomeController@showCompany')->name('show-company');
    Route::get('show-warehouses', 'HomeController@showWarehouses')->name('show-warehouses');

    Route::get('warehouse-zipcodes/{id}', 'WarehouseController@showzipcodes')->name('warehouse-zipcodes');
    Route::get('get-warehousezipcodes', 'WarehouseController@showallzipcodes')->name('get-warehousezipcodes');
    Route::post('add-zipcodes-form', 'WarehouseController@addzipcodes')->name('add-zipcodes-form');
    Route::post('save-warehousezipcode-data', 'WarehouseController@savewarehousezipcode')->name('save-warehousezipcode-data');
    Route::post('save-warehousezipcode-data-for-all-regions', 'WarehouseController@savewarehousezipcodeForAllRegions')->name('save-warehousezipcode-data-for-all-regions');
    Route::get('delete-warehousezipcde', 'WarehouseController@deletewarehouezipcode')->name('delete-warehousezipcde');


    Route::get('get-company', 'HomeController@getCompanyData')->name('get-company');
    Route::post('add-company', 'HomeController@addCompany')->name('add-company');
    Route::get('company-editing', 'HomeController@editCompany')->name('company-editing');
    Route::post('company-updating', 'HomeController@updateCompany')->name('company-updating');
    Route::post('update-warehouses-on-product-level', 'HomeController@UpdateWarehouseOnProductLevel')->name('update-warehouses-on-product-level');
    Route::post('update-comapny-logo', 'HomeController@updateCompanyLogo')->name('update-comapny-logo');
    Route::post('/save-comapny-data-from-detail', 'HomeController@saveCompanyData')->name('save-comapny-data-from-detail');
    Route::get('add-new-company', 'HomeController@addNewCompany')->name('add-new-company');
    Route::get('company-detail-info/{id}', 'HomeController@companyDetailInfo')->name('company-detail-info');
    Route::post('add-company-banks', 'HomeController@addCompanyBanks')->name('add-company-banks');
    //**************** Courier ************************** */
    Route::get('show-couriers', 'HomeController@showCouriers')->name('show-couriers');
    Route::get('/get-couries', 'HomeController@getCourierData')->name('get-couriers');
    Route::post('/add-courier', 'HomeController@addCourier')->name('add-courier');
    Route::post('/edit-courier', 'HomeController@editCourier')->name('edit-courier');
    Route::get('/delete-courier', 'HomeController@deleteCourier')->name('delete-courier');

    Route::post('/add-courier-type', 'CourierTypeController@addCourierType')->name('add-courier-type');
    Route::get('/delete-courier-type', 'CourierTypeController@delete')->name('courier-type.delete');
    Route::get('/get-courier-types', 'CourierTypeController@getCourierTypesData')->name('get-courier-types');



    //*************** Export History Table ******************** */
    Route::get('import-history', 'HomeController@importHistoryFunction')->name('import-history');
    Route::get('get-import-file-history', 'HomeController@getHistories')->name('get-import-file-history');

    Route::get('reserved-quantity-history', 'HomeController@reservedQuantityHistory')->name('reserved.quantity-history');
    Route::get('get-reserved-quantity-history', 'HomeController@getReservedQuantityHistory')->name('get-reserved-quantity-history');

    /************   Customer Category Routes  ************/
    Route::get('/customer-categories-list', 'CustomerCategoryController@index')->name('customer-categories-list');
    Route::get('/get-customer-categories', 'CustomerCategoryController@getData')->name('get-customer-categories');
    Route::post('/add-customer-category', 'CustomerCategoryController@add')->name('add-customer-category');
    //customer category controller enable ecommerce , author basit
    Route::get('/enable-ecommerce-customer-category/', 'CustomerCategoryController@enableEcommerceForCustomerCategory')->name('enable-ecommerce-customer-category');

    Route::post('/edit-customer-category', 'CustomerCategoryController@edit')->name('edit-customer-category');
    Route::get('/make-binding', 'CustomerCategoryController@makeBinding')->name('make-binding');

    Route::get('get-cust-cat-name-for-edit', 'CustomerCategoryController@getCustCatNameForEdit')->name('get-cust-cat-name-for-edit');
    Route::get('delete-cust-category', 'CustomerCategoryController@deleteCustomerCategory')->name('delete-cust-category');

    //customer category type recursive status route.
    Route::get('/recursive-type-category-status', 'CustomerCategoryController@recursiveCategoryStatusCheck')->name('recursive-type-category-status');

    Route::get('/check-status-for-first-time-category', 'CustomerCategoryController@checkStatusFirstTimeCategory')->name('check-status-for-first-time-category');


    //*******   Email Templates Management   ******/
    Route::get('get-template', 'TemplateController@getData')->name('get-template');
    Route::resource('/template', 'TemplateController', [
        'names' => [
            'index'     => 'list-template',
            'create' => 'create-template',
            'store'  => 'store-template',
            'edit'   => 'edit-template',
            'update' => 'update-template',
        ]
    ]);

    //******* System Configurations Routes *****/

    Route::get('system-configuration', [SystemConfigurationController::class, 'index'])->name('system-configurations');
    Route::get('system-configuration/create', [SystemConfigurationController::class, 'create'])->name('create-system-configurations');
    Route::post('system-configuration/store', [SystemConfigurationController::class, 'store'])->name('store-system-configurations');
    Route::get('system-configuration/show', [SystemConfigurationController::class, 'getSystemConfigurations'])->name('show-system-configurations');
    Route::get('system-configuration/{id}/edit', [SystemConfigurationController::class, 'editSystemConfigurations'])->name('edit-system-configurations');
    Route::put('system-configuration/{id}/update', [SystemConfigurationController::class, 'updateSystemConfiguration'])->name('update-system-configurations');
    Route::get('system-configuration/{id}/delete', [SystemConfigurationController::class, 'deleteSystemConfigurations'])->name('delete-system-configurations');

    //*******   Configurations Routes    ******/

    Route::get('list-configuration', 'ConfigurationController@index')->name('list-configuration');
    Route::get('get-configuration', 'ConfigurationController@getData')->name('get-configuration');
    Route::get('edit-configuration', 'ConfigurationController@edit')->name('edit-configuration');
    Route::get('edit-roles-configuration', 'ConfigurationController@editRolesConfiguration')->name('edit-roles-configuration');
    Route::post('update-configuration', 'ConfigurationController@update')->name('update-configuration');
    Route::post('barcode-save', 'ConfigurationController@barcodeSave')->name('barcodeConfig-save');
    Route::get('barcode', 'ConfigurationController@barcodeView')->name('barcode-configuation');

    //*******   Deployments Routes    ******/
    Route::get('get-deployments-data', 'ConfigurationController@GetDeploymentsData')->name('get-deployments-data');
    Route::post('save-deployment-data', 'ConfigurationController@SaveDeploymentsData')->name('save-deployment-data');
    Route::post('delete-deployment-data', 'ConfigurationController@DeleteDeploymentsData')->name('delete-deployment-data');
    Route::post('save-deployment-status', 'ConfigurationController@SaveDeploymentsStatus')->name('save-deployment-status');

    //*******   Ticket Routes    ******/

    Route::post('create-ticket', 'TicketController@postTicketRequest')->name('create-ticket');
    Route::get('ticket-departments', 'TicketController@ticketDepartments')->name('ticket-departments');
    Route::get('tickets', 'TicketController@roleTickets')->name('tickets');
    Route::get('ticket-info/{ref}', 'TicketController@ticketDetail')->name('ticket-info');
    Route::get('sales-new-comment', 'TicketController@ticketDetail')->name('sales-new-comment');
    Route::get('new-comment', 'TicketController@ticketDetail')->name('new-comment');

    /******* Invoice Setting Routes *******/
    Route::get('invoice-setting', 'InvoiceSettingController@index')->name('invoice-setting');
    Route::post('invoice-setting-update', 'InvoiceSettingController@updateinvoiceSetting')->name('invoice-setting-update');
    Route::get('edit-invoice', 'InvoiceSettingController@edit')->name('edit-invoice');
    Route::post('update-invoice', 'InvoiceSettingController@update')->name('update-invoice');
    Route::post('add-invoice', 'InvoiceSettingController@addNew')->name('add-invoice');
    Route::post('add-new-invoice-setting', 'InvoiceSettingController@addNewInvoiceSetting')->name('add-new-invoice-setting');
    Route::get('fetch-states', 'InvoiceSettingController@fetchStates')->name('fetch-states');



    /******* Purchase Order Setting Routes *******/
    Route::get('/purchase-order-setting', 'PurchaseOrderSettingController@index')->name('purchase-order-setting');
    Route::post('purchase-order-setting-update', 'PurchaseOrderSettingController@updatepoSetting')->name('purchase-order-setting-update');
    Route::get('edit-po-setting', 'PurchaseOrderSettingController@edit')->name('edit-po-setting');
    Route::post('update-po-setting', 'PurchaseOrderSettingController@update')->name('update-po-setting');

    /*************************All Dashboard Routes****************************/
    Route::get('show-doc-number-setting', 'HomeController@showDocNoSettings')->name('show-doc-number-setting');
    Route::get('get-doc-number-settings', 'HomeController@getDocNoSettings')->name('get-doc-number-settings');
    Route::post('update-doc-number-settings', 'HomeController@updateDocNoSettings')->name('update-doc-number-settings');




    /*************************All Dashboard Routes****************************/
    Route::get('sales_dashboard', 'HomeController@getSaleDashboard')->name('admin.sales.dashboard');
    Route::get('get-completed-quotation-admin', 'HomeController@getCompletedQuotationsDataAdmin')->name('get-completed-quotation-admin');
    Route::get('get-pending-quotation-admin', 'HomeController@getPendingQuotationsDataAdmin')->name('get-pending-quotation-admin');
    Route::get('admin_draft_invoices', 'HomeController@getDraftInvoiceAdmin');
    Route::get('admin_invoices', 'HomeController@getInvoiceAdmin');
    Route::get('admin-get-completed-quotation-products/{id}', 'HomeController@getCompletedQuotationProductsAdmin')->name('admin-get-completed-quotation-products');
    Route::get('admin-get-completed-quotation-products-to-list/{id}', 'HomeController@getProductsDataAdmin')->name('admin-get-completed-quotation-products-to-list');


    Route::get('get_total_invoices', function () {
        return redirect('sales/invoices')->with('total_invoices', 'Yes');
    })->name('get_total_invoices');

    /************   Notification Configurations  ************/
    Route::get('/notification-list', 'NotificationConfigurationController@index')->name('notificationList');
    Route::get('/get-notification-configuration-records', 'NotificationConfigurationController@getNotificationsConfiguration')->name('getNotificationConfigurationRecords');
    Route::post('/save-notification-configuration', 'NotificationConfigurationController@store')->name('saveNotificationConfiguration');
    Route::post('/update-notification-configuration', 'NotificationConfigurationController@getNotificationDetail')->name('getNotificationDetail');
    Route::post('/save-notification-template', 'NotificationConfigurationController@saveNotificationTemplate')->name('saveNotificationTemplate');
    Route::post('/get-selected-users', 'NotificationConfigurationController@getSelectedUser')->name('getSelectedUser');
    Route::post('/get-selected-configuration-content', 'NotificationConfigurationController@getSelectedConfigurationContent')->name('getSelectedConfigurationContent');
    Route::post('/save-keyword-against-configuration', 'NotificationConfigurationController@saveKeywordAgainstConfiguration')->name('saveKeywordAgainstConfiguration');
    Route::post('/clear-notification', 'NotificationController@clearNotification')->name('clear-notification');
    Route::post('/clear-all-notifications', 'NotificationController@cleaAllrNotifications')->name('clear-all-notifications');
    Route::post('/save-custom-email', 'NotificationConfigurationController@saveCustomEmail')->name('saveCustomEmail');

    /************   POS Integeration  ************/

    Route::get('/pos-integeration', [POSIntegrationController::class, 'PosData'])->name('pos-integeration');
    Route::get('pos-update-status', [POSIntegrationController::class, 'inActiveStatus'])->name('pos-integeration-update-status');
    // Route::get('get_total_draft',function(){
    // 	return redirect('sales/draft_invoices')->with('total_draft','Yes');
    // })->name('get_total_draft');

    // Route::get('get_customer_invoices/{customer_id}/{month}',function($customer_id, $month){
    // 	// dd($customer_id);
    // 	return redirect('sales/invoices')->with(['find'=>'Yes','customer_id'=>$customer_id,'month'=>$month]);
    // })->name('get_customer_invoices');

    // Route::get('get_customer_invoices/{customer_id}/{month}/{status}',function($customer_id, $month,$status){
    // 	// dd($customer_id);
    // 	return redirect('sales/invoices')->with(['find'=>'Yes','customer_id'=>$customer_id,'month'=>$month,'status'=>$status]);
    // })->name('get_customer_invoices_unpaid');


});

// Common Routes
Route::group(['namespace' => 'Common', 'prefix' => 'common'], function () {
    Route::get('filter-state', 'GeneralFunctionController@filterState');
    Route::get('filter-sub-category', 'GeneralFunctionController@filterSubCategory')->name('filter-sub-category');

    /******************* Customer  *****************/
    Route::get('/common_customers', 'CustomerController@index')->name('common_customers');
    Route::get('assign-customers-to-sale', 'CustomerController@assignCustomersToSale')->name('assign-customers-to-sale');
    Route::get('common-customer-list-data', 'CustomerController@getData')->name('common-customer-list-data');
    Route::get('/get-common-customer-detail/{id}', 'CustomerController@getCustomerDetail')->name('get-common-customer-detail');
    Route::get('get-customer-contact-common', 'CustomerController@getCustomerContact')->name('get-customer-contact-common');
    Route::get('get-customer-general-documents-common', 'CustomerController@getCustomerGeneralDocuments')->name('get-customer-general-documents-common');
    Route::post('show-single-billing-record-common', 'CustomerController@showSingleBilling')->name('show-single-billing-record-common');
    Route::get('get-customer-documents-common/{id}', 'CustomerController@getCustomerDocuments')->name('get-customer-documents-common');
    Route::get('get-customer-product-fixed-prices-common/{id}', 'CustomerController@getCustomerProductFixedPrices')->name('get-customer-product-fixed-prices-common');
    Route::post('add-customer-note-common', 'CustomerController@addCustomerNote')->name('add-customer-note-common');
    Route::post('get-customer-note-common', 'CustomerController@getCustomerNote')->name('get-customer-note-common');

    /*****************   Products *************/
    Route::get('/common_products', 'ProductController@index')->name('common_products');
    Route::get('common-product-list-data', 'ProductController@getData')->name('common-product-list-data');
    Route::get('get-common-product-images', 'ProductController@getImages')->name('get-common-product-images');
    Route::get('product-details/{id}', 'ProductController@getProductDetail')->name('product-details');
    Route::get('get-customer-fixed-prices/{id}', 'ProductController@getCustomerFixedPrices')->name('get-customer-fixed-prices');
    Route::get('get-common-product-suppliers-data/{id}', 'ProductController@getProductSuppliersData')->name('get-common-product-suppliers-data');
    Route::post('purchase-fetch-product-common', 'ProductController@purchaseFetchProduct')->name('purchase-fetch-product-common');
    /**************   Supplier *****************/
    Route::get('/common_supplier', 'SupplierController@index')->name('common_supplier');
    Route::get('get-common-supplier-list-data', 'SupplierController@getData')->name('get-common-supplier-list-data');
    Route::get('/get-common-supplier-detail/{id}', 'SupplierController@getSupplierDetailByID')->name('get-common-supplier-detail');
    Route::get('get-common-supplier-contacts', 'SupplierController@getSupplierContact')->name('get-common-supplier-contacts');
    Route::get('get-common-supplier-general-docs', 'SupplierController@getSupplierGeneralDocuments')->name('get-common-supplier-general-docs');


    /************* Display toggle and column reorder routes *************/
    Route::post('toggle-column-display', 'GeneralFunctionController@toggleTableColumnDisplay')->name('toggle-column-display');
    Route::get('/column-reorder', 'GeneralFunctionController@sortColumnDisplay')->name('column-reorder');

    /****************** Orders Routes ***************************/
    Route::get('/get-order-detail/{id}', 'GeneralFunctionController@getOrderDetail')->name('get-order-detail');
    Route::get('/get-order-product-detail/{id}', 'GeneralFunctionController@getOrderProductDetail')->name('get-order-product-detail');
    Route::post('common-autocomplete-fetch-orders', 'GeneralFunctionController@autocompleteFetchOrders')->name('common-autocomplete-fetch-orders');
    Route::get('get-order-prod-note', 'GeneralFunctionController@getCompletedQuotProdNote')->name('get-order-prod-note');

    /****************** Profile Image***************/
    Route::post('update-profile-img', 'CustomerController@updateImg')->name('update-profile-img');

    Route::post('update-profile-img-user', 'CustomerController@updateImguser')->name('update-profile-img-user');

    Route::get('check-active-user', 'CustomerController@checkActive')->name('check-active-user');
});

/***********************************************************************************/
Route::group(['namespace' => 'Sales', 'prefix' => 'sales', 'middleware' => 'sales'], function () {
    // Route::get('/sender',function(){
    // 	$data = 'me';
    // 	event(new ProductCreated($data));
    // });
    Route::get('get-notifications', 'HomeController@getNotifications');
    Route::get('complete-profile', 'HomeController@completeProfile');
    Route::post('complete-profile', 'HomeController@completeProfileProcess');
    // Route::post('check-old-password','HomeController@checkOldPassword');

    // Route::group(['middleware' => 'incomplete-profile'], function(){
    // 	Route::get('/','HomeController@getHome');
    // });

    Route::get('/', 'HomeController@getHome')->name('sales');
    Route::get('/stats', 'HomeController@getStats');
    Route::get('get_sales', 'HomeController@getSales');

    Route::get('change-password', 'HomeController@changePassword')->name('change-password');
    Route::post('change-password', 'HomeController@changePasswordProcess')->name('change-password-process');
    Route::post('check-old-password', 'HomeController@checkOldPassword');

    Route::get('profile-setting', 'HomeController@profile');
    Route::post('profile-setting', 'HomeController@updateProfile');

    Route::get('/draft_invoices', 'HomeController@getDraftInvoice')->name('draft_invoices');
    Route::get('/invoices', 'HomeController@getInvoice')->name('invoices');
    Route::get('/others', 'HomeController@getOther')->name('others');

    Route::post('get-customer-products-data', 'CustomerController@getCustProdData')->name('get-customer-products-data');
    Route::post('get-product-against-cat', 'CustomerController@getProductAgainstCat')->name('get-product-against-cat');

    /****************** Confirm pick instruction from draft invoice page**************************/
    Route::post('confirm-order-pick-instruction-from-draft-invoice', 'OrderController@confirmPickInstructionFromDraftInvoice')->name('confirm-order-pick-instruction-from-draft-invoice');
    /****************** CUSTOMER ROUTES**************************/
    Route::get('/account-recievable', 'CustomerController@accountRecievable')->name('account-recievable');
    // Route::post('/export-ar-invoice-table','CustomerController@exportAccountReceivableInvoices')->name('export-ar-invoice-table');

    Route::post('/export-account-transaction', 'CustomerController@exportAccountTransaction')->name('export-account-transaction');

    Route::get('/check-status-for-first-time-transaction-exp', 'CustomerController@checkStatusTransactionExp')->name('check-status-for-first-time-transaction-exp');

    Route::get('/recursive-transaction-exp-status', 'CustomerController@recursiveTransactionExp')->name('recursive-transaction-exp-status');
    Route::get('/export-ar-invoice-table', 'CustomerController@exportAccountReceivableInvoices')->name('export-ar-invoice-table');
    Route::post('save-transaction-data', 'CustomerController@saveTransactionDataIncomplete')->name('save-transaction-data');
    Route::get('get-transaction-history', 'CustomerController@getTransactionHistory')->name('get-transaction-history');

    Route::get('get-transaction-del-history', 'CustomerController@getDeletedTransaction')->name('get-transaction-del-history');

    // Billing Notes History
    Route::get('get-billing_notes_history', 'CustomerController@getBllingNoesHistory')->name('get-billing_notes_history');
    Route::post('delete-billing-note-history', 'CustomerController@deleteBllingNoesHistory')->name('delete-billing-note-history');

    //Ends Here

    Route::post('export-orders-receipt-pdf', 'CustomerController@exportOrdersReceipt')->name('export-orders-receipt-pdf');
    Route::get('export-orders-receipt-pdf/{customer_id?}/{total_received?}/{orders_a?}/{receipt_date?}', 'CustomerController@exportOrdersReceipt')->name('export-orders-receipt-pdf');

    Route::get('/get-invoices-for-receivable', 'CustomerController@getInvoicesForReceivables')->name('get-invoices-for-receivable');
    Route::post('delete_order_transaction', 'CustomerController@deleteOrderTransaction')->name('delete_order_transaction');
    Route::get('get_auto_ref_no', 'CustomerController@getAutoRefNo')->name('get_auto_ref_no');


    // For Invoice
    Route::post('export-account-received-pdf', 'CustomerController@exportAccountReceivablePDF')->name('export-account-received-pdf');
    Route::get('export-account-received-pdf/{orders?}/{billing_type?}/{receipt_date?}', 'CustomerController@exportAccountReceivablePDF')->name('export-account-received-pdf');
    Route::get('/get-payment-ref-invoices-for-receivable', 'CustomerController@getPaymentRefInvoicesForReceivables')->name('get-payment-ref-invoices-for-receivable');
    Route::get('/get-payment-ref-invoices-for-receivable-last-five', 'CustomerController@getPaymentRefInvoicesForReceivablesLastFive')->name('get-payment-ref-invoices-for-receivable-last-five');
    Route::post('export-payment-receipt-pdf', 'CustomerController@exportPaymentReceiptPDFF')->name('export-payment-receipt-pdf');
    Route::get('export-payment-receipt-pdf/{payment_id}', 'CustomerController@exportPaymentReceiptPDF')->name('export-payment-receipt-pdf');
    Route::get('/get-open-invoices-for-receivable', 'CustomerController@getOpenInvoicesForReceivables')->name('get-open-invoices-for-receivable');
    Route::post('/get-open-invoice-received-amount', 'CustomerController@getOpenInvoicesReceivedAmount')->name('get-open-invoice-received-amount');
    Route::get('/get-customer-orders', 'CustomerController@getCustomerOrders')->name('get-customer-orders');
    Route::get('/customer-transaction-detail/{id}', 'CustomerController@getCustomerTransactionDetail')->name('customer-transaction-detail');


    Route::get('/customer', 'CustomerController@index')->name('list-customer');
    Route::get('change-private-to-ecom', 'CustomerController@PrivateToEcom')->name('change-private-to-ecom');
    Route::get('/save-customer-data', 'CustomerController@saveCustomerData')->name('save-customer-data');
    Route::get('/get-salesperson', 'CustomerController@getSalesperson')->name('get-salesperson');
    Route::get('/bulk-upload-customer-form', 'CustomerController@bulkUploadCustomersForm')->name('bulk-upload-customer-form');
    Route::get('/get-temp-customers', 'CustomerController@getTempCustomers')->name('get-temp-customers');
    Route::get('delete-temp-customers', 'CustomerController@deleteTempCustomers')->name('delete-temp-customers');
    Route::get('move-customers-to-inventory', 'CustomerController@moveCustomersToInventory')->name('move-customers-to-inventory');
    Route::post('save-temp-customer-data', 'CustomerController@saveTempCustomerDataa')->name('save-temp-customer-data');
    Route::post('/bulk-upload-customers', 'CustomerController@bulkUploadCustomers')->name('bulk-upload-customers');
    Route::post('customer-bulk-upload-pos', 'CustomerController@customerBulkUploadPO')->name('customer-bulk-upload-pos');
    Route::get('customer-recursive-import-status-bulk-pos', 'CustomerController@customerRecursiveCallForBulkPos')->name('customer-recursive-import-status-bulk-pos');
    Route::get('customer-check-status-for-first-time-bulk-pos', 'CustomerController@customerCheckStatusFirstTimeForBulkPos')->name('customer-check-status-for-first-time-bulk-pos');



    Route::post('/change-draft-vat', 'OrderController@changeDraftVat')->name('change-draft-vat');
    Route::post('/change-quotation-vat', 'OrderController@changeQuotationVat')->name('change-quotation-vat');

    Route::post('/copy-quotation', 'OrderController@copyQuotation')->name('copy-quotation');
    Route::post('/discard-quotation', 'OrderController@discardQuotation')->name('discard-quotation');

    Route::get('save-incom-customer', 'CustomerController@saveIncomCustomer')->name('save-incom-customer');
    Route::get('/add-customer-new', 'CustomerController@addCustomer')->name('add-customer-new');
    Route::get('/get-datatables-for-customer', 'CustomerController@getData')->name('get-customer');
    Route::get('/export-customer-data', 'CustomerController@exportCustomerData')->name('export-customer-data');
    Route::post('/sync-customer-to-ecom', 'CustomerController@syncCustomers')->name('sync-customer-to-ecom');


    Route::get('/check-status-for-first-time-customer-list', 'CustomerController@checkStatusFirstTimeForCustomerList')->name('check-status-for-first-time-customer-list');
    Route::get('/recursive-export-status-customer-list', 'CustomerController@recursiveStatusCheckCustomerList')->name('recursive-export-status-customer-list');

    Route::get('/get-customer-addresses', 'CustomerController@getCustomerAddresses')->name('get-customer-addresses');
    Route::get('/get-customer-addresses-ship', 'CustomerController@getCustomerAddressesShip')->name('get-customer-addresses-ship');
    Route::get('/get-customer-detail/{id}', 'CustomerController@getCustomerDetail')->name('get-customer-detail');
    Route::get('delete-customer-fixed-price', 'CustomerController@deleteCustomerFixedPrice')->name('delete-customer-fixed-price');
    Route::get('/get-customer-documents/{id}', 'CustomerController@getCustomerDocuments')->name('get-customer-documents');
    Route::get('/get-customer-product-fixed-prices/{id}', 'CustomerController@getCustomerProductFixedPrices')->name('get-customer-product-fixed-prices');
    Route::get('/customer-suspension', 'CustomerController@suspendCustomer')->name('customer-suspension');
    Route::get('/customer-activation', 'CustomerController@activateCustomer')->name('customer-activation');
    Route::post('add-customer-general-document', 'CustomerController@uploadCustomerGeneralDocuments')->name('add-customer-general-document');
    Route::get('get-customer-company-addresses', 'CustomerController@getCustomerCompanyAddresses')->name('get-customer-company-addresses');

    Route::post('get-fix-price-excel', 'CustomerController@getFixPriceExcel')->name('get-fix-price-excel');
    Route::post('upload-fix-prices-bulk', 'CustomerController@uploadFixPricesBulk')->name('upload-fix-prices-bulk');

    Route::post('get-customer-secondary-suppliers', 'CustomerController@getCustomerSecondarySalesPerson')->name('CustomerSecondarySalesPersons');
    Route::post('delete-sales-person-record', 'CustomerController@deleteSalesPersonRecord')->name('deleteSalesPersonRecord');

    // Route::post('/save-customer-shipping','CustomerController@saveShippingInfo')->name('save-customer-shipping');
    Route::post('/save-customer-billing', 'CustomerController@saveBillingInfo')->name('save-customer-billing');
    Route::post('check-duplicate-address', 'CustomerController@checkDuplicateAddress')->name('check-duplicate-address');
    Route::post('/save-customer-billing-ship', 'CustomerController@saveShippingInfo')->name('save-customer-billing-ship');
    Route::get('/check_default_address', 'CustomerController@checkDefaultAddress')->name('check_default_address');
    Route::get('/setting-default-shipping-for-customer', 'CustomerController@settingDefaultShipping')->name('setting-default-shipping-for-customer');
    Route::get('/setting-default-customer-contact', 'CustomerController@settingDefaultConatact')->name('setting-default-customer-contact');
    Route::get('/replace_default_address', 'CustomerController@replaceDefaultAddress')->name('replace_default_address');
    Route::get('/delete-customer', 'CustomerController@deleteCustomer')->name('delete-customer');
    Route::get('/delete-customer-note-info', 'CustomerController@deleteCustomerNoteInfo')->name('delete-customer-note-info');
    Route::post('/add-customer-note', 'CustomerController@addCustomerNote')->name('add-customer-note');
    Route::post('/get-customer-note', 'CustomerController@getCustomerNote')->name('get-customer-note');
    // saving customer details page fields route
    Route::post('/save-cust-data-cust-detail-page', 'CustomerController@saveCustDataCustDetailPage')->name('save-cust-data-cust-detail-page');
    Route::post('/save-product-update-record', 'CustomerController@saveProductUpdateData')->name('save-product-update-record');
    Route::post('/save-shipping-update-record', 'CustomerController@saveShippingUpdateData')->name('save-shipping-update-record');
    Route::post('/save-billing-update-record', 'CustomerController@saveBillingUpdateData')->name('save-billing-update-record');
    Route::post('/update-profile-pic/{id}', 'CustomerController@updateCustomerProfile')->name('update-profile-pic');
    Route::post('/show-single-shipping-record', 'CustomerController@showSingleShipping')->name('show-single-shipping-record');
    Route::post('/show-single-billing-record', 'CustomerController@showSingleBilling')->name('show-single-billing-record');

    Route::get('/delete-cust-company-address', 'CustomerController@deleteCustomerCompanyAddress')->name('delete-cust-company-address');
    Route::get('/delete-customer-note', 'CustomerController@deleteCustomerNote')->name('delete-customer-note');
    Route::get('/delete-customers-permanent', 'CustomerController@deleteCustomersPermanent')->name('delete-customers-permanent');
    // Route::get('get-states-from-country', 'CustomerController@getStateFromCountry')->name('get-states-from-country');

    Route::post('/add-customer-product-fixed-price', 'CustomerController@addCustProdFixedPrice')->name('add-customer-product-fixed-price');
    Route::post('/add-customer-contact', 'CustomerController@addCustomerContact')->name('add-customer-contact');
    Route::get('/get-customer-contact', 'CustomerController@getCustomerContact')->name('get-customer-contact');
    Route::get('delete-customer-contact', 'CustomerController@deleteCustomerContact')->name('delete-customer-contact');
    Route::get('/getting-product-selling-price', 'CustomerController@getProductSellingPrice')->name('getting-product-selling-price');
    Route::get('/get-customer-general-documents', 'CustomerController@getCustomerGeneralDocuments')->name('get-customer-general-documents');
    Route::get('delete-customer-general-document', 'CustomerController@deleteCustomerGeneralDocuments')->name('delete-customer-general-document');
    Route::post('save-cus-contacts-data', 'CustomerController@saveCusContactsData')->name('save-cus-contacts-data');


    /****************** Products ROUTES**************************/
    Route::get('get-product-suppliers-record/{id}', 'ProductController@getProductSuppliersRecord')->name('get-product-suppliers-record');
    Route::get('/products-list', 'ProductController@index')->name('products-list');
    Route::get('/get-datatables-for-products', 'ProductController@getData')->name('get-products');

    Route::get('/inquiry-products', 'ProductController@indexForInquiry')->name('inquiry-products');
    Route::get('/get-datatables-for-inquiry-products', 'ProductController@getDataForInquiry')->name('get-inquiry-products');
    Route::get('get-product-detail/{id}', 'ProductController@getProductDetail');
    Route::get('mark-read/{id}/{product_id}', function ($id, $product_id) {
        Auth()->user()->notifications[$id]->markAsRead();
        return redirect('common/product-details/' . $product_id);
    });
    Route::get('/read-mark', 'ProductController@readMark')->name('read-mark');
    Route::get('get-prod-image', 'ProductController@getProdImages');

    /****************** Quotations ROUTES**************************/

    Route::post('add-customer-to-quotation', 'OrderController@AddCustomerToQuotation')->name('add-customer-to-quotation');
    Route::post('edit-customer-address', 'OrderController@editCustomerAddress')->name('edit-customer-address');
    Route::post('edit-customer-address-on-completed-quotation', 'OrderController@editCustomerAddressOnCompletedQuotation')->name('edit-customer-address-on-completed-quotation');
    Route::post('edit_customer_for_order', 'OrderController@editCustomerForOrder')->name('edit_customer_for_order');
    Route::post('edit-customer-address-ship', 'OrderController@editCustomerAddressShip')->name('edit-customer-address-ship');
    Route::post('autocomplete-fetch-orders', 'OrderController@autocompleteFetchOrders')->name('autocomplete-fetch-orders');
    Route::post('autocomplete-fetch-product', 'OrderController@autocompleteFetchProduct')->name('autocomplete-fetch-product');
    Route::post('autocomplete-fetch-product-cdp', 'OrderController@autocompleteFetchProductCdp')->name('autocomplete-fetch-product-cdp');
    Route::get('product-order-invoice', 'OrderController@postInvoiceDirect')->name('product-order-invoice');
    Route::post('product-order-invoice', 'OrderController@postInvoice')->name('product-order-invoice');
    Route::get('get-invoice/{id}', 'OrderController@index')->name('get-invoice');
    Route::post('payment-term-save-in-draft-quotation', 'OrderController@paymentTermSaveInDquotation')->name('payment-term-save-in-draft-quotation');
    Route::post('from-warehouse-save-in-draft-quotation', 'OrderController@fromWarehouseSaveInDquotation')->name('from-warehouse-save-in-draft-quotation');
    Route::post('payment-term-save-in-my-quotation', 'OrderController@paymentTermSaveInMyQuotation')->name('payment-term-save-in-my-quotation');
    Route::post('from-warehouse-id-save-in-my-quotation', 'OrderController@fromWarehouseSaveInMyQuotation')->name('from-warehouse-id-save-in-my-quotation');
    Route::post('action-invoice', 'OrderController@doActionInvoice')->name('action-invoice');

    Route::get('save-sale-person', 'OrderController@SaveSalesPerson')->name('save-sale-person');

    Route::post('check-customer-credit-limit', 'OrderController@checkCustomerCreditLimit')->name('check-customer-credit-limit');
    Route::get('get-invoice-to-list/{id}', 'OrderController@getData')->name('get-invoice-to-list');
    Route::post('export-draft-quotation', 'OrderController@exportDraftQuotation')->name('export-draft-quotation');
    Route::get('check-if-inquiry-prod-exist', 'OrderController@checkIfInquiryItemExist')->name('check-if-inquiry-prod-exist');
    Route::post('add-existing-invoice', 'OrderController@postExistingInvoice')->name('add-existing-invoice');
    Route::get('remove-invoice-product', 'OrderController@removeProduct')->name('remove-invoice-product');
    Route::get('completed-quotations', 'OrderController@completedQuotations')->name('completed-quotations');
    Route::get('get-completed-quotation', 'OrderController@getCompletedQuotationsData')->name('get-completed-quotation');
    Route::get('get-completed-quotation-footer', 'OrderController@getCompletedQuotationsDataFooter')->name('get-completed-quotation-footer');
    Route::get('get-completed-other', 'OrderController@getCompletedOtherData')->name('get-completed-other');
    Route::post('export-invoice-table', 'OrderController@exportInvoiceTable')->name('export-invoice-table');
    Route::get('/check-status-for-first-time-invoice-table', 'OrderController@checkStatusFirstTimeForInvoiceTable')->name('check-status-for-first-time-invoice-table');
    Route::get('/recursive-export-status-invoice-table', 'OrderController@recursiveStatusCheckInvoiceTable')->name('recursive-export-status-invoice-table');
    Route::post('add-completed-quotation-note', 'OrderController@addCompQuotProdNote')->name('add-completed-quotation-note');
    Route::get('get-completed-quotation-note', 'OrderController@getCompQuotProdNote')->name('get-completed-quotation-note');
    Route::get('delete-completed-quot-prod-note', 'OrderController@deleteCompQuotProdNote')->name('delete-completed-quot-prod-note');

    Route::get('pending-quotations', 'OrderController@pendingQuotations')->name('pending-quotations');
    Route::get('get-pending-quotation', 'OrderController@getPendingQuotationsData')->name('get-pending-quotation');
    Route::get('delete-single-draft-quotation', 'OrderController@deleteSingleDraftQuot')->name('delete-single-draft-quotation');
    Route::get('delete-single-order-quotation', 'OrderController@deleteSingleOrderQuot')->name('delete-single-order-quotation');
    Route::get('delete-draft-quotations', 'OrderController@deleteDraftQuots')->name('delete-draft-quotations');
    Route::get('delete-order-quotations', 'OrderController@deleteOrderQuots')->name('delete-order-quotations');
    Route::post('add-draft-quotation-note', 'OrderController@addDraftQuotProdNote')->name('add-draft-quotation-note');
    Route::get('get-draft-quotation-note', 'OrderController@getDraftQuotProdNote')->name('get-draft-quotation-note');
    Route::get('delete-draft-quot-prod-note', 'OrderController@deleteDraftQuotProdNote')->name('delete-draft-quot-prod-note');
    Route::get('update-draft-quot-prod-note', 'OrderController@updateDraftQuotProdNote')->name('update-draft-quot-prod-note');
    Route::get('enquiry-item-as-new-product', 'OrderController@AddEnquiryItemAsNewPr')->name('enquiry-item-as-new-product');
    Route::get('enquiry-item-as-new-product-op', 'OrderController@AddEnquiryItemAsNewOrdPr')->name('enquiry-item-as-new-product-op');
    Route::get('fetch-suppliers-for-inquiry-product', 'OrderController@fetchSuppliersForInquiry')->name('fetch-suppliers-for-inquiry-product');


    Route::get('cancel-orders', 'OrderController@cancelOrders')->name('cancel-orders');
    Route::get('merge-draft-invoices', 'OrderController@mergeDraftInvoices')->name('merge-draft-invoices');
    Route::get('cancel-invoice-orders', 'OrderController@cancelInvoiceOrders')->name('cancel-invoice-orders');
    Route::get('revert-invoice-orders', 'OrderController@revertInvoiceOrders')->name('revert-invoice-orders');
    Route::get('get-cancelled-orders', 'OrderController@getCancelledOrders')->name('get-cancelled-orders');
    Route::get('get-cancelled-orders-data', 'OrderController@getCancelledOrdersData')->name('get-cancelled-orders-data');
    Route::post('export-cancelled_order', 'OrderController@exportCancelledOrders')->name('export-cancelled_order');

    Route::get('/check-status-for-first-time-cancelled-order', 'OrderController@checkStatusFirstTimeForCancelledOrder')->name('check-status-for-first-time-cancelled-order');
    Route::get('/recursive-export-status-cancelled-order', 'OrderController@recursiveStatusCancelledOrder')->name('recursive-export-status-cancelled-order');



    Route::get('get-cancelled-order-detail/{id}', 'OrderController@getCancelledOrderDetail')->name('get-cancelled-order-detail');

    Route::get('revert-draft-invoice', 'OrderController@revertDraftInvoice')->name('revert-draft-invoice');
    Route::post('add-inquiry-product', 'OrderController@addInquiryProduct')->name('add-inquiry-product');
    Route::get('get-completed-quotation-products/{id}', 'OrderController@getCompletedQuotationProducts')->name('get-completed-quotation-products');
    Route::post('order-product-edits', 'OrderController@orderProductsEdits')->name('order-product-edits');

    Route::get('get-completed-draft-invoices/{id}', 'OrderController@getCompletedDraftInvoice')->name('get-completed-draft-invoices');
    Route::get('get-completed-invoices-details/{id}', 'OrderController@getCompletedInvoicesDetails')->name('get-completed-invoices-details');
    Route::get('get-completed-quotation-products-to-list/{id}', 'OrderController@getProductsData')->name('get-completed-quotation-products-to-list');
    Route::post('export-complete-quotation', 'OrderController@exportCompleteQuotation')->name('export-complete-quotation');
    Route::post('upload-bulk-product-in-order-detail', 'OrderController@bulkImortInOrders')->name('upload-bulk-product-in-order-detail');
    Route::get('get-completed-quotation-products-to-list-cancel/{id}', 'OrderController@getProductsDataCancel')->name('get-completed-quotation-products-to-list-cancel');
    Route::post('add-completed-quotation-prod-note', 'OrderController@addCompletedQuotProdNote')->name('add-completed-quotation-prod-note');
    Route::get('get-completed-quotation-prod-note', 'OrderController@getCompletedQuotProdNote')->name('get-completed-quotation-prod-note');
    Route::get('delete-completed-quot-prod-note', 'OrderController@deleteCompletedQuotProdNote')->name('delete-completed-quot-prod-note');
    Route::get('update-completed-quot-prod-note', 'OrderController@updateCompletedQuotProdNote')->name('update-completed-quot-prod-note');
    Route::post('make-draft-invoice', 'OrderController@makeDraftInvoice')->name('make-draft-invoice');
    Route::get('draft-invoices', 'OrderController@draftInvoices')->name('draft-invoices');
    Route::get('get-draft-invoices', 'OrderController@getDraftInvoicesData')->name('get-draft-invoices');
    Route::post('add-by-refrence-number', 'OrderController@addByRefrenceNumber')->name('add-by-refrence-number');


    Route::get('remove-order-product', 'OrderController@removeOrderProduct')->name('remove-order-product');
    Route::post('add-to-order-by-refrence-number', 'OrderController@addToOrderByRefrenceNumber')->name('add-to-order-by-refrence-number');
    Route::post('add-inquiry-product-to-order', 'OrderController@addInquiryProductToOrder')->name('add-inquiry-product-to-order');

    Route::post('update-quotation-data', 'OrderController@UpdateQuotationData')->name('update-quotation-data');
    Route::get('get-quotation-history', 'OrderController@getQuotationHistory')->name('get-quotation-history');

    Route::get('checking-item-shortDesc', 'OrderController@checkItemShortDesc')->name('checking-item-shortDesc');
    Route::get('checking-item-shortDesc-in-Op', 'OrderController@checkItemShortDescInOp')->name('checking-item-shortDesc-in-Op');
    Route::post('update-order-quotation-data', 'OrderController@UpdateOrderQuotationData')->name('update-order-quotation-data');
    Route::get('get-order-history', 'OrderController@getOrderHistory')->name('get-order-history');
    Route::post('upload-excel', 'OrderController@uploadExcel')->name('upload-excel');
    Route::post('add-draft-quotation-document', 'OrderController@uploadDraftQuotationDocuments')->name('add-draft-quotation-document');
    Route::post('check_product_qty_draft', 'OrderController@checkProductQtyDraft')->name('check_product_qty_draft');
    Route::post('upload-order-excel', 'OrderController@uploadOrderExcel')->name('upload-order-excel');
    Route::post('add-order-document', 'OrderController@uploadOrderDocuments')->name('add-order-document');

    // upload draft quotation excel data
    Route::post('upload-Quotaion-excel', 'OrderController@uploadQuotationExcel')->name('upload-Quotation-excel');

    Route::get('order-docs-download/{id}', 'OrderController@downloadOrderDocuments')->name('order-docs-download');
    Route::post('get-quotation-files', 'OrderController@getQuotationFiles')->name('get-quotation-files');
    Route::post('get-draft-quotation-files', 'OrderController@getDraftQuotationFiles')->name('get-draft-quotation-files');
    Route::get('remove-quotation-file', 'OrderController@removeQuotationFile')->name('remove-quotation-file');
    Route::get('remove-draft-quotation-file', 'OrderController@removeDraftQuotationFile')->name('remove-draft-quotation-file');


    Route::post('search-product', 'OrderController@searchProduct')->name('search-product');
    Route::post('add-prod-to-quotation', 'OrderController@addProdToQuotation')->name('add-prod-to-quotation');
    Route::post('add-prod-to-order-quotation', 'OrderController@addProdToOrderQuotation')->name('add-prod-to-order-quotation');

    // Route::post('export-quot-to-pdf/{id}','OrderController@exportToPDF')->name('export-quot-to-pdf');
    //popup print route
    Route::get('export-quot-to-pdf-cancelled/{id}/{discount?}/{vat?}', 'OrderController@exportToPDFCancelled')->name('export-quot-to-pdf-cancelled');
    Route::get('export-quot-to-pdf/{id}/{page_type}/{column_name}/{default_sort}/{discount?}/{bank_id?}/{vat?}', 'OrderController@exportToPDF')->name('export-quot-to-pdf');

    Route::post('export-quot-to-pdf-inc-vat/{id}/{page_type}', 'OrderController@exportToPDFIncVat')->name('export-quot-to-pdf-inc-vat');

    // Draft Quotation View and view (inc vat) button route
    Route::get('export-draft-quot-to-pdf/{id}/{page_type}/{column_name}/{default_sort?}/{discount?}/{bank_id?}/{vat?}', 'OrderController@DraftQuotExportToPDF')->name('export-draft-quot-to-pdf');
    Route::get('export-draft-quot-to-pdf-inc-vat/{id}/{page_type}/{column_name}/{default_sort?}/{discount?}/{bank?}', 'OrderController@DraftQuotExportToPDFIncVat')->name('export-draft-quot-to-pdf-inc-vat');
    Route::get('export-draft-quot-to-pdf-exc-vat/{id}/{page_type}/{default_sort?}/{discount?}/{bank_id?}/{vat?}', 'OrderController@DraftQuotExportToPDF')->name('export-draft-quot-to-pdf-exc-vat');

    //popup print route
    Route::get('export-quot-to-pdf-inc-vat/{id}/{page_type}/{column_name}/{default_sort}/{proforma?}/{bank_id?}', 'OrderController@exportToPDFIncVat')->name('export-quot-to-pdf-inc-vat');

    Route::get('export-quot-to-pdf-exc-vat/{id}/{page_type}/{column_name}/{default_sort}/{proforma?}/{bank_id?}', 'OrderController@exportToPDFExcVat')->name('export-quot-to-pdf-exc-vat');

    // New Purchase Order Print in Draft invoice and Invoice
    Route::get('export-po-quot-to-pdf-exc-vat/{id}/{page_type}/{column_name}/{default_sort}/{proforma?}/{bank_id?}', 'OrderController@exportToPoPDFExcVat')->name('export-po-quot-to-pdf-exc-vat');

    Route::get('credit-note-print/{id}/{column_name}/{sortorder}/{proforma?}', 'OrderController@exportCreditNote')->name('credit-note-print');
    // Route::post('export-proforma-to-pdf/{id}','OrderController@exportProformaToPDF')->name('export-proforma-to-pdf');
    // popup print
    Route::get('export-proforma-to-pdf/{id}/{page_type}/{column_name}/{default_sort}/{bank_id?}', 'OrderController@exportProformaToPDF')->name('export-proforma-to-pdf');

    Route::get('export-proforma-to-pdf-copy/{id}/{page_type}/{default_sort}/{bank_id?}', 'OrderController@exportProformaToPDFCopy')->name('export-proforma-to-pdf-copy');
    Route::post('save-quotation-discount', 'OrderController@SaveQuotationDiscount')->name('save-quotation-discount');
    Route::post('save-order-data', 'OrderController@SaveOrderData')->name('save-order-data');
    /*********************************Notifications**************************/
    Route::get('/list-notifications', 'HomeController@allNotifications')->name('list-notifications');
    /****************** Supplier ROUTES OF SALES**************************/
    Route::get('/list-supplier', 'SupplierController@index')->name('list-supplier');
    Route::post('/add-supplier', 'SupplierController@add')->name('add-supplier');
    Route::get('/get-datatables-for-supplier', 'SupplierController@getData')->name('get-supplier');

    Route::get('/edit-supplier', 'SupplierController@edit')->name('supplier-edit');
    Route::post('/update-supplier', 'SupplierController@update')->name('update-supplier');
    Route::post('/update-supplier-profile-pic/{id}', 'SupplierController@updateSupplierProfile')->name('update-supplier-profile-pic');
    // getting supplier complete details
    Route::get('/get-supplier-details/{id}', 'SupplierController@getSupplierDetailByID')->name('get-supplier-details');
    // delete supplier from detail page
    Route::get('/delete-supplier', 'SupplierController@deleteSupplier')->name('delete-supplier');
    // getting product category childs for supplier
    Route::get('getting-product-category-childs-for-supplier', 'SupplierController@getProductCategoryChilds')->name('getting-product-category-childs-for-supplier');

    Route::post('/get-supplier-note', 'SupplierController@getSupplierNote')->name('get-supplier-note');
    // Route::post('export-draft-pi/{id}','HomeController@exportDraftPi')->name('export-draft-pi');
    // popup print
    Route::get('export-draft-pi/{id}/{page_type}/{column_name}/{default_sort}', 'HomeController@exportDraftPi')->name('export-draft-pi');
    Route::post('update_order_products', 'OrderController@updateOrderProducts')->name('update_order_products');

    Route::get('get_total_draft', function () {
        return redirect('sales/draft_invoices')->with('total_draft', 'Yes');
    })->name('get_total_draft');

    Route::get('get_customer_invoices/{customer_id}/{month}', function ($customer_id, $month) {
        // dd($customer_id);
        return redirect('sales/invoices')->with(['find' => 'Yes', 'customer_id' => $customer_id, 'month' => $month]);
    })->name('get_customer_invoices');

    Route::get('get_customer_report/{customer_id}/{month}', function ($customer_id, $month) {
        // dd($customer_id);
        return redirect('product-sales-report')->with(['find' => 'Yes', 'customer_id' => $customer_id, 'month' => $month]);
    })->name('get_customer_report');

    Route::get('get_customer_invoices/{customer_id}/{month}/{status}', function ($customer_id, $month, $status) {
        // dd($customer_id);
        return redirect('sales/invoices')->with(['find' => 'Yes', 'customer_id' => $customer_id, 'month' => $month, 'status' => $status]);
    })->name('get_customer_invoices_unpaid');

    Route::get('get-widgets-values', 'HomeController@getWidgetValues')->name('get-widgets-values');
});

Route::group(['namespace' => 'Importing', 'prefix' => 'importing', 'middleware' => 'importing'], function () {

    Route::get('run-group-script', 'PurchaseOrderGroupsController@runScript')->name('run-group-script');
    Route::get('/', 'HomeController@getHome')->name('importing-dashboard');

    Route::get('complete-profile', 'HomeController@completeProfile');
    Route::post('complete-profile', 'HomeController@completeProfileProcess');
    // Route::post('check-old-password','HomeController@checkOldPassword');

    // Route::group(['middleware' => 'incomplete-profile'], function(){
    // 	Route::get('/','HomeController@getHome');
    // });

    Route::get('/receiving-queue', 'HomeController@receivingQueue')->name('receiving-queue');
    Route::get('get-po-groups', 'PurchaseOrderGroupsController@getPoGroups')->name('get-po-groups');
    Route::get('incompleted-po-groups', 'PurchaseOrderGroupsController@inCompletedPoGroups')->name('incompleted-po-groups');
    Route::get('get-incompleted-po-groups', 'PurchaseOrderGroupsController@getInCompletedPoGroupsData')->name('get-incompleted-po-groups');
    Route::get('get-product-suppliers-record/{id}', 'PurchaseOrderGroupsController@getProductSuppliersRecord')->name('get-product-suppliers-record');

    Route::post('save-group-data', 'PurchaseOrderGroupsController@saveGroupData')->name('save-group-data');
    Route::get('get-details-of-completed-po/{id}', 'PurchaseOrderGroupsController@getDetailsOfCompletedPo')->name('get-details-of-completed-po');
    Route::get('/products-received/{id}', 'PurchaseOrderGroupsController@productReceived')->name('products-received');
    Route::get('/get-po-group-details/{id}', 'PurchaseOrderGroupsController@getPoGroupDetails')->name('get-po-group-details');
    Route::post('/edit-po-group', 'PurchaseOrderGroupsController@savePoGroupChanges')->name('edit-po-group');
    Route::post('/edit-po-group-details', 'PurchaseOrderGroupsController@savePoGroupDetailChanges')->name('edit-po-group-details');
    Route::post('/edit-po-goods', 'PurchaseOrderGroupsController@saveGoodsData')->name('edit-po-goods');

    Route::post('/get-incomplete-pos', 'PurchaseOrderGroupsController@getIncompletePos')->name('get-incomplete-pos');
    Route::get('/products-receiving-queue/{id}', 'PurchaseOrderGroupsController@productReceivingQueue')->name('products-receiving-queue');
    Route::get('get-details-of-po/{id}', 'PurchaseOrderGroupsController@getDetailsOfPo')->name('get-details-of-po');

    Route::post('confirm-po-group', 'PurchaseOrderGroupsController@confirmPoGroup')->name('confirm-po-group');
    Route::post('export-group-to-pdf', 'PurchaseOrderGroupsController@exportGroupToPDF')->name('export-group-to-pdf');
    Route::post('export-group-to-pdf2', 'PurchaseOrderGroupsController@exportGroupToPDFF')->name('export-group-to-pdf2');



    Route::get('pick-instruction', 'PurchaseOrderGroupsController@pickInstruction')->name('pick-instruction');
    Route::get('get-draft-invoices-importing', 'PurchaseOrderGroupsController@getDratInvoices')->name('get-draft-invoices-importing');
    Route::get('pick-instruction/{id}', 'PurchaseOrderGroupsController@pickInstructionDetail')->name('pick-instruction');
    Route::get('get-pick-instruction/{id}', 'PurchaseOrderGroupsController@getPickInstruction')->name('get-pick-instruction');



    /****************** Importing Profile Setting ROUTES**************************/
    Route::get('change-password-importing', 'HomeController@changePassword')->name('change-password-importing');
    Route::post('change-importing-password-process', 'HomeController@changePasswordProcess')->name('change-importing-password-process');
    Route::post('check-importing-old-password', 'HomeController@checkOldPassword')->name('check-importing-old-password');
    Route::get('importing-profile-setting', 'HomeController@profile')->name('importing-profile-setting');
    Route::post('importing-update-profile-setting', 'HomeController@updateProfile')->name('importing-update-profile-setting');
    Route::post('get-purchase-order-files-importing', 'PurchaseOrderGroupsController@getPurchaseOrderFiles')->name('get-purchase-order-files-importing');


    /****************** Importing Product Detail ROUTES**************************/
    Route::get('product-details/{id}', 'PurchaseOrderGroupsController@getProductDetail')->name('product-details');
    Route::get('get-product-suppliers-data/{id}', 'PurchaseOrderGroupsController@getProductSuppliersData')->name('get-product-suppliers-data');

    Route::get('get-single-po-detail/{id}', 'PurchaseOrderGroupsController@getPurchaseOrderDetail')->name('get-single-po-detail');
    Route::get('get-purchase-order-product-detail/{id}', 'PurchaseOrderGroupsController@getPurchaseOrderProdDetail')->name('get-purchase-order-product-detail');

    /******************************Code for new Groups***************************/

    Route::get('importing-receiving-queue', 'PoGroupsController@receivingQueue')->name('importing-receiving-queue');
    Route::get('get-importing-receiving-po-groups', 'PoGroupsController@getImportingReceivingPoGroups')->name('get-importing-receiving-po-groups');
    Route::get('view-po-numbers', 'PoGroupsController@viewPoNumbers')->name('view-po-numbers');
    Route::get('view-supplier_name', 'PoGroupsController@viewSupplierName')->name('view-supplier_name');
    Route::get('importing-receiving-queue-detail/{id}', 'PoGroupsController@receivingQueueDetail')->name('importing-receiving-queue-detail');
    Route::post('upload-bulk-product-in-group-detail', 'PoGroupsController@uploadBulkUploadInGroupDetail')->name('upload-bulk-product-in-group-detail');
    Route::post('save-po-group-info', 'PoGroupsController@savePoGroupInfoData')->name('save-po-group-info');

    //bulk import in group detail temporary table route
    Route::get('importing-receiving-queue-detail-import/{id}', 'PoGroupsController@receivingQueueDetailImport')->name('importing-receiving-queue-detail-import');
    Route::post('get-group-import-data', 'PoGroupsController@receivingQueueImportDetail')->name('get-group-import-data');
    Route::get('confirm-group-import-data', 'PoGroupsController@receivingQueueImportDetailConfirm')->name('confirm-group-import-data');
    Route::get('recursive-confirm-group-import-data', 'PoGroupsController@receivingQueueImportDetailConfirmRecursive')->name('recursive-confirm-group-import-data');


    Route::get('export-importing-product-receiving-record', 'PoGroupsController@exportImportingProductReceivingRecord')->name('export-importing-product-receiving-record');
    Route::post('upload-bulk-product-in-group-detail-job', 'PoGroupsController@exportImportingProductReceivingRecordImport')->name('upload-bulk-product-in-group-detail-job');

    Route::get('recursive-export-status-importing-receiving-products', 'PoGroupsController@recursiveExportStatusImportingPeceivingProducts')->name('recursive-export-status-importing-receiving-products');
    Route::get('recursive-export-status-importing-receiving-bulk-products', 'PoGroupsController@recursiveExportStatusImportingPeceivingBulkProducts')->name('recursive-export-status-importing-receiving-bulk-products');

    Route::post('get-po-group-product-details/{id}', 'PoGroupsController@getPoGroupProductDetails')->name('get-po-group-product-details');
    Route::get('get-po-group-product-details-footer-values/{id}', 'PoGroupsController@getPoGroupProductDetailsFooterValues')->name('get-po-group-product-details-footer-values');
    Route::post('edit-po-group-product-details', 'PoGroupsController@editPoGroupProductDetails')->name('edit-po-group-product-details');
    Route::post('edit-po-group-product-details-each', 'PoGroupsController@editPoGroupProductDetailsEach')->name('edit-po-group-product-details-each');
    //Route::post('export-importing-product-receiving-record','PoGroupsController@exportImportingProductReceivingRecord')->name('export-importing-product-receiving-record');
    Route::post('save-po-group-data', 'PoGroupsController@savePoGroupData')->name('save-po-group-data');
    Route::post('confirm-po-group-product-detail-cost', 'PoGroupsController@confirmPoGroup')->name('confirm-po-group-product-detail-cost');
    Route::get('importing-completed-receiving-queue-detail/{id}', 'PoGroupsController@completedReceivingQueueDetail')->name('importing-completed-receiving-queue-detail');
    Route::get('check-status-for-first-time', 'PoGroupsController@checkStatusFirstTime')->name('check-status-for-first-time');

    Route::post('get-completed-po-g-pd/{id}', 'PoGroupsController@getCompletedPoGPD')->name('get-completed-po-g-pd');

    Route::post('get-po-group-every-product-details-importing', 'PoGroupsController@getPoGroupEveryProductDetails')->name('get-po-group-every-product-details-importing');
    Route::get('get-pogroupproduct-history', 'PoGroupsController@getPoGroupProductHistory')->name('get-pogroupproduct-history');
    Route::post('clear-group-values', 'PoGroupsController@clearGroupValues')->name('clear-group-values');
});

Route::group(['namespace' => 'Warehouse', 'prefix' => 'warehouse', 'middleware' => 'warehouse'], function () {

    //Route for getting available stock of required product in TD (Sprint-21)
    Route::post('get-available-stock-of-product', 'TransferDocumentController@getStockDataForTD')->name('get-available-stock-of-product');
    Route::post('save-available-stock-of-product_in_td', 'TransferDocumentController@saveAvailableStockOfProductInTd')->name('save-available-stock-of-product_in_td');
    Route::post('get-reserved-stock-of-product', 'TransferDocumentController@getReservedStockDataForTD')->name('get-reserved-stock-of-product');




    Route::get('/', 'HomeController@getHome')->name('warehouse-dashboard');
    Route::get('get-draft-invoices-warehouse', 'HomeController@getDraftInvoices')->name('get-draft-invoices-warehouse');
    Route::get('get-transfer-document', 'HomeController@getTransferDocument')->name('get-transfer-document');

    Route::get('complete-profile', 'HomeController@completeProfile');
    Route::post('complete-profile', 'HomeController@completeProfileProcess');
    // Route::post('check-old-password','HomeController@checkOldPassword');

    // Route::group(['middleware' => 'incomplete-profile'], function(){
    // 	Route::get('/','HomeController@getHome');
    // });
    Route::post('export-pi-to-pdf/{id}/{column_name}/{default_sort}', 'HomeController@exportPiToPdf')->name('export-pi-to-pdf');
    Route::get('export-pi-to-pdf/{id}/{column_name}/{default_sort}', 'HomeController@exportPiToPdf')->name('export-pi-to-pdf');
    Route::get('export-stricker-to-pdf/{id}', 'HomeController@exportStrickerToPdf')->name('export-stricker-to-pdf');
    Route::get('warehouse-stock-adjustment', 'HomeController@warehouseStockAdjustment')->name('warehouse-stock-adjustment');
    Route::get('/pick-instruction/{id}', 'HomeController@pickInstruction')->name('pick-instruction');
    Route::get('/pick-instruction-of-td/{id}', 'HomeController@pickInstructionOfTransferDocument')->name('pick-instruction-of-td');
    Route::get('get-pick-instruction/{id}', 'HomeController@getPickInstruction')->name('get-pick-instruction');
    Route::post('full-qty-ship-importing', 'HomeController@fullQtyShipImporting')->name('full-qty-ship-importing');
    Route::post('full-pcs-ship-importing', 'HomeController@fullPCSShipImporting')->name('full-pcs-ship-importing');
    Route::get('get-transfer-pick-instruction/{id}', 'HomeController@getTransferPickInstruction')->name('get-transfer-pick-instruction');
    Route::post('full-qty-ship', 'HomeController@fullQTYShippedFunction')->name('full-qty-ship');
    Route::post('edit-pick-instruction', 'HomeController@editPickInstruction')->name('edit-pick-instruction');
    Route::post('edit-transfer-pick-instruction', 'HomeController@editTransferPickInstruction')->name('edit-transfer-pick-instruction');
    Route::post('confirm-order-pick-instruction', 'HomeController@confirmPickInstruction')->name('confirm-order-pick-instruction');
    Route::get('recersive_call_for_pi_job', 'HomeController@recursiveCallForPIJob')->name('recersive_call_for_pi_job');

    Route::post('confirm-transfer-pick-instruction', 'HomeController@confirmTransferPickInstruction')->name('confirm-transfer-pick-instruction');
    // Route::get('/recieving-queue','HomeController@recievingQueue')->name('recieving-queue');

    /****************** Warehouse Transfer ROUTES**************************/
    Route::get('create-w-transfer-document', 'HomeController@createTransferDocWarehouse')->name('create-w-transfer-document');
    Route::get('get-draft-warehouse-td/{id}', 'HomeController@getDraftWarehouseTd')->name('get-draft-warehouse-td');
    Route::get('warehouse-transfer-document-dashboard', 'HomeController@getWarehouseTransferDashboard')->name('warehouse-transfer-document-dashboard');
    Route::get('get-w-transfer-document-data', 'HomeController@getWarehouseTransferDocumentData')->name('get-w-transfer-document-data');
    Route::get('get-w-draft-transfer-data', 'HomeController@getDraftWarehouseTdData')->name('get-w-draft-transfer-data');
    Route::post('action-draft-w-td', 'HomeController@doActionDraftWarehouseTd')->name('action-draft-w-td');
    Route::get('get-warehouse-transfer-detail/{id}', 'HomeController@getWarehouseTransferDetail')->name('get-warehouse-transfer-detail');
    Route::post('confirm-w-transfer-document', 'HomeController@confirmWarehouseTransferDocument')->name('confirm-w-transfer-document');
    Route::get('delete-w-transfer-documents', 'HomeController@deleteTransferDocWarehouse')->name('delete-w-transfer-documents');
    /****************** Warehouse Profile Setting ROUTES**************************/
    Route::get('change-password-warehouse', 'HomeController@changePassword')->name('change-password-warehouse');
    Route::post('change-warehouse-password-process', 'HomeController@changePasswordProcess')->name('change-warehouse-password-process');
    Route::post('check-warehouse-old-password', 'HomeController@checkOldPassword')->name('check-warehouse-old-password');
    Route::get('warehouse-profile-setting', 'HomeController@profile')->name('warehouse-profile-setting');
    Route::post('warehouse-update-profile-setting', 'HomeController@updateProfile')->name('warehouse-update-profile-setting');
    Route::get('warehouse-incompleted-po-groups', 'PurchaseOrderGroupsController@inCompletedPoGroups')->name('warehouse-incompleted-po-groups');
    Route::get('warehouse-incompleted-transfer-groups', 'PurchaseOrderGroupsController@inCompletedTransferGroups')->name('warehouse-incompleted-transfer-groups');
    Route::get('get-warehouse-incompleted-po-groups', 'PurchaseOrderGroupsController@getWarehouseInCompletedPoGroupsData')->name('get-warehouse-incompleted-po-groups');

    Route::get('get-warehouse-incompleted-td-groups', 'PurchaseOrderGroupsController@getWarehouseInCompletedTDGroupsData')->name('get-warehouse-incompleted-td-groups');

    Route::get('transfer-warehouse-products-receiving-queue/{id}', 'PurchaseOrderGroupsController@transferProductReceivingQueue')->name('transfer-warehouse-products-receiving-queue');
    Route::get('warehouse-products-receiving-queue/{id}', 'PurchaseOrderGroupsController@productReceivingQueue')->name('warehouse-products-receiving-queue');
    Route::get('warehouse-complete-products-receiving-queue/{id}', 'PurchaseOrderGroupsController@completeProductReceivingQueue')->name('warehouse-complete-products-receiving-queue');

    Route::get('warehouse-complete-transfer-products-receiving-queue/{id}', 'PurchaseOrderGroupsController@completeTransferProductReceivingQueue')->name('warehouse-complete-transfer-products-receiving-queue');
    Route::get('get-details-of-po/{id}', 'PurchaseOrderGroupsController@getDetailsOfPo')->name('get-details-of-po');
    Route::get('get-details-of-transfer-doc/{id}', 'PurchaseOrderGroupsController@getDetailsOfTransDoc')->name('get-details-of-transfer-doc');
    Route::get('get-details-of-transfer-doc-history', 'PurchaseOrderGroupsController@gettransferProductReceivingQueueHistory')->name('get-details-of-transfer-doc-history');

    Route::get('get-details-of-complete-transfer-doc/{id}', 'PurchaseOrderGroupsController@getDetailsOfCompleteTransDoc')->name('get-details-of-complete-transfer-doc');
    Route::get('get-details-of-completed-po-group/{id}', 'PurchaseOrderGroupsController@getDetailsOfCompletedPoGroup')->name('get-details-of-completed-po-group');
    Route::post('edit-po-group-details', 'PurchaseOrderGroupsController@savePoGroupDetailChanges')->name('edit-po-group-details');
    Route::post('full-qty-add', 'PurchaseOrderGroupsController@fullQtyAdd')->name('full-qty-add');
    Route::post('edit-po-goods', 'PurchaseOrderGroupsController@saveGoodsData')->name('edit-po-goods');
    Route::post('confirm-warehouse-po-group', 'PurchaseOrderGroupsController@confirmPoGroup')->name('confirm-warehouse-po-group');
    Route::post('confirm-transfer-warehouse-po-group', 'PurchaseOrderGroupsController@confirmTransferGroup')->name('confirm-transfer-warehouse-po-group');
    Route::post('check-transfer-document-status', 'PurchaseOrderGroupsController@checkTransferStatus')->name('check-transfer-document-status');

    Route::get('get-product-suppliers-dataa/{id}', 'PurchaseOrderGroupsController@getProductSuppliersData')->name('get-product-suppliers-dataa');

    Route::post('export-group-to-pdf2', 'PurchaseOrderGroupsController@exportGroupToPDFF')->name('export-group-to-pdf2');
    Route::get('get-pdf-status', 'PurchaseOrderGroupsController@getPdfStatus')->name('get-pdf-status');

    Route::post('completed-export-group-to-pdf', 'PurchaseOrderGroupsController@exportCompletedGroupToPDF')->name('completed-export-group-to-pdf');

    /******************************Code for new Groups***************************/
    Route::get('warehouse-receiving-queue', 'PoGroupsController@receivingQueue')->name('warehouse-receiving-queue');
    Route::get('get-warehouse-receiving-po-groups', 'PoGroupsController@getWarehouseReceivingPoGroups')->name('get-warehouse-receiving-po-groups');
    Route::get('view-po-numbers-warehouse', 'PoGroupsController@viewPoNumbersWarehouse')->name('view-po-numbers-warehouse');
    Route::get('view-supplier_names_warehouse', 'PoGroupsController@viewSupplierNamesWarehouse')->name('view-supplier_names_warehouse');
    Route::get('warehouse-receiving-queue-detail/{id}', 'PoGroupsController@receivingQueueDetail')->name('warehouse-receiving-queue-detail');
    Route::get('get-po-group-product-details/{id}', 'PoGroupsController@getPoGroupProductDetails')->name('get-po-group-product-details');
    Route::get('get-po-group-product-details-history', 'PoGroupsController@getPoGroupProductDetailsHistory')->name('warehouse-products-receiving-history');
    Route::get('warehouse-reverted-pos', 'PoGroupsController@getGroupRevertedPos')->name('warehouse-reverted-pos');
    Route::post('full-qty-for-receiving', 'PoGroupsController@fullQtyForReceiving')->name('full-qty-for-receiving');
    Route::post('edit-po-group-product-details', 'PoGroupsController@editPoGroupProductDetails')->name('edit-po-group-product-details');
    Route::post('edit-po-group-detail-goods', 'PoGroupsController@savePoGroupGoodsData')->name('edit-po-group-detail-goods');
    Route::post('confirm-po-group-product-detail', 'PoGroupsController@confirmPoGroupDetail')->name('confirm-po-group-product-detail');
    Route::get('warehouse-completed-receiving-queue-detail/{id}', 'PoGroupsController@completeReceivingQueueDetail')->name('warehouse-completed-receiving-queue-detail');

    Route::get('get-completed-po-group-product-details/{id}', 'PoGroupsController@getCompletedPoGroupProductDetails')->name('get-completed-po-group-product-details');

    Route::get('get-po-group-every-product-details', 'PoGroupsController@getPoGroupEveryProductDetails')->name('get-po-group-every-product-details');
    Route::post('export-product-receiving-record', 'PoGroupsController@exportProductReceivingRecord')->name('export-product-receiving-record');
});

Route::group(['namespace' => 'Accounting', 'prefix' => 'accounting', 'middleware' => 'accounting'], function () {

    Route::get('/', 'HomeController@getDashboard')->name('accounting-dashboard');
    Route::get('change-password-accounting', 'HomeController@changePassword')->name('change-password-accounting');

    Route::get('create-credit-note', 'HomeController@createCreditNote')->name('create-credit-note');
    Route::get('get-credit-note-detail/{id}', 'HomeController@getCreditNoteDetail')->name('get-credit-note-detail');
    Route::post('complete-credit-note', 'HomeController@completeCreditNote')->name('complete-credit-note');

    Route::get('create-debit-note', 'HomeController@createDebitNote')->name('create-debit-note');
    Route::get('get-debit-note-detail/{id}', 'HomeController@getDebitNoteDetail')->name('get-debit-note-detail');
    Route::post('complete-debit-note', 'HomeController@completeDebitNote')->name('complete-debit-note');

    Route::get('get-credit-notes', 'HomeController@getCreditNotes')->name('get-credit-notes');
    Route::get('get-supplier-credit-notes', 'HomeController@getSupplierCreditNotes')->name('get-supplier-credit-notes');
    Route::get('get-supplier-debit-notes', 'HomeController@getSupplierDebitNotes')->name('get-supplier-debit-notes');
    Route::post('accounting-fetch-customer', 'HomeController@accountingFetchCustomer')->name('accounting-fetch-customer');
    Route::get('debit-notes-dashboard', 'HomeController@debitNotesDashboard')->name('debit-notes-dashboard');
    Route::get('get-debit-notes', 'HomeController@getDebitNotes')->name('get-debit-notes');

    Route::get('get-completed-quotation-products-to-list/{id}', 'HomeController@getProductsData')->name('get-completed-quotation-products-to-list');

    Route::get('delete-credit-note', 'HomeController@deleteCreditNote')->name('delete-credit-note');
    Route::get('delete-debit-note', 'HomeController@deleteDebitNote')->name('delete-debit-note');

    Route::get('/get_draft_invoices_dashboard', 'HomeController@getDraftInvoices')->name('get_draft_invoices_dashboard');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('partial-shipment-order-process/{order_id}', 'PartialShipmentController@index')->name('partial-shipment-order-process');
    Route::get('partial-shipment-order/{order_id}', 'PartialShipmentController@partialShipmentRequest')->name('partial-shipment-order');
});

// Route for Updating DB in Ecommerce Platform
Route::get('update-products', 'Ecom\EcomApiController@updateproducts');
Route::get('update-configuration', 'Ecom\EcomApiController@updateconfiguration');
Route::get('update-cus-category', 'Ecom\EcomApiController@updatecustomercategory');
Route::get('update-cust-cat-margin', 'Ecom\EcomApiController@updatecustomertypecategorymargin');
Route::get('update-cus-pro-margin', 'Ecom\EcomApiController@customertypeproductmargin');
Route::get('update-pro-category', 'Ecom\EcomApiController@updateproductcategory');
Route::get('update-pro-cus-fixed-price', 'Ecom\EcomApiController@updateproductcustomerfixedprice');
Route::get('update-pro-fixed-price', 'Ecom\EcomApiController@updateproductfixedprice');
Route::get('update-pro-image', 'Ecom\EcomApiController@updateproductimage');
Route::get('update-pro-type', 'Ecom\EcomApiController@producttype');
Route::get('update-quo-config', 'Ecom\EcomApiController@updatequotationconfig');
Route::get('update-warehouse', 'Ecom\EcomApiController@updatewarehouse');
Route::get('update-whouse-product', 'Ecom\EcomApiController@updatewarehouseproduct');
Route::get('update-sc-statuses', 'Ecom\EcomApiController@updatescstatuses');

Route::group(['prefix' => 'ecom', 'middleware' => 'ecom'], function () {
    Route::get('/ecom-dashboard', 'Ecom\HomeController@getDashboard')->name('ecom-dashboard');
    Route::get('get-completed-quotation-ecom', 'Ecom\HomeController@getDraftInvoices')->name('get-completed-quotation-ecom');
    Route::get('/invoices', 'Ecom\HomeController@getInvoiceEcom')->name('ecom-invoices');
    Route::get('get-invoices-ecom', 'Ecom\HomeController@getInvoicesData')->name('get-invoices-ecom');
    Route::get('ecommerce-product-list', 'Ecom\EcomProductController@index')->name('ecommerce-product-list');
    Route::get('ecom-customer-list', 'Ecom\CustomerController@index')->name('ecom-customer-list');
    Route::get('/get-ecommerce-customer', 'Ecom\CustomerController@getEcomData')->name('get-ecom-customer');
    Route::get('ecom-sold-product', 'Ecom\SoldProductReportController@ecomsoldproductreport')->name('ecom-sold-product');
    Route::get('get-ecom-sold-product-data-for-report', 'Ecom\SoldProductReportController@ecomsoldproductreportdata')->name('get-ecom-sold-product-data-for-report');

    Route::post('get-datatables-for-ecom-products', 'Ecom\EcomProductController@getEcomProductData')->name('get-ecom-product');

    Route::get('get-payment-image', 'Ecom\HomeController@getPaymentImage')->name('get-payment-image');

    Route::get('cancel-ecom-order', 'Ecom\HomeController@CancelOrder')->name('cancel-ecom-order');

    Route::get('proceed-invoice-order', 'Ecom\HomeController@ProceedInvoiceOrder')->name('proceed-invoice-order');


    Route::get('get-ecom-cancelled-orders', 'Ecom\CustomerController@EcomCancelledOrders')->name('get-ecom-cancelled-orders');
    Route::get('get-ecom-cancelled-data', 'Ecom\CustomerController@EcomCancelledOrdersData')->name('get-ecom-cancelled-data');
    Route::post('new_pin_generate', 'Ecom\CustomerController@EcomPinGenerate')->name('new_pin_generate');
});
Route::get('testing-route', function () {
    $x = \App\ProductSaleReportDetailHistory::all();
    dd($x);
});
