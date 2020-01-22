<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use DB;
use Auth;
use Image;
use File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{

  public function profile() {
    $user = auth()->user();
    if(!is_null($user->customer_id) or $user->customer_id!="")
    {
      $customer = DB::table('Users as u')
                  ->leftjoin('customers as c','u.customer_id','=','c.id')
                  ->leftjoin('category_outlets as co','c.outlet_type_id','=','co.id')
                  ->where([
                    ['u.id','=',$user->id],
                    ['c.status','=','A'],
                  ])
                  ->select('u.email','c.id','u.name','co.name as tipeoutlet','c.tax_reference','u.avatar','c.psc_flag','c.export_flag','c.pharma_flag','c.tollin_flag')
                  ->first();
      $customer_sites = CustomerSite::where('customer_id','=',$user->customer_id)->orderBy('site_use_code')->get();
      $customer_contacts = CustomerContact::where('customer_id','=',$user->customer_id)->get();
    }else{
      $customer = DB::table('Users as u')
                  ->where('u.id','=',$user->id                    )
                  ->select('u.email','u.name','u.avatar')
                  ->first();
      $customer_sites ="";
      $customer_contacts ="";
    }
    $menu='profile';
    return view('auth.profile.index', ['customer'=>$customer,'customer_sites'=>$customer_sites,'customer_contacts'=>$customer_contacts,'menu'=>$menu]);
  }

  public function update_avatar(Request $request) {

    // Handle the user upload of avatar
    if($request->hasFile('avatar')) {
      $validator = Validator::make($request->all(), [
          'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      $user = Auth::user();
      $tmp = $user->avatar ;

      if ($validator->fails())
      return array(
          'fail' => true,
          'errors' => $validator->getMessageBag()->toArray(),
          'filename' => $tmp
      );

      if ($tmp!="default.jpg")
      {
        if (File::exists(public_path('uploads/avatars/'.$tmp ))){
          unlink(public_path('uploads/avatars/'.$tmp ));
          //echo ("<br>delete image");
        }
      }

      $avatar = $request->file('avatar');
      $filename = time() . '.' . $avatar->getClientOriginalExtension();
      Image::make($avatar)->resize(300, 300)->save( public_path('uploads/avatars/' . $filename));
      $user->avatar = $filename;
      $user->save();
      return response()->json([
                      'result' => 'success',
                      'filename' => $filename,
                    ],200);
    }else{
      return array(
          'fail' => true,
          'errors' => 'file not found',
          'filename' => $tmp
      );
    }

    //return view('profile', array('user' => Auth::user()));
  }
  public function addaddress(Request $request)
  {
    if(!is_null(auth()->user()->customer_id))
    {
      $validator = Validator::make($request->all(), [
        'fungsi' => 'required|string|max:10',
        'address' => 'required|string|max:255',
        'postalcode' => 'required|digits:5',
      ])->validate();

      DB::beginTransaction();
      try{
        $custsites = new CustomerSite();
        $custsites->site_use_code = $request->fungsi;
        $custsites->status = "A";
        $custsites->address1 = strtoupper($request->address);
        $custsites->province_id = $request->province;
        $custsites->city_id = $request->city;
        $custsites->district_id = $request->district;
        $custsites->state_id = $request->state;
        $custsites->postalcode = $request->postalcode;
        $provincename=DB::table('provinces')->where('id','=',$request->province)->select('name')->first();
        $custsites->province = $provincename->name;
        $cityname=DB::table('regencies')->where('id','=',$request->city)->where('province_id','=',$request->province)
                    ->select('name')->first();
        $custsites->city = $cityname->name;
        $districtname = DB::table('districts')->where('id','=',$request->district)->where('regency_id','=',$request->city)->select('name')->first();
        $custsites->district = $districtname->name;
        $villagename = DB::table('villages')->where('id','=',$request->state)->where('district_id','=',$request->district)->select('name')->first();
        $custsites->state = $villagename->name;
        $custsites->Country = 'ID';
        $custsites->customer_id = auth()->user()->customer_id;
        $custsites->save();
        DB::commit();
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      return redirect(route('profile.index'))->with('message',trans("pesan.successaddaddress"));
    }else{
      return redirect(route('profile.index'))->with('message',trans("pesan.failed"));
    }

  }

  public function addcontact(Request $request)
  {
    if(!is_null(auth()->user()->customer_id))
    {
      $validator = Validator::make($request->all(), [
        'cp' => 'required|string|max:10',
        'no_tlpn' => 'nullable|regex:/[0-9]{9}/',
        'tipe_kontak' => 'required|string',
      ])->validate();


      $custcontact= new CustomerContact();
       $custcontact->contact_name = $request->cp;
       $custcontact->contact_type = $request->tipe_kontak;
       $custcontact->contact ='+62'.$request->no_tlpn;
       $custcontact->customer_id=auth()->user()->customer_id;
       $custcontact->save();

      return redirect(route('profile.index'))->with('message',trans("pesan.successaddcontact"));
    }else{
      return redirect(route('profile.index'))->with('message',trans("pesan.failed"));
    }

  }

  public function editAddress(Request $request,$id)
  {
    DB::beginTransaction();
    try{
      $site = CustomerSite::find($id);
      if(is_null($site->langitude))
      {
        $site->langitude =$site->customer->langitude;
        $site->longitude =$site->customer->longitude;
      }
      $provinces  = DB::table('provinces')->get();
      if($request->isMethod('patch'))
      {
      	$kota = DB::table('regencies')->where('id','=',$request->city)->first();
      	if($kota) $site->city = $kota->name;
      	$kecamatan = DB::table('districts')->where('id','=',$request->district)->first();
      	if($kecamatan) $site->district = $kecamatan->name;
      	$kabupaten = DB::table('villages')->where('id','=',$request->state)->first();
      	if($kabupaten ) $site->state = $kabupaten->name;
      	$propinsi = DB::table('provinces')->where('id','=',$request->province)->first();
      	if($propinsi) $site->province = $propinsi->name;

        $site->site_use_code = $request->fungsi;
        $site->address1 = strtoupper($request->address);
        //$site->state = strtoupper($request->state);
        //$site->city = strtoupper($request->city);
  	    $site->province_id = strtoupper($request->province);
        $site->city_id = strtoupper($request->city);
      	$site->district_id = strtoupper($request->district);
      	$site->state_id = strtoupper($request->state);
        $site->postalcode = $request->postalcode;
        $site->Country = 'ID';
        $site->langitude = $request->langitude;
        $site->longitude = $request->longitude;
        if($site->primary_flag=="Y" and isset($request->langitude) and isset($request->longitude))
        {
          $updatecustomer =customer::where('id','=',$site->customer_id)
          ->update(['langitude'=>$request->langitude,'longitude'=> $request->longitude,'last_update_by'=>Auth::user()->id]);
        }
        $site->save();
        if($site->province_id) $listcity = DB::table('regencies')->where('province_id','=',$site->province_id)->get();
        if($site->city_id) $listdistrict = DB::table('districts')->where('regency_id','=',$site->city_id)->get();
        if($site->district_id) $listvillage = DB::table('villages')->where('district_id','=',$site->district_id)->get();
        DB::commit();
        $prevpage = $request->prevpage;
        //dd($prevpage);
        return view('auth.profile.edit_address',compact('site','prevpage','provinces','listcity','listdistrict','listvillage'))->withMessage(trans('pesan.update'));
      }else{
          $prevpage = null;
          if($site->province_id) $listcity = DB::table('regencies')->where('province_id','=',$site->province_id)->get();
          if($site->city_id) $listdistrict = DB::table('districts')->where('regency_id','=',$site->city_id)->get();
          if($site->district_id) $listvillage = DB::table('villages')->where('district_id','=',$site->district_id)->get();
          return view('auth.profile.edit_address',compact('site','prevpage','provinces','listcity','listdistrict','listvillage'));
      }
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }

  }

  public function removeaddress($id)
  {
    $delete = DB::table("customer_sites")->where([
      ['id','=',$id],
      ['customer_id','=',auth()->user()->customer_id],
      ])->delete();
    return back()->withMessage(trans('pesan.delete'));
  }

  public function removecontact($id)
  {
    $delete = DB::table("customer_contacts")->where([
      ['id','=',$id],
      ['customer_id','=',auth()->user()->customer_id],
      ])->delete();
    return back()->withMessage(trans('pesan.delete'));
  }

  public function getListProvince(Request $request)
  {
      $provinces = DB::table('provinces')->where('name','like',$request->input('query')."%")->get();
      return response()->json($provinces);
  }

  public function getListCity(Request $request,$propid=null)
  {
      //$id = Input::get('id');
      $city = DB::table('regencies')->where('name','like',$request->input('query')."%");
      if(!is_null($propid)) $city=$city->where('province_id','=',$propid);
      $city =  $city->get();
      return response()->json($city);
  }

  public function addaddressview()
  {
      $provinces = $provinces = DB::table('provinces')->get();
      return view('auth.profile.add_address',compact('provinces'));
  }

  public function updateprofile(Request $request)
  {
    if($request->Save=="updateprofile")
    {
      /*$changepswd =false;
      $pesanchangepswd=null;
      if(!is_null($request->current_pswd) and !is_null($request->new_pswd))
        $changepswd = $this->changePassword($request);
      if($changepswd) $pesanchangepswd = "Password berhasil diubah! ";else return redirect()->back();*/
      if($request->email!=Auth::user()->email)
      {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email,'.Auth::user()->id
        ]);
        $user= Auth::user();
        $user->email = $request->email;
        $user->save();
      }

      if(!is_null(Auth::user()->customer_id))
      {
        $customer=Auth::user()->customer;
        $groupdc=null;
        $sites = $customer->sites()->where('primary_flag','=','Y')->first();
        if($sites)
        {
          $city = $sites->city_id;
        }else{
          $city = null;
        }
        if(isset($request->tax)) $customer->tax_reference = $request->tax;
        if(isset($customer->outlet_type_id)){
        /*  $sub=DB::table('subgroup_datacenters as sdc')
                    ->where('id','=',  $customer->subgroup_dc_id)
                    ->select('group_id')->first();
          if($sub) $groupdc = $sub->group_id;*/
          $groupdc = $customer->outlet_type_id;
        }
        if($customer->psc_flag !=$request->psc_flag)
        {
          if($request->psc_flag=="1")//adddistributor pharma
          {
            $distributor = app('App\Http\Controllers\Auth\RegisterController')->mappingDistributor($groupdc,$city,"PSC")->get();
            if($distributor)
            {
              $customer->hasDistributor()->attach($distributor->pluck('id')->toArray(),['created_by'=>Auth::user()->id, 'last_update_by'=>Auth::user()->id]);
            }
          }
        }
        if($customer->pharma_flag !=$request->pharma_flag)
        {
          if($request->pharma_flag=="1")//adddistributor pharma
          {
            $distributor = app('App\Http\Controllers\Auth\RegisterController')->mappingDistributor($groupdc,$city,"PHARMA")->get();
            if($distributor)
            {
              $customer->hasDistributor()->attach($distributor->pluck('id')->toArray(),['last_update_by'=>Auth::user()->id,'created_by'=>Auth::user()->id]);
            }
          }
        }

        $customer->psc_flag = $request->psc_flag;
        $customer->pharma_flag = $request->pharma_flag;
        $customer->save();
        return redirect(route('profile.index'))->with('message',trans("pesan.update"));
      }
    }elseif($request->Save=="ChangePassword")
    {
      if (!(Hash::check($request->current_pswd, Auth::user()->password))) {
          // The passwords matches
          return redirect()->back()->with("error",trans('auth.notmatchpswd'));
          return false;
      }

      if(strcmp($request->current_pswd, $request->new_pswd) == 0){
          //Current password and new password are same
          return redirect()->back()->with("error",trans('auth.diffchangepwsd'));
          return false;
      }

      $validatedData = $this->validate($request,[
          'current_pswd' => 'required',
          'new_pswd' => 'required|string|min:6',
          'confirm_new_pswd' => 'required|string|min:6|same:new_pswd'
      ]);

      //Change Password
      $user = Auth::user();
      $user->password = bcrypt($request->new_pswd);
      $user->save();
      $pesanchangepswd = "Password berhasil diubah! ";
      return redirect(route('profile.index'))->with('message',$pesanchangepswd);
    }
  }

}
