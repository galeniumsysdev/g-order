<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        $ios =false;
        if ((strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile/') !== false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari/') == false)) {
            $ios =true;
        }
        try{
          if(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
          $android = $_SERVER['HTTP_X_REQUESTED_WITH'] == "mobileapps.yasa.g_order";
          else $android=false;
        }catch (Exception $e) {
          $android =false;
        } catch (\Throwable $e) {
          $android =false;
        }
        if($android or $ios)
        {
          if (!$request->has('remember')) $request->request->add(['remember'=>'on']);
          config(['session.expire_on_close' => false]);
        }else{
          config(['session.expire_on_close' => true]);
        }
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->has('remember')
        );
    }

    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['validate_flag']=1;
        $credentials['register_flag']=1;
        return $credentials;
    }
    protected function authenticated(Request $request, $user)
    {
      if(Auth::check()) {
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


}
