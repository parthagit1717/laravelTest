<?php

namespace App\Http\Controllers\Modules\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class UserController extends Controller
{
    /**
     * Display user profile.
     *
     * @return \Illuminate\Http\Response
    */
    public function userProfile()
    {   
        
        $data['user'] =  User::where('id',(Auth::User()->id))->first();  
        return view('modules.user.user_profile')->with(@$data);
    }
}
