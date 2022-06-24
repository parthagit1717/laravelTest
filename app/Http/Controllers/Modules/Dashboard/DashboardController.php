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

    	return view('modules.dashboard.dashboard',compact('user'));
    }
}
