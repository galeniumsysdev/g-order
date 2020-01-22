<?php

namespace App\Http\Controllers\Api\Auth;

use App\User;
use App\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function register(Request $request){
      $this->validate($request,[
        'nama_distributor'=>'required|string|max:255',
        'email'=>'required|email|unique:users,email',
        'no_hp_1' => 'required|string|min:11',
      ]);


      $customer = Customer::create([
          'customer_name' => request('nama_distributor'),
          'Status' => 'A',
      ]);
	     $response = fractal()
       ->item($customer)
       ->transformWith(new UserTransformer)
       ->toArray();
       return response()->json($response,201);
      /*$params = [
        'grant_type' => 'password',
        'client_id' => $this->client->id,
        'client_secret' => $this->client->secret,
        'username' =>request('email'),
        'password' =>request('password'),
        'scope' => '*'
      ];

      $request->request->add($params);

      $proxy = Request::create('oauth/token','POST');

      return Route::dispatch($proxy);*/
      //dd($request->all());
    }
}
