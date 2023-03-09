<?php

namespace App\Jobs;

use App\ExportStatus;
use App\Exports\UserLoginHistoryExport;
use App\FailedJobException;
use App\UserLoginHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserLoginHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $from_login_date_exp;
    protected $to_login_date_exp;
    public $tries=1;
    public $timeout=1500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from_login_date_exp, $to_login_date_exp, $user_id)
    {
        $this->from_login_date_exp = $from_login_date_exp;
        $this->to_login_date_exp   = $to_login_date_exp;
        $this->user_id             = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from_login_date_exp = $this->from_login_date_exp; 
        $to_login_date_exp   = $this->to_login_date_exp; 
        $user_id             = $this->user_id;

        try {

            $query = UserLoginHistory::with('user_detail')->orderBy('id', 'DESC');

            if($from_login_date_exp != '')
            {  
                $from_login = str_replace("/","-",$from_login_date_exp);
                $from_login =  date('Y-m-d',strtotime($from_login));

                $query->where('last_login', '>=', $from_login.' 00:00:00');
            }

            if($to_login_date_exp != '')
            {  
                $to_login = str_replace("/","-",$to_login_date_exp);
                $to_login =  date('Y-m-d',strtotime($to_login));

                $query->where('last_login', '<=', $to_login.' 23:59:59');
            }

            $query = $query->get();

            $return = \Excel::store(new UserLoginHistoryExport($query), 'users-login-history-export.xlsx');

            if($return)
            {
                ExportStatus::where('user_id',$user_id)->where('type','users_login_history_list')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
            
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    public function failed( $exception)
    {
        ExportStatus::where('type','users_login_history_list')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException            = new FailedJobException();
        $failedJobException->type      = "Users Login History Export";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
