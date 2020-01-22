<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use App\Notifications\MobileRegisterNotif;
use App\Notifications\VerificationUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Auth\Passwords\PasswordBroker;
use Webpatser\Uuid\Uuid;
//use App\Http\Controllers\Auth\RegisterController;

class LoginController2 extends Controller
{
  use SendsPasswordResetEmails;

  protected function validator(array $data)
  {
      return Validator::make($data, [
          'name' => 'required|string|max:100',
          'email' => 'required|string|email|max:100|unique:users',
          'alamat' => 'required|string|max:255',
          'kota' => 'required|string|max:100',
          'kode_pos' => 'required|digits:5',
          'nomor_hp_1' => 'required|regex:/(8)[0-9]{8,}/',
          'nomor_hp_2' => 'nullable|regex:/(8)[0-9]{8,}/',
          'nomor_telephone' => 'nullable|regex:/[0-9]{9}/',
          'jenis_usaha'=>'required',
      ]);
  }

  protected function save_register(array $data)
  {
    $customer = Customer::create([
        'customer_name' => $data['name'],
        'status' => 'A',
        'pharma_flag' => $data['non_psc'],
        'psc_flag' => $data['psc'],
        'outlet_type_id' => $data['jenis_usaha'],
      ]);
      $customer->save();

     $custsites = new CustomerSite();
     $custsites->site_use_code = "BILL_TO";
     $custsites->status = "A";
     $custsites->address1 = $data['alamat'];
     $custsites->state = $data['kecamatan'];
     $custsites->city = $data['kota'];
     $custsites->postalcode = $data['kode_pos'];
     $custsites->Country = 'ID';
     $custsites->customer_id = $customer->id;
     $custsites->save();

     $custcontact= new CustomerContact();
      $custcontact->contact_name = $data['nama_kontak_person'];
      $custcontact->contact_type = 'EMAIL';
      $custcontact->contact =$data['email'];
      $custcontact->customer_id=$customer->id;
      $custcontact->save();

      $custcontact1= new CustomerContact();
      $custcontact1->contact_type = 'PHONE';
      $custcontact1->contact =$data['nomor_hp_1'];
      $custcontact1->customer_id=$customer->id;
      $custcontact1->save();

      if(isset($data['HP_2'])){
          $custcontact2= new CustomerContact();
          $custcontact2->contact_type = 'PHONE';
          $custcontact2->contact =$data['nomor_hp_2'];
          $custcontact2->customer_id=$customer->id;
          $custcontact2->save();
      }
      if(isset($data['no_tlpn'])){
          $custcontact3= new CustomerContact();
           $custcontact3->contact_type = 'PHONE';
           $custcontact3->contact =$data['nomor_telephone'];
           $custcontact3->customer_id=$customer->id;
           $custcontact3->save();
      }
      $user= new User();
      $user->id = Uuid::generate()->string;
      $user->name = $data['name'];
      $user->email = $data['email'];
      $user->api_token =str_random(60);
      $customer->users()->save($user);
      return $user;
  }

  protected function validateEmail(array $data)
  {
    return Validator::make($data, [
      'email' => 'required|email'
    ]);
  }

  public function broker()
  {
      return Password::broker();
  }

  public function operations(Request $request){
    $data = $request->user;
    if($request->operation=="register"){

        $validator = $this->validator($request->user);
        if ($validator->fails()) {
          return response()->json([
            'result' => 'failure',
            'message' => $validator->messages()->first(),
          ],200);
        }
         $user = $this->save_register($data);
       $user->notify(new VerificationUser($user));
       //$user->notify(new MobileRegisterNotif($user));
         return response()->json([
           'result' => 'success',
           'message' => 'User Registered Successfully !',
         ],200);
    }elseif($request->operation=="verifikasi"){
      $validator = $this->validateEmail($request->user);
      if ($validator->fails()) {
        return response()->json([
          'result' => 'failure',
          'message' => $validator->messages()->first(),
        ],200);
      }

      $user = User::where('email',$data['email'])->where('code_verifikasi',$data['kode'])->first();
      if (!$user) {
        return response()->json([
          'result' => 'failure',
          'message' => 'Invalid verification',
        ],200);
      }else{
        $user->validate_flag = 1;
        $user->save();
        return response()->json([
          'result' => 'success',
          'message' => 'verifiaction success',
        ],200);
      }
    }elseif($request->operation=="login"){

      $validator= Validator::make($data, [
        'email'=>'required|email',
        'password' => 'required|string|min:6',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'result' => 'failure',
          'message' => $validator->messages()->first(),
        ],200);
      }

      $credentials = [
          'email' => $data['email'],
          'password' => $data['password'],
          'validate_flag' => 1
      ];

      if ( ! Auth::attempt($credentials))
      {
          return response()->json([
            'result' => 'failure',
            'message' => 'Invaild Login Credentials',
          ],401);
      }
      $user = Auth::user();

      return response()->json([
        'result' => 'success',
        'message' => 'Login Successful',
        'user' => $user
      ],200);

    }elseif($request->operation=="chgPass"){
      //dd($data);
      $credentials = [
          'email' => $data['email'],
          'password' => $data['old_password'],
          'register_flag' => 1
      ];

      if ( !Auth::attempt($credentials))
      {
          return response()->json([
            'result' => 'failure',
            'message' => 'Invaild Login Credentials',
          ],200);
      }
      $validator= Validator::make($data, [
        'email'=>'required|email',
        'new_password' => 'required|string|min:6',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'result' => 'failure',
          'message' => $validator->messages()->first(),
        ],200);
      }
      if ($data['new_password']<>$data['password_confirmation']){
        return response()->json([
          'result' => 'failure',
          'message' => 'The new password confirmation does not match',
        ],200);
      }

      $user = Auth::user();
      if (!$user) {
        return response()->json([
          'result' => 'failure',
          'message' => 'Data not match',
        ],200);
      }else{
        $user->password =  bcrypt($data['new_password']);
        $user->save();
        return response()->json([
          'result' => 'success',
          'message' => 'verifiaction success',
        ],200);
      }
    }elseif($request->operation=="resPassReq"){
      $validator = $this->validateEmail($data);
      if ($validator->fails()) {
        return response()->json([
          'result' => 'failure',
          'message' => $validator->messages()->first(),
        ],200);
      }

      $user = User::where('email', $data['email'])->where('register_flag',1)->first();

      if (!$user){
        return response()->json([
          'result' => 'failure',
          'message' => 'Data not found',
        ],200);
      }else{
        /*$token = $this->broker()->createToken($user);
        return response()->json([
          'result' => 'success',
          'message' => 'Reset password has been send to your email',
          'token' => $token
        ],200);*/
        $response = $this->broker()->sendResetLink(
            $data
        );

        if ($response == "passwords.sent")
        {
          return response()->json([
            'result' => 'success',
            'message' => 'Email Reset Password has been send. Please check your email.',
          ],200);
        }else{
          return response()->json([
            'result' => 'failure',
            'message' => 'Email Reset Password was not send',
          ],200);
        }


      }



    }

  }


}
