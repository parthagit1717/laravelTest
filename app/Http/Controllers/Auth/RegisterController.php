<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\UserAccount;
use App\Mail\EmailVerifyMail;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Mail;


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
    protected $redirectTo = RouteServiceProvider::HOME;

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function register(Request $request)
    {
        // dd($request->all());

        $this->validate($request, [ 
          'name' => 'required|string|max:255',
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^\w+[-\.\w]*@(?!(?:myemail)\.com$)\w+[-\.\w]*?\.\w{2,4}$/'], 
          'password' =>'required|string|min:8|required_with:password-confirm', 
          'password-confirm' =>'required|required_with:password|same:password',
        ],['password-confirm.same'=>'The confirm password and password must match.',
          'password-confirm.required'=>'The confirm password field is required']);

        $Userdata['name'] = $request->name;
        $Userdata['email'] = $request->email;

        $useraccount = UserAccount::create($Userdata);

        $user = User::create([
            'account_id' =>$useraccount->id, 
            'name' => $request->name,
            'email' => $request->email,
            'email_vcode' => rand(10000,99999),
            'status' => 0, // 0=>Unverified.
            'password' => Hash::make($request->password),
        ]);
        // dd($user);
        // $input['user_id'] = $user->id;
        // $input['role_id'] = 2; 
        // $role = RoleUser::create($input);


        $maildata['name'] = $request->name;
        $maildata['email'] = $request->email;
        $maildata['email_vcode'] =$user->email_vcode;
        $maildata['id'] = $user->id;
        $maildata['email'] = $request->email;

        // dd(env('MAIL_FROM_ADDRESS'));
        Mail::send(new EmailVerifyMail($maildata));

        return redirect()->route('login')
      ->with('success', 'Thanks for signing up! A verification link has been sent to your email, Please verify email to active your account.');

        // Session::flash('success', 'Your are successfully registered. Please login with your email and password !!!');
        // return redirect('/login');
    }

    // /**
    //  * Create a new user instance after a valid registration.
    //  *
    //  * @param  array  $data
    //  * @return \App\Models\User
    //  */
    // protected function create(array $data)
    // {
    //     return User::create([
    //         'name' => $data['name'],
    //         'email' => $data['email'],
    //         'password' => Hash::make($data['password']),
    //     ]);
    // }

    /**
     * This use for verify user registered email.
     *
     * @param  array  $data
     * @return \App\User
     */

    public function verifyEmail(Request $request, $email_vcode = null, $id = null)
    {
         
        try {
           $user = User::where(\DB::raw('MD5(id)'), $id)->first();


            if (@$user->email_vcode != null && @$user->status == 0 && $user->email_vcode==$email_vcode) {

                $update['email_verified_at'] = date('Y-m-d H:i:s');
                $update['email_vcode'] = null;
                $update['status'] = 1; //1=>Active.
                User::where('id', $user->id)->update($update);
                return redirect()->route('login')->with('success', 'Your email is verified successfully. Now you can Sign in with your email and password.');
            }
            else if (@$user->email_vcode == null &&  @$user->status == 1)
            {
              return redirect()->route('login')->with('success', 'Your account already verified !!.');
            }
            else {
              return redirect()->route('login')->with('error', 'Your verification link has been expired.');
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }
}
