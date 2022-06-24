<?php

namespace App\Http\Controllers\Modules\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	// dd('DashboardController');
        $user = User::where('user_type',3)->get();
        $unveriuser = User::where('user_type',3)->where('status',0)->get();
        $activeuser = User::where('user_type',3)->where('status',1)->get();
        $inactiveuser = User::where('user_type',3)->where('status',2)->get();
    	return view('modules.dashboard.dashboard',compact('user','unveriuser','activeuser','inactiveuser'));
    }
}
