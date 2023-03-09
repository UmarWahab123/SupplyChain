<?php

namespace App\Http\Controllers\Backend;

use Auth;
use Hash;
use Mail;
use App\User;
use App\General;
use App\Variable;
use Carbon\Carbon;
use App\Exports\BulkUser;
use App\Helpers\MyHelper;
use App\UserLoginHistory;
use App\Exports\BulkUsers;
use App\ImportFileHistory;
use App\Models\Common\Role;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\BillingConfiguration;
use App\Models\Common\Company;
use App\Models\Sales\Customer;
use App\Imports\UserBulkImport;
use App\Models\Common\Warehouse;
use Yajra\Datatables\Datatables;
use App\Models\Common\UserDetail;
use App\Models\Common\UserHistory;
use App\Mail\Backend\AddSalesEmail;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use App\Models\Common\EmailTemplate;
use App\Models\Sales\SalesWarehouse;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Mail\Backend\AddPurchasingEmail;
use Illuminate\Foundation\Auth\User as AuthUser;

class UsersController extends Controller
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
        $current_version = '4.2.1';
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version, 'dummy_data' => $dummy_data]);
    }
    public function index()
    {
        $warehouse = Warehouse::where('status', 1)->get();
        $roles = Role::orderBy('name', 'DESC')->where('id', '!=', 8);
        $roles = (Auth::user()->role_id == 10) ? $roles->get() : $roles->where('id', '!=', 10)->get();
        $companies = Company::all();
        $usersCount = User::where('role_id', '!=', 10)->where('role_id', '!=', 8)->whereNull('parent_id')->count();
        $total_users_allowed = BillingConfiguration::select('total_users_allowed', 'no_of_free_users', 'type')->where('status', 1)->first();
        $total_allowed = 0;
        if ($total_users_allowed != null) {
            if ($total_users_allowed->type == 'annual') {
                $total_allowed = $total_users_allowed->total_users_allowed;
            } else {
                $total_allowed = $total_users_allowed->no_of_free_users;
            }
        }

        $addUser = true;
        if ($total_allowed != null && $total_allowed != 0 && $usersCount >= $total_allowed) {
            $addUser = false;
        }

        return $this->render('backend.all_users.index', ['warehouse' => $warehouse, 'roles' => $roles, 'companies' => $companies, 'addUser' => $addUser]);
    }

    public function getData(Request $request)
    {
        $roles = Role::select('id', 'name')->where('id', '!=', 8)->get();
        $query = User::with('getCompany:id,company_name', 'roles:id,name', 'get_warehouse:id,warehouse_title')->where('role_id', '!=', 8);
        User::doSort($request, $query);

        if ($request->status !== null) {
            $query->where('users.status', $request->status);
        }

        if ($request->role_user) {
            $query->where('users.role_id', $request->role_user);
        }

        if ($request->company) {
            $query->where('users.company_id', $request->company);
        }

        $columns = ['status', 'action', 'company', 'name', 'phone_number', 'email', 'location', 'created_at', 'roles'];
        $dt = Datatables::of($query);
        foreach ($columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $roles) {
                return User::returnColumn($column, $item, $roles);
            });
        }

        $columns = ['company', 'location', 'phone_number', 'email', 'roles', 'name', 'user_name'];
        foreach ($columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return User::returnFilterColumn($column, $item, $keyword);
            });
        }

        $dt->rawColumns(['action', 'status', 'company', 'created_at', 'location', 'phone_number', 'email', 'roles']);
        return $dt->make(true);
    }

    public function add(Request $request)
    {
        $validator = $request->validate([
            'first_name'   => 'required',
            'user_name'    => 'required|unique:users',
            'email'        => 'nullable|unique:users',
            'user_role'    => 'required|not_in:0',
            'user_company' => 'required',
        ]);

        if ($request->user_company == 1) //validate company
        {
            $validator = $request->validate([
                'warehouse_id' => 'required'
            ]);
        }

        // generate random password //
        $password = uniqid();
        $role = Role::where('id', '=', $request->user_role)->first();
        $total_users_allowed = BillingConfiguration::select('total_users_allowed', 'no_of_free_users', 'type')->where('status', 1)->first();
        $total_allowed = 0;
        if ($total_users_allowed != null) {
            if ($total_users_allowed->type == 'annual') {
                $total_allowed = $total_users_allowed->total_users_allowed;
            } else {
                $total_allowed = $total_users_allowed->no_of_free_users;
            }
        }

        $usersCount = User::where('role_id', '!=', 10)->where('role_id', '!=', 8)->whereNull('parent_id')->count();
        if ($total_allowed != 0 && $usersCount >= $total_allowed) {
            return response()->json(['max_accounts' => true, 'message' => 'Maximum accounts reached']);
        }

        $user                    = new User;
        $user->name              = $request->first_name;
        $user->company_id        = $request->user_company;

        $user->warehouse_id = $request->warehouse_id != null ? $request->warehouse_id : 1;

        $user->email             = $request->email;
        $user->phone_number      = $request->phone_number;
        $user->user_name         = $request->user_name;
        $user->password          = bcrypt("12345678");
        $user->email_verified_at = now();
        $user->status            = true;
        if ($request->user_role == 6) {
            if ($request->is_default) {
                if (User::where('is_default', '=', 1)->exists()) {
                    $user->delete();
                    return response()->json(["success" => false, 'message' => "The Default already Exists"]);
                } else {
                    $user->is_default = $request->is_default;
                }
            }
        } else if ($request->user_role == 10) {
            $user->parent_id = 1;
        }

        $users_role = $role->user()->save($user);
        return response()->json(['success' => true]);
    }

    public function detail($id)
    {
        $id = $id;
        $userdetail = UserDetail::where('user_id', $id)->first();
        $user_role = User::where('id', $id)->with('roles')->first();
        $user_companies = Company::all();
        $defaultUser = User::where('is_default', '=', 1)->whereNull('parent_id');
        return $this->render('backend.all_users.user_detail', compact('user_role', 'defaultUser', 'id', 'user_companies', 'userdetail'));
    }

    public function saveUserDataUserDetailPage(Request $request)
    {
        $user_role = User::where('id', $request->user_id)->with('roles')->first();
        foreach ($request->except('user_id', 'new_value', 'old_value') as $key => $value) {
            if ($key == 'name') {
                $user_role->$key = $value;
            } elseif ($key == 'user_name') {
                if (strtolower($user_role->user_name) != strtolower($value)) {
                    $checkIfUnameExist = User::where('user_name', $value)->first();
                    if ($checkIfUnameExist) {
                        $error = "User name " . $value . " has already been taken";
                        return response()->json(['success' => false, 'error' => $error, 'old_name' => $user_role->user_name]);
                    } else {
                        $user_role->$key = $value;
                    }
                } else {
                    $user_role->$key = $value;
                }
            } elseif ($key == 'email') {
                if (strtolower($user_role->email) != strtolower($value)) {
                    $checkIfUnameExist = User::where('email', $value)->first();
                    if ($checkIfUnameExist) {
                        $error = "Email " . $value . " has already been taken";
                        return response()->json(['success' => false, 'error' => $error, 'old_email' => $user_role->email]);
                    } else {
                        $user_role->$key = $value;
                    }
                } else {
                    $user_role->$key = $value;
                }
            } elseif ($key == 'company_id') {
                $user_role->$key = $value;
            } elseif ($key == 'phone_number') {
                $user_role->$key = $value;
            } elseif ($key = 'primary_salesperson_id') {
                $role = Role::find($request->old_value);
                $confi = Configuration::first();
                if ($request->new_value != $request->old_value) {
                    if ($request->old_value == 3) {
                        $customers = Customer::where('primary_sale_id', $user_role->id)->get();

                        foreach ($customers as $cust) {
                            $cust->primary_sale_id = null;
                            $cust->status = 0;
                            $cust->save();
                        }
                        $customers = Customer::where('secondary_sale_id', $user_role->id)->get();
                        foreach ($customers as $cust) {
                            $cust->secondary_sale_id = null;
                            $cust->save();
                        }
                    }

                    $user_role->role_id = $request->new_value;
                    $new_history = new UserHistory;
                    $new_history->user_id = $user_role->name;
                    $new_history->updated_by = Auth::user()->name;
                    $new_history->column_name = 'Role';
                    $new_history->old_value = $request->old_value;
                    $new_history->new_value = $request->new_value;
                    $new_history->save();
                }
            }
        }

        $user_role->save();
        if ($user_role->role_id == 10) {
            $user_role->parent_id = 1;
        } else {
            $user_role->parent_id = null;
        }
        $user_role->save();
        return response()->json(['success' => true]);
    }

    public function changeUserPassword(Request $request)
    {
        // dd($request->all());
        $validator = $request->validate([
            'new_password' => 'required',
            'confirm_new_password'  => 'required',

        ]);

        $user = User::where('id', $request->user_id)->first();
        if ($user) {
            if ($request['new_password'] == $request['confirm_new_password']) {
                $user->password = bcrypt($request['new_password']);
            }

            $user->save();
        }

        return response()->json(['success' => true]);
    }

    public function createTokenOfUserForAdminLogin()
    {
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $token_for_admin_login = uniqid();
            $user = User::where('id', $user_id)->first();

            if ($user->status != 1) {
                return response()->json(
                    [
                        "success" => false,
                        'user_id' => $user_id,
                    ]
                );
            }

            $user->token_for_admin_login = $token_for_admin_login;
            $user->save();
            return response()->json([
                'token_for_admin_login' => $token_for_admin_login,
                'user_id' => $user_id
            ]);
        }
    }



    public function getUserHistory()
    {
        $query = UserHistory::with('roles', 'roles_second')->orderBy('id', 'desc')->select('user_histories.*');
        return Datatables::of($query)

            ->addColumn('user', function ($item) {
                return @$item->user_id != null ? @$item->user_id : '--';
            })

            ->addColumn('updated_by', function ($item) {
                return $item->updated_by != null ? $item->updated_by : '--';
            })

            ->addColumn('column_name', function ($item) {
                return $item->column_name != null ? $item->column_name : '--';
            })

            ->addColumn('old_value', function ($item) {
                return $item->roles_second != null ?  $item->roles_second->name : '--';
            })
            ->addColumn('new_value', function ($item) {
                return $item->roles != null ?  $item->roles->name : '--';
            })

            ->rawColumns(['user', 'updated_by', 'column_name', 'old_value', 'new_value'])
            ->make(true);
    }

    public function uploadUserBulk(Request $request)
    {
        $user_id = Auth::user()->id;
        $import = new UserBulkImport($user_id);
        \Excel::import($import, $request->file('excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'Add Bulk User', $request->file('excel'));
    }

    public function bulkUserUploadFileDownload()
    {
        return \Excel::download(new BulkUsers(), 'Bulk_Users.xlsx');
    }
}
