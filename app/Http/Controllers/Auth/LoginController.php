<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

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
    protected $redirectTo = RouteServiceProvider::HOME;

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
        // dd($request->all());

        $this->validate($request,[
            'email' => ['required', 'string', 'email', 'max:255','regex:/^\w+[-\.\w]*@(?!(?:myemail)\.com$)\w+[-\.\w]*?\.\w{2,4}$/'],
            'password' => 'required|string',
        ]);

        $username = $request->email;
        $userpass = $request->password;       

        //Find user by this email... 
        $user = User::where('email',$username)->first();

        // dd($username,$userpass);

        if($user !=null)
        {
            if ($user->status == 1) 
            {
                // dd('ok');
                //Login this user..... 
                if ($user->google_id) {
                    return back()->with('error', 'This account is linked with Google. Please use "Login with Google" to access your account.');
                }

                else if (Auth::guard('web')->attempt(['email' => $username, 'password' =>$userpass])) 
                {  
                    return redirect()->intended(route('dashboard'));
                }

                else
                {   
                    // Session::flash('msg', 'Invalide login !!!');
                    return back()->with('error', 'Invalide login !!!');

                }

            }
            elseif($user->status == 0)
            {
                return redirect()->route('login')->with('error', 'Your email is not verified. Please verify your your email to active your account.');
            }
            else
            {
                // Session::flash('msg', 'Your account is inactive !!!');
                return redirect()->route('login')->with('error', 'Your account is inactive !!!');
                
            }

        }  
        else
        {
             return back()->with('error', 'No record found !!!');
        }      
        

        // $this->guard()->logout();
    }
}
