<?php

namespace App;

use App\Models\Common\Company;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Common\Order\Order;
use App\Models\Common\Role;
use App\Models\Common\Warehouse;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Auth;
use Carbon\Carbon;

class User extends Authenticatable implements JWTSubject
{
    protected $with = ['roles'];
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'company_id', 'parent_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // public function setPasswordAttribute($password)
    // {
    //     if ( !empty($password) ) {
    //         $this->attributes['password'] = bcrypt($password);
    //     }
    // }

    public function roles()
    {
        return $this->belongsTo('App\Models\Common\Role', 'role_id', 'id');
    }

    public function user_details()
    {
        return $this->belongsTo('App\Models\Common\UserDetail', 'id', 'user_id');
    }

    public function email_templates()
    {
        return $this->hasMany('App\Models\Common\EmailTemplate', 'updated_by', 'id');
    }

    public function getUserDetail()
    {
        return $this->hasOne('App\Models\Common\UserDetail', 'user_id', 'id');
    }

    public function customer()
    {
        return $this->hasMany('App\Models\Sales\Customer', 'primary_sale_id', 'id');
    }

    public function secondary_customer()
    {
        return $this->hasMany('App\Models\Sales\Customer', 'secondary_sale_id', 'id');
    }

    public function user_customers_secondary()
    {
        return $this->hasMany('App\CustomerSecondaryUser', 'user_id', 'id');
    }

    public function supplier()
    {
        return $this->hasMany('App\Models\Common\Supplier', 'user_id', 'id');
    }

    public function get_warehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\Common\PurchaseOrders\PurchaseOrder', 'created_by', 'id');
    }

    public function orderProduct()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProduct', 'warehouse_id', 'id');
    }

    public function getCompany()
    {
        return $this->belongsTo('App\Models\Common\Company', 'company_id', 'id');
    }

    public function get_total_sale($seller_id)
    {
        $sales_orders = Order::where('user_id', $seller_id)->whereIn('primary_status', [2, 3])->get();
        $total_sales = 0;
        foreach ($sales_orders as  $sales_order) {
            $total_sales += $sales_order->order_products->sum('total_price_with_vat');
            $total_sales -= $sales_order->discount;
        }
        return round($total_sales, 2);
    }
    public function customersBySecondarySalePerson()
    {
        return $this->hasMany('App\CustomerSecondaryUser', 'user_id', 'id');
    }
    public function customersByPrimarySalePerson()
    {
        return $this->hasMany('App\Models\Sales\Customer', 'primary_sale_id', 'id');
    }

    public static function doSort($request, $query)
    {

        if ($request->sortbyvalue == 1) {
            $sort_order     = 'DESC';
        } else {
            $sort_order     = 'ASC';
        }

        if ($request->sortbyparam == 'name') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'user_name') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }


        if ($request->sortbyparam == 'email') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'phone_number') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'created_at') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'role_id') {
            $query->select('users.*')->leftJoin('roles', 'roles.id', '=', 'users.role_id')->orderBy('roles.name', $sort_order);
        }

        if ($request->sortbyparam == 'company_id') {
            $query->select('users.*')->leftJoin('companies', 'companies.id', '=', 'users.company_id')->orderBy('companies.company_name', $sort_order);
        }

        if ($request->sortbyparam == 'location') {
            $query->select('users.*')->leftJoin('warehouses', 'warehouses.id', '=', 'users.warehouse_id')->orderBy('warehouses.warehouse_title', $sort_order);
        }
    }
    public static function doSortby($request, $products, $total_items_sale, $total_items_gp)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if ($request['sortbyparam'] == 1) {
            $sort_variable  = 'sales';
        } elseif ($request['sortbyparam'] == 2) {
            $sort_variable  = 'products_total_cost';
        } elseif ($request['sortbyparam'] == 3) {
            $sort_variable  = 'marg';
        } elseif ($request['sortbyparam'] == 'sales_person') {
            $sort_variable  = 'name';
        } elseif ($request['sortbyparam'] == 'vat_out') {
            $sort_variable  = 'vat_amount_total';
        } elseif ($request['sortbyparam'] == 'percent_sales') {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END /' . $total_items_sale . ')*100'), $sort_order);
        } elseif ($request['sortbyparam'] == 'vat_in') {
            $sort_variable  = 'vat_in';
        }
        elseif($request['sortbyparam'] == 'gp')
        {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'percent_gp')
        {
            $products->orderBy(\DB::raw('((CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))) /'.$total_items_gp.'*100'), $sort_order);
        }

        if ($sort_variable != null) {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }

    public static function returnColumn($column, $item, $roles)
    {
        switch ($column) {
            case 'status':
                if ($item->status == 1) {
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Active</span>';
                } elseif ($item->status == 2) {
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Suspended</span>';
                } else {
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">InActive</span>';
                }
                return $status;
                break;

            case 'action':
                $html_string = '';
                $parent_event = 'auto';
                if (Auth::user()->id == $item->id) {
                    $parent_event = 'none';
                }
                $html_string .= '<a href="javascript:void(0);" class="login-as-user" data-userid="' . $item->id . '" data-role_name="' . $item->roles->name . '" ><i class="fa fa-user"></i></a>
                    <a href="' . route('user_detail', $item->id) . '" data-id="' . $item->id . '" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
                if ($item->status == 1) {
                    $html_string .= ' <a href="javascript:void(0);" class="actionicon deleteIcon suspend-user" data-id="' . $item->id . '" data-role_name="' . $item->roles->name . '" title="Suspend" style="pointer-events:' . $parent_event . '"><i class="fa fa-ban"></i></a>';
                } else {
                    $html_string .= ' <a href="javascript:void(0);" class="actionicon viewIcon activate-user activateIcon" data-id="' . $item->id . '" data-role_name="' . $item->roles->name . '" title="Activate"  style="pointer-events:' . $parent_event . '"><i class="fa fa-check"></i></a>';
                }
                $html_string .= '</div>';
                return $html_string;
                break;

            case 'company':
                return @$item->company_id != null ? @$item->getCompany->company_name : '--';
                break;

            case 'name':
                return @$item->name != null ? @$item->name : '--';
                break;

            case 'phone_number':
                $num = $item->phone_number !== null ? $item->phone_number : '--';
                $html_string = '<span class="m-l-15 inputDoubleClick" id="phone_number" data-fieldvalue="' . @$item->phone_number . '" >' . @$num . '</span><input type="phone_number" name="phone_number" class="fieldFocus d-none phone_number" value="' . @$num . '" data-id="' . @$item->id . '" style="width:100%">';
                return $html_string;
                break;

            case 'email':
                return $item->email != null ? $item->email : '--';
                break;

            case 'location':
                return $item->get_warehouse != null ? $item->get_warehouse->warehouse_title : '--';
                break;

            case 'created_at':
                return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
                break;

            case 'roles':
                if (Auth::user()->id == $item->id) {
                    $html_string = '<span>' . $item->roles->name . '</span>';
                } else {
                    $html_string = '<span class="m-l-15 font-weight-bold inputDoubleClick primary_span_' . $item->id . '" id="primary_salesperson_id" data-fieldvalue="' . @$item->role_id . '" data-id="salesperson ' . @$item->role_id . ' ' . @$item->id . '"> ';
                    $html_string .= ($item->role_id != null) ? $item->roles->name : "--";
                    $html_string .= '</span>';

                    $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson primary_select_' . $item->id . '">
                    <select data-row_id="' . @$item->id . '" class=" font-weight-bold form-control-lg form-control js-states state-tags select-common primary_salesperson_id primary_salespersons_select' . @$item->id . '" name="primary_salesperson_id" required="true">';
                    $html_string .= '<option value="">Choose Category</option>';

                    // $roles = Role::where('id','!=',8)->get();

                    if ($roles->count() > 0) {
                        $html_string .= '<optgroup label="Roles">';
                        foreach ($roles as $role) {
                            $html_string .= '<option ' . ($item->role_id == $role->id ? 'selected' : '') . ' value="' . $role->id . '">' . $role->name . '</option>';
                        }
                        $html_string .= '</optgroup>';
                    }
                    $html_string .= '</select></div>';
                }

                return $html_string;
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword)
    {
        switch ($column) {
            case 'name':
                return $item->where('name', 'LIKE', "%$keyword%");
                break;
            case 'company':
                return $item->whereIn('company_id', Company::select('id')->where('company_name', 'LIKE', "%$keyword%")->pluck('id'));
                break;
            case 'location':
                return $item->whereIn('warehouse_id', Warehouse::select('id')->where('warehouse_title', 'LIKE', "%$keyword%")->pluck('id'));
                break;
            case 'user_name':
                return $item->where('user_name', 'LIKE', "%$keyword%");
                break;
            case 'email':
                return $item->where('email', 'LIKE', "%$keyword%");
                break;
            case 'phone_number':
                return $item->where('phone_number', 'LIKE', "%$keyword%");
                break;
            case 'roles':
                return $item->whereIn('role_id', Role::select('id')->where('name', 'LIKE', "%$keyword%")->pluck('id'));
                break;
        }
    }
}
