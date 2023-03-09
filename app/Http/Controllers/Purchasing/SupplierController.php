<?php

namespace App\Http\Controllers\Purchasing;

use App\ExportStatus;
use App\Http\Controllers\Controller;
use App\Imports\SupplierBulkImport;
use App\Mail\Backend\SupplierActivationEmail;
use App\Mail\Backend\SupplierSuspensionEmail;
use App\Models\Common\Country;
use App\Models\Common\Currency;
use App\Models\Common\EmailTemplate;
use Illuminate\Support\Facades\Validator;
use App\Models\Common\PaymentTerm;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\State;
use App\Models\Common\PaymentType;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierAccount;
use App\Models\Common\SupplierCategory;
use App\Models\Common\SupplierContacts;
use App\Models\Common\SupplierGeneralDocument;
use App\Models\Common\SupplierNote;
use App\Models\Common\TempProduct;
use App\Models\Common\TempSupplier;
use App\Models\Common\Warehouse;
use App\Models\Common\Product;
use App\ImportFileHistory;
// use App\Supplier;
use App\PoPaymentRef;
use App\PurchaseOrderTransaction;
use Auth;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mail;
use Yajra\Datatables\Datatables;
use App\General;
use App\Variable;
use App\Notification;
use App\Models\Common\Configuration;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Imports\POBulkImport;
use App\Helpers\SupplierHelper;
use App\Jobs\SupplierListJob;
use App\Models\Common\TableHideColumn;

class SupplierController extends Controller
{
    protected $user;
    public function __construct()
    {

        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
        $dummy_data = null;
        if ($this->user && Schema::has('notifications')) {
            $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        }
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;

        $vairables = Variable::select('slug', 'standard_name', 'terminology')->get();
        $global_terminologies = [];
        foreach ($vairables as $variable) {
            if ($variable->terminology != null) {
                $global_terminologies[$variable->slug] = $variable->terminology;
            } else {
                $global_terminologies[$variable->slug] = $variable->standard_name;
            }
        }

        $config = Configuration::first();
        $sys_name = $config->company_name;
        $sys_color = $config;
        $sys_logos = $config;
        $part1 = explode("#", $config->system_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $num1 = hexdec($value);
        $num2 = hexdec('001500');
        $sum = $num1 + $num2;
        $sys_border_color = "#";
        $sys_border_color .= dechex($sum);
        $part1 = explode("#", $config->btn_hover_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $number = hexdec($value);
        $sum = $number + $num2;
        $btn_hover_border = "#";
        $btn_hover_border .= dechex($sum);
        $current_version = '3.8';

        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version, 'dummy_data' => $dummy_data]);
    }
    public function index()
    {
        // $countries = Country::orderby('name', 'ASC')->pluck('name', 'id');
        // $allcategory = ProductCategory::all();
        // $pcategory = ProductCategory::where('parent_id',0)->get();
        // $currencies = Currency::all();
        // $product = ProductType::all()->pluck('title','id');
        // $payment_terms = PaymentTerm::all();

        // return view('users.suppliers.index',compact('pcategory','allcategory','countries','product','currencies','payment_terms'));
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'supplier_list')->first();

        return view('users.suppliers.index', compact('table_hide_columns'));
    }

    public function getData(Request $request)
    {
        $query = Supplier::query();
        $query->with('getcountry:id,name', 'getstate:id,name', 'supplier_po:id,supplier_id,confirm_date', 'getnotes:id,supplier_id,note_description')->select('suppliers.*');

        if ($request->suppliers_status != '') {
            $query->where('suppliers.status', $request->suppliers_status);
        }
        $query = Supplier::SupplierListSorting($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['supplier_nunmber', 'address', 'phone', 'postalcode', 'status', 'action', 'product_type', 'created_at', 'open_pos', 'total_pos', 'last_order_date', 'notes'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return Supplier::returnAddColumn($column, $item);
            });
        }

        $edit_columns = ['company', 'reference_name', 'country', 'state', 'email', 'city', 'tax_id'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Supplier::returnEditColumn($column, $item);
            });
        }

        $filter_columns = ['supplier_nunmber', 'country'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return Supplier::returnFilterColumn($column, $item, $keyword);
            });
        }

        $dt->rawColumns(['action', 'status', 'country', 'state', 'email', 'city', 'postalcode', 'detail', 'product_type', 'created_at', 'open_pos', 'total_pos', 'last_order_date', 'notes', 'reference_name', 'supplier_nunmber']);
        return $dt->make(true);
    }

    public function getProductCategoryChilds(Request $request)
    {
        $subCategories = ProductCategory::where('parent_id', $request->category_id)->get();
        $html_string = '';
        $html_string .= '<option value="">Select Sub-Category</option>';
        foreach ($subCategories as $value) {
            $html_string .= '<option value="' . $value->id . '">' . $value->title . '</option>';
        }
        return response()->json(['html_string' => $html_string]);
    }

    public function getSupplierDetailByID($id)
    {
        $supplier = Supplier::with('getcountry:id,name', 'productcategory', 'getstate:id,name', 'supplier_categories:id,supplier_id,category_id', 'supplier_categories.categoryTitle:id,parent_id,title', 'supplier_categories.categoryTitle.parent_category:id,parent_id,title')->where('id', $id)->first();
        $supplier_categories_titles = $supplier->supplier_categories;
        // dd($supplier);

        $parent_ids = $supplier_categories_titles->pluck('category_id')->toArray();

        $parent_categories = ProductCategory::whereIn('id', $parent_ids)->groupBy('parent_id')->pluck('parent_id')->toArray();

        $parent_categories1 = ProductCategory::whereIn('id', $parent_categories)->groupBy('title')->get();
        // exit;
        $countries = Country::select('id', 'name')->get();
        $country_id = $supplier->country;
        $states = State::select('id', 'name')->where('country_id', $country_id)->get();
        $supplierNotes = SupplierNote::with('getuser')->where('supplier_id', $id)->get();
        $paymentTerms = PaymentTerm::select('id', 'title')->get();

        $categories = ProductCategory::select('id', 'title')->with('get_Child')->where('parent_id', 0)->get();

        $SupplierCat_count = SupplierCategory::with('supplierCategories')->where('supplier_id', $id)->count();
        $currencies = Currency::select('id', 'currency_name')->get();
        $supplierCats = SupplierCategory::where('supplier_id', $id)->pluck('category_id')->toArray();
        // $warehouses = Warehouse::where('status',1)->get();
        $primary_category = $categories;

        $porders_data = PurchaseOrder::with('PoSupplier.getCurrency:id,currency_symbol')->where('supplier_id', $id)->whereIn('status', [13, 14, 15])->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            });

        $supplier_order_docs = PurchaseOrderDocument::with('PurchaseOrder:id,created_at,confirm_date')->whereHas('PurchaseOrder', function ($q) use ($id) {
            $q->whereIn('status', [13, 14, 15])
            ->where('supplier_id', $id);
        })->orderBy('po_id', 'ASC')->get();
        $temp_product_count = TempProduct::where('supplier_id', $id)->count();

        $extra_space = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        return view('users.suppliers.supplier-detail', compact('supplier_categories_titles', 'supplier', 'categories', 'id', 'SupplierCat_count', 'countries', 'states', 'paymentTerms', 'supplierCats', 'currencies', 'supplierNotes', 'primary_category', 'porders_data', 'supplier_order_docs', 'temp_product_count', 'parent_categories', 'parent_categories1', 'extra_space'));
    }

    public function redirectToProducts($id)
    {
        return redirect('complete-list-product')->with('id', $id);
    }

    public function addSupplierCats(Request $request)
    {
        $getCats = SupplierCategory::where('supplier_id', $request->edit_supplier_id)->count();
        if ($getCats > 0) {
            $getCats = SupplierCategory::where('supplier_id', $request->edit_supplier_id)->get();
            foreach ($getCats as $cat) {
                $cat->delete();
            }
        }

        if ($request->category_id[0] != null) {
            for ($i = 0; $i < sizeof($request->category_id); $i++) {
                $supplierCategories              = new SupplierCategory;
                $supplierCategories->supplier_id = $request->edit_supplier_id;
                $supplierCategories->category_id = $request->category_id[$i];
                $supplierCategories->save();
            }
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function getCountryStates(Request $request)
    {
        $states = State::where('country_id', $request->country_id)->get();
        $html_string = '';
        if ($states) {
            $html_string .= '<option value="" disabled="" selected="">States</option>';
            foreach ($states as $state) {
                $html_string .= '<option value="' . $state->id . '">' . $state->name . '</option>';
            }
        }

        return response()->json([
            'html_string' => $html_string
        ]);
    }

    public function saveSuppDataSuppDetail(Request $request)
    {
        return SupplierHelper::saveSuppDataSuppDetail($request);
    }

    public function add(Request $request)
    {
        $supplier = new Supplier;

        // $check_ref_supplier = Supplier::orderby('id','DESC')->first();
        // $prefix = "SC";
        // $str = @$check_ref_supplier->reference_number;
        // if($str  == NULL)
        // {
        //   $str = "1";
        // }
        // $matches = array();
        // preg_match('/([a-zA-Z]+)(\d+)/', $str, $matches );
        // $system_gen_no =  $prefix.str_pad(@$matches[2] + 1, STR_PAD_LEFT);

        // $supplier->reference_number = $system_gen_no;
        $supplier->user_id = Auth::user()->id;
        $supplier->status = 0;
        $supplier->save();

        return response()->json(['id' => $supplier->id]);
    }

    public function doSupplierCompleted(Request $request)
    {
        if ($request->id) {
            $supplier_detail = Supplier::find($request->id);
            $missingPrams = array();

            if ($supplier_detail->company == null) {
                $missingPrams[] = 'Company';
            }
            if ($supplier_detail->reference_name == null) {
                $missingPrams[] = 'Ref Name';
            }
            if ($supplier_detail->address_line_1 == null) {
                $missingPrams[] = 'Address';
            }
            if ($supplier_detail->country == null) {
                $missingPrams[] = 'Country';
            }
            if ($supplier_detail->city == null) {
                $missingPrams[] = 'City';
            }
            if ($supplier_detail->postalcode == null) {
                $missingPrams[] = 'Postal Code';
            }
            if ($supplier_detail->currency_id == null) {
                $missingPrams[] = 'Currency';
            }

            // if($supplier_detail->supplierMultipleCat->count() < 0)
            // {
            //     $missingPrams[] = 'Categories';
            // }
            if (sizeof($missingPrams) == 0) {
                if ($supplier_detail->reference_number == NULL) {
                    $check_ref_supplier = Supplier::where('id', '!=', $request->id)->orderby('id', 'DESC')->first();
                    $prefix = "SC";
                    $str = @$check_ref_supplier->reference_number;
                    // dd($str);
                    if ($str  == NULL) {
                        $str = "1";
                    }
                    $matches = array();
                    preg_match('/([a-zA-Z]+)(\d+)/', $str, $matches);
                    $system_gen_no =  $prefix . str_pad(@$matches[2] + 1, STR_PAD_LEFT);
                    $supplier_detail->reference_number = $system_gen_no;
                }
                $supplier_detail->status = 1;
                $supplier_detail->save();
                return response()->json(['success' => true]);
            } else {
                $message = implode(', ', $missingPrams);
                return response()->json(['success' => false, 'message' => $message]);
            }
        }
    }

    public function doTempSupplierCompleted(Request $request)
    {
        if ($request->id) {
            $supplier_detail = TempSupplier::find($request->id);
            $missingPrams = array();

            if ($supplier_detail->company == null) {
                $missingPrams[] = 'Company';
            }

            if ($supplier_detail->address_line_1 == null) {
                $missingPrams[] = 'Address';
            }

            if ($supplier_detail->currency_id == null || !(int)$supplier_detail->currency_id) {
                $missingPrams[] = 'Currency';
            }
            if ($supplier_detail->country == null || !(int)$supplier_detail->country) {
                $missingPrams[] = 'Country';
            }

            if ($supplier_detail->city == null) {
                $missingPrams[] = 'City';
            }
            if ($supplier_detail->postalcode == null) {
                $missingPrams[] = 'Postal Code';
            }
            if ($supplier_detail->c_name == null) {
                $missingPrams[] = 'Contact Name';
            }
            if ($supplier_detail->c_email == null) {
                $missingPrams[] = 'Contact Email';
            }
            if (sizeof($missingPrams) == 0) {
                $supplier_detail->status = 1;
                $supplier_detail->save();
                return response()->json(['success' => true]);
            } else {
                $message = implode(', ', $missingPrams);
                return response()->json(['success' => false, 'message' => $message]);
            }
        }
    }

    public function updateSupplierProfile(Request $request, $id)
    {
        $request->validate([
            'logo' => 'required'
        ]);

        $supplier = Supplier::where('id', $id)->first();
        // dd($customer);
        if ($request->hasFile('logo') && $request->logo->isValid()) {
            $fileNameWithExt = $request->file('logo')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('logo')->getClientOriginalExtension();
            $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
            $path = $request->file('logo')->move('public/uploads/sales/customer/logos/', $fileNameToStore);
            $supplier->logo = $fileNameToStore;
        }

        $supplier->save();
        return redirect()->back();
    }

    public function deleteSupplierNoteDetailPage(Request $request)
    {
        $supplier_notes = SupplierNote::where('id', $request->id)->first();
        $supplier_notes->delete();
        return response()->json(['success' => true]);
    }

    public function bulkUploadSuppliers(Request $request)
    {
        $validator = Validator::make(
            [
                'file'      => $request->excel,
                'extension' => strtolower($request->excel->getClientOriginalExtension()),
            ],
            [
                'file'          => 'required',
                'extension'      => 'required|in:xlsx',
            ]
          );

        Excel::import(new SupplierBulkImport(), $request->file('excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'Add Bulk Suppliers', $request->file('excel'));
        return redirect()->back();
    }

    public function bulkUploadSuppliersForm()
    {
        $temp_suppliers = TempSupplier::where('status', 1)->get();
        foreach ($temp_suppliers as $temp_supplier) {
            $supplier                 = new Supplier;
            $check_ref_supplier = Supplier::orderby('id', 'DESC')->first();
            $prefix = "SC";
            $str = @$check_ref_supplier->reference_number;
            if ($str  == NULL) {
                $str = "1";
            }
            $matches = array();
            preg_match('/([a-zA-Z]+)(\d+)/', $str, $matches);
            $system_gen_no =  $prefix . str_pad(@$matches[2] + 1, STR_PAD_LEFT);
            $supplier->reference_number = $system_gen_no;

            $supplier->reference_name = $temp_supplier->reference_name;
            $supplier->company        = $temp_supplier->company;
            $supplier->email          = $temp_supplier->email;
            $supplier->phone          = $temp_supplier->phone;
            $supplier->address_line_1 = $temp_supplier->address_line_1;
            $supplier->country        = $temp_supplier->country;
            $supplier->state          = $temp_supplier->state;
            $supplier->city           = $temp_supplier->city;
            $supplier->postalcode     = $temp_supplier->postalcode;
            $supplier->currency_id    = $temp_supplier->currency_id;
            $supplier->credit_term    = $temp_supplier->credit_term;
            $supplier->tax_id         = $temp_supplier->tax_id;
            $supplier->status         = $temp_supplier->status;
            $supplier->user_id        = Auth::user()->id;
            $supplier->save();

            $supplier_contact                  = new SupplierContacts;
            $supplier_contact->supplier_id     = $supplier->id;
            $supplier_contact->name            = $temp_supplier->c_name;
            $supplier_contact->sur_name        = $temp_supplier->c_sur_name;
            $supplier_contact->email           = $temp_supplier->c_email;
            $supplier_contact->telehone_number = $temp_supplier->c_telehone_number;
            $supplier_contact->postion         = $temp_supplier->c_position;
            $supplier_contact->save();

            $temp_supplier->delete();
        }
        $temp_supplier = TempSupplier::all();
        if ($temp_supplier->count() == 0) {
            return $this->render('users.suppliers.add-bulk-suppliers');
        }

        $temp_suppliers_count = TempSupplier::all()->count();
        return view('users.suppliers.complete-bulk-upload-suppliers', compact('temp_suppliers_count'));
    }

    public function getTempSuppliersData()
    {
        $query = TempSupplier::query();
        $query->orderBy('id')->get();

        return Datatables::of($query)


            ->addColumn('supplier_company', function ($item) {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="company"  data-fieldvalue="' . @$item->company . '">';
                $html_string .= $item->company != NULL ? $item->company : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="company" class="fieldFocus d-none" value="' . $item->company . '">';
                return $html_string;
            })

            ->addColumn('reference_name', function ($item) {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="reference_name"  data-fieldvalue="' . @$item->reference_name . '">';
                $html_string .= $item->reference_name != NULL ? $item->reference_name : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="reference_name" class="fieldFocus d-none" value="' . $item->reference_name . '">';
                return $html_string;
            })

            ->addColumn('email', function ($item) {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="email"  data-fieldvalue="' . @$item->email . '">';
                $html_string .= $item->email != NULL ? $item->email : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="email" class="fieldFocus d-none" value="' . $item->email . '">';
                return $html_string;
            })

            ->addColumn('phone', function ($item) {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="phone"  data-fieldvalue="' . @$item->phone . '">';
                $html_string .= $item->phone != NULL ? $item->phone : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="phone" class="fieldFocus d-none" value="' . $item->phone . '">';
                return $html_string;
            })

            ->addColumn('address', function ($item) {
                $text_color = $item->address_line_1 == null ? 'color: red;' : '';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="address_line_1" style="' . $text_color . '" data-fieldvalue="' . @$item->address_line_1 . '">';
                $html_string .= $item->address_line_1 != NULL ? $item->address_line_1 : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="address_line_1" class="fieldFocus d-none" value="' . $item->address_line_1 . '">';
                return $html_string;
            })

            ->addColumn('country', function ($item) {
                $countries = Country::get();
                $countries_ids = $countries->pluck('id')->toArray();
                $html_string = '';
                if ($item->country == null || !in_array($item->country, $countries_ids)) {
                    $html_string .= '<td>
                        <select required name="country"
                                class="form-control turngreen btn-outline-danger" required>
                            <option value="" disabled selected>' . $item->country . '</option>';
                    foreach ($countries as $cat) {
                        $html_string .= '<option value="' . $cat->id . '">' . $cat->name . '</option>';
                    }

                    $html_string .= '</select>
                    </td>';
                } else {
                    $html_string .= '<td>' . Country::find($item->country)->name . '</td>';
                }


                return $html_string;
            })

            ->addColumn('state', function ($item) {

                $states = State::where('country_id', $item->country)->get();
                $states_ids = $states->pluck('id')->toArray();
                $html_string = '';
                if ($item->state == null || !in_array($item->state, $states_ids)) {
                    $html_string .= '<td>
                        <select required name="state"
                                class="form-control turngreen" required>
                            <option value="" disabled selected>' . $item->state . '</option>';
                    foreach ($states as $st) {
                        $html_string .= '<option value="' . $st->id . '">' . $st->name . '</option>';
                    }

                    $html_string .= '</select>
                    </td>';
                } else {
                    $html_string .= '<td>' . State::find($item->state)->name . '</td>';
                }


                return $html_string;
            })

            ->addColumn('city', function ($item) {
                $text_color = $item->city == null ? 'color: red;' : '';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="city" style="' . $text_color . '" data-fieldvalue="' . @$item->city . '">';
                $html_string .= $item->city != NULL ? $item->city : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="city" class="fieldFocus d-none" value="' . $item->city . '">';
                return $html_string;
            })

            ->addColumn('tax_id', function ($item) {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="tax_id"  data-fieldvalue="' . @$item->tax_id . '">';
                $html_string .= $item->tax_id != NULL ? $item->tax_id : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="tax_id" class="fieldFocus d-none" value="' . $item->tax_id . '">';
                return $html_string;
            })

            ->addColumn('postal_code', function ($item) {
                $text_color = $item->postalcode == null ? 'color: red;' : '';
                $html_string = '
              <span class="m-l-15 inputDoubleClick" id="postalcode" style="' . $text_color . '" data-fieldvalue="' . @$item->postalcode . '">';
                $html_string .= $item->postalcode != NULL ? $item->postalcode : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="postalcode" class="fieldFocus d-none" value="' . $item->postalcode . '">';
                return $html_string;
            })

            ->addColumn('currency', function ($item) {
                $currencies = Currency::get();
                $currencies_ids = $currencies->pluck('id')->toArray();
                $html_string = '';
                if ($item->currency_id == null || !in_array($item->currency_id, $currencies_ids)) {
                    $html_string .= '<td>
                        <select required name="currency_id"
                                class="form-control turngreen btn-outline-danger" required>
                            <option value="" disabled selected>' . $item->currency_id . '</option>';
                    foreach ($currencies as $st) {
                        $html_string .= '<option value="' . $st->id . '">' . $st->currency_code . '</option>';
                    }

                    $html_string .= '</select>
                    </td>';
                } else {
                    $html_string .= '<td>' . Currency::find($item->currency_id)->currency_code . '</td>';
                }


                return $html_string;
            })

            ->addColumn('credit_term', function ($item) {
                $payment_terms = PaymentTerm::get();
                $payment_terms_ids = $payment_terms->pluck('id')->toArray();
                $html_string = '';
                if ($item->credit_term == null || !in_array($item->credit_term, $payment_terms_ids)) {
                    $html_string .= '<td>
                        <select required name="credit_term"
                                class="form-control turngreen" required>
                            <option value="" disabled selected>' . $item->credit_term . '</option>';
                    foreach ($payment_terms as $term) {
                        $html_string .= '<option value="' . $term->id . '">' . $term->title . '</option>';
                    }

                    $html_string .= '</select>
                    </td>';
                } else {
                    $html_string .= '<td>' . PaymentTerm::find($item->credit_term)->title . '</td>';
                }


                return $html_string;
            })

            ->addColumn('c_name', function ($item) {
                $text_color = $item->c_name == null ? 'color: red;' : '';
                $html_string = '
              <span class="m-l-15 inputDoubleClick" id="c_name" style="' . $text_color . '" data-fieldvalue="' . @$item->c_name . '">';
                $html_string .= $item->c_name != NULL ? $item->c_name : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="c_name" class="fieldFocus d-none" value="' . $item->c_name . '">';
                return $html_string;
            })

            ->addColumn('c_sur_name', function ($item) {
                $html_string = '
              <span class="m-l-15 inputDoubleClick" id="c_sur_name" data-fieldvalue="' . @$item->c_sur_name . '">';
                $html_string .= $item->c_sur_name != NULL ? $item->c_sur_name : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="c_sur_name" class="fieldFocus d-none" value="' . $item->c_sur_name . '">';
                return $html_string;
            })

            ->addColumn('c_email', function ($item) {
                $text_color = $item->c_email == null ? 'color: red;' : '';
                $html_string = '
              <span class="m-l-15 inputDoubleClick" id="c_email" style="' . $text_color . '" data-fieldvalue="' . @$item->c_email . '">';
                $html_string .= $item->c_email != NULL ? $item->c_email : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="c_email" class="fieldFocus d-none" value="' . $item->c_email . '">';
                return $html_string;
            })

            ->addColumn('c_telehone_number', function ($item) {
                $html_string = '
              <span class="m-l-15 inputDoubleClick" id="c_telehone_number" data-fieldvalue="' . @$item->c_telehone_number . '">';
                $html_string .= $item->c_telehone_number != NULL ? $item->c_telehone_number : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="c_telehone_number" class="fieldFocus d-none" value="' . $item->c_telehone_number . '">';
                return $html_string;
            })

            ->addColumn('c_position', function ($item) {
                $html_string = '
              <span class="m-l-15 inputDoubleClick" id="c_position" data-fieldvalue="' . @$item->c_position . '">';
                $html_string .= $item->c_position != NULL ? $item->c_position : "--";
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="c_position" class="fieldFocus d-none" value="' . $item->c_position . '">';
                return $html_string;
            })

            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Active</span>';
                } elseif ($item->status == 2) {
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Suspended</span>';
                } else {
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">InActive</span>';
                }
                return $status;
            })

            ->setRowId(function ($item) {
                return @$item->id;
            })
            ->rawColumns(['supplier_company', 'reference_name', 'email', 'phone', 'address', 'country', 'state', 'city', 'tax_id', 'postal_code', 'currency', 'credit_term', 'c_name', 'c_sur_name', 'c_email', 'c_telehone_number', 'c_position', 'status'])
            ->make(true);
    }

    public function discardTempSuppliers()
    {
        $suppliers = TempSupplier::all();
        foreach ($suppliers as $supplier) {
            $supplier->delete();
        }
        return redirect('bulk-upload-suppliers-form');
    }

    public function saveTempSupplierData(Request $request)
    {
        // dd($request->all());
        $completed = 0;
        $reload = 0;
        $temp_supplier = TempSupplier::find($request->supplier_id);
        //dd($temp_supplier);
        foreach ($request->except('supplier_id') as $key => $value) {
            if ($key == '') {
            } else {
                //dd($key,$value);
                $temp_supplier->$key = $value;
                $temp_supplier->save();
            }
        }

        $temp_supplier->save();

        if ($temp_supplier->status == 0) {
            $request->id = $request->supplier_id;
            $mark_as_complete = $this->doTempSupplierCompleted($request);
            $json_response = json_decode($mark_as_complete->getContent());
            if ($json_response->success == true) {
                $supplier = TempSupplier::find($request->id);
                $supplier->status = 1;
                $supplier->save();
                $completed = 1;
            }
        }
        return response()->json(['completed' => $completed, 'reload' => $reload]);
    }

    protected function generateRandomString($length)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function addSupplierNote(Request $request)
    {
        $request->validate([
            'note_description' => 'required|max:255',
        ]);

        $supplierNote = new SupplierNote;
        $supplierNote->supplier_id      = $request->supplier_id_note;
        $supplierNote->note_description = $request->note_description;
        $supplierNote->user_id          = Auth::user()->id;
        $supplierNote->save();

        return json_encode(['success' => true]);
    }

    public function editSupplierNote(Request $request)
    {
        $request->validate([
            'note_description' => 'required|max:255',
            'supplier_note_id' => 'required|numeric',
        ]);

        $supplierNote = SupplierNote::find($request->supplier_note_id);
        $supplierNote->note_description = $request->note_description;
        $supplierNote->user_id          = Auth::user()->id;
        $supplierNote->save();

        return json_encode(['success' => true]);
    }

    public function getSupplierContact(Request $request)
    {
        $query = SupplierContacts::where('supplier_id', $request->id)->get();

        return Datatables::of($query)

            ->addColumn('name', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="name"  data-fieldvalue="' . @$item->name . '">' . (@$item->name != NULL ? @$item->name : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="name" class="fieldFocusContact d-none" value="' . @$item->name . '">';
                return $html_string;
            })

            ->addColumn('sur_name', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="sur_name"  data-fieldvalue="' . @$item->sur_name . '">' . (@$item->sur_name != NULL ? @$item->sur_name : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="sur_name" class="fieldFocusContact d-none" value="' . @$item->sur_name . '">';
                return $html_string;
            })

            ->addColumn('email', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="email"  data-fieldvalue="' . @$item->email . '">' . (@$item->email != NULL ? @$item->email : "--") . '</span>
                <input type="email" autocomplete="nope" style="width:100%;" name="email" class="fieldFocusContact d-none" value="' . @$item->email . '">';
                return $html_string;
            })

            ->addColumn('telehone_number', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="telehone_number"  data-fieldvalue="' . @$item->telehone_number . '">' . (@$item->telehone_number != NULL ? @$item->telehone_number : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="telehone_number" class="fieldFocusContact d-none" value="' . @$item->telehone_number . '">';
                return $html_string;
            })

            ->addColumn('postion', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="postion"  data-fieldvalue="' . @$item->postion . '">' . (@$item->postion != NULL ? @$item->postion : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="postion" class="fieldFocusContact d-none" value="' . @$item->postion . '">';
                return $html_string;
            })
            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteSupplierContact" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';

                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['name', 'sur_name', 'email', 'telehone_number', 'postion', 'action'])
            ->make(true);
    }

    public function getSupplierAccount(Request $request)
    {
        $query = SupplierAccount::where('supplier_id', $request->id);

        return Datatables::of($query)

            ->addColumn('bank_name', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickAccounts" id="bank_name"  data-fieldvalue="' . @$item->bank_name . '">' . (@$item->bank_name != NULL ? @$item->bank_name : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="bank_name" class="fieldFocusAccount d-none" value="' . @$item->bank_name . '">';
                return $html_string;
            })

            ->addColumn('bank_address', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickAccounts" id="bank_address"  data-fieldvalue="' . @$item->bank_address . '">' . (@$item->bank_address != NULL ? @$item->bank_address : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="bank_address" class="fieldFocusAccount d-none" value="' . @$item->bank_address . '">';
                return $html_string;
            })

            ->addColumn('account_name', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickAccounts" id="account_name"  data-fieldvalue="' . @$item->account_name . '">' . (@$item->account_name != NULL ? @$item->account_name : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="account_name" class="fieldFocusAccount d-none" value="' . @$item->account_name . '">';
                return $html_string;
            })

            ->addColumn('account_no', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickAccounts" id="account_no"  data-fieldvalue="' . @$item->account_no . '">' . (@$item->account_no != NULL ? @$item->account_no : "--") . '</span>
                <input type="number" autocomplete="nope" style="width:100%;" name="account_no" class="fieldFocusAccount d-none" value="' . @$item->account_no . '">';
                return $html_string;
            })

            ->addColumn('swift_code', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickAccounts" id="swift_code"  data-fieldvalue="' . @$item->swift_code . '">' . (@$item->swift_code != NULL ? @$item->swift_code : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="swift_code" class="fieldFocusAccount d-none" value="' . @$item->swift_code . '">';
                return $html_string;
            })
            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteSupplierAccount" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';

                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['bank_name', 'bank_address', 'account_name', 'account_no', 'swift_code', 'action'])
            ->make(true);
    }

    public function saveSuppAccountData(Request $request)
    {
        $sup_account = SupplierAccount::where('id', $request->id)->where('supplier_id', $request->supplier_id)->first();

        foreach ($request->except('supplier_id', 'id') as $key => $value) {
            if ($value != '') {
                $sup_account->$key = $value;
            }
        }
        $sup_account->save();
        return response()->json(['success' => true]);
    }

    public function deleteSupplierContact(Request $request)
    {
        $deleteContact = SupplierContacts::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function deleteSupplierAccount(Request $request)
    {
        $deleteAccount = SupplierAccount::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function saveSuppContactsData(Request $request)
    {
        return SupplierHelper::saveSuppContactsData($request);
    }

    public function addSupplierContact(Request $request)
    {

        $supplier_contact  = new SupplierContacts;
        $supplier_contact->name = $request['name'];
        $supplier_contact->supplier_id = $request['supplier_id'];
        $supplier_contact->sur_name = $request['sur_name'];
        $supplier_contact->email = $request['email'];
        $supplier_contact->telehone_number = $request['telehone_number'];
        $supplier_contact->postion = $request['postion'];
        $supplier_contact->save();

        return response()->json(['success' => true]);
    }

    public function addSupplierAccount(Request $request)
    {

        $supplier_account  = new SupplierAccount;
        $supplier_account->supplier_id = $request['id'];

        $supplier_account->save();

        return response()->json(['success' => true]);
    }

    public function getSupplierNote(Request $request)
    {
        $supplier_notes = SupplierNote::where('supplier_id', $request->supplier_id)->get();

        $html_string = '<div class="table-responsive">
                        <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>S.no</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                        </thead><tbody>';
        if ($supplier_notes->count() > 0) {
            $i = 0;
            foreach ($supplier_notes as $note) {
                $i++;
                $html_string .= '<tr id="gem-note-' . $note->id . '">
                            <td>' . $i . '</td>
                            <td>' . $note->note_description . '</td>
                            <td><a href="javascript:void(0);" data-id="' . $note->id . '" class="delete-note actionicon deleteIcon" title="Delete Note"><i class="fa fa-trash"></i></a></td>
                         </tr>';
            }
        } else {
            $html_string .= '<tr>
                            <td colspan="4">No Note Found</td>
                         </tr>';
        }
        $html_string .= '</tbody></table></div>';
        return $html_string;
    }

    public function deleteSupplierNote(Request $request)
    {
        $supplier_note = SupplierNote::where('id', $request->note_id)->first();
        $supplier_note->delete();
        return response()->json(['error' => false]);
    }

    public function discradSupplierFromDP(Request $request)
    {
        $supplier = Supplier::where('id', '=', $request->id)->with('supplier_po', 'product')->first();

        if ($supplier->supplier_po->count() > 0) {
            return response()->json(['success' => false, 'errorMsg' => 'Supplier has POs linked, Can\'t Be deleted']);
        } elseif ($supplier->product != null) {
            return response()->json(['success' => false, 'errorMsg' => 'Supplier has products linked, Can\'t Be deleted']);
        } else {
            Supplier::where('id', $request->id)->update(['deleted_at' => NOW(), 'status' => 3]);
            return response()->json(['success' => true]);
        }

        // if($supplier != null) {
        //   return response()->json(['success' => true]);

        // } else {
        //   return response()->json(['success' => false, 'errorMsg' => 'Supplier Can\'t Be deleted']);
        // }

        // return $supplier ? response()->json(['success' => true]): response()->json(['success' => false, 'errorMsg' => 'Supplier Can\'t Be deleted']);
        // dd($supplier);
        // if($supplier->status == 1 || $supplier->status == 2) // checking if this supplier have a po
        // {
        //   $checkPOs = PurchaseOrder::where('supplier_id', $supplier->id)->get();
        //   if($checkPOs->count() > 0)
        //   {
        //     $errorMsg = '<ol>';
        //     foreach ($checkPOs as $po)
        //     {
        //       $errorMsg .= "<li>This Supplier exist in this ".'<b>PO-'.$po->ref_id.'</b>'." Purchase Order.</li>";
        //     }
        //     $errorMsg .= '</ol>';
        //     return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        //   }
        //   $products = Product::where('supplier_id', $supplier->id)->get();
        //   if($products->count() > 0){
        //     $errorMsg = '<ol>';
        //     $errorMsg .= "<li>This Supplier is bind to the following products ".'<b>';
        //     foreach ($products as $po)
        //     {
        //       $errorMsg .= $po->refrence_code.' ,';
        //     }
        //     $errorMsg .= '</b>'.".</li>";
        //     $errorMsg .= '</ol>';
        //     return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        //   }
        //   else
        //   {
        //     $supplier->getnotes()->delete();
        //     $supplier->supplierContacts()->delete();
        //     $supplier->supplierDocuments()->delete();
        //     $supplier->delete();
        //     return response()->json(['success' => true]);
        //   }

        // }
        // else
        // {
        //   $supplier->getnotes()->delete();
        //   $supplier->supplierContacts()->delete();
        //   $supplier->supplierDocuments()->delete();
        //   $supplier->delete();

        // }
        // return response()->json(['success' => true]);
    }

    public function suspendSupplier(Request $request)
    {
        // dd("testing");
        $suspend = Supplier::where('id', $request->id)->update(['status' => 2]);

        $suspendsupplier = Supplier::find($request->id);
        $first_name = $suspendsupplier->first_name;
        $name = $first_name . " " . $suspendsupplier->last_name;

        // get suspension email //
        // $template = EmailTemplate::where('type', 'account-suspension')->first();

        // send email //
        // Mail::to($suspendsupplier->email, $name)->send(new SupplierSuspensionEmail($suspendsupplier, $template));

        return response()->json(['error' => false, 'successmsg' => 'Supplier has been blocked']);
    }

    public function activateSupplier(Request $request)
    {
        $activate = Supplier::where('id', $request->id)->update(['status' => 1]);

        $activatesupplier = Supplier::find($request->id);
        $first_name = $activatesupplier->first_name;
        $name = $first_name . " " . $activatesupplier->last_name;


        // get activation email //
        // $template = EmailTemplate::where('type', 'account-activation')->first();

        // send email //
        // Mail::to($activatesupplier->email, $name)->send(new SupplierActivationEmail($activatesupplier, $template));

        return response()->json(['error' => false, 'successmsg' => 'The supplier account has been activated']);
    }

    public function getSupplierGeneralDocuments(Request $request)
    {

        $query = SupplierGeneralDocument::where('supplier_id', $request->id);
        return Datatables::of($query)

            ->addColumn('file_name', function ($item) {
                return $item->file_name != null ? $item->file_name : '--';
            })
            ->addColumn('description', function ($item) {
                return $item->description != null ? $item->description : "--";
            })
            ->addColumn('action', function ($item) {
                $html_string = '
         <a href="javascript:void(0);" class="actionicon deleteIcon deleteGeneralDocument" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
         ';
                $html_string .= '<a href="' . asset('public/uploads/documents/' . $item->file_name) . '" class="actionicon download" data-id="' . @$item->file_name . '" title="Download"><i class="fa fa-download"></i></a>';
                return $html_string;
            })
            ->setRowId(function ($item) {
                return $item->id;
            })
            ->rawColumns(['file_name', 'description', 'action'])
            ->make(true);
    }

    public function addSupplierGeneralDocuments(Request $request)
    {
        $validator = $request->validate([
            'supplier_docs' => 'required|max:2048',

        ]);

        $html_string = '';
        $invalid = 0;
        if (isset($request->supplier_docs)) {
            for ($i = 0; $i < sizeof($request->supplier_docs); $i++) {

                $doc        = new SupplierGeneralDocument;
                $doc->supplier_id = $request->supplier_docs_id;
                //file
                $extension = $request->supplier_docs[$i]->extension();
                // dd($extension);
                if ($extension != 'xlsx' && $extension != 'xls' && $extension != 'doc' && $extension != 'docx' && $extension != 'ppt' && $extension != 'pptx' && $extension != 'txt' && $extension != 'pdf' && $extension != 'jpeg' && $extension != 'jpeg' && $extension != 'png') {
                    $html_string .= '.' . $extension . ' ';
                    $invalid++;
                } else {
                    $filename = date('m-d-Y') . mt_rand(999, 999999) . '__' . time() . '.' . $extension;
                    $request->supplier_docs[$i]->move(public_path('uploads/documents'), $filename);
                    $doc->file_name = $filename;
                    $doc->description = $request->description;
                    $doc->save();
                }
            }
        }
        return response()->json(['success' => true, 'html' => '' . $invalid . ' files have invalid extension i.e(' . $html_string . ')', 'invalid' => $invalid]);
    }

    public function deleteSupplierdocs(Request $request)
    {
        $supplier_docs = SupplierGeneralDocument::where('id', $request->id)->first();
        $supplier_docs->delete();
        return response()->json(['success' => true]);
    }

    public function saveIncompSupplier()
    {
        return redirect('/supplier')->with('msg', 'incomplete');
    }

    public function getSupplierTransactionDetail($id)
    {
        // dd('hi');
        $supplier = Supplier::find($id);

        // if(Auth::user()->role_id == 2)
        // {
        //   $suppliers = Supplier::whereIn('id',PurchaseOrder::where('status',15)->pluck('supplier_id')->toArray())->where('status',1)->where('user_id', Auth::user()->id)->get();
        // }
        // else
        // {
        $suppliers = Supplier::whereIn('id', PurchaseOrder::where('status', 15)->pluck('supplier_id')->toArray())->where('status', 1)->get();
        // }

        return view('users.suppliers.supplier-transaction-detail', compact('supplier', 'suppliers'));
    }

    public function getPaymentRefInvoicesForPayable(Request $request)
    {
        // dd($request->all());

        $from_date = str_replace("/", "-", $request->from_date);
        $from_date =  date('Y-m-d', strtotime($from_date));

        $to_date = str_replace("/", "-", $request->to_date);
        $to_date =  date('Y-m-d', strtotime($to_date));

        $supplier_id = $request->selecting_customer;
        $query = PoPaymentRef::where('supplier_id', $supplier_id)->get();


        return Datatables::of($query)
            ->addColumn('ref_no', function ($item) use ($supplier_id) {
                // $html_string = '<a target="_blank" href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" >'.$item->order->ref_id.'</a>';

                $orders_ref_no = $item->getTransactions->where('supplier_id', $supplier_id)->pluck('po_order_ref_no')->unique()->toArray();
                $orders_ref_no = implode(", ", $orders_ref_no);
                // dd($orders_ref_no);
                return $orders_ref_no;
            })



            ->addColumn('invoice_total', function ($item) use ($supplier_id) {
                $ids = $item->getTransactions->where('supplier_id', $supplier_id)->pluck('po_id')->unique()->toArray();
                $total_amount = PurchaseOrder::whereIn('id', $ids)->sum('total_in_thb');
                return number_format($total_amount);
            })

            ->addColumn('total_paid', function ($item) use ($supplier_id) {
                $total_paid = $item->getTransactions->where('supplier_id', $supplier_id)->sum('total_received');
                return number_format($total_paid);
            })

            ->addColumn('received_date', function ($item) use ($supplier_id) {
                $order_transaction = $item->getTransactions->where('supplier_id', $supplier_id)->last();
                return $order_transaction->received_date !== null ? Carbon::parse($order_transaction->received_date)->format('d/m/Y') : '--';
            })

            ->addColumn('payment_type', function ($item) use ($supplier_id) {
                $ids = $item->getTransactions->where('supplier_id', $supplier_id)->pluck('payment_method_id')->unique()->toArray();
                $payment_types = PaymentType::whereIn('id', $ids)->pluck('title')->unique()->toArray();
                $payment_types = implode(", ", $payment_types);
                return $payment_types;
            })

            ->addColumn('payment_reference_no', function ($item) use ($supplier_id) {
                return $item->payment_reference_no;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->addColumn('reference_name', function ($item) {
                // $total_paid = $item->customer->reference_name;
                return $item->PoSupplier !== null ? $item->PoSupplier->reference_name : '--';
            })

            ->addColumn('reference_number', function ($item) {
                // $total_paid = $item->customer->reference_name;
                return $item->PoSupplier !== null ? $item->PoSupplier->reference_number : '--';
            })

            ->rawColumns(['ref_no', 'invoice_total', 'total_paid', 'received_date', 'payment_type'])
            ->make(true);
    }

    public function getInvoicesForPayable(Request $request)
    {
        // dd($request->all());
        if ($request->from_date != null || $request->to_date != null) {
            $query = PurchaseOrderTransaction::with('order.PoSupplier', 'get_payment_type', 'get_payment_ref', 'order.p_o_statuses.parent');
        } else {
            $query = PurchaseOrderTransaction::with('order.PoSupplier', 'get_payment_type', 'get_payment_ref', 'order.p_o_statuses.parent')->orderBy('id', 'Desc')->limit(10);
        }

        if ($request->from_date != null) {
            // dd($request->from_date);
            $query = $query->where('received_date', '>=', $request->from_date . '00:00:00');
        }
        if ($request->to_date != null) {
            $query = $query->where('received_date', '<=', $request->to_date . '23:59:59');
        }

        if ($request->selecting_supplier != null) {
            $query = $query->whereIn('po_id', PurchaseOrder::where('supplier_id', $request->selecting_supplier)->pluck('id')->toArray());
        }

        if ($request->order_no != null) {
            $order = PoPaymentRef::where('payment_reference_no', $request->order_no)->first();
            $query = $query->where('payment_reference_no', $order->id);
        }

        $dt =  Datatables::of($query);
        $add_columns = ['action', 'payment_reference_no', 'payment_type', 'received_date', 'total_paid', 'invoice_total', 'difference', 'supplier_company', 'ref_no'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrderTransaction::returnAddColumnAccountTransaction($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['ref_no', 'supplier_company', 'invoice_total', 'total_paid', 'received_date', 'payment_type', 'action']);

        return $dt->make(true);
    }

    public function getInvoicesForPayableLastFive(Request $request)
    {
        // dd($request->all());
        $supplier_id = $request->selecting_supplier;
        if (Auth::user()->role_id == 2) {
            // dd(Auth::user()->supplier->pluck('id')->toArray());
            $query = poPaymentRef::whereIn('supplier_id', PurchaseOrder::where('created_by', Auth::user()->id)->where('status', 15)->pluck('supplier_id')->toArray())->orderBy('id', 'desc')->limit(5);
        } else {
            $query = poPaymentRef::orderBy('id', 'desc')->limit(5);
        }
        if ($supplier_id !== null) {
            $query = $query->where('supplier_id', $supplier_id);
        }
        if ($request->order_no !== null) {
            $query = $query->where('payment_reference_no', 'LIKE', '%' . $request->order_no . '%');
        }
        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('received_date', '>=', $date);
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('received_date', '<=', $date);
        }
        $query = $query->get();

        return Datatables::of($query)
            ->addColumn('ref_no', function ($item) {
                $i = 1;
                // $html_string = '<a target="_blank" href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" >'.$item->order->ref_id.'</a>';

                $orders_ref_no = $item->getTransactions->pluck('po_order_ref_no')->unique()->toArray();
                // $orders_ref_no = implode (", ", $orders_ref_no);

                $html_string = '
                        <a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal' . $item->id . '">
                          <i class="fa fa-tty"></i>
                        </a>
                    ';

                $html_string .= '
                    <div class="modal fade" id="poNumberModal' . $item->id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">PO ref #</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                          <table class="bordered" style="width:100%;">
                                <thead style="border:1px solid #eee;text-align:center;">
                                    <tr><th>S.No</th><th>PO ref#</th></tr>
                                </thead>
                                <tbody>';
                foreach ($orders_ref_no as $p_g_d) {
                    $html_string .= '<tr><td>' . $i . '</td><td>' . @$p_g_d . '</td></tr>';
                    $i++;
                }
                $html_string .= '
                                </tbody>
                          </table>

                          </div>
                        </div>
                      </div>
                    </div>
                    ';
                // dd($orders_ref_no);
                // return $orders_ref_no;
                return $html_string;
            })



            ->addColumn('invoice_total', function ($item) {
                $ids = $item->getTransactions->pluck('po_id')->unique()->toArray();
                $total_amount = PurchaseOrder::whereIn('id', $ids)->sum('total_in_thb');
                return number_format($total_amount);
            })

            ->addColumn('total_paid', function ($item) {
                $total_paid = $item->getTransactions->sum('total_received');
                return number_format($total_paid);
            })

            ->addColumn('received_date', function ($item) {
                $order_transaction = $item->getTransactions->last();
                return Carbon::parse($order_transaction->received_date)->format('d/m/Y');
            })

            ->addColumn('payment_type', function ($item) {
                $ids = $item->getTransactions->pluck('payment_method_id')->unique()->toArray();
                $payment_types = PaymentType::whereIn('id', $ids)->pluck('title')->unique()->toArray();
                $payment_types = implode(", ", $payment_types);
                return $payment_types;
            })

            ->addColumn('reference_name', function ($item) {
                // $total_paid = $item->customer->reference_name;
                return $item->PoSupplier !== null ? $item->PoSupplier->reference_name : '--';
            })

            ->addColumn('reference_number', function ($item) {
                // $total_paid = $item->customer->reference_name;
                return $item->PoSupplier !== null ? $item->PoSupplier->reference_number : '--';
            })

            ->addColumn('payment_reference_no', function ($item) {
                $html_string = '<a href="javascript:void(0)" class="download_transaction" data-id="' . @$item->id . '">' . @$item->payment_reference_no . '</a>';
                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['ref_no', 'invoice_total', 'total_paid', 'received_date', 'payment_type', 'payment_reference_no'])
            ->make(true);
    }

    public function bulkUploadPO(Request $request)
    {
        return Supplier::bulkUploadPO($request);
    }

    public function RecursiveCallForBulkPos(Request $request)
    {
        return Supplier::RecursiveCallForBulkPos($request);
    }

    public function CheckStatusFirstTimeForBulkPos(Request $request)
    {
        return Supplier::CheckStatusFirstTimeForBulkPos($request);
    }

    public function exportSupplierData(Request $request)
    {
        $job_status = ExportStatus::where('type', 'supplier_list_export_job')->where('user_id', Auth::user()->id)->first();
        if ($job_status == null) {
            $job_status = new ExportStatus();
            $job_status->type = 'supplier_list_export_job';
            $job_status->user_id = Auth::user()->id;
        }
        $job_status->status = 1;
        $job_status->exception = null;
        $job_status->error_msgs = null;
        $job_status->save();
        SupplierListJob::dispatch($request->all(), Auth::user());
        return response()->json(['status' => 1, 'success' => true]);
    }

    public function recursiveExportStatusSupplierList(Request $request)
    {
        $job_status = ExportStatus::where('type', 'supplier_list_export_job')->where('user_id', Auth::user()->id)->first();
        return response()->json(['status' => $job_status->status, 'exception' => $job_status->exception]);
    }
}
