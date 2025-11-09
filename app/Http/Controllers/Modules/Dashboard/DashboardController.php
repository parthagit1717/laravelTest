<?php

namespace App\Http\Controllers\Modules\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use Auth;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $unveriuser = User::where('user_type', 3)->where('status', 0)->get();
        $activeuser = User::where('user_type', 3)->where('status', 1)->get();
        $inactiveuser = User::where('user_type', 3)->where('status', 2)->get();

        $posts = Post::with(['user', 'likes.user', 'comments.user'])
                     ->where('is_blocked', false)
                     ->latest()
                     ->paginate(5);

        if ($request->ajax()) {
            return view('modules.dashboard.dashboard_posts', compact('posts'))->render();
        } 
        
        $totalPosts = $user->posts()->where('is_blocked', 0)->count();
        $totalLikes = $user->posts()->withCount('likes')->get()->sum('likes_count');

         

        return view('modules.dashboard.dashboard', compact(
            'user', 'unveriuser', 'activeuser', 'inactiveuser', 'posts','totalPosts','totalLikes'
        ));
    }
}
