<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Post;
use App\Models\Order; // example, any models you want

class AdminDashboardController extends Controller
{
    public function __construct()
    {
    	 
        $this->middleware('auth.admin'); // ensure only logged-in admins
    }

    public function index()
    {
        // Example data to send to dashboard view
        $admin = Auth::guard('admin')->user();

        $userCount = User::count();
        $postCount = Post::count();
        $recentUsers = User::latest()->take(5)->get();
        $recentPosts = Post::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'admin',
            'userCount',
            'postCount',
            'recentUsers',
            'recentPosts'
        ));
    }
}
