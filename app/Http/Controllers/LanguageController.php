<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use App;
use Lang;

class LanguageController extends Controller
{
    /*public function index(Request $request){
      if($request->lang <> ''){
        app()->setLocale($locale);
        echo trans('language.message');
      }
    }*/
    /**
      *@desc To change the current language
      *@request ajax
    */
    public function changeLanguage(Request $request){
      if($request->ajax()){
        $request->session()->put('locale',$request->locale);
        if($request->locale <> ''){
          app()->setLocale($request->locale);
            App::setLocale($request->locale);
        }
        //$request->session()->flash('alert-success',('label.Locale_Change_Success'));
          return response()->json(['response' => $request->locale]);
      }

      //echo Session::get(locale);
    }
    public function testfunction(Request $request)
    {
        if ($request->isMethod('post')){
            return response()->json(['response' => 'This is post method']);
        }

        return response()->json(['response' => 'This is get method']);
    }
}
