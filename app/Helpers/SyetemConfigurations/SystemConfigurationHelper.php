<?php

namespace App\Helpers\SyetemConfigurations;

use App\SystemConfiguration;
use Illuminate\Support\Facades\Auth;
// use Auth;

class SystemConfigurationHelper
{
	public static function store($request)
    	{
		$request->validate([
			'type' => 'required',
			'subject' => 'required',
			'detail' => 'required'
		]);
		$system_configuration 			= new SystemConfiguration();
		$system_configuration->type 		= $request->type;
		$system_configuration->subject 	= $request->subject;
		$system_configuration->detail 	= $request->detail;
		$system_configuration->user_id     = Auth::user()->id;
		$system_configuration->save();
		return true;
    	} 

	public static function edit($id)
	{
		$conf = SystemConfiguration::whereId($id)->first();
		return $conf;
	}

	public static function updateConfiguration($request,$id) 
	{
		$request->validate([
			'type' => 'required',
			'subject' => 'required',
			'detail' => 'required'
		]);
		$system_configuration 			= SystemConfiguration::findOrFail($id);
		$system_configuration->type 		= $request->type;
		$system_configuration->subject 	= $request->subject;
		$system_configuration->detail 	= $request->detail;
		$system_configuration->user_id   	= auth()->user()->id;
		$system_configuration->save();
		return true;
	}

	public static function deleteConfiguration($id)
	{
		SystemConfiguration::findOrFail($id)->delete();
		return true;
	}
}