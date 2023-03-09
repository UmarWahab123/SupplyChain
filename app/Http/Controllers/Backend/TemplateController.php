<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Common\EmailTemplate;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TemplateController extends Controller
{
    public function index()
    {
        return $this->render('backend.templates.index');
    }

    public function getData()
    {
        $query = EmailTemplate::with('users')->select('email_templates.*');
        return Datatables::of($query)

         ->addColumn('status', function($item){
              return $item->status = $item->status == 1 ? 'Active' : 'Inactive';
            })
            ->addColumn('action', function ($item) { 
                $html_string = '<div class="icons">'.'
                              <a href="'.route('edit-template', ['id' => $item->id]).'" class="actionicon tickIcon"><i class="fa fa-pencil"></i></a> 
                          </div>';
                return $html_string;         
                })
            ->addColumn('created_at', function ($item) { 
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';         
            })
            
        ->addColumn('updated_at', function ($item) { 
        return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';        
        })
                ->rawColumns(['content', 'action','created_at','updated_at'])
                    ->make(true);
    }

    public function create()
    {
        return $this->render('backend.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'subject' => 'required',
            'content' => 'required'
        ]);

        // create new template //
        $template 			= new EmailTemplate;
        $template->type 	= $request->type;
        $template->subject 	= $request->subject;
        $template->content 	= $request->content;
        $template->updated_by   = $this->user->id;
        $template->save();

        return redirect()->route('list-template')->with('successmsg', 'New Template added successfully');
    }

    public function edit($id)
    {
        $template = EmailTemplate::findorfail($id);
        return $this->render('backend.templates.edit', compact('template'));
        //
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required',
            'content' => 'required'
        ]);

        // create new template //
        $template = EmailTemplate::findorfail($id);
        $template->subject      = $request->subject;
        $template->content      = $request->content;
        $template->updated_by   = $this->user->id;
        $template->save();

        return redirect()->route('list-template')->with('successmsg', 'Template updated successfully');

    }

}
