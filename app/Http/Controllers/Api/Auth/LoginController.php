<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\Notifications\MobileRegisterNotif;
//use App\Http\Controllers\Auth\RegisterController;

class LoginController extends Controller
{
  //use RegisterController;
  public function register(Request $request){
   $this->validate($request,[
      'nama'=>'required|string|max:255',
      'email'=>'required|email|unique:users,email',
      /*'no_hp_1' => 'required|regex:/(628)[0-9]{10,}/',
      'alamat' => 'required|string|max:255',
      'kota' => 'required|string|max:100',
      'postal_code' => 'required|digits:5',*/

    ]);

    if ($request->psc=="" and $request->non_psc==""){
      //$errors = new array(['error' => [trans('auth.pscflag')]]);
      //return Redirect::back()->withErrors('psc', trans('auth.pscflag'))->withInput();
      return response()->json([
        'psc' => 'Harus pilih salah satu',
      ],401);
    }
    $customer = Customer::create([
        'customer_name' => $request->name,
        'status' => 'A',
        'pharma_flag' => $request->non_psc,
        'psc_flag' => $request->psc,
        'outlet_type_id' => $request->jenis_usaha,
      ]);
      $customer->save();

     $custsites = new CustomerSite();
     $custsites->site_use_code = "BILL_TO";
     $custsites->status = "A";
     $custsites->address1 = $request->alamat;
     $custsites->state = $request->kecamatan;
     $custsites->city = $request->kota;
     $custsites->postalcode = $request->postal_code;
     $custsites->Country = 'ID';
     $custsites->customer_id = $customer->id;
     $custsites->save();

     $custcontact= new CustomerContact();
      $custcontact->contact_name = $request->nama_kontak_person;
      $custcontact->contact_type = 'EMAIL';
      $custcontact->contact =$request->email;
      $custcontact->customer_id=$customer->id;
      $custcontact->save();

      $custcontact1= new CustomerContact();
      $custcontact1->contact_type = 'PHONE';
      $custcontact1->contact ='+62'.$request->no_hp_1;
      $custcontact1->customer_id=$customer->id;
      $custcontact1->save();

      if(isset($request->HP_2)){
          $custcontact2= new CustomerContact();
          $custcontact2->contact_type = 'PHONE';
          $custcontact2->contact ='+62'.$request->no_hp_2;
          $custcontact2->customer_id=$customer->id;
          $custcontact2->save();
      }
      if(isset($request->no_tlpn)){
          $custcontact3= new CustomerContact();
           $custcontact3->contact_type = 'PHONE';
           $custcontact3->contact ='+62'.$request->phone;
           $custcontact3->customer_id=$customer->id;
           $custcontact3->save();
      }
      $user= new User();
      $user->name = $request->name;
      $user->email = $request->email;
      $user->code_verifikasi = str_random(6);
      //$user->save();
      $customer->users()->save($user);
      /*$user = User::where('email','mexar.shanty@gmail.com')->first();
      $user->code_verifikasi = str_random(6);
      $user->save();*/

    $user->notify(new MobileRegisterNotif($user));
    return response()->json([
      'token' => $user->code_verifikasi,
      'message' => 'success',
      ],201);
  }

  public function register_verification(Request $request){
    $user = User::where('email',$request->email)->where('code_verifikasi',$request->kode_verifikasi)->first();
    if (!$user) {
      return response()->json([
        'message' => 'failed',
      ],200);
    }else{
      return response()->json([
        'message' => 'success',
      ],200);
    }
  }

  public function login(Request $request){
    $this->validate($request,[
      'email'=>'required|email',
      'password' => 'required|string|min:6',
    ]);
    $credentials = [
        'email' => $request->email,
        'password' => $request->password,
        'validate_flag' => 1
    ];
    if ( ! Auth::attempt($credentials, $remember = true))
    {
        return response()->json([
          'message' => 'User has not been verified',
        ],401);
    }
    return response()->json([
      'message' => 'success',
    ],200);

  }

  public function register2(Request $request){
    $this->validate($request,[
      'email'=>'required|email',
      'password' => 'required|string|min:6|confirmed',
    ]);
    $user_check = User::where('email', $request->email)->where('api_token',$request->token)->first();
    if (!$user_check) {
      return response()->json([
        'message' => 'User has not been verified',
      ],401);
    }
    $user_check->password = bcrypt($data['password']);
    $user_check->register_flag = 1;
    $user_check->validate_flag = 1;
    $user_check->save();

    return response()->json([
      'message' => 'success',
      'user' => $user_check,
    ],200);

  }

  public function operation(Request $request){
    if($request->operation=="register"){
        $request = $request->user;
        $data = $this->validate($request,[
           'nama'=>'required|string|max:255',
           'email'=>'required|email|unique:users,email',
           /*'no_hp_1' => 'required|regex:/(628)[0-9]{10,}/',
           'alamat' => 'required|string|max:255',
           'kota' => 'required|string|max:100',
           'postal_code' => 'required|digits:5',*/

         ]);
         dd($data);
         return response()->json([
           'result' => 'success',
           'message' => 'User Registered Successfully !'
         ],401);
    }

  }


}
