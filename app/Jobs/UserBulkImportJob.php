<?php

namespace App\Jobs;

use App\User;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Company;
use App\Models\Common\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Facades\Log;

class UserBulkImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $rows,$user_id;
    protected $tries = 1;
    public function __construct($rows,$user_id)
    {
        $this->rows        = $rows;
        $this->user_id     = $user_id;
    }
    

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $rows        = $this->rows;
            $user_id     = $this->user_id;
            $html_string = '';
            $error_msgss = '';
            $error       = 0;
            $errors = [];
            if ($rows->count()>0) 
            {
                $row1 = $rows->toArray();
                foreach($row1 as $row)
                {
                    $user_role = $row['user_role'];
                    $user_company = $row['company'];
                    $role = Role::where('name',$user_role)->first();
                    $company = Company::where('company_name',$user_company)->first();
                    if($role == NULL || $company == null)
                    {
                        array_push($errors, $row);
                        continue;
                    }
                    $user_exists = User::where('user_name',$row['user_name'])->orWhere('email',$row['email'])->first();
                    if($user_exists == NULL)
                    {
                        $user = new User();
                        $user->name = $row['name'];
                        $user->user_name = $row['user_name'];
                        $user->phone_number = $row['phone_no'];
                        $user->email = $row['email'];
                        $user->role_id = $role->id;
                        $user->company_id = $company->id;
                        $user->save();
                    }
                }
            }
            else
            {
                $success = 'fail';
                $error_msgss = 'File is Empty Please Upload Valid File !!!';
                $error = 2;
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            if($error == 1)
            {
                $error_msgss = "Inactive User, But Some Of Them Has Issues !!!";
                $success = 'hasError';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            elseif($error == 2)
            {
                $error_msgss = 'File is Empty Please Upload Valid File !!!';
                $success = 'fail';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            elseif($error == 3)
            {
                $error_msgss = 'File you are uploading is not a valid file, please upload a valid file !!!';
                $success = 'invalid';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            else
            {
                if($error == 0)
                {
                    $html_string = '';
                }
                $error_msgss = 'Users Move Successfully !!!';
                $success = 'pass';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }

            ExportStatus::where('type','user_bulk_import')->update(['status'=>0,'last_downloaded'=>date('Y-m-d'),'exception'=>$html_string, 'error_msgs'=>$error_msgss]);
            return response()->json(['msg'=>'File Saved','success'=>true]);
        }
        catch(\Exception $e)
        {
            dd($e);
        }
        catch(MaxAttemptsExceededException $e) {
            dd($e);
            $this->failed($e);
        }
    }
    public function failed( $exception)
    {
        ExportStatus::where('type','user_bulk_import')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException            = new FailedJobException();
        $failedJobException->type      = "user_bulk_import";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }

    public function filterFunction($success = null)
    {
        if($success == 'fail')
        {
            $this->response = "File is Empty Please Upload Valid File !!!";
            $this->result   = "true";
        }
        elseif($success == 'pass')
        {
            $this->response = "User Imported Successfully !!!";
            $this->result   = "false";
        }
        elseif($success == 'hasError')
        {
            $this->response = "User Imported Successfully, But Some Of Them Has Issues !!!";
            $this->result   = "withissues";
        }
        elseif($success == 'invalid')
        {
            $this->response = "File you are uploading is not a valid file, please upload a valid file !!!";
            $this->result   = "true";
        }
    }
}
