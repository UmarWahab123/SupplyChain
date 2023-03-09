<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Auth;
use App\User;
class TicketController extends Controller
{
    public function index(Request $request)
    {
        dd($request->all());
    }

    public function postTicketRequest(Request $request){
        // dd($request->all());
        $client = new Client();
        $token = config('services.ticket.api_key');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $url = 'https://support.d11u.com/api';
        /*get all departments*/
        $depResponse = $client->request('POST',$url.'/new-ticket',[
            'headers' => $headers,
            'json' => [
                // "role" => $role_name,
                "title" => $request->title,
                "detail" => $request->detail,
                "department_id" => $request->department_id,
                "url" => $request->url,
                "auto_generate" => $request->auto_generate,
                "notification_email" => $request->notification_email,
                "role" => $request->role,
                "role_name" => $request->role_name,
                "parent_email" => $request->parent_email,
                "parent_role" => $request->parent_role,
                "attachments[]" => $request->attachments

            ]
        ]);
        $depStatusCode = $depResponse->getStatusCode();
        $depBody = $depResponse->getBody()->getContents();
        // dd($depBody);
        // or when your server returns json
        $depContent = json_decode($depResponse->getBody(), true);
        if($depContent['success'] == true){
            return response()->json(['success' => true]);
        }else{
            return response()->json(['success' => false]);
        }
    }
    
        public function ticketDepartments(Request $request)
        {     
        
            $client = new Client();
            $token = config('services.ticket.api_key');
            // dd($token);
            $headers = [
            'Authorization' => 'Bearer ' . $token, 
            'Accept' => 'application/json',
            ];

            /*get all departments*/
            $depResponse = $client->request('GET','https://support.d11u.com/api/all-departments',[
                'headers' => $headers
            ]);
            $depStatusCode = $depResponse->getStatusCode();
            $depBody = $depResponse->getBody()->getContents();

            // or when your server returns json
            $depContent = json_decode($depResponse->getBody(), true);
            $departments = $depContent['departments'];

            $html ='<select class="form-control" name="department_id" required>';
            $html .= '<option value="" selected >Choose Primary One of the following </option>';
            foreach ($departments as $department)
            {
            $html.='<option value="'.$department['id'].'">'.$department['title'].'</option>';
            }
            $html.='</select>';
            // dd($departments);
            return response()->json([

            "html"=>@$html
            ]);
            }

        public function roleTickets()
        {
            $role_name = $this->user->roles->name;
            $client = new Client();
            $token = config('services.ticket.api_key');
            $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            ];

            $response = $client->request('POST','https://support.d11u.com/api/role-tickets',[
            'headers' => $headers,
            'json' => [
            // "role" => $role_name,
            "role" => Auth::user()->roles->name,
            "email" => Auth::user()->email,

            ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            // or when your server returns json
            $content = json_decode($response->getBody(), true);
            $tickets = $content['tickets'];
            // return $this->render('user.ticket.index',compact('countries','tickets', 'user'));
            // dd('d');
            if (true)
            {
            return $this->render('backend.ticket.index',compact('tickets'));
            }
            // if (Auth::user()->role_id == 4 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6 || Auth::user()->role_id == 7 )
            // {
            // return $this->render('user.ticket.index',compact('tickets'));
            // }
            // if (Auth::user()->role_id == 3)
            // {
            // return $this->render('backend.ticket.index',compact('tickets'));
            // }
            // if (Auth::user()->role_id == 8)
            // {
            // return $this->render('partner.ticket.index',compact('tickets'));
            // }
            // if (Auth::user()->role_id == 9)
            // {
            // return $this->render('lab.ticket.index',compact('tickets'));
            // }
            // if (Auth::user()->role_id == 1)
            // {
            // return $this->render('backend.ticket.my-tickets',compact('tickets'));
            // }


        }
    public function ticketDetail($ref)
    {
    // dd($ref);
    $role_name = Auth::user()->roles->name;

    $client = new Client();
    $token = config('services.ticket.api_key');
    $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
    ];
    $response = $client->request('GET', 'https://support.d11u.com/api/ticket-detail', [
    // 'auth' => [$username, $password],
    'headers' => $headers,
    'json' => [
    "ticket_ref" => $ref
    ]
    ]);
    $statusCode = $response->getStatusCode();
    $body = $response->getBody()->getContents();
    // or when your server returns json
    $content = json_decode($response->getBody(), true);
    // dd($content);
    $ticket_user =User::where('email',$content['ticket_detail']['notify_mail'])->first();
    // dd($user->parent);
    // if (Auth::user()->role_id == 2)
    // {
    // return $this->render('sales-company.ticket.ticket-detail',compact('content','ref','ticket_user'));
    // }
    // if (Auth::user()->role_id == 4 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6 || Auth::user()->role_id == 7)
    // {
    // return $this->render('user.ticket.ticket-detail',compact('content','ref','ticket_user'));
    // }
    // if (Auth::user()->role_id == 3)
    // {
    // return $this->render('logistics.ticket.ticket-detail',compact('content','ref','ticket_user'));
    // }
    // if (Auth::user()->role_id == 8)
    // {
    // return $this->render('partner.ticket.ticket-detail',compact('content','ref','ticket_user'));
    // }
    // if (Auth::user()->role_id == 9)
    // {
    // return $this->render('lab.ticket.ticket-detail',compact('content','ref','ticket_user'));
    // }
    if (true)
    {
    return $this->render('backend.ticket.ticket-detail',compact('content','ref','ticket_user'));
    }
    else
    {
        return redirect()->back();
    }
    }
}
