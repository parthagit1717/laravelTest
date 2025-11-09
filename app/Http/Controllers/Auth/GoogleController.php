<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class GoogleController extends Controller
{
    // 1. Redirect to Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // 2. Handle Callback from Google
    public function handleGoogleCallback()
    {
        try {
            // Get user info from Google. We use stateless() to avoid session issues.
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if user already exists by google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                // Check if user exists by email (for linking existing accounts)
                $user = User::where('email', $googleUser->email)->first();

                if (!$user) {
                    // User is new, create a new record
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        // Use a secure dummy password for social login
                        'password' => Hash::make(uniqid()),
                        'status' => 1, 
                        'email_verified_at' => now(),
                    ]);
                } else {
                    // User exists by email, link the Google ID
                    $user->google_id = $googleUser->id;
                    $user->save();
                }
            }

            // Log the user in
            Auth::login($user);

            // Redirect to a dashboard or home page
            return redirect()->intended('/dashboard'); 

        } catch (Exception $e) {
            // Log the error and redirect back to login
            // \Log::error('Google login failed: ' . $e->getMessage());
            return redirect('/login')->withErrors(['google_login' => 'Google login failed. Please try again.']);
        }
    }
}