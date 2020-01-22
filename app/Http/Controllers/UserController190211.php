<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use DB;
use Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use Webpatser\Uuid\Uuid;
use App\Notifications\InvitationUser;
use Auth;
use Datatables;
use Carbon\Carbon;
use Validator;
//use Session;


class UserController extends Controller
{
  protected $menu;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
      $this->menu="user";
    }
    public function index(Request $request)
    {
        $data = User::whereNull('customer_id')->orderBy('id','name')->get();
        return view('admin.user.index',['data'=>$data,'menu'=>$this->menu]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('display_name','id')->all();
        return view('admin.user.create',['roles'=>$roles,'menu'=>$this->menu]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['id']= Uuid::generate()->string;
        $input['password'] = Hash::make($input['password']);
        $input['validate_flag']=1;
        $input['register_flag']=1;
        $user = User::create($input);
        foreach ($request->input('roles') as $key => $value) {
            $user->attachRole($value);
        }
        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('admin.user.show',['user'=>$user,'menu'=>$this->menu]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        //$roles = Role::lists('display_name','id');
        $roles = Role::pluck('display_name','id');

        //$userRole = $user->roles->lists('id','id')->toArray();
        $userRole = $user->roles->pluck('id','id')->toArray();
        return view('admin.user.edit',['user'=>$user,'roles'=>$roles,'userRole'=>$userRole,'menu'=>$this->menu]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'roles' => 'required'
        ]);

        $input = $request->all();
        /*if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));
        }*/

        $user = User::find($id);
        $user->update($input);
        DB::table('role_user')->where('user_id',$id)->delete();

        foreach ($request->input('roles') as $key => $value) {
            $user->attachRole($value);
        }

        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }

    public function getListCity()
    {
        $id = Input::get('id');
        if(is_null($id)) $city = DB::table('regencies')->orderBy('name')->get();
        else $city = DB::table('regencies')->where('province_id','=',$id)->get();
        return Response::json($city);
    }

    public function getListDistrict()
    {
      $id = Input::get('id');
        $districts = DB::table('districts')->where('regency_id','=',$id)->get();
        return Response::json($districts);
    }

    public function getListSubdistrict()
    {
      $id = Input::get('id');
        $subdistricts = DB::table('villages')->where('district_id','=',$id)->get();
        return Response::json($subdistricts);
    }

    public function getkategoriOutlet()
    {
      $kategori = DB::table('category_outlets')->where('enable_flag','=','Y');
      $id = Input::get('id');
      if(!is_null($id)){
        $kategori =$kategori->whereNotExists(function ($query) use($id) {
                  $query->select(DB::raw(1))
                        ->from('distributor_mappings')
                        ->where('data','=','category_outlets')
                        ->whereRaw('distributor_mappings.data_id= category_outlets.id')
                        ->where('distributor_mappings.distributor_id','=',$id);
              });
      }
      $kategori=$kategori->select('id','name')->get();
      return Response::json($kategori);
    }

    public function oracleIndex()
    {
        $customers = Customer::whereNotNull('oracle_customer_id')
                    //->where('status','=','A')
                    ->orderBy('customer_name','asc')->get();
          /*$customers = DB::table('customers as c')->leftjoin('users as u','c.id','=','u.customer_id')->leftjoin('role_user as ru','u.id','=','ru.user_id')
                      ->leftjoin('roles as r','ru.role_id','=','r.id')
                      ->whereNotNull('oracle_customer_id')
                      ->where('c.status','=','A')
                      ->select('c.id','c.customer_name','c.customer_number','c.tax_reference','customer_category_code'
                              ,'c.customer_class_code','c.pharma_flag','c.psc_flag','c.export_flag','c.tollin_flag','c.subgroup_dc_id','u.email','ru.role_id','r.name as role_name')
                      ->orderBy('customer_name','asc')->get();*/
        return view('admin.oracle.customerindex',['customers'=>$customers,'menu'=>'customer-oracle']);
    }

    public function oracleShow($id)
    {
      DB::enableQueryLog();
        $groupid=null;
        $customer = Customer::whereNotNull('oracle_customer_id')//->where('status','=','A')
                    ->where('id','=',$id)
                    ->orderBy('customer_name','asc')->first();
        $provinces = DB::table('provinces')->get();
        if(isset($customer->subgroup_dc_id)) $groupid =  $customer->subgroupdc->group_id;
        $customer_sites = CustomerSite::where('customer_id','=',$id)->get();
        $customer_contacts = CustomerContact::where('customer_id','=',$id)->get();
        $roles = Role::whereIn('name',['Distributor','Distributor Cabang','Principal','Outlet','Apotik/Klinik'])->get();
        $group = DB::table('Group_DataCenters')->where('enabled_flag','1')->get();
        $categories = DB::table('category_outlets')->where('enable_flag','Y')->get();
        $principals = DB::table('users as u')->join('role_user as ru','ru.user_id','=','u.id')
                      ->join('roles as r','r.id','=','ru.role_id')
                      ->where('r.name','=','Principal')
                      ->select('u.name','u.id','u.customer_id')
                      ->get();
        $mappings = DB::table('distributor_mappings')
                    ->leftjoin('regencies as r',function($join){
              				$join->on('distributor_mappings.data_id','=','r.id');
              				$join->on('distributor_mappings.data','=',DB::raw("'regencies'"));
              			})
                    ->leftjoin('category_outlets as co',function($join){
              				$join->on('distributor_mappings.data_id','=','co.id');
              				$join->on('distributor_mappings.data','=',DB::raw("'category_outlets'"));
              			})
                    ->where('distributor_id','=',$id)
                    ->select('distributor_mappings.id','data',db::raw("case when data = 'regencies' then r.name else co.name end name"))
                    ->get();
        $dataijin = array(0=>'Tidak Berijin',1=>'Ijin PBF',2=>'Ijin Toko Obat');
        return view('admin.oracle.customershow',['customer'=>$customer,'customer_sites'=>$customer_sites
                                                ,'customer_contacts'=>$customer_contacts,'roles'=>$roles
                                                ,'principals'=>$principals,'mappings'=>$mappings
                                                ,'menu'=>'customer-oracle','groups'=>$group,'groupid'=>$groupid
                                                , 'provinces'=>$provinces,'categories'=>$categories,'dataijin'=>$dataijin
                                              ]);
    }

    public function oracleUpdate(Request $request,$id)
    {
      DB::beginTransaction();
      try{
        //dd($request->all());
        if($request->save_customer=="Send" or $request->save_customer=="Save")
        {
          $customer = Customer::whereNotNull('oracle_customer_id')//->where('status','=','A')
                      ->where('id','=',$id)
                      ->orderBy('customer_name','asc')->first();
          //dd($customer);
          if($request->ijin_pbf!=0)
          {
            $this->validate($request, [
                'noijin' => 'required',
                'masaberlaku' =>'required|date',
            ]);
            $customer->ijin_pbf=$request->ijin_pbf;
            $customer->no_ijin=$request->noijin;
            $customer->masa_berlaku=date('Y-m-d',strtotime($request->masaberlaku));
          }else{
            $customer->ijin_pbf=$request->ijin_pbf;
            $customer->no_ijin=null;
            $customer->masa_berlaku=null;
          }
          if($customer->customer_class_code=="OUTLET" and $request->psc_flag=="1")
          {
            $this->validate($request, [
                'subgroupdc' => 'required',
                'categoryoutlet' =>'required',
            ]);
            $customer->subgroup_dc_id = $request->subgroupdc;
            $customer->outlet_type_id = $request->categoryoutlet;
          }
          $customer->psc_flag = $request->psc_flag;
          $customer->pharma_flag = $request->pharma_flag;
          $customer->export_flag = $request->export_flag;
          $customer->tollin_flag = $request->tollin_flag;
          $customer->price_list_id = $request->price;
          if($request->status=="A")
          {
            $customer->status='A';
          }else $customer->status='I';
          $customer->save();
          if($request->distributor!="")
          {
            $customer->hasDistributor()->sync($request->distributor);
          }

          $usercustomer = User::where('customer_id','=',$customer->id)->first();

          if($usercustomer)
          {
              $this->validate($request, [
                  'email' => 'required|email|unique:users,email,'.$usercustomer->id
              ]);
              if($request->status=="A" and $usercustomer->register_flag==1) $usercustomer->validate_flag=1;
              elseif($request->status!="A" and $usercustomer->register_flag==1) $usercustomer->validate_flag=0;
              $usercustomer->email = $request->email;
              $usercustomer->save();
          }else{
            $this->validate($request, [
                'email' => 'required|email|unique:users'
            ]);
            $usercustomer = User::firstorCreate(
                              ['customer_id'=>$customer->id],
                              ['email'=>$request->email,'name'=>$request->customer_name
                              ,'api_token'=>str_random(60)
                              , 'id'=>Uuid::generate()->string]
                        );

          }
          if($request->role!="")
          {
            $usercustomer->roles()->sync($request->role);
          }
          DB::commit();
          if($request->save_customer=="Send")
          {
            $usercustomer->validate_flag=1;
            if(empty($usercustomer->api_token)) $usercustomer->api_token =str_random(60);
            $usercustomer->save();
            $usercustomer->notify(new InvitationUser($usercustomer));
            return redirect()->route('useroracle.show',$id)
                            ->with('success','Successfully Send Email');
          }elseif($request->save_customer=="Save"){
            return redirect()->route('useroracle.show',$id)
                            ->with('success','Customer Oracle updated successfully');
          }
        }elseif($request->action_mapping=="delete"){
          if(isset($request->mapping)){
            foreach($request->mapping as $key=>$value)
            {
              DB::enableQueryLog();
                $deletedata = DB::table('distributor_mappings')->whereraw("distributor_id='".$id."'")
                  ->whereraw("data='".$key."'")
                  ->whereIn('id',$value)
                  ->delete();
              //dd(DB::getQueryLog());
            }
            DB::commit();
            return redirect()->route('useroracle.show',$id)
                            ->with('success','Successfully delete data mapping');
          }else{
            return redirect()->back()->withInput()
                            ->withErrors('mapping','Pilih salah satu!');
          }
        }elseif($request->action_mapping == "delete-join")
        {
          $balikan = $this->DeleteMappingInclude($request);
          if($balikan) return redirect()->route('useroracle.show',$id)
                          ->with('success','Successfully delete data mapping');
        }
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

    }
    public function cabangCreate($parent_id)
    {
      $customer = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                  ->where('id','=',$parent_id)
                  ->orderBy('customer_name','asc')->first();
      $provinces = DB::table('provinces')->get();
      return view('admin.oracle.cabangcreate',['parent'=>$customer,'provinces'=>$provinces,'menu'=>'customer-cabang']);
    }


    public function cabangStore(Request $request,$parent_id)
    {
      $this->validate($request, [
          'customer_name' => 'required',
          'email' => 'required|email|unique:users,email',
          'address'=>'required',
          'province'=>'required',
          'city'=>'required',
          'district'=>'required',
          'subdistricts'=>'required',
      ]);
      $customer = Customer::where('customer_name','like',$request->customer_name)->first();
      if($customer)
      {
          return redirect()->back()->withErrors(['customer_name', trans('validation.unique')]);
      }else{
        $parentcustomer =Customer::where('id','=',$parent_id)->first();
        if($parentcustomer)
        {
          $newcustomer =  Customer::create(['customer_name'=>strtoupper($request->customer_name)
                                            ,'status'=>'A'
                                            ,'pharma_flag'=>$parentcustomer->pharma_flag
                                            ,'psc_flag'=>$parentcustomer->psc_flag
                                            ,'export_flag'=>$parentcustomer->export_flag
                                            ,'tollin_flag'=>$parentcustomer->tollin_flag
                                            ,'parent_dist'=>$parentcustomer->id
                                          ]);
          $province=DB::table('provinces')->where('id','=',$request->province)->first();
          $city=DB::table('regencies')->where('id','=',$request->city)->first();
          $district=DB::table('districts')->where('id','=',$request->district)->first();
          $state=DB::table('villages')->where('id','=',$request->subdistricts)->first();
          $newsite = CustomerSite::create(['site_use_code'=>'SHIP_TO'
                                          ,'primary_flag'=>'P'
                                          ,'status'=>'A'
                                          ,'address1'=>strtoupper($request->address)
                                          ,'state'=>$state->name
                                          ,'district'=>$district->name
                                          ,'city'=>$city->name
                                          ,'province'=>$province->name
                                          ,'province_id'=>$request->province
                                          ,'city_id'=>$request->city
                                          ,'district_id'=>$request->district
                                          ,'state_id'=>$request->subdistricts
                                          ,'postalcode'=>$request->postal_code
                                          ,'country'=>'ID'
                                          ,'customer_id'=>$newcustomer->id

          ]);
          $custcontact= new CustomerContact();
           $custcontact->contact_name = '';
           $custcontact->contact_type = 'EMAIL';
           $custcontact->contact =$request->email;
           $custcontact->customer_id=$newcustomer->id;
           $custcontact->save();
          $newuser = User::create(['id'=>Uuid::generate()->string
                                  ,'email'=> $request->email
                                  ,'name'=>strtoupper($request->customer_name)
                                  ,'customer_id'=>$newcustomer->id
                                  ,'api_token'=>str_random(60)
                                  ,'validate_flag'=>1
                    ]);
          //$newuser = User::where('id','=','ebf4e3f0-c6f9-11e7-9af5-8b1784dd6434')->first();
          $roleoutlet = Role::where('name','=','Distributor Cabang')->first();
          $newuser->roles()->attach($roleoutlet->id);
          $newuser->notify(new InvitationUser($newuser));
          return redirect()->route('useroracle.show',$parent_id)
                          ->with('message','Distributor Cabang berhasil ditambahkan dan email verifikasi telah dikirim.');
        }
      }
    }

    public function cabangEdit($id)
    {
      $distcabang = Customer::where('id','=',$id)->first();
      $alamat = CustomerSite::where([['customer_id','=',$id],['site_use_code','=','SHIP_TO']])->orderBy('created_at','asc')->first();
      $provinces = DB::table('provinces')->get();
      $regencies = DB::table('regencies')->where('province_id','=',$alamat->province_id)->get();
      $districts = DB::table('districts')->where('regency_id','=',$alamat->city_id)->get();
      $villages = DB::table('villages')->where('district_id','=',$alamat->district_id)->get();
      $roles = DB::table('roles')->whereIn('name',['Distributor','Distributor Cabang'])->get();
      return view('admin.oracle.cabangedit',['customer'=>$distcabang,'alamat'=>$alamat,'roles'=>$roles,'provinces'=>$provinces,'menu'=>'customer-cabang'
                  ,'regencies'=>$regencies,'districts'=>$districts,'villages'=>$villages
                    ]);
    }

    public function cabangUpdate(Request $request,$id)
    {
      if($request->save=="update")
      {
        DB::beginTransaction();
        try{
          $user = User::where('customer_id','=',$id)->first();
          $this->validate($request, [
              'customer_name' => 'required',
              'email' => 'required|email|unique:users,email,'.$user->id.',id',
              'address'=>'required',
              'province'=>'required',
              'city'=>'required',
              'district'=>'required',
              'subdistricts'=>'required',
          ]);
          $distcabang = Customer::where('id','=',$id)->first();
          $distcabang->customer_name = $request->customer_name;

          $province=DB::table('provinces')->where('id','=',$request->province)->first();
          $city=DB::table('regencies')->where('id','=',$request->city)->first();
          $district=DB::table('districts')->where('id','=',$request->district)->first();
          $state=DB::table('villages')->where('id','=',$request->subdistricts)->first();
          if($request->role==6 and !in_array($request->role,$user->roles->pluck('id')->toArray())){
            /*update dari Distributor Cabang ke Distributor*/
            $user->roles()->detach();
            $user->roles()->attach($request->role);
            $distpusat = Customer::where('id','=',$distcabang->parent_dist)->where('status','A')->first();
            if($distpusat){
              $distcabang->customer_number = $distpusat->customer_number;
              $distcabang->oracle_customer_id = $distpusat->oracle_customer_id;
              $distcabang->primary_salesrep_id = $distpusat->primary_salesrep_id;
              $distcabang->tax_reference = $distpusat->tax_reference;
              $distcabang->price_list_id = $distpusat->price_list_id;
              $distcabang->order_type_id = $distpusat->order_type_id;
              $distcabang->payment_term_name = $distpusat->payment_term_name;
              $distcabang->customer_class_code = $distpusat->customer_class_code;
              $distcabang->customer_category_code = $distpusat->customer_category_code;
              /*update customer site*/
              if($distpusat->sites()->count()>1)
              {
                  $shipto = $distpusat->sites()
                            ->where('site_use_code','=','SHIP_TO')
                            ->where('province_id','=',$request->province)
                            ->where('city_id','=',$request->city)
                            ->where('status','A')
                            ->get();
                  if($shipto->count()>=1){
                    $shipto_pusat = $shipto->first();
                    $alamat = CustomerSite::where([['customer_id','=',$id]
                                                  ,['site_use_code','=','SHIP_TO']
                                                  ,['id','=',$request->siteid]])
                            ->update(['province_id'=>$request->province
                                      ,'city_id'=>$request->city
                                      ,'district_id'=>$request->district
                                      ,'state_id'=>$request->subdistricts
                                      ,'address1'=>strtoupper($request->address)
                                      ,'state'=>$state->name
                                      ,'district'=>$district->name
                                      ,'city'=>$city->name
                                      ,'province'=>$province->name
                                      ,'postalcode'=>$request->postal_code
                                      ,'oracle_customer_id'=>$distpusat->oracle_customer_id
                                      ,'cust_acct_site_id'=>$shipto_pusat->cust_acct_site_id
                                      ,'site_use_id'=>$shipto_pusat->site_use_id
                                      ,'primary_flag'=>$shipto_pusat->primary_flag
                                      ,'status'=>$shipto_pusat->status
                                      ,'bill_to_site_use_id'=>$shipto_pusat->bill_to_site_use_id
                                      ,'payment_term_id'=>$shipto_pusat->payment_term_id
                                      ,'price_list_id'=>$shipto_pusat->price_list_id
                                      ,'order_type_id'=>$shipto_pusat->order_type_id
                                      ,'org_id'=>$shipto_pusat->org_id
                                      ,'warehouse'=>$shipto_pusat->warehouse
                                      ,'area'=>$shipto_pusat->area
                                      ,'last_update_by'=>Auth::user()->id
                            ]);
                    $billto = $distpusat->sites()
                                      ->where('site_use_code','=','BILL_TO')
                                      ->get();
                    if($billto->count()>1)
                    {
                      if (!is_null($shipto_pusat->bill_to_site_use_id))
                        $billto =$billto->where('site_use_id',$shipto_pusat->bill_to_site_use_id)->get();
                      else
                        $billto =$billto->where('province_id',$request->province)
                                ->where('city_id',$request->city)
                                ->get();
                    }
                    if($billto->count()>=1) $billto=$billto->first();
                    $newsite = CustomerSite::create(['site_use_code'=>$billto->site_use_code
                                                    ,'primary_flag'=>$billto->primary_flag
                                                    ,'status'=>$billto->status
                                                    ,'address1'=>strtoupper($billto->address1)
                                                    ,'state'=>$billto->state
                                                    ,'district'=>$billto->district
                                                    ,'city'=>$billto->city
                                                    ,'province'=>$billto->province
                                                    ,'province_id'=>$billto->province_id
                                                    ,'city_id'=>$billto->city_id
                                                    ,'district_id'=>$billto->district_id
                                                    ,'state_id'=>$billto->state_id
                                                    ,'postalcode'=>$billto->postalcode
                                                    ,'country'=>$billto->country
                                                    ,'customer_id'=>$id
                                                    ,'oracle_customer_id'=>$billto->oracle_customer_id
                                                    ,'cust_acct_site_id'=>$billto->cust_acct_site_id
                                                    ,'site_use_id'=>$billto->site_use_id
                                                    ,'payment_term_id'=>$billto->payment_term_id
                                                    ,'price_list_id'=>$billto->price_list_id
                                                    ,'order_type_id'=>$billto->order_type_id
                                                    ,'org_id'=>$billto->org_id
                                                    ,'warehouse'=>$billto->warehouse
                                                    ,'area'=>$billto->area
                                                    ,'langitude'=>$billto->langitude
                                                    ,'longitude'=>$billto->longitude

                    ]);
                  }
              }
              if($distpusat->hasDistributor()->count()>0)
              {
                //$mappingdistributor =$distpusat->hasDistributor;
                $distributor = DB::table('outlet_distributor')->where('outlet_id','=',$distpusat->id)->select('distributor_id')->get();

                if($distributor) $distcabang->hasDistributor()->attach($distributor->pluck('distributor_id')->toArray());
              }
            }
          }else{
            $alamat = CustomerSite::where([['customer_id','=',$id],['id','=',$request->siteid]])
                    ->update(['province_id'=>$request->province
                              ,'city_id'=>$request->city
                              ,'district_id'=>$request->district
                              ,'state_id'=>$request->subdistricts
                              ,'address1'=>strtoupper($request->address)
                              ,'state'=>$state->name
                              ,'district'=>$district->name
                              ,'city'=>$city->name
                              ,'province'=>$province->name
                              ,'postalcode'=>$request->postal_code
                              ,'last_update_by'=>Auth::user()->id
                    ]);
          }
          $distcabang->save();


          if ($user->email!=$request->email)
          {
            $user = User::where('customer_id','=',$id)
                      ->update(['email'=>$request->email,'last_update_by'=>Auth::user()->id]);
          }
          DB::commit();
        }catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }
        return redirect()->route('usercabang.edit',$id)->with('message','Successfully edit data');
      }elseif($request->action_mapping == "delete")
      {
        DB::beginTransaction();
        try{
          if(isset($request->mapping)){
            foreach($request->mapping as $key=>$value)
            {
              DB::enableQueryLog();
                $deletedata = DB::table('distributor_mappings')->whereraw("distributor_id='".$id."'")
                  ->whereraw("data='".$key."'")
                  ->whereIn('id',$value)
                  ->delete();
              //dd(DB::getQueryLog());
            }
            DB::commit();
            return redirect()->route('usercabang.edit',$id)
                            ->with('message','Successfully delete data mapping');
          }else{
            return redirect()->back()->withInput()
                            ->withErrors('mapping','Pilih salah satu!');
          }
        }catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }
      }

    }

    public function DeleteMappingInclude(Request $request){
      //dd($request);
      $delete = DB::table('distributor_mapping_include')
      ->whereIn('id',$request->kombinasi)
      ->where('distributor_id',$request->customer_id)
      ->delete();
      DB::commit();
      if($delete) return true;else return false;
    }

    public function CustYasaNonOracle()
    {
      $customers = Customer::join('outlet_distributor as od','od.outlet_id','=','customers.id')
                ->join('customers as yasa','od.distributor_id','=','yasa.id')
                ->where('yasa.customer_number','=',config('constant.customer_yasa'))
                ->whereNull('customers.oracle_customer_id')
                ->where('customers.status','=','A')
                ->select ('customers.*')
                ->get();
      return view('admin.oracle.CustYasaNonOracle',['customers'=>$customers,'menu'=>'custYasa']);
    }

    public function mergeCustomer(Request $request,$id)
    {
      if($request->save=="save")
      {
        DB::beginTransaction();
        try{
          $user = User::find($id);
          if(Auth::user()->hasRole('IT Galenium'))
          {
            $oldcustomer=$user->customer_id;
            if(isset($request->c_number))
            {
              $oraclecustomer = Customer::leftjoin('users','customers.id','=','users.customer_id')
                              ->where('customer_number','=',$request->c_number)
                              ->select('customers.*','users.email',DB::raw("ifnull('users.register_flag',0) as register_flag"))
                              ->first();
              if($oraclecustomer)
              {
                if($oraclecustomer->register_flag==0){
                  /*update all po outlet_id*/
                  DB::table('po_draft_headers')->where('customer_id','=',$oldcustomer)->update(['customer_id'=>$oraclecustomer->id]);
                  $soheaders = DB::table('so_headers')->where('customer_id','=',$oldcustomer)
                    //->whereIn('status',[0,1])
                    ->select('cust_ship_to','cust_bill_to','customer_id')
                    ->groupBy('cust_ship_to','cust_bill_to','customer_id')
                    ->get();
                  foreach($soheaders as $sh)
                  {
                    $orasitebill=null;
                    $orasiteship=null;
                    $shipto =CustomerSite::where('site_use_code','SHIP_TO')
                                ->where('customer_id',$sh->customer_id)
                                ->where('id',$sh->cust_ship_to)
                                ->first();

                    $billto =CustomerSite::where('site_use_code','BILL_TO')
                                ->where('customer_id',$sh->customer_id)
                                ->where('id',$sh->cust_bill_to)
                                ->first();

                    if($shipto){
                      $orasiteship =CustomerSite::where('customer_id',$oraclecustomer->id)
                                ->where('site_use_code','=',$shipto->site_use_code)
                                ->where('province','=',$shipto->province)
                                ->where('city','=',$shipto->city)
                                ->where('district','=',$shipto->district)
                                ->first();

                    }

                    if($billto){
                      $orasitebill =CustomerSite::where('customer_id',$oraclecustomer->id)
                                ->where('site_use_code','=',$billto->site_use_code)
                                ->where('province','=',$billto->province)
                                ->where('city','=',$billto->city)
                                ->where('district','=',$billto->district)
                                ->first();

                    }
                    if($orasitebill and $orasiteship){
                      DB::table('so_headers')->where('customer_id','=',$sh->customer_id)
                      ->where('cust_ship_to','=',$sh->cust_ship_to)
                      ->where('cust_bill_to','=',$sh->cust_bill_to)
                      ->update(['customer_id'=>$oraclecustomer->id
                                ,'cust_ship_to'=>$orasiteship->id
                                ,'cust_bill_to'=>$orasitebill->id
                                ,'price_list_id'=>$orasitebill->price_list_id
                                ,'payment_term_id'=>$orasitebill->payment_term_id
                                ,'oracle_ship_to'=>$orasiteship->site_use_id
                                ,'oracle_bill_to'=>$orasitebill->site_use_id
                                ,'oracle_customer_id'=>$oraclecustomer->oracle_customer_id
                                ,'last_update_by'=>Auth::user()->id
                              ]);
                    }
                  }


                  /*attach to all outlet_distributor*/
                  $distributor = DB::table('outlet_distributor')->where('outlet_id','=',$oldcustomer)->select('distributor_id')->get();

                  if($distributor) $oraclecustomer->hasDistributor()->sync($distributor->pluck('distributor_id')->toArray());
                  /*product stock jika role Apotik/Klinik*/
                  if($user->hasRole('Apotik/Klinik')){
                    DB::table('outlet_products')->where('outlet_id','=',$oldcustomer)->update(['outlet_id'=>$oraclecustomer->id,'last_update_by'=>Auth::user()->id]);
                    DB::table('outlet_stock')->where('outlet_id','=',$oldcustomer)->update(['outlet_id'=>$oraclecustomer->id,'last_update_by'=>Auth::user()->id]);
                  }
                  /*Inactive old customer_id*/
                  $dataoldcustomer = Customer::where('id','=',$oldcustomer)->first();
                  $dataoldcustomer->status = 'I';
                  $dataoldcustomer->save();
                  $oraclecustomer->longitude = $dataoldcustomer->longitude;
                  $oraclecustomer->langitude = $dataoldcustomer->langitude;
                  $oraclecustomer->psc_flag = $dataoldcustomer->psc_flag;
                  $oraclecustomer->pharma_flag = $dataoldcustomer->pharma_flag;
                  $oraclecustomer->outlet_type_id = $dataoldcustomer->outlet_type_id;
                  $oraclecustomer->subgroup_dc_id = $dataoldcustomer->subgroup_dc_id;
                  $oraclecustomer->save();
                  $useroracle=User::where('customer_id','=',$oraclecustomer->id)->where('register_flag','=','0')->delete();

                  $user->customer_id = $oraclecustomer->id;
                  $user->save();
                  DB::commit();
                  return redirect()->route('useroracle.show',['id'=>$id])->withMessage('Berhasil dimerge!');
                }else{
                  return redirect()->back()->withInput()->withErrors(['c_number'=>'Customer number already has registered to another user']);
                }
              }
            }else{
              return redirect()->back()->withInput()->withMessage('Customer Number Oracle harus diisi!');
            }
          }

        }catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }
      }
    }

    public function ajaxAddMappingType(Request $request)
    {
       $error_array = array();
       $success_output = '';
      if($request->get('button_action')=="add")
      {
        if($request->get('jenis')=="cross"){
          $customerid=$request->get('customerid');
          $datatype = $request->get('type');
          foreach ($request->value as $nil){
            $insert = DB::table("distributor_mappings")
                      ->insert(['distributor_id'=>$customerid
                                ,'data'=>$datatype
                                ,'data_id'=>$nil
                                ,'created_at'=>Carbon::now()
                                ,'updated_at'=>Carbon::now()
                                ,'created_by'=>Auth::user()->id
                              ]);
          }
          $success_output = '<div class="alert alert-success">Data Inserted</div>';
        }elseif($request->get('jenis')=="kombinasi"){
          $validator = Validator::make($request->all(), [
            'customerid' => 'required',
            'category' => 'required',
            'value'=>'required',
            ]);
            //dd($validator->errors());
          if ($validator->fails()) {
                $error_array = $validator->errors();//->toArray();
                $output = array(
                      'error'     =>  $error_array,
                      'success'   =>  ''
                  );
                //return Response::json($output);
                return json_encode($output);
          }
          foreach ($request->value as $nil){
            $exists = DB::table('distributor_mapping_include')->where('distributor_id',$request->get('customerid'))
                      ->where('category_id',$request->get('category'))
                      ->where('regency_id',$nil)
                      ->first();
            $x=0;
            if(!$exists){
              $insert = DB::table('distributor_mapping_include')
                        ->insert([
                          'distributor_id'=>$request->get('customerid')
                          ,'category_id'=>$request->get('category')
                          ,'regency_id'=>$nil
                          ,'created_by'=>Auth::user()->id
                          ,'last_update_by'=>Auth::user()->id
                        ]);
              $x+=1;
            }
          }
          if($x>0) $success_output = '<div class="alert alert-success">Data Inserted</div>';
        }
      }
      $output = array(
            'error'     =>  $error_array,
            'success'   =>  $success_output
        );
      echo json_encode($output);

    }

    public function ajaxGetMappingType($id=null)
    {
      $mappings = DB::table('distributor_mappings')
                  ->leftjoin('regencies as r',function($join){
                    $join->on('distributor_mappings.data_id','=','r.id');
                    $join->on('distributor_mappings.data','=',DB::raw("'regencies'"));
                  })
                  ->leftjoin('category_outlets as co',function($join){
                    $join->on('distributor_mappings.data_id','=','co.id');
                    $join->on('distributor_mappings.data','=',DB::raw("'category_outlets'"));
                  });
      if(!is_null($id))
      $mappings =$mappings->where('distributor_id','=',$id);

      $mappings =$mappings->select('distributor_mappings.id','data',db::raw("case when data = 'regencies' then r.name else co.name end name"));

      return Datatables::of($mappings)
            ->editColumn('id',function($mappings){
              return '<input type="checkbox" class="chk-mapping" name="mapping['.$mappings->data.'][]" value='.$mappings->id.'>';
            })->rawColumns(['id'])
            ->make(true);
    }

    public function ajaxGetMappingInclude($id=null)
    {
      $mappings = DB::table('distributor_mapping_include')
                  ->join('regencies as r','distributor_mapping_include.regency_id','=','r.id')
                  ->join('category_outlets as co','distributor_mapping_include.category_id','=','co.id')
                  ->join('provinces as p','p.id','=','r.province_id');

      if(!is_null($id))
      $mappings =$mappings->where('distributor_id','=',$id);

      $mappings =$mappings->select('distributor_mapping_include.id','co.name as category','p.name as province','r.name as regency');

      return Datatables::of($mappings)
            ->editColumn('id',function($mappings){
              return '<input type="checkbox" class="chk-mapping1" name="kombinasi[]" value='.$mappings->id.'>';
            })->rawColumns(['id'])
            ->make(true);
    }

    public function MappingOutletDistributor($id=null)
    {
      DB::enableQueryLog();
      if($id!=null)
      {
        $dists= Customer::where('id','=',$id)
              ->select('customers.id',DB::Raw("ifnull(customers.psc_flag,0) as psc_flag"),DB::Raw("ifnull(customers.pharma_flag,0) as pharma_flag"),'customers.outlet_type_id','customer_number','customer_name')
              ->get();
      }else{
        $dists = Customer::join('users as u','u.customer_id','customers.id' )
              ->leftjoin('role_user as ru','ru.user_id','=','u.id')
              ->leftjoin('roles as r','ru.role_id','=','r.id')
              ->where('customers.status','=','A')
              ->whereIn('r.name',['Distributor','Distributor Cabang','Principal'])
              ->select('customers.id',DB::Raw("ifnull(customers.psc_flag,0) as psc_flag"),DB::Raw("ifnull(customers.pharma_flag,0) as pharma_flag"),'customers.outlet_type_id','customer_number','customer_name')
              ->get();
      }
      foreach ($dists as $dist)
      {
          if($dist->customer_number==config('constant.customer_yasa')) $dist->pharma_flag=0;
          $mappings = DB::table('distributor_mappings')
                ->where('distributor_id','=',$dist->id)
                ->select('data','data_id')->groupBy('data','data_id')->get();
          $data = customer::leftjoin('category_outlets as co','customers.outlet_type_id','co.id')
            ->whereExists(function ($query2){
            $query2->select(DB::raw(1))
                  ->from('users as u')
                  ->leftjoin('role_user as ru','ru.user_id','u.id')
                  ->leftjoin('roles as r','r.id','ru.role_id')
                  ->whereraw("u.customer_id=customers.id")
                  ->whereIn('r.name',['Outlet','Apotik/Klinik']);
                });
          if($dist->psc_flag=="1" and $dist->pharma_flag=="1")
          {
            $data=$data->where('psc_flag','=',$dist->psc_flag)->orwhere('pharma_flag','=',$dist->pharma_flag);
          }elseif($dist->psc_flag=="1") {
            $data=$data->whereraw("ifnull(psc_flag,0) = '".$dist->psc_flag."'");
          }elseif($dist->pharma_flag=="1") {
            $data=$data->whereraw("ifnull(pharma_flag,0)='".$dist->pharma_flag."'");
          }else{
            $data=$data->where('psc_flag','=',$dist->psc_flag)->where('pharma_flag','=',$dist->pharma_flag);
          }
          $data = $data->where (function($query) use ($dist, $mappings){
              $query->whereRaw("exists (select 1 from distributor_mapping_include as dmi,customer_sites as cs
                                where cs.customer_id = customers.id
                                  and dmi.category_id =customers.outlet_type_id and dmi.regency_id = cs.city_id
                                  and dmi.distributor_id = '".$dist->id."')");
              $query->orwhere(function ($query2) use  ($mappings){

              foreach($mappings->groupBy('data') as $key=>$values)
              {
                  $map1 = $values->pluck('data_id')->toArray();
                  //$map1 = $mappings->where('data',$key)->pluck('data_id')->toArray();
                  if($key == "regencies")
                  {
                    $query2 = $query2->whereExists(function ($query3) use($map1) {
                          $query3->select(DB::raw(1))
                                ->from('customer_sites as cs')
                                ->whereraw('cs.customer_id=customers.id')
                                ->whereIn('cs.city_id',$map1);
                    });
                  }elseif($key=="category_outlets"){
                    $query2 = $query2->whereIn('customers.outlet_type_id',$map1);
                  }
              }
            });
          });
          $data=$data->select('customers.*','co.name as cat_name')->get();
          //dd(DB::getQueryLog());
          foreach($data as $d)
          {
            $d->ada='T';
            if($d->hasDistributor->where('id',$dist->id)->count()>0){
              $d->ada='Y';
            }
          }
          $dist->mapping = $data;
          $dist->delete_mapping = collect([]);
          $oldoutlet =$dist->hasOutlet;
          $oldoutlet = $oldoutlet->filter(function ($value, $key) {
                        return ($value->customer_class_code ==null or $value->customer_class_code=="OUTLET");
                    });
          if($oldoutlet->count()>0 and $data->count()>0)
          {
            $deletemapping = $oldoutlet->whereNotIn('id',$data->pluck('id')->unique());
            if($deletemapping->count()>0)
            {
              $dist->delete_mapping = $deletemapping;
            }
          }
      }
      $menu = "";
      return view('admin.oracle.mappingoutlet',compact('id','dists','menu','deletemapping'));

    }

    public function remappingOutlet(Request $request, $id=null)
    {
      DB::beginTransaction();
      try{
        if(is_array($request->delarray))
        {
          foreach($request->delarray as $keydist=>$dist)
          {
            $distributor = Customer::where('id','=',$keydist)
                          ->whereExists(function ($query2){
                              $query2->select(DB::raw(1))
                                    ->from('users as u')
                                    ->leftjoin('role_user as ru','ru.user_id','u.id')
                                    ->leftjoin('roles as r','r.id','ru.role_id')
                                    ->whereraw("u.customer_id=customers.id")
                                    ->whereIn('r.name',['Principal','Distributor','Distributor Cabang']);
                                  })
                          ->first();
            if(is_array($dist) and $distributor)
            {
              foreach($dist as $keyoutlet=>$outlet)
              {
                echo "delete:".$keydist."-".$outlet."<br>";
                $distributor->hasOutlet()->detach($outlet);
              }
            }
          }
        }
        if(is_array($request->insertarray))
        {
          foreach($request->insertarray as $keydist=>$dist)
          {
            $distributor = Customer::where('id','=',$keydist)
                          ->whereExists(function ($query2){
                              $query2->select(DB::raw(1))
                                    ->from('users as u')
                                    ->leftjoin('role_user as ru','ru.user_id','u.id')
                                    ->leftjoin('roles as r','r.id','ru.role_id')
                                    ->whereraw("u.customer_id=customers.id")
                                    ->whereIn('r.name',['Principal','Distributor','Distributor Cabang']);
                                  })
                          ->first();
            if(is_array($dist) and $distributor)
            {
              foreach($dist as $keyoutlet=>$outlet)
              {
                $distributor->hasOutlet()->attach($outlet);
                echo "insert:".$keydist."-".$outlet."<br>";
              }
            }
          }
        }
        DB::commit();
        return redirect()->route('customer.mappingOutlet',$id)->with('success','Successfull ReMapping Distributor-Outlet');
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

    }

    public function sendEmailInvitation()
    {
      $allcustomer =User::wherenotNull('customer_id')
                  ->whereExists(function ($query){
                    $query->select(DB::raw(1))
                        ->from('role_user as ru')
                        ->join('roles as r','ru.role_id','r.id')
                        ->whereraw('ru.user_id = users.id')
                        ->whereIn('r.name',['Distributor','Distributor Cabang']);
                  })->where('validate_flag','0')
                  ->where('register_flag','0')
                  ->get();
      foreach($allcustomer as $usercustomer)
      {
        $usercustomer->validate_flag=1;
        $usercustomer->api_token =str_random(60);
        $usercustomer->save();
        $usercustomer->notify(new InvitationUser($usercustomer));
        echo $usercustomer->name."-".$usercustomer->email."berhasil dikirim<br>";
      }
    }

    public function logOn($id)
    {
      $new_user = User::where('id',$id)->first();
      //Session::put( 'orig_user', Auth::id() );
      Auth::login( $new_user );
      return $this->redirecthome();
    }

    public function redirecthome()
    {
      if(Auth::user()->hasRole('IT Galenium')) {
          return redirect('/admin');
      }elseif(Auth::user()->hasRole('Distributor') || Auth::user()->hasRole('Outlet') || Auth::user()->hasRole('Apotik/Klinik') ) {
            return redirect('/product/buy');
      }elseif(Auth::user()->hasRole('KurirGPL'))      {
            return redirect()->route('order.shippingSO');
      }else{/*if(Auth::user()->hasRole('Marketing PSC') || Auth::user()->hasRole('Marketing Pharma')) {*/
          return redirect('/home');
      }
    }

}
