<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use App\CategoryOutlet;
use App\SubgroupDatacenter;
use App\Role;
use App\GroupDatacenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Auth;
use App\Notifications\NewoutletDistributionNotif;
use App\Notifications\MarketingGaleniumNotif;
use App\Notifications\RejectDistributorNotif;
use App\Notifications\FirstLoginRegister;
use App\Notifications\RejectbyGPLNotif;
use App\OutletDistributor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Image;
use File;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function show($id=null,$notif_id=null)
  {
    $user = User::find($id);
    $customer_email = $user->email;
    $roleid =  DB::table('Users')
      ->join('role_user', 'users.id', '=', 'role_user.user_id')
      ->join('roles', 'role_user.role_id', '=', 'roles.id')
      ->select('role_user.role_id')
      ->where('Users.id','=',$id)
      ->first();
    $dataijin = array(0=>'Tidak Berijin',1=>'Ijin PBF',2=>'Ijin Toko Obat');
    if (isset($user->customer_id)){
      $customer = $user->customer;
      $customer_contacts = $customer->contact()->where('contact_type','<>', 'EMAIL')->get();

      //dd($groupdcs);
      //$customer_email = $customer->contact()->where('contact_type', 'EMAIL')->first();
      //$customer_contacts= $customer->whereHas(colors, function ($query) {                                         $query->where('color', 'blue'); //'color' is the column on the Color table where 'blue' is stored.}])->get();
      $customer_sites = $customer->sites;
      Auth::User()->notifications()
                    ->where('id','=',$notif_id)
                      ->update(['read_at' => Carbon::now()]);

      if (Auth::user()->ability(array('MarketingGPL','Marketing PSC', 'Marketing Pharma'),'') )
      {
        if($customer->contact()->where('contact_type', 'EMAIL')->first())
        {
          $customer_email = $customer->contact()->where('contact_type', 'EMAIL')->first();
          $customer_email =$customer_email->contact;
        }
        if (!empty($customer->subgroup_dc_id))
        {
          $subgroupdc =SubGroupDatacenter::find($customer->subgroup_dc_id);
          if($subgroupdc){
              $groupdcid = $subgroupdc->group_id;
          }

        }else{
          $groupdcid="";
        }
        $groupdcs = GroupDatacenter::where('enabled_flag','1')->get();
        $categories = CategoryOutlet::where('enable_flag','Y')->orderBy('name','asc')->get();
        $roles = Role::whereIn('name',['Outlet','Apotik/Klinik'])->get();
        $distributors = DB::table('customers as a')
                        ->join('outlet_distributor as b','a.id','=','b.outlet_id')
                        ->join('customers as c','b.distributor_id','=','c.id')
                        ->join('Users as d','a.id','=','d.customer_id')
                        ->select ('c.id as distributor_id','c.customer_name as distributor_name','b.approval','b.keterangan','b.inactive','b.end_date_active')
                        ->where('d.id','=',$id)
                        ->get();
          return view('admin.customer.edit',compact('user','customer','customer_contacts','customer_email','customer_sites','categories','roles','groupdcs','groupdcid','roleid','distributors','notif_id','dataijin'));
      }elseif(Auth::user()->ability(array('Distributor', 'Principal', 'Distributor Cabang','IT Galenium'),'')  )
      {
          if($customer->categoryOutlet){
              $categoryoutlet = $customer->categoryOutlet->name;
          }

          $subgroupdc = $customer->subgroupdc;
          if($subgroupdc)
          {
            $groupdc = $subgroupdc->groupdatacenter->display_name;
            $subgroupname = $subgroupdc->display_name;
          }

          $email = $user->email;
          if(Auth::user()->hasRole('IT Galenium')) {
              $menu = "custYasa";
              //return redirect()->route('')
              return view('admin.oracle.show',compact('user','customer','email','categoryoutlet','subgroupname','groupdc','customer_sites','customer_contacts','notif_id','menu','dataijin'))
              ->withMessage('Data Berhasil diubah');
          }
          $outletdist = OutletDistributor::where([
            ['outlet_id','=',$user->customer_id],
            ['distributor_id','=',Auth::User()->customer_id],
            ])->first();

            if($outletdist){
              return view('admin.customer.show',compact('user','customer','email','categoryoutlet','subgroupname','groupdc','customer_sites','customer_contacts','outletdist','notif_id','dataijin'));
            }else{
              abort(403, 'Unauthorized action.');
            }

      }else{
          return view('errors.403');
      }

    }
    return view('/home')->withmessage('Data Customer Not Found');
  }

  public function search($search,$id)
  {/*Menampilkan list distributor yang bisa dipilih*/
    //$id adalah id dari outlet
    $user = User::find($id);
    $customer_id = $user->customer_id;

    /*$customers = DB::table('customers')
            ->join('customer_sites', 'customers.id', '=', 'customer_sites.customer_id')
            ->join('users','customers.id','=','users.customer_id')
            ->select('customers.id','customers.customer_name', 'customer_sites.address1','customer_sites.city'
                    ,'customer_sites.state', 'customer_sites.postalcode')
            ->where([
              ['users.register_flag','=',true],
              ['customers.customer_name','like','%'.strtolower($search).'%']
            ]);*/
    $customers = DB::table('customers')
            ->join('users','customers.id','=','users.customer_id')
            ->select('customers.id','customers.customer_name')
            ->where([
              ['users.register_flag','=',true],
              ['customers.customer_name','like','%'.strtolower($search).'%']
            ]);

    if ($user->hasRole("Outlet"))
    {
      $customers = $customers ->whereExists(function ($query) {
                /*$query->select(DB::raw(1))
                      ->from('role_user')
                      ->whereRaw('users.id = role_user.user_id')
                      ->whereIn('role_user.role_id', [6,7,8]);*/
                      $query->select(DB::raw(1))
                            ->from('role_user')
                            ->join('roles', 'role_user.role_id', '=', 'roles.id')
                            ->whereRaw('users.id = role_user.user_id')
                            ->whereIn('roles.name', ['Principal','Distributor','Distributor Cabang']);
            });
    }elseif($user->hasRole("Distributor")  or $user->hasRole("Distributor Cabang"))
    {
      $customers = $customers ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('role_user')
                      ->join('roles', 'role_user.role_id', '=', 'roles.id')
                      ->whereRaw('users.id = role_user.user_id')
                      //->whereIn('role_user.role_id', [8]);
                      ->whereIn('roles.name', ['Principal']);
            });
    }

    if(Auth::user()->hasRole('Marketing PSC'))
    {
      $customers = $customers->where('customers.psc_flag','=','1');
    }elseif(Auth::user()->hasRole('Marketing Pharma'))
    {
      $customers = $customers->where('customers.pharma_flag','=','1');
    }

    $customers = $customers ->whereNotExists(function ($query) use($customer_id) {
              $query->select(DB::raw(1))
                    ->from('outlet_distributor')
                    ->whereRaw('outlet_distributor.distributor_id= customers.id')
                    ->where('outlet_distributor.outlet_id','=',$customer_id);
          });

      $customers = $customers->get(); //paginate(5);
      //dd($customers);
      if($customers->count()>0){
        return view('admin.customer.listdistributor',compact('customers'));
      }else{
        return "";
      }

    //return Response::json($customers);
  }

  public function rejectGPL(Request $request)
  {
    $user = User::find($request->id);
    //$user->detachRoles($user->roles);
    $customer = Customer::find($user->customer_id);
    $customer->status = 'R';
    if(Auth::User()->hasRole('Marketing PSC'))
    {
      $customer->id_approval_psc = Auth::User()->id;
      $customer->date_approval_psc =Carbon::now();
    }
    if(Auth::User()->hasRole('Marketing Pharma'))
    {
      $customer->id_approval_pharma = Auth::User()->id;
      $customer->date_approval_pharma =Carbon::now();
    }
    $customer->keterangan = $request->alasan;
    $customer->save();
    Auth::User()->notifications()
                ->where('id','=',$request->notif_id)
                  ->update(['read_at' => Carbon::now()]);
    $user->notify(new RejectbyGPLNotif());
    return response()->json([
                    'result' => 'success',
                    'message' => trans('pesan.reject'),
                  ],200);
  }

  public function update(Request $request, $id)
  {//approve outlet by gpl
    $pesan = "";

    if($request->save=="save")
    {
      DB::beginTransaction();
      try{
        $user = User::find($id);
        if($request->hasFile('avatar')) {
          $validator = Validator::make($request->all(), [
              'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
          ])->validate();
          $tmp = $user->avatar ;
          if (File::exists(public_path('uploads/avatars/'.$tmp ))){
            unlink(public_path('uploads/avatars/'.$tmp ));
            //echo ("<br>delete image");
          }
          $avatar = $request->file('avatar');
          $filename = time() . '.' . $avatar->getClientOriginalExtension();
          Image::make($avatar)->resize(300, 300)->save( public_path('uploads/avatars/' . $filename));
          $user->avatar = $filename;
        }
        if($request->ijin_pbf!=0){
          $this->validate($request, [
              'noijin' => 'required',
              'masaberlaku' =>'required|date',
          ]);
        }

        if(isset($request->name)) $user->name=$request->name;
        $user->save();
        $user->detachRoles($user->roles);
        $user->roles()->attach($request->role);
        $customer = Customer::find($user->customer_id);
        //dd($customer->hasDistributor->count());
        $customer->tax_reference = $request->npwp;
        if (isset($request->name)) $customer->customer_name = $request->name;
        $customer->outlet_type_id =$request->category;
        if($request->ijin_pbf!=0){
          $customer->ijin_pbf=$request->ijin_pbf;
          $customer->no_ijin=$request->noijin;
          $customer->masa_berlaku=date('Y-m-d',strtotime($request->masaberlaku));
        }else{
          $customer->ijin_pbf=$request->ijin_pbf;
          $customer->no_ijin=null;
          $customer->masa_berlaku=null;
        }
        $sites = $customer->sites()->where('primary_flag','=','Y')->first();
        if($sites)
        {
          $city = $sites->city_id;
        }else{
          $city = null;
        }
        $groupdc =$customer->outlet_type_id;
        if($request->psc_flag=="1" and $request->pharma_flag=="1") {
          $customer->subgroup_dc_id =$request->subgroupdc;
          $distributorpsc = app('App\Http\Controllers\Auth\RegisterController')->mappingDistributor($groupdc,$city,"PSC");
          $distributorpharma = app('App\Http\Controllers\Auth\RegisterController')->mappingDistributor($groupdc,$city,"PHARMA");
          $distributor = $distributorpsc->union($distributorpharma);
        }elseif($request->psc_flag=="1") {
          $customer->subgroup_dc_id =$request->subgroupdc;
          $distributor = app('App\Http\Controllers\Auth\RegisterController')->mappingDistributor($groupdc,$city,"PSC");
        }elseif($request->pharma_flag=="1") {
          $customer->subgroup_dc_id =null;
          $distributor = app('App\Http\Controllers\Auth\RegisterController')->mappingDistributor($groupdc,$city,"PHARMA");
        }
        $distributor = $distributor->get();
        $olddistributor = $customer->hasDistributor;
        if($distributor)
        {
          $newdist = $distributor->whereNotIn('id',$customer->hasDistributor()->pluck('id')->toArray());
          /*attach distributor yang belum ada*/
          $customer->hasDistributor()->attach($newdist->pluck('id')->toArray(),['created_by'=>Auth::user()->id,'last_update_by'=>Auth::user()->id]);
        }

        if($customer->pharma_flag=="1" and $request->pharma_flag==""){
          /*hapus semua distributor pharma*/
          $pharmaflag = $olddistributor->where('pharma_flag','1');
          $pharmaflag = $pharmaflag->where('psc_flag','!=','1');
          $customer->hasDistributor()->detach($pharmaflag->pluck('id')->toArray());

        }
        if($customer->psc_flag=="1" and $request->psc_flag==""){
          /*hapus semua distributor pharma*/
          $pscflag = $olddistributor->where('psc_flag','1');
          $pscflag = $pscflag->where('pharma_flag','!=','1');
          $customer->hasDistributor()->detach($pscflag->pluck('id')->toArray());
        }

        $customer->psc_flag =$request->psc_flag;
        $customer->pharma_flag =$request->pharma_flag;
        $customer->save();
        if($customer->hasDistributor->count()==0)
        {
          $pesan = " ".trans("pesan.adddistributor");
        }
        DB::commit();
          return redirect()->route('customer.show',['id'=>$id,'notif_id'=>$request->notif_id])->withMessage(trans("pesan.update").$pesan);
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }elseif($request->save=="Inactive")
    {
      DB::beginTransaction();
      try{
        $user = User::find($id);
        $customer = Customer::find($user->customer_id);
        $customer->status='I';
        $customer->save();
        $user->validate_flag=0;
        $user->save();
        DB::commit();
        return redirect()->route('customer.show',['id'=>$id,'notif_id'=>$request->notif_id])->withMessage("Customer telah diInactivekan");
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }elseif($request->save=="active"){
      DB::beginTransaction();
      try{
        $user = User::find($id);
        $customer = Customer::find($user->customer_id);
        $customer->status='A';
        $customer->save();
        $user->validate_flag=1;
        $user->save();
        DB::commit();
          return redirect()->route('customer.show',['id'=>$id,'notif_id'=>$request->notif_id])->withMessage("Customer telah diactivekan");
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }
  }

  public function addlist($id, $outletid)
  {/*untuk tambahkan data distributor
    /* id adalah id customer dari distributor yang ingin ditambahkan
    /* $outletid adalah userid dari outlet
    */
    $userlogin = Auth::user();
    /*$notifications = $userlogin->notifications()
                    ->where([
                      ['type','like','%MarketingGaleniumNotif'],
                      ['notifiable_id','=',$userlogin->id],
                      ['notifiable_type','like', 'App%User'],
                      ['data','like', '%{"User":{"id":'.$outletid.'%'],
                      ])
                      ->whereNull('read_at')
                      ->get();
    //dd($notifications);
    foreach ($notifications as $notification) {
      if (empty($notification->read_at))
      {
          $notification->markAsRead();
      }
    }*/
    //notif to distributor and add data to outlet distributor
    $distributor = Customer::find($id);
    $distributor_user = User::where('customer_id','=',$id)->first();
    if ($distributor)
    {
      $user = User::find($outletid);
      $outlet = Customer::find($user->customer_id);
    //  $distributor_user->notify(new NewoutletDistributionNotif($user,$distributor));
      $outlet->hasDistributor()->attach($id,['created_by'=>Auth::user()->id,'last_update_by'=>Auth::user()->id]);
      return Response::json($distributor);
    }
  }

    public function approve(Request $request)
    {/*tidak digunakan karena perubahan alur*/
      if (Auth::User()->can('ApproveOutlet')){
        DB::table('outlet_distributor')
            ->where([
              ['outlet_id','=',$request->id],
              ['distributor_id','=',Auth::User()->customer_id],
              ])
            ->update(['approval' => true,'tgl_approve'=>Carbon::now(),'last_update_by'=>Auth::user()->id]);
            //unreadmark notification
            Auth::User()->notifications()
                        ->where('id','=',$request->notif_id)
                          ->update(['read_at' => Carbon::now()]);
          $datadist = DB::table('outlet_distributor')
              ->where([
                ['outlet_id','=',$request->id],
                ['distributor_id','!=',Auth::User()->customer_id],
                ['approval','=',1]
                ])->get();
          if (count($datadist)==0)
          {
            $outletusers = User::where('customer_id','=',$request->id)->get();
            foreach($outletusers as $outletuser)
            {
              if (!$outletuser->register_flag)
              {
                  $outletuser->api_token = str_random(60);
                  $outletuser->save();
                  $outletuser->notify(new FirstLoginRegister($outletuser));
              }

            }
          }
          //check outlet. If has not register and just have 1 record outlet approve then email
        return response()->json([
          'result' => 'success',
          'message' => trans('pesan.approve'),
        ],200);
      }else{
        return response()->json([
          'result' => 'failed',
          'message' => trans('auth.noaccess'),
        ],200);
      }

    }

    public function reject(Request $request)
    {
      if (Auth::User()->can('ApproveOutlet')){
       DB::table('outlet_distributor')
            ->where([
              ['outlet_id','=',$request->id],
              ['distributor_id','=',Auth::User()->customer_id],
              ])
            ->update(['approval' => false,'keterangan'=>$request->alasan,'tgl_approve'=> Carbon::now(), 'updated_at'=>Carbon::now() ,'last_update_by'=>Auth::user()->id ]);
        //unreadmark notification
        Auth::User()->notifications()
                    ->where('id','=',$request->notif_id)
                      ->update(['read_at' => Carbon::now()]);
        $outlet = Customer::find($request->id);
        //notif back ke marketing pharma or psc
        if (Auth::User()->customer->psc_flag=="1" and $outlet->psc_flag=="1")
        {
          $dist =OutletDistributor::where([
                  ['outlet_id','=',$request->id],
                  ['distributor_id','=',Auth::User()->customer_id],
                  ])->first();
          //dd($dist);
          $marketings_psc = User::whereHas('roles', function($q){
              $q->where('name','Marketing PSC');
          })->get();
          foreach ($marketings_psc as $mpsc)
          {
              $mpsc->notify(new RejectDistributorNotif($dist,Auth::User()));
          }
        }
        if (Auth::User()->customer->pharma_flag=="1" and $outlet->pharma_flag=="1")
        {
          $dist =OutletDistributor::where([
                  ['outlet_id','=',$request->id],
                  ['distributor_id','=',Auth::User()->customer_id],
                  ])->first();
          //dd($dist);
          $marketings_pharma = User::whereHas('roles', function($q){
              $q->where('name','Marketing Pharma');
          })->get();
          foreach ($marketings_pharma as $mphr)
          {
              $mphr->notify(new RejectDistributorNotif($dist,Auth::User()));
          }

        }
        return response()->json([
          'result' => 'success',
          'message' => trans('pesan.reject'),
        ],200);
      }else{
        return response()->json([
          'result' => 'failed',
          'message' => trans('auth.noaccess'),
        ],200);
      }

    }

    public function ajaxSearchAlamat()
    {
      $search = Input::get('term');
      $results = array();
      if($search!=""){
            $queries = DB::table("customer_sites")
            		->select("id", "address1","state","city","province","postalcode")
                ->where('customer_id','=',auth()->user()->customer_id)
                ->Where(function ($query) use($search) {
                $query->orwhere('city','LIKE',"%$search%")
                      ->orWhere('province','LIKE',"%$search%")
                      ->orWhere('postalcode','LIKE',"%$search%")
                      ->orWhere('address1','LIKE',"%$search%")    ;
                  })->take(10)->get();

                foreach ($queries as $query)
    	          {
                  //if (!is_null($query->state))
    	             $results[] = [ 'id' => $query->id, 'address1' => $query->address1.','.$query->state.','.$query->city.','.$query->province." ".$query->postalcode];
    	          }
                return Response::json($results);
        }

    }

	/*LIST NOO*/
    public function listNoo(Request $request)
    {
      $categories = CategoryOutlet::where('enable_flag','Y')->orderBy('name','asc')->get();
      $roles = Role::whereIn('name',['Outlet','Apotik/Klinik'])->get();
      $subgroupdc=DB::table('subgroup_datacenters as s')
                  ->join('group_datacenters as g','g.id','=','s.group_id')
                  ->where([['s.enabled_flag','=',1],['g.enabled_flag','=',1]])
                  ->select('s.id as id','g.name as group','s.name as subgroup')
                  ->get();
      if($request->isMethod('post'))
      {
        $outlets = Customer::where('status','A');//wherenull('oracle_customer_id');
        $outlets =$outlets->leftjoin('category_outlets as co','co.id','=','customers.outlet_type_id');
        $outlets =$outlets->leftjoin('subgroup_datacenters as sdc','sdc.id','=','customers.subgroup_dc_id');
        if($request->name)
        {
            $outlets = $outlets->where('customer_name','like',$request->name."%");
        }
        if ($request->category!="")
        {
            $outlets = $outlets->where('outlet_type_id','=',$request->category);
        }
        if ($request->role!="")
        {
            $r=$request->role;
            $outlets = $outlets->whereExists(function ($query) use($r) {
                  $query->select(DB::raw(1))
                        ->from('users as u')
                        ->join('role_user as ru','ru.user_id','=','u.id')
                        ->join('roles as r','r.id','=','ru.role_id')
                        ->whereRaw("u.customer_id = customers.id and r.id = '".$r."'");
              });
        }else{
          $outlets = $outlets->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('users as u')
                      ->join('role_user as ru','ru.user_id','=','u.id')
                      ->join('roles as r','r.id','=','ru.role_id')
                      ->whereRaw("u.customer_id = customers.id and r.name in ('Apotik/Klinik','Outlet')");
            });
        }
        if($request->psc_flag=="1" and $request->pharma_flag!="1")
        {
            $outlets = $outlets->where('psc_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag!="1")
        {
            $outlets = $outlets->where('pharma_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag=="1")
        {
            $outlets = $outlets->where(function ($query) {
              $query->where('pharma_flag','=','1')
                    ->orWhere('psc_flag','=','1');
            });
        }
        //dd($request->subgroupdc);
        if($request->subgroupdc)
        {
          $outlets = $outlets->whereIn('subgroup_dc_id',$request->subgroupdc);
        }
        //var_dump($outlets->toSql());
        $outlets = $outlets->select('customers.customer_name as customer_name','customers.id as id','co.name as category_name','tax_reference','pharma_flag','psc_flag','sdc.name as subdc','customers.status');
        $outlets = $outlets->get();
      }else{
        $outlets = null;
      }

      return view('admin.customer.listNoo',compact('categories','roles','outlets','request','subgroupdc'));
    }
	/*-------*/

	/*REPORT NOO*/
    public function reportNoo(Request $request)
    {
      $categories = CategoryOutlet::where('enable_flag','Y')->orderBy('name','asc')->get();
      $roles = Role::whereIn('name',['Outlet','Apotik/Klinik'])->get();
      $regencies = DB::table('regencies')->get();
      $subgroupdc=DB::table('subgroup_datacenters as s')
                  ->join('group_datacenters as g','g.id','=','s.group_id')
                  ->where([['s.enabled_flag','=',1],['g.enabled_flag','=',1]])
                  ->select('s.id as id','g.name as group','s.name as subgroup')
                  ->get();
      if($request->isMethod('post'))
      {
        $outlets = Customer::wherenull('oracle_customer_id');
        $outlets =$outlets->leftjoin('category_outlets as co','co.id','=','customers.outlet_type_id');
        $outlets =$outlets->leftjoin('subgroup_datacenters as sdc','sdc.id','=','customers.subgroup_dc_id');
        if($request->name)
        {
            $outlets = $outlets->where('customer_name','like',$request->name."%");
        }
        if ($request->category!="")
        {
            $outlets = $outlets->where('outlet_type_id','=',$request->category);
        }
        if ($request->role!="")
        {
            $r=$request->role;
            $outlets = $outlets->whereExists(function ($query) use($r) {
                  $query->select(DB::raw(1))
                        ->from('users as u')
                        ->join('role_user as ru','ru.user_id','=','u.id')
                        ->join('roles as r','r.id','=','ru.role_id')
                        ->whereRaw("u.customer_id = customers.id and r.id = '".$r."'");
              });
        }else{
          $outlets = $outlets->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('users as u')
                      ->join('role_user as ru','ru.user_id','=','u.id')
                      ->join('roles as r','r.id','=','ru.role_id')
                      ->whereRaw("u.customer_id = customers.id and r.name in ('Apotik/Klinik','Outlet')");
            });
        }
        if($request->psc_flag=="1" and $request->pharma_flag!="1")
        {
            $outlets = $outlets->where('psc_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag!="1")
        {
            $outlets = $outlets->where('pharma_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag=="1")
        {
            $outlets = $outlets->where(function ($query) {
              $query->where('pharma_flag','=','1')
                    ->orWhere('psc_flag','=','1');
            });
        }
        //dd($request->subgroupdc);
        if($request->subgroupdc)
        {
          $outlets = $outlets->whereIn('subgroup_dc_id',$request->subgroupdc);
        }
        //var_dump($outlets->toSql());
        $outlets = $outlets->select('customers.customer_name as customer_name','customers.id as id','co.name as category_name','tax_reference','pharma_flag','psc_flag','sdc.name as subdc','customers.status');
        $outlets = $outlets->get();
      }else{
        $outlets = null;
      }

      return view('admin.customer.reportNoo',compact('categories','roles','outlets','request','subgroupdc','regencies'));
    }
	/*-------*/

    public function searchOutlet(Request $request)
    {
      $data = Customer::where("customer_name","LIKE",$request->input('query')."%")
            ->whereNull("oracle_customer_id")
            ->where('status','=','A');
      $data = $data->select('id','customer_name');
      $data = $data->get();
      return response()->json($data);
    }

    public function searchOracleOutlet(Request $request)
    {
      $data = Customer::where("customer_number","LIKE",$request->input('query')."%")
            ->whereNotNull("oracle_customer_id")
            ->where('status','=','A')
            ->where('customer_class_code','=','OUTLET')
            ->whereNotExists(function($query){
              $query->select(DB::raw(1))
                    ->from('users as u')
                    ->whereRaw("u.customer_id= customers.id and u.register_flag='1'");
            });

      $data = $data->select('id','customer_name','customer_number','oracle_customer_id','tax_reference');
      $data = $data->orderBy('customer_number','asc')->get();
      return response()->json($data);
    }

    public function searchDistributor(Request $request,$flag=null)
    {
      $data = DB::table('customers as c')
              ->join('users as u','c.id','=','u.customer_id')
              ->join('role_user as ru','u.id','=','ru.user_id')
              ->join('roles as r','ru.role_id','=','r.id')
              ->select('c.id','c.customer_name')
              ->where([
                ['u.register_flag','=',true],
                ['c.status','=','A'],
              ])->whereIn('r.name',['Distributor','Distributor Cabang','Principal']);
      if($flag=="PHARMA")  $data=$data->where('c.pharma_flag','=','1')      ;
      $data=$data->orderBy('c.customer_name','asc')
              ->get();
      return response()->json($data);
    }

    public function searchOutletDistributor(Request $request)
    {
      $data = DB::table('customers as c')
              ->join('users as u','c.id','=','u.customer_id')
              ->join('role_user as ru','u.id','=','ru.user_id')
              ->join('roles as r','ru.role_id','=','r.id')
              ->join('permission_role as pr','pr.role_id','=','r.id')
              ->join('permissions as p','p.id','=','pr.permission_id')
              ->select('c.id','c.customer_name')
              ->where([
                ['u.register_flag','=',true],
                ['c.status','=','A'],
              ])->where('p.name','=','Create PO')
              ->orderBy('c.customer_name','asc')
              ->get();
      return response()->json($data);
    }

    public function inactiveDistributor(Request $request)
    {
      if($request->inactive=="inactive")
      {
        DB::table('outlet_distributor')
            ->where([
              ['outlet_id','=',$request->customer_id],
              ['distributor_id','=',$request->distributor_id],
              ])
            ->update(['inactive' => true,'end_date_active'=>Carbon::now(),'approval'=>false,'last_update_by'=>Auth::user()->id]);
        $distributor=DB::table('customers')->where('id','=',$request->distributor_id)->select('customer_name')->first();
        $customer =DB::table('customers')->where('id','=',$request->customer_id)->select('customer_name')->first();
        return redirect()->route('customer.show',['id'=>$request->user_id,'notif_id'=>$request->notif_id])->withMessage(trans("pesan.inactive",["dist"=>$distributor->customer_name,"cust"=>$customer->customer_name]));
      }elseif($request->inactive=="active"){
        DB::table('outlet_distributor')
            ->where([
              ['outlet_id','=',$request->customer_id],
              ['distributor_id','=',$request->distributor_id],
              ])
            ->update(['inactive' => false,'end_date_active'=>null,'approval'=>true,'last_update_by'=>Auth::user()->id]);
        $distributor=DB::table('customers')->where('id','=',$request->distributor_id)->select('customer_name')->first();
        $customer =DB::table('customers')->where('id','=',$request->customer_id)->select('customer_name')->first();
        return redirect()->route('customer.show',['id'=>$request->user_id,'notif_id'=>$request->notif_id])->withMessage(trans("pesan.distributoractive",["dist"=>$distributor->customer_name,"cust"=>$customer->customer_name]));
      }

    }

    public function searchCustomerOracle(Request $request)
    {
      $data = Customer::where("customer_number","LIKE",$request->input('query')."%")
            ->whereNotNull("oracle_customer_id")
            ->where('status','=','A')
            ->whereExists(function($query){
              $query->select(DB::raw(1))
                    ->from('outlet_distributor as od')
                    ->join('customers as dist', 'od.distributor_id','=','dist.id')
                    ->join('users as u','u.customer_id','=','dist.id')
                    ->join('role_user as ru','ru.user_id','=','u.id')
                    ->join('roles as r','r.id','=','ru.role_id')
                    ->whereraw("od.outlet_id= customers.id and r.name = 'Principal'");
            });

      $data = $data->select('id','customer_name','customer_number','oracle_customer_id','tax_reference');
      $data = $data->orderBy('customer_name','asc')->get();
      return response()->json($data);
    }

}
