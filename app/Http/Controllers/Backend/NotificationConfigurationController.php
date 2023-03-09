<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Role;
use App\NotificationConfiguration;
use Yajra\Datatables\Datatables;
use App\User;
use App\ConfigurationTemplate;
use App\TemplateKeyword;
use App\CustomEmail;
class NotificationConfigurationController extends Controller
{
    public function index()
    {
        return view('Notification Configuration.index');
    }
    public function store(Request $request)
    {

        // update Notification Type Email,Notification,Both
        if($request->action=='updateNotificationType'){
            $notificationTypeName=$request->notificationTypeName;
            $dbRecordId=$request->dbRecordId;
            $Result=NotificationConfiguration::where('id',$dbRecordId)->update(['notification_type'=>$notificationTypeName]);
        }
        // update new status on or off
        elseif($request->action=='updateStatusType'){
            $notification_status=$request->statusTypeValue;
            $dbRecordId=$request->dbRecordId;
            NotificationConfiguration::where('id',$dbRecordId)->update(['notification_status'=>$notification_status]);
        }
        // save new configuration
        elseif($request->dbId==NULL){
            $IsNotificationAlreadyExist=NotificationConfiguration::where([
                'notification_name'=>$request->notification_name,
                'notification_discription'=>$request->notification_discription,
                'notification_type'=>"both",
                'notification_status'=>0,
                ])->first();
                if(!($IsNotificationAlreadyExist)){
                    $NotificationConfiguration=new NotificationConfiguration();
                    $NotificationConfiguration->slug=$request->slug;
                    $NotificationConfiguration->notification_name=$request->notification_name;
                    $NotificationConfiguration->notification_discription=$request->notification_discription;
                    $NotificationConfiguration->notification_type="both";
                    $NotificationConfiguration->notification_status=0;
                    $NotificationConfiguration->save();
                }
        }
            // update existing configuration title and description
            elseif($request->dbId){
                NotificationConfiguration::where('id',$request->dbId)->update(['notification_name'=>$request->notification_name,'notification_discription'=>$request->notification_discription]);
                 return response()->json(['success' => 'updated']);
            }
        return response()->json(['success' => true]);
    }

    public function getNotificationsConfiguration()
    {
        $Notifications = NotificationConfiguration::all();
        return Datatables::of($Notifications)
        ->addColumn('action',function($Notifications){
            return '
            <a href="" data-toggle="modal" data-target="#configurationDetails"  onclick="setCurrentRecordIdForConfiguration('.$Notifications->id.')" title="View Configurations"><i class="fa fa-gears" style="font-size:24px; margin-right:30px;"></i></a>'.''.'
            <a href="" data-toggle="modal" data-target="#NewNotificationConfiguration"  title="View Detail"  onclick="updateCongifugrationDetail('.$Notifications->id.')"><i    class="fa fa-edit" style="font-size:24px"></i></a>';
        })
        ->addColumn('notification_type',function($Notifications){
             if($Notifications->notification_type){
               return ' <input type="radio" id="email'.$Notifications->id.'"  name="notification_type_'.$Notifications->id.'" value="email" '.($Notifications->notification_type=="email" ? "checked" :"").' onclick="updateNotifcationType(this.value,'.$Notifications->id.')" >

                 <label for="email'.$Notifications->id.'">Email</label>

                 <input type="radio" id="notification'.$Notifications->id.'"  name="notification_type_'.$Notifications->id.'" value="notification"       '.($Notifications->notification_type=="notification" ? "checked" :"").' onclick="updateNotifcationType(this.value,'.$Notifications->id.')">

                 <label for="notification'.$Notifications->id.'">Notification</label>

                 <input type="radio" id="both'.$Notifications->id.'"  name="notification_type_'.$Notifications->id.'" value="both"
               '.($Notifications->notification_type=="both" ? "checked" :"").' onclick="updateNotifcationType(this.value,'.$Notifications->id.')">

                 <label for="both'.$Notifications->id.'">Both</label>
               ';
            }
        })
        ->addColumn('notification_status',function($Notifications){
               return ' <input type="radio" id="on'.$Notifications->id.'" name="notification_status_'.$Notifications->id.'" value="1" '.($Notifications->notification_status==1 ? "checked" :"").' onclick="updateNotficationStatus(this.value,'.$Notifications->id.')">
              <label for="on'.$Notifications->id.'">On</label>
              <input type="radio" id="off'.$Notifications->id.'" name="notification_status_'.$Notifications->id.'" value="0" '.($Notifications->notification_status==0 ? "checked" :"").' onclick="updateNotficationStatus(this.value,'.$Notifications->id.')">
              <label for="off'.$Notifications->id.'">Off</label>
            ';
        })
        ->addColumn('notification_discription',function($Notifications){
            return '<span id="short_desc">'.$Notifications->notification_discription.'</span>';
        })
        ->addColumn('notification_name',function($Notifications){
            return '<span id="short_desc">'.$Notifications->notification_name.'</span>';
        })
        ->rawColumns(['action','notification_type','notification_status','notification_discription','notification_name'])
        ->setRowId('id')
        ->make(true);
    }

    public function getNotificationDetail(Request $request)
    {
        $notification_discription='';
        $notification_name='';
        $NotificationDetail=NotificationConfiguration::find($request->dbRecordId);
        if($NotificationDetail){
            $notification_discription=$NotificationDetail->notification_discription;
            $notification_name=$NotificationDetail->notification_name;
        }
        return response()->json(['notification_discription'=>$notification_discription,'notification_name'=>$notification_name]);
    }

    public function getSelectedUser(Request $request)
    {
        $html_result='';
         if($request->userDefinedForConfigurations=="users"){
            $Admin=User::where([
                'role_id' =>1,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Admin=$Admin->toArray();

            $Purchasing=User::where([
                'role_id' =>2,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Purchasing=$Purchasing->toArray();


            $Sales=User::where([
                'role_id' =>3,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Sales=$Sales->toArray();

            $Sales_Coordinator=User::where([
                'role_id' =>4,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Sales_Coordinator=$Sales_Coordinator->toArray();

            $Logistic=User::where([
                'role_id' =>5,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Logistic=$Logistic->toArray();

            $Warehouse=User::where([
                'role_id' =>6,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Warehouse=$Warehouse->toArray();

            $Accounting=User::where([
                'role_id' =>7,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Accounting=$Accounting->toArray();

            $Developer=User::where([
                'role_id' =>8,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $Developer=$Developer->toArray();

            $SuperAdmin=User::where([
                'role_id' =>10,
                'status'=>1
            ])->whereNull('parent_id')->select(['id','name','email'])->get();
            $SuperAdmin=$SuperAdmin->toArray();
            $html_result='
            <optgroup  label="Admin">';
                if($Admin)
                {
                    foreach($Admin as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Purchasing">';
                if($Purchasing)
                {
                    foreach($Purchasing as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Sales">';
                if($Sales)
                {
                    foreach($Sales as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Sales Coordinator">';
                if($Sales_Coordinator)
                {
                    foreach($Sales_Coordinator as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Logistic">';
                if($Logistic)
                {
                    foreach($Sales_Coordinator as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Warehouse">';
                if($Warehouse)
                {
                    foreach($Sales_Coordinator as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Accounting">';
                if($Accounting)
                {
                    foreach($Accounting as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Developer">';
                if($Developer)
                {
                    foreach($Developer as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result.='
            <optgroup  label="Super Admin">';
                if($SuperAdmin)
                {
                    foreach($SuperAdmin as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result .= '</select>';

         }
         elseif($request->userDefinedForConfigurations=="roles"){
            $Roles=Role::all();
            $Roles= $Roles->toArray();
            $html_result='
            <optgroup  label="Roles">';
                if($Roles)
                {
                    foreach($Roles as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["name"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result .= '</select>';
         }elseif($request->userDefinedForConfigurations=="emails_custom"){
            $CustomEmails=CustomEmail::all();
            $CustomEmails=$CustomEmails->toArray();
            $html_result='
            <optgroup  label="Custom Emails">';
                if($CustomEmails)
                {
                    foreach($CustomEmails as $detail)
                    {
                        $html_result .= '
                        <option value='.$detail["id"].'>'.$detail["email"].'</option>
                        ';
                    }
                }
            $html_result .= '</optgroup>';
            $html_result .= '</select>';
         }
         return response()->json(['html' => $html_result]);
    }

    public function saveNotificationTemplate(Request $request)
    {


        $body=$request->body;
        $confiuguredUsers = $request->all_user_ids;
        $configuration_id=$request->configuration_id;
        $notification_type=$request->notification_type;
        $subject=$request->subject;
        $user_type=$request->user_type;
        $ConfigurationTemplate=new ConfigurationTemplate;
        $isRecordAlreadyExists=$ConfigurationTemplate::where(['notification_type'=>$notification_type,'notification_configuration_id'=>$configuration_id])->first();
        if($isRecordAlreadyExists){
            $ConfigurationTemplate::where(['notification_type'=>$notification_type,'notification_configuration_id'=>$configuration_id])->update([
                'notification_configuration_id'=>$configuration_id,'notification_type'=>$notification_type,'subject'=>$subject,'body'=>$body,'to_type'=>$user_type,'values'=>$confiuguredUsers
            ]);
            return response()->json(['success'=>true,'msg'=>'Record Updated Successfully']);
        }else{
            $ConfigurationTemplate->notification_configuration_id=$configuration_id;
            $ConfigurationTemplate->notification_type=$notification_type;
            $ConfigurationTemplate->subject=$subject;
            $ConfigurationTemplate->body=$body;
            $ConfigurationTemplate->to_type=$user_type;
            $ConfigurationTemplate->values =$confiuguredUsers;
            $ConfigurationTemplate->save();
            return response()->json(['success'=>true,'msg'=>'Record Inserted Successfully']);
        }
    }

    public function getSelectedConfigurationContent(Request $request)
    {
        $subject='';
        $body='';
        $notification_type='';
        $user_type='';
        $configured_users=[];
        $notification_template_type_keywords=[];
        $templates_keywords='';
        $html_keywords_template='';
        $ConfigurationTemplate=new ConfigurationTemplate;
        $configurationDetail=$ConfigurationTemplate::where(['notification_type'=>$request->notification_type,'notification_configuration_id'=>$request->configuration_id])->first();
        if($configurationDetail){
                $subject=$configurationDetail['subject'];
                $body=$configurationDetail['body'];
                $notification_type=$configurationDetail['notification_type'];
                $user_type=$configurationDetail['to_type'];
                $configured_users=$configurationDetail['values'];
            }
                $notification_template_type_keywords=new TemplateKeyword();
                $templates_keywords=$notification_template_type_keywords::where(['notification_configuration_id'=>$request->configuration_id,
                'notification_type'=>$request->notification_type
                ])->get();
                if($templates_keywords){
                    $html_keywords_template='<div class="font-weight-bold">';
                   foreach($templates_keywords as  $keyword){
                            $html_keywords_template.='[['.$keyword["keywords"].']]'.'<br>';
                        }
                        $html_keywords_template.='</div>';
                }
        return response()->json(['subject'=>$subject,'body'=>$body,'notification_type'=>$notification_type,'user_type'=>$user_type,'configured_users'=>$configured_users,'html_keywords_template'=>$html_keywords_template]);
    }

    public function saveKeywordAgainstConfiguration(Request $request)
    {
        $msg='Keyword Already Exist';
        $TemplateKeyword=new TemplateKeyword;
        $isDataAlreadyExists=$TemplateKeyword::where([
            'notification_configuration_id'=>$request->configuration_id,
            'notification_type'=>$request->notification_type,
            'keywords'=>$request->keyword,
            ])->get();
            if(count($isDataAlreadyExists)==0){
                $TemplateKeyword->notification_configuration_id=$request->configuration_id;
                $TemplateKeyword->notification_type=$request->notification_type;
                $TemplateKeyword->keywords=$request->keyword;
                $TemplateKeyword->save();
                $msg='Data Inserted Successfully';
            }
            return response()->json([
                'success'=>true,'msg'=>$msg
            ]);
        }

        public function saveCustomEmail(Request $request)
        {
            $CustomEmail=new CustomEmail();
            $msg='';
            $isEmailAlreadyExists=$CustomEmail->where('email',$request->custom_email)->first();
            if(!($isEmailAlreadyExists)){
                $CustomEmail->email=$request->custom_email;
                $CustomEmail->save();
                $msg="Custom Email Added Successfully";
            }else{
                $msg="Custom Email Exists Already";
            }
            return response()->json(['success'=>true,'msg'=>$msg]);
        }
}
