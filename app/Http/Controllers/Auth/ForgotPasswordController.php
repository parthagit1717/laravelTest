<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Models\ProductCatagory;
use App\Models\Accountservice;
use App\Models\Order;
use Auth;
use Mail;


class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

     /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * This is for send forgot password link via email.
     *
     * @return \Illuminate\Http\Response
     */

    public function sendResetLinkEmail(Request $request)
    { 
        
        $this->validate($request,[
            'email' => ['required', 'string', 'email', 'max:255','regex:/^\w+[-\.\w]*@(?!(?:myemail)\.com$)\w+[-\.\w]*?\.\w{2,4}$/'],
        ]);

        $data['email'] = $request->email;
        $user = User::where('email',$request->email)->where('status','=',1)->first();
        // dd($user);
        if(!@$user){
            return redirect()->back()->with('error','No record found.');
        }
        $vcode = rand();
        User::where('email',$request->email)->update(['remember_token'=>$vcode]);
        $data['link'] = route('user.password.reset',[$vcode]);
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['mailBody'] = 'forgotpassword';
        Mail::send(new ForgotPasswordMail($data));
        return redirect()->back()->with('success','Password reset link send your email.');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
