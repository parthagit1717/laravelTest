<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Only guests (non-logged-in admins) can use this.
     */
    public function __construct()
    {
        $this->middleware('guest.admin');
    }

    /**
     * Use the 'admins' password broker from config/auth.php
     */
    public function broker()
    {
        return Password::broker('admins');
    }

    /**
     * Show the admin "Forgot Password" request form.
     */
    public function showLinkRequestForm()
    {
        return view('admin.auth.passwords.email');
    }
}
