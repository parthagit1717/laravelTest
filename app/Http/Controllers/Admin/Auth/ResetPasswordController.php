<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Where to redirect admins after reset.
     */
    protected $redirectTo = '/admin/dashboard';

    public function __construct()
    {
        $this->middleware('guest.admin');
    }

    /**
     * Use the 'admins' password broker.
     */
    public function broker()
    {
        return Password::broker('admins');
    }

    /**
     * Use the 'admin' guard after resetting password.
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Show the admin password reset form.
     */
    public function showResetForm($token = null)
    {
        return view('admin.auth.passwords.reset')->with([
            'token' => $token,
            'email' => request('email'),
        ]);
    }
}
