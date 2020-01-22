<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use App\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Mail;
use App\Notifications\VerificationUser;
//use App\Notifications\MarketingGaleniumNotif;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;
use Webpatser\Uuid\Uuid;
use DB;
use Image;
use File;
use App\CategoryOutlet;
use Illuminate\Auth\Events\Registered;
use App\GroupDatacenter;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'address' => 'required|string|max:255',
            'province' => 'required',
            'city' => 'required|string|max:100',
            //'subdistricts' => 'required',
            'district' => 'required',
//            'postal_code' => 'required|digits:5',
            'HP_1' => 'required|regex:/(8)[0-9]{8,}/',
            'HP_2' => 'nullable|regex:/(8)[0-9]{8,}/',
            'no_tlpn' => 'nullable|regex:/[0-9]{9}/',
            'category' => 'required',
        ]);
    }

    protected function verification(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            //'langitude'=> 'required|numeric',
            //'longitude'=> 'required|numeric',
            'token1' => 'required|string',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
      //$customer->save();
        return User::create([
            'id' => Uuid::generate()->string,
            'name' => $data['name'],
            'email' => $data['email'],
            //'password' => bcrypt($data['password']),
            'api_token' => str_random(60),
        ]);
    }

    protected function update(array $data)
    {
       $user_check = User::where('email', $data['email'])->where('api_token',$data['token1'])->first();
       if (!$user_check) {
           return back()->with('status', trans('auth.failed'));
       }
       if($user_check->register_flag==0)
       {
         if (isset($user_check->customer_id)){
            $customer = Customer::find($user_check->customer_id);
            $groupdc = $customer->outlet_type_id;
            /*if($customer->subgroupdc){
                $groupdc =$customer->subgroupdc->groupdatacenter->id;
            }else $groupdc = null;*/


            $sites = $customer->sites()->where('primary_flag','=','Y')->first();
            if($sites)
            {
              $city = $sites->city_id;
            }else{
              $city = null;
            }
            $customer->longitude = $data['longitude'];
            $customer->langitude = $data['langitude'];
            $customer->last_update_by = $user_check->id;
            $customer->save();
            /*getdistributor*/
            if(is_null($customer->oracle_customer_id)){
              if($customer->psc_flag=="1" and $customer->pharma_flag=="1")
              {
                  $distributorpsc = $this->mappingDistributor($groupdc,$city,'PSC');
                  $distributorpharma = $this->mappingDistributor($groupdc,$city,'PHARMA');
                  $distributor = $distributorpsc->union($distributorpharma);
              }elseif($customer->psc_flag=="1"){
                $distributor = $this->mappingDistributor($groupdc,$city,'PSC');
              }elseif($customer->pharma_flag=="1"){
                $distributor = $this->mappingDistributor($groupdc,$city,'PHARMA');
              }

              $distributor = $distributor->get();
		          if($distributor)
              {
                $customer->hasDistributor()->attach($distributor->pluck('id')->toArray(),['created_by'=>$user_check->id,'last_update_by'=>$user_check->id]);
                $customeryasa=$distributor->where('customer_number','=',config('constant.customer_yasa'))->first();
                if($customeryasa)
                {
                  $shipto=$customer->sites()->where('site_use_code','=','SHIP_TO')->where('primary_flag','=','Y')
                          ->where('status','=','A')
                          ->first();
                  $billto=$customer->sites()->where('site_use_code','=','BILL_TO')->where('primary_flag','=','Y')
                          ->where('status','=','A')
                          ->first();
                  if($shipto and !$billto){
                    $sitesbillto = new CustomerSite();
                    $sitesbillto->oracle_customer_id = $shipto->oracle_customer_id;
                    $sitesbillto->cust_acct_site_id = $shipto->cust_acct_site_id;
                    $sitesbillto->site_use_id = $shipto->site_use_id;
                    $sitesbillto->site_use_code = "BILL_TO";
                    $sitesbillto->primary_flag='Y';
                    $sitesbillto->status = "A";
                    $sitesbillto->address1 = $shipto->address1;
                    $sitesbillto->state = $shipto->state;
                    $sitesbillto->district = $shipto->district;
                    $sitesbillto->city = $shipto->city;
                    $sitesbillto->province = $shipto->province;
                    $sitesbillto->postalcode = $shipto->postal_code;
                    $sitesbillto->Country = 'ID';
                    $sitesbillto->customer_id = $shipto->customer_id;
                    $sitesbillto->province_id = $shipto->province_id;
                    $sitesbillto->city_id=$shipto->city_id;
                    $sitesbillto->district_id=$shipto->district_id;
                    $sitesbillto->state_id=$shipto->state_id;
                    $sitesbillto->area=$shipto->area;
                    $sitesbillto->created_by = $user_check->id;
                    $sitesbillto->last_update_by = $user_check->id;
                    $sitesbillto->save();
                  }
                  $usernotif= User::where('customer_id','=',$customeryasa->id)
                        ->orwhereExists(function ($query) {
                                $query->select(DB::raw(1))
                                      ->from('role_user as ru')
                                      ->join('roles as r','ru.role_id','=','r.id')
                                      ->whereRaw("ru.user_id = users.id and r.name = 'IT Galenium'");
                            })->get();
                  //dd($usernotif);
                  $content="<strong>".$customer->customer_name."</strong> telah mendaftar melalui aplikasi ".config('app.name'). ". Harap mappingkan segera dengan data outlet yang ada pada master Customer Oracle.<br>";
                  $data_email = [
                    'title'=> 'Register Outlet',
            				'message' => 'Pendaftaran outlet baru: '.$customer->customer_name,
            				'id' => $user_check->id,
            				'href' => route('customer.show')
                    ,'mail' => [ 'greeting'=> "Pendaftaran Outlet Baru di ". config('app.name'),
                                    'content' => $content,
                            		]
            			];

                  foreach($usernotif as $u)
                  {
                    $data_email ['email']= $u->email;
                    $u->notify(new PushNotif($data_email));
                  }
                }
              }
            }
         }

         $user_check->password = bcrypt($data['password']);
         $user_check->register_flag=1;
         $user_check->first_login = date('Y-m-d H:i:s');
         $user_check->api_token='';

         if($user_check->roles->count()==0)
         {
           $roleoutlet = Role::where('name','=','Outlet')->first();
           $user_check->roles()->attach($roleoutlet->id);
         }
         $user_check->last_update_by=$user_check->id;
         $user_check->save();
         return $user_check;
       }else{
         //return back()->with('status', trans('auth.alreadyregistered'));
         return $user_check;
       }

    }

    protected function register(Request $request)
    {
      $input = $request->all();


      $validator = $this->validator($input)->validate();
      if ($request->psc=="" and $request->pharma==""){
        return redirect(route('register'))->withErrors(['psc'=> [trans('auth.pscflag')]])->withInput();
      }

      $customer = Customer::create([
          'customer_name' => strtoupper($request->name),
          'status' => 'A',
          'tax_reference' => $request->NPWP,
          'pharma_flag' => $request->pharma,
          'psc_flag' => $request->psc,
          'outlet_type_id' => $request->category,
          'subgroup_dc_id' => $request->subgroupdc
        ]);
        /*if($request->psc=="1")
        {
          $customer->price_list_id = config('constant.price_rbp')
        }elseif($request->pharma=="1")
        {
          $customer->price_list_id = config('constant.price_hna')
        }*/
       $customer->save();

       $province=DB::table('provinces')->where('id','=',$request->province)->first();
       $city=DB::table('regencies')->where('id','=',$request->city)->first();
       $district=DB::table('districts')->where('id','=',$request->district)->first();
       $state=DB::table('villages')->where('id','=',$request->subdistricts)->first();


       $custsites = new CustomerSite();
       $custsites->site_use_code = "SHIP_TO";
       $custsites->status = "A";
       $custsites->address1 = strtoupper($request->address);
       $custsites->province = $province->name;
       $custsites->city = $city->name;
       $custsites->district = $district->name;
       if($state){
         $custsites->state = $state->name;
       }
       $custsites->postalcode = $request->postal_code;
       $custsites->Country = 'ID';
       $custsites->customer_id = $customer->id;
       $custsites->primary_flag='Y';
       $custsites->province_id = $request->province;
       $custsites->city_id=$request->city;
       $custsites->district_id=$request->district;
       $custsites->state_id=$request->subdistricts;
       $custsites->save();

       $custcontact= new CustomerContact();
        $custcontact->contact_name = $request->contact_person;
        $custcontact->contact_type = 'EMAIL';
        $custcontact->contact =$request->email;
        $custcontact->customer_id=$customer->id;
        $custcontact->save();

        $custcontact1= new CustomerContact();
        $custcontact1->contact_name = $request->contact_person;
        $custcontact1->contact_type = 'PHONE';
        $custcontact1->contact ='+62'.$request->HP_1;
        $custcontact1->customer_id=$customer->id;
        $custcontact1->save();

        if(isset($request->HP_2)){
            $custcontact2= new CustomerContact();
            $custcontact2->contact_name = $request->contact_person;
            $custcontact2->contact_type = 'PHONE';
            $custcontact2->contact ='+62'.$request->HP_2;
            $custcontact2->customer_id=$customer->id;
            $custcontact2->save();
        }
        if(isset($request->no_tlpn)){
             $custcontact3= new CustomerContact();
             $custcontact3->contact_name = $request->contact_person;
             $custcontact3->contact_type = 'PHONE';
             $custcontact3->contact ='+62'.$request->no_tlpn;
             $custcontact3->customer_id=$customer->id;
             $custcontact3->save();
        }

      $user= new User();
      $user->id = Uuid::generate()->string;
      $user->name = strtoupper($request->name);
      $user->email = $request->email;
      $user->created_by = $user->id;
      $user->last_update_by= $user->id;

      $user->api_token =str_random(60);
      $customer->users()->save($user);

      $customer->created_by=$user->id;
      $customer->last_update_by=$user->id;
      $customer->save();
      $custsites->created_by=$user->id;
      $custsites->last_update_by=$user->id;
      $custsites->save();
      $updatecontact = CustomerContact::where('customer_id',$customer->id)->update(['created_by'=>$user->id,'last_update_by'=>$user->id]);

      $data = $user->toArray();
      $user->notify(new VerificationUser($user));
        return redirect(route('login'))->with('status',trans("passwords.confirm"));
    }

    public function mappingDistributor($groupdc,$city,$tipe)
    {
      DB::enableQueryLog();
      $distributor = DB::table('customers as c')
              ->join('users as u','c.id','=','u.customer_id')
              ->join('role_user as ru','u.id','=','ru.user_id')
              ->join('roles as r','ru.role_id','=','r.id')
              //->leftjoin ('distributor_groupdc as dg','c.id','=', 'dg.distributor_id')
              ->select('c.id','c.customer_name','c.customer_number')
              ->where([
                ['u.register_flag','=',true],
                ['c.status','=','A'],
              ])->whereIn('r.name',['Distributor','Distributor Cabang','Principal']);
      if($tipe=="PSC"){
        $distributor = $distributor->where([
          ['c.psc_flag','=',"1"],
        //  ['dg.group_id','=',$groupdc]
        ])/*->whereRaw("((not exists (select 1 from distributor_regency as dr where c.id=dr.distributor_id)
                  	  or exists (select 1 from distributor_regency as dr where c.id=dr.distributor_id and dr.regency_id = '".$city."')
                       ))")*/;

      }
      if($tipe=="PHARMA"){
        $distributor = $distributor->where([
          ['c.pharma_flag','=',"1"],
          ['r.name','!=','Principal']
        ]);/*->whereNull('dg.group_id')
          ->whereRaw("exists (select 1 from distributor_regency as dr where c.id = dr.distributor_id and dr.regency_id = '".$city."')");*/
      }
      $dist_id = $distributor->get()->pluck('id')->toArray();

      $groupmapping = DB::table("distributor_mappings")
                    ->whereIn('distributor_id',$dist_id)
                    ->groupBy('data')
                    ->select('data')
                    ->get();
      $distributor = $distributor->where (function($query) use ($groupdc, $city,$groupmapping){
          $query->whereRaw("exists (select 1 from distributor_mapping_include as dmi where dmi.distributor_id=c.id
                                                        and dmi.category_id ='".$groupdc."' and dmi.regency_id = '".$city."')");
          $query->orwhere(function ($query2) use  ($groupdc, $city,$groupmapping){
            foreach($groupmapping as $mapping)
            {
              if($mapping->data=="regencies")
              {
                $query2->whereRaw("exists (select 1 from distributor_mappings where c.id = distributor_mappings.distributor_id
                                        and distributor_mappings.data='".$mapping->data."' and distributor_mappings.data_id ='".$city."')");
              }elseif($mapping->data=="category_outlets"){
                $query2 ->whereRaw("exists (select 1 from distributor_mappings where c.id = distributor_mappings.distributor_id
                                        and distributor_mappings.data='".$mapping->data."' and distributor_mappings.data_id ='".$groupdc."')");
              }
            }
          });

      });
      return $distributor;
    }


    public function confirmation($token)
    {
      $user = User::where('api_token',$token)->first();
      //dd($user->customer->psc_flag);
      if ($user){
        if($user->validate_flag=="0"){
          $marketings = User::whereHas('roles', function($q){
              $q->where('name','MarketingGPL');
          })->get();
          $content="<strong>".$user->name."</strong> telah mendaftar di aplikasi ".config('app.name'). ". Harap verifikasi segera data outlet/distributor tersebut melalui aplikasi ".config('app.name');
          $data = [
            'title'=> 'Register Outlet',
    				'message' => 'Pendaftaran outlet baru: '.$user->name,
    				'id' => $user->id,
    				'href' => url('/manageOutlet')
            ,'mail' => [ 'greeting'=> "Pendaftaran Baru di ". config('app.name'),
                            'content' => $content,
                    		]
    			];
          foreach ($marketings as $sales)
          {
            $data ['email']= $sales->email;
              //$sales->notify(new MarketingGaleniumNotif($user));
              //event(new PusherBroadcaster($data, $sales->email));
              $sales->notify(new PushNotif($data));
          }
          /*
          if ($user->customer->psc_flag ==="1"){
            $marketings_psc = User::whereHas('roles', function($q){
                $q->where('name','Marketing PSC');
            })->get();

            foreach ($marketings_psc as $mpsc)
            {
                $mpsc->notify(new MarketingGaleniumNotif($user));
            }
          }
          if ($user->customer->pharma_flag ==="1"){
            $marketings_pharma = User::whereHas('roles', function($q){
                $q->where('name','Marketing Pharma');
            })->get();
            //$marketings_pharma = Role::with('users')->where('name', 'Marketing Pharma')->get();
            //dd($marketings_pharma);
            foreach ($marketings_pharma as $mpharma)
            {
                $mpharma->notify(new MarketingGaleniumNotif($user));
            }
          }*/
          $user->validate_flag=1;

          //$user->api_token='';
          $user->save();
          //return redirect(route('verification','token'=>$token))->with('status','Your activation is completed. Please Input Your Password')
          return view('auth.verification',['data' => $user]);
          //return redirect(route('login'))->with('status',trans("auth.registerverified"));
        }else{
          if($user->register_flag==0)
          {
            return view('auth.verification',['data' => $user]);
          }else{
            return redirect(route('login'))->with('status',trans("auth.alreadyverified"));
          }

        }
      }
      return redirect(route('login'))->with('status',trans("auth.registerfailed"));
    }

    public function verification_email($token)
    {
      $user = User::where('api_token',$token)->first();
      if ($user){
          return view('auth.verification',['data' => $user]);
      }else{
        redirect(route('login'))->with('status',trans("auth.registerfailed"));
      }
    }

    public function showRegistrationForm()
    {
      $categories = CategoryOutlet::where('enable_flag','Y')->get();
      $provinces = DB::table('provinces')->get();
      $groupdcs = GroupDatacenter::where('enabled_flag','1')->get();
      $pscproducts = DB::table('flexvalue')->where('master','=','PSC_PRODUCT')->where('enabled_flag','=','Y')->select('name')->orderBy('id')->get();
      $pharmaproducts = DB::table('flexvalue')->where('master','=','PHARMA_PRODUCT')->where('enabled_flag','=','Y')->select('name')->orderBy('id')->get();
      return view('auth.register',compact('categories','provinces','groupdcs','pscproducts','pharmaproducts'));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register2(Request $request)
    {
        DB::beginTransaction();
        try{
          $this->verification($request->all())->validate();


          //event(new Registered($user = $this->create($request->all())));
          event( new Registered( $user = $this->update($request->all()) ) );
          DB::commit();
        }catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }
        if($user->register_flag){
          $this->guard()->login($user);

          return $this->registered($request, $user)
                          ?: redirect($this->redirectPath());
        }else{
          return back()->with('status', trans('auth.alreadyregistered'));
        }

    }

    public function contactview (Request $request)
    {
      return view('auth.contact.contactview');
    }
    
    protected function redirectTo()
    {
        return route('product.buy');
    }

}
