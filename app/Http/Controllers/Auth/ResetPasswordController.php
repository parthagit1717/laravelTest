<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Models\ProductCatagory;
use App\Models\Accountservice;
use App\Models\Order;
use Auth;
use Mail;


class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function showResetForm(Request $request, $token = null)
    {
        $chkTocken = User::where('remember_token',@$token)->first();

        // dd($chkTocken->email);
 
        if(!@$chkTocken){
            return redirect()->route('login')->with('error', 'This link has been expired.');
        }
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $chkTocken->email]
        );
    }

    public function reset(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [  
          'password' =>'required|string|min:8|required_with:password-confirm', 
          'password_confirm' =>'required|required_with:password|same:password',
        ],['password_confirm.same'=>'The confirm password and password must match.',
          'password_confirm.required'=>'The confirm password field is required']);

        if($request->password == $request->password_confirm && $request->password){
            $remember_token = $request->token; 
            $update['password'] = Hash::make($request->password);
            $update['remember_token'] = '';
            $user = User::Where('remember_token',$remember_token)->update($update);
            if($user) {
                return redirect()->route('login')->with('success','Password changed successfully !!');
            } else {
                return redirect()->back()->with('error','Somthing went be wrong');
            }
        } else {
            return redirect()->back()->with('error','Password and Confirm Password not matched');
        }
    }

    public function broker()
    {
        return Password::broker('');
    }
}
