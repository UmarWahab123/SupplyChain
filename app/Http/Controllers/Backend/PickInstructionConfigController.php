<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\QuotationConfig;
class PickInstructionConfigController extends Controller
{
    public function index()
    {
        $pi_config = [];
        $pi_config = QuotationConfig::where('section','pick_instruction')->first();
        $partial_pi_config = QuotationConfig::where('section','partial_pick_instruction')->first();
        $pi_redirection_config = QuotationConfig::where('section','pick_instruction_redirecion')->first();
        if($pi_config != null)
        {
            $pi_config = unserialize($pi_config->print_prefrences);
        }

        return view('backend.pick-instruction-config.index',compact('pi_config','partial_pi_config', 'pi_redirection_config'));
    }

    public function addConfig(Request $request)
    {
        $data = $request->all();
        $data = serialize($data);
        if(QuotationConfig::where('section','pick_instruction')->update(['print_prefrences'=>$data]))
        {
            return response()->json(['success'=>true]);
        }
        else
        {
            return response()->json(['success'=>false]);
        }
    }

    public function partialPickConfig(Request $request)
    {
        if($request->partial_config == "true")
        {
            $value = 1;
        }
        else
        {
            $value = 0;
        }
        $partial_pick = QuotationConfig::where('section','partial_pick_instruction')->first();
        if($partial_pick)
        {
            $partial_pick->display_prefrences = $value;
            $partial_pick->save();
        }
        else
        {
            $new_partial_config                     = new QuotationConfig;
            $new_partial_config->section            = 'partial_pick_instruction';
            $new_partial_config->display_prefrences = $value;
            $new_partial_config->save();
        }

        return response()->json(['success' => true]);
    }

    public function RedirectionPickConfig(Request $request)
    {
        if($request->redirection_config == "true")
        {
            $value = 1;
        }
        else
        {
            $value = 0;
        }
        $redirection_config = QuotationConfig::where('section','pick_instruction_redirecion')->first();
        if($redirection_config)
        {
            $redirection_config->display_prefrences = $value;
            $redirection_config->save();
        }
        else
        {
            $redirection_config                     = new QuotationConfig;
            $redirection_config->section            = 'pick_instruction_redirecion';
            $redirection_config->display_prefrences = $value;
            $redirection_config->save();
        }

        return response()->json(['success' => true]);
    }
}
