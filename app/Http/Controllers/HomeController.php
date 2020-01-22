<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      /*if(Auth::user()->can('Create PO'))
      {
        return redirect()->route('product.index');
      }*/
      $menu="";
        $request->status_read="0";
        $notifications = Auth::User()->notifications()->whereNull('read_at')->paginate(10);
        $group = array_column(Auth::User()->notifications()->get()->pluck('data')->toArray(),'tipe');
        $jnsnotif= array_unique($group);
        return view('home', compact('notifications','request','jnsnotif','menu'));
    }

    public function search(Request $request)
    {
      $menu="";
      $notifications = Auth::User()->notifications();
      if (isset($request->tipe))
      {
        $notifications = $notifications->where('data','like','%"tipe":"'.$request->tipe.'"%');
      }
      if (isset($request->subject))
      {
        $notifications = $notifications->where('data','like','%"subject":"%'.$request->subject.'%"%');
      }
      /*if (isset($request->tgl_aw_kirim) and isset($request->tgl_ak_kirim) )
      {

      }else
      */
      if (isset($request->tgl_aw_kirim))
      {
        $notifications = $notifications->wheredate('created_at','>=',$request->tgl_aw_kirim);
      }elseif (isset($request->tgl_ak_kirim))
      {
        $notifications = $notifications->wheredate('created_at','<=',$request->tgl_aw_kirim);
      }

      if($request->status_read=="0")
      {
        $notifications = $notifications->whereNull('read_at');
      }elseif($request->status_read=="1"){
        $notifications = $notifications->whereNotNull('read_at');
      }
      $notifications = $notifications->paginate(10);
    //  var_dump($notifications->pluck('data')->toArray());
      $group = array_column(Auth::User()->notifications()->pluck('data')->toArray(),'tipe');
      $jnsnotif= array_unique($group);
      return view('home', compact('notifications','request','jnsnotif','menu'));

    }

    public function indexAdmin()
    {
      $userdist =\App\User::where('register_flag',1)->where('validate_flag',1)
                ->whereExists(function($query){
                  $query->select(DB::raw(1))
                        ->from('role_user as ru')
                        ->join('roles as r', 'r.id','ru.role_id')
                        ->whereraw('ru.user_id=users.id')
                        ->wherein('r.name',['Distributor','Distributor Cabang']);
                })->count();
      $useroutlet =\App\User::where('register_flag',1)->where('validate_flag',1)
                ->whereExists(function($query){
                  $query->select(DB::raw(1))
                        ->from('role_user as ru')
                        ->join('roles as r', 'r.id','ru.role_id')
                        ->whereraw('ru.user_id=users.id')
                        ->wherein('r.name',['Outlet','Apotik/Klinik']);
                })->count();
      return view('admin.index',['menu'=>'blank','jmldist'=>$userdist,'jmloutlet'=>$useroutlet]);
    }
}
