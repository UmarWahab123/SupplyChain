<?php

namespace App\Exceptions;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Auth;
use GuzzleHttp\Client;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (app()->environment() == 'local')
        {
        return parent::render($request, $exception);
        }
        if (app()->environment() == 'production')
        {
        if ($exception instanceof \PDOException) {
        // dd($exception->getSql());
        $this->createTicket($exception , $exception->getSql(),$request);
        }
        if ($exception instanceof \ErrorException) {
        $this->createTicket($exception , $exception->getMessage(),$request);
        }
        if ($exception instanceof ModelNotFoundException) {
        // dd('dfd');
        $this->createTicket($exception , $exception->getMessage(),$request);
        }

        if ($this->isHttpException($exception)) {
        $this->createTicket($exception , $exception->getMessage(),$request);
        }
        // return response()->view('errors.500', [], 500);
        }
        // dd($exception->getBindings());
        return parent::render($request, $exception);

    }
    public function createTicket($exception ,$title , $request)
    {
        try {
            $error_detail = 'message:'.@$exception->getMessage().', file:'.@$exception->getFile().', line:'.@$exception->getLine().', Url:'.@$request->getRequestUri();

        $token = config('services.ticket.api_key');
        $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
        ];
        $client = new Client();

        $response = $client->request('POST', 'https://support.d11u.com/api/new-ticket', [
        // 'auth' => [$username, $password],

        'headers' => $headers,
        'json' => [
            "department_id" => 1,
            "title" => $title,
            "detail" => $error_detail,
            "role" => @Auth::user()->roles->name,
            "notification_email" => @Auth::user()->email,
            "auto_generate" => 1,
        ]
        ]);
        $statusCode = @$response->getStatusCode();
        $body = @$response->getBody()->getContents();
        // or when your server returns json
        $content = json_decode(@$response->getBody(), true);
        } catch (Exception $e) {
            \Log::info($e->getMessage());
        }
        
    }
    }

