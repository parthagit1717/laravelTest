<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\User;

class Subscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userdata = User::find(Auth::User()->id); 
        $subid = $userdata->subs_id;
        $subsend = $userdata->subs_end;
        $today = date('Y-m-d h:i:s a');
        


        // $userrole = $userdata->roles->first()->name;

        // dd($userrole);
         if ($userdata->id!=1) 
         {
             
            if($subid!==null && $today<$subsend)
            {
                return $next($request);
            }
            else
            {
                return redirect()->route('subscribe_error');
                 
            }
        } 
        else
        {

            return $next($request);
        }
        
        
    }
}
