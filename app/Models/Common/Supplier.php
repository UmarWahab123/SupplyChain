<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Jobs\BulkUploadPOJob;
use App\ExportStatus;
use Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'category_id', 'product_type_id', 'credit_term', 'address_line_1', 'address_line_2', 'first_name', 'last_name', 'company', 'phone', 'postalcode', 'email', 'country', 'state', 'city', 'reference_number', 'status', 'currency_id', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getnotes()
    {
        return $this->hasMany('App\Models\Common\SupplierNote', 'supplier_id', 'id');
    }

    public function suppliers()
    {
        return $this->hasMany('App\Models\Common\SupplierProducts', 'supplier_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Common\Product', 'default_supplier', 'id');
    }

    public function productcategory()
    {
        return $this->belongsTo('App\Models\Common\ProductCategory', 'category_id', 'id');
    }

    public function producttype()
    {
        return $this->belongsTo('App\Models\Common\ProductType', 'product_type_id', 'id');
    }

    public function productTypeTertiary()
    {
        return $this->belongsTo('App\ProductTypeTertiary', 'product_type_tertiary_id', 'id');
    }

    public function getcountry()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country', 'id');
    }

    public function getstate()
    {
        return $this->belongsTo('App\Models\Common\State', 'state', 'id');
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'supplier_id', 'id');
    }

    public function purchaseOrderSup()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'supplier_id', 'id');
    }

    public function supplier_po()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrder', 'supplier_id', 'id');
    }

    public function draftPurchaseOrder()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\DraftPurchaseOrder', 'supplier_id', 'id')->orderby('id', 'DESC');
    }

    public function getpayment_term()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm', 'credit_term', 'id');
    }

    public function getCurrency()
    {
        return $this->belongsTo('App\Models\Common\Currency', 'currency_id', 'id');
    }

    public function supplierMultipleCat()
    {
        return $this->hasMany('App\Models\Common\SupplierCategory', 'supplier_id', 'id');
    }

    public function supplierContacts()
    {
        return $this->hasMany('App\Models\Common\SupplierContacts', 'supplier_id', 'id');
    }

    public function supplierDocuments()
    {
        return $this->hasMany('App\Models\Common\SupplierGeneralDocument', 'supplier_id', 'id');
    }

    public function supplier_categories()
    {
        return $this->hasMany('App\Models\Common\SupplierCategory', 'supplier_id', 'id');
    }

    public static function bulkUploadPO($request)
    {
        $validator = $request->validate([
            'excel' => 'required|mimes:xlsx'
        ]);
        try {
            // $fileName = time().'_'.$request['excel']->getClientOriginalName();
            // $contents = file_get_contents($request['excel']->getRealPath());
            // Storage::disk('local')->put($fileName, $contents);

            $file = $request->file('excel');
            $file_arr = explode('.', $file->getClientOriginalName());
            $file_name = $file_arr[0] . '-' . time() . '.' . $file_arr[1];
            Storage::disk('bulk_uploads')->put($file_name, file_get_contents($file));

            $fileName = $file_name;

            $statusCheck = ExportStatus::where('type', 'bulk_upload_po')->where('user_id', Auth::user()->id)->first();
            if ($statusCheck == null) {
                $new = new ExportStatus();
                $new->type = 'bulk_upload_po';
                $new->user_id = Auth::user()->id;
                $new->status = 1;
                if ($new->save()) {
                    BulkUploadPOJob::dispatch($fileName, Auth::user()->id);
                    return response()->json(['status' => 1]);
                }
            } else if ($statusCheck->status == 0 || $statusCheck->status == 2) {
                ExportStatus::where('type', 'bulk_upload_po')->where('user_id', Auth::user()->id)->update(['status' => 1, 'exception' => null, 'error_msgs' => null]);
                BulkUploadPOJob::dispatch($fileName, Auth::user()->id);
                return response()->json(['status' => 1]);
            } else {
                return response()->json(['msg' => 'File is Already Uploding, Please wait...!', 'status' => 1]);
            }
        } catch (Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
                'success' => false
            ]);
        }
    }

    public static function RecursiveCallForBulkPos($request)
    {
        $status = ExportStatus::where('type', 'bulk_upload_po')->where('user_id', Auth::user()->id)->first();
        return response()->json([
            'status' => $status->status,
            'exception' => $status->exception,
            'error_msgs' => $status->error_msgs
        ]);
    }

    public static function CheckStatusFirstTimeForBulkPos($request)
    {
        $status = ExportStatus::where('type', 'bulk_upload_po')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public static function SupplierListSorting($request, $query)
    {
        $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
        if ($request['column_name'] == 'supplier_no') {
            $query->orderBy('reference_number', $sort_order);
        } elseif ($request['column_name'] == 'reference_name') {
            $query->orderBy('reference_name', $sort_order);
        } elseif ($request['column_name'] == 'company_name') {
            $query->orderBy('company', $sort_order);
        } elseif ($request['column_name'] == 'country') {
            $query->leftJoin('countries', 'countries.id', '=', 'suppliers.country')->orderBy('countries.name', $sort_order);
        } elseif ($request['column_name'] == 'address') {
            $query->orderBy('address_line_1', $sort_order);
        } elseif ($request['column_name'] == 'state') {
            $query->leftJoin('states', 'states.id', '=', 'suppliers.state')->orderBy('states.name', $sort_order);
        }
        elseif ($request['column_name'] == 'postalcode') {
            $query->orderBy('postalcode', $sort_order);
        }
        elseif ($request['column_name'] == 'tex_id') {
            $query->orderBy('tex_id', $sort_order);
        }
        elseif ($request['column_name'] == 'supplier_since') {
            $query->orderBy('created_at', $sort_order);
        } elseif ($request['column_name'] == 'open_pos') {
            // $query->leftJoin('purchase_orders', 'purchase_orders.supplier_id', '=', 'suppliers.id')->where('purchase_orders.status', 12)->groupBy('suppliers.id')->orderBy(\DB::Raw('count(purchase_orders.id)'), $sort_order);
            $query->leftJoin('purchase_orders', 'purchase_orders.supplier_id', '=', 'suppliers.id')->groupBy('suppliers.id')->orderBy(\DB::Raw('CASE WHEN purchase_orders.status="12" THEN COUNT(purchase_orders.id) END'), $sort_order);
        } elseif ($request['column_name'] == 'total_pos') {
            $query->leftJoin('purchase_orders', 'purchase_orders.supplier_id', '=', 'suppliers.id')->groupBy('suppliers.id')->orderBy(\DB::Raw('count(purchase_orders.id)'), $sort_order);
        } else {
            $query->orderBy('id');
        }
        return $query;
    }

    public static function returnAddColumn($column, $item)
    {
        switch ($column) {
            case 'supplier_nunmber':
                if ($item->reference_number !== null) {
                    $html_string = '<a id="sup_num_' . $item->id . '" href="' . url('get-supplier-detail/' . $item->id) . '"><b>' . $item->reference_number . '</b></a>';
                } else {
                    $html_string = '<a id="sup_num_' . $item->id . '" href="' . url('get-supplier-detail/' . $item->id) . '"><b>--</b></a>';
                }
                return $html_string;
                break;

            case 'address':
                return $item->address_line_1 !== null ? $item->address_line_1 . ' ' . $item->address_line_2 : '--';
                break;

            case 'phone':
                return $item->phone !== null ? $item->phone : '--';
                break;

            case 'postalcode':
                return $item->postalcode !== null ? $item->postalcode : '--';
                break;

            case 'status':
                if ($item->status == 1) {
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Completed</span>';
                } elseif ($item->status == 2) {
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Suspended</span>';
                } else {
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Incomplete</span>';
                }
                return $status;
                break;

            case 'action':
                $html_string = '';
                if (Auth::user()->role_id != 7) {
                    if ($item->status == 1 && $item->deleted_at == Null) {
                        $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon suspend-supplier" data-id="' . $item->id . '" title="Suspend"><i class="fa fa-ban"></i></a>';
                    } elseif ($item->status == 2) {
                        $countPos = $item->supplier_po->count();
                        $html_string .= ' <a href="javascript:void(0);" class="actionicon viewIcon activateIcon" data-id="' . $item->id . '" title="Activate"><i class="fa fa-check"></i></a>';
                        $html_string .= ' <a href="javascript:void(0)" data-id="' . $item->id . '" data-pos="' . $countPos . '" class="actionicon delete-incomplete-supplier deleteIcon" title="Delete Supplier"><i class="fa fa-trash"></i></a>';
                    } elseif ($item->status == 0 && $item->deleted_at == Null) {
                        $html_string .= ' <a href="javascript:void(0)" data-id="' . $item->id . '" class="actionicon delete-incomplete-supplier deleteIcon" title="Delete Supplier"><i class="fa fa-trash"></i></a>';
                    }
                }
                return $html_string;
                break;

            case 'product_type':
                $html_string = '';
                if ($item->main_tags != null) {
                    $multi_tags = explode(',', $item->main_tags);
                    foreach ($multi_tags as $tag) {
                        $html_string .= ' <span class="abc">' . $tag . '</span>';
                    }
                    return $html_string;
                } else {
                    $html_string = '--';
                    return $html_string;
                }
                break;

            case 'created_at':
                return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : '--';
                break;

            case 'open_pos':
                $countPos = $item->supplier_po->where('status', 12)->count();
                $html_string = $countPos;
                return $html_string;
                break;

            case 'total_pos':
                $countPos = $item->supplier_po->count();
                $html_string = $countPos;
                return $html_string;
                break;

            case 'last_order_date':
                $last_order = $item->supplier_po->first();
                if ($last_order) {
                    return (@$last_order->confirm_date != null) ? Carbon::parse(@$last_order->confirm_date)->format('d/m/Y') : '--';
                } else {
                    return "--";
                }
                break;

            case 'notes':
                $notes = $item->getnotes->count('id');

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if ($notes > 0) {
                    $note = $item->getnotes->first()->note_description;
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="' . $item->id . '" class=" d-block show-notes mr-2 font-weight-bold" title="View Notes">' . mb_substr($note, 0, 30) . ' ...</a>';
                }

                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="' . $item->id . '"  class="add-notes fa fa-plus" title="Add Note"></a>
                            </div>';
                return $html_string;
                break;
        }
    }

    public static function returnEditColumn($column, $item)
    {
        switch ($column) {
            case 'company':
                return $item->company !== null ? $item->company : '--';
                break;

            case 'reference_name':
                if ($item->reference_name !== null) {
                    $html_string = '<a id="sup_ref_' . $item->id . '" href="' . url('get-supplier-detail/' . $item->id) . '"><b>' . $item->reference_name . '</b></a>';
                } else {
                    $html_string = '<a id="sup_ref_' . $item->id . '" href="' . url('get-supplier-detail/' . $item->id) . '"><b>--</b></a>';
                }
                return $html_string;
                break;

            case 'country':
                return $item->country !== null && $item->getcountry !== null ? $item->getcountry->name : '--';
                break;

            case 'state':
                return $item->state !== null && $item->getstate !== null ? $item->getstate->name : '--';
                break;

            case 'email':
                return $item->email !== null ? $item->email : '--';
                break;

            case 'city':
                return $item->city !== null ? $item->city : '--';
                break;
            case 'tax_id':
                return $item->tax_id !== null ? $item->tax_id : '--';
                break;
        }
    }
    public static function returnFilterColumn($column, $item, $keyword)
    {
        switch ($column) {
            case 'supplier_nunmber':
                $query = $item->where('reference_number', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'country':
                $query = $item->whereHas('getcountry', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%$keyword%");
                });
                return $query;
                break;
        }
    }
}
