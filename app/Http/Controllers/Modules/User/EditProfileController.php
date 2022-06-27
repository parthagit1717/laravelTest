<?php

namespace App\Http\Controllers\Modules\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Factory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Models\User; 
use App\Models\Subscription; 
use App\Mail\SiteDownMail;
use Redirect,Response;
use Mail;
use DateTime;
use DB;
use Auth;
use Carbon\Carbon;
use Hash;
use File; 
use Storage;

class EditProfileController extends Controller
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

    /**
     * Display user profile from.
     *
     * @return \Illuminate\Http\Response
    */
    public function editProfile()
    { 
        // $ip=$_SERVER['REMOTE_ADDR']; 
        $ip = '103.94.87.67';
        $apikey = '2d86224bddc6748a8bef648399bbb9f084ebba62aa52882060d69e59';
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.ipdata.co/'.$ip.'?api-key='.$apikey,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $useripdetails =  json_decode($response);

        if($httpcode==200)
        {
            $data['ipdetails'] = $useripdetails;
        }
        else
        {
            $data['ipdetails'] = '';
        } 

        
        $data['user'] =  User::find(Auth::User()->id);  


        
        
        return view('modules.user.edit_profile_from')->with(@$data);
    }

    /**
     * This is use for update user profile.
     *
     * @return \Illuminate\Http\Response
    */

   public function updateProfile(Request $request)
    {
    	 	 	 
    	$user = Auth::user(); 

        
    	 
    	$this->validate($request, [ 
            'name'  =>'required|string|max:255', 
            'zipcode' => 'integer',
            'image' => 'mimes:jpeg,jpg,png|max:10000|dimensions:min_width=100,min_height=100', 
        ],
        ['name.required'=>'Please enter your full name.']); 
    
    	// $data = request()->validate([ 
     //    'password' => ['nullable','required_with:old_password', 'string', 'min:6','different:old_password'],
     //    'password_confirmation' => ['nullable', 'required_with:password', 'same:password'],
     //    'old_password' => ['nullable','required_with:password', function ($attribute, $value, $fail) {

     //        if (!\Hash::check($value, Auth::user()->password)) {
     //            return $fail(__('Current  password does not match please try again!'));
     //        }
     //    }]
     //    ],['old_password.required_with'=>'Please enter your current password.']);
    	  

    	 
        $input['name']  = $request->name;  
        $input['country']  = $request->country;
        $input['state']  = $request->state;
        $input['city']  = $request->city;
        $input['zipcode']  = $request->zipcode;
         

        // if (!empty($request->old_password) && !empty($user)) 
        // {
            
        //     if (\Hash::check($request->old_password, $user->password)) 
        //     {
        //         $input['password'] = \Hash::make($request->password);
                 
        //     }
        //     else 
        //     {
        //         return redirect()->back()->with('error', 'Current  password does not match please try again!');
        //     }
        // }  

        // if(@$request->hasFile('image'))
        //     { 

        //         @unlink(storage_path('app/public/images/user_image/'.$user->image));  

        //         $image = $request->image;
        //         $filename = time().'-'.rand(1000,9999).'.'.$image->getClientOriginalExtension();
        //         Storage::putFileAs('public/images/user_image/', $image, $filename);
               
        //         $input['image'] =  $filename;
        //     } 
        if(@$request->hasFile('image'))
            { 

                @unlink(storage_path('app/public/images/user_image/'.$user->image));  

                $image = $request->image;
                $filename = time().'-'.rand(1000,9999).'.'.$image->getClientOriginalExtension();
                Storage::putFileAs('public/images/user_image/', $image, $filename);
               
                $input['image'] =  $filename;
            } 
        // dd($input);

        User::where('id', $user->id)->update($input);

        return redirect()->route('profile')->with('success','Profile updated successfully!!');

    }

    /**
     * This is use for remove user profile image.
     *
     * @return \Illuminate\Http\Response
    */

    public function removeProfileImage($userid)
    {
      $user =  User::where('id',$userid)->first();
      if(isset($user->image))
      {
        @unlink(storage_path('app/public/images/user_image/'.$user->image)); 
        $input['image'] = '';
        User::where('id', $user->id)->update($input);
        return redirect()->route('profile')->with('success','Profile image deleted successfully!!');
      }
      else
      {
        return redirect()->back()->with('error','No user found.');
      }
      dd($user);
    }

    /**
     * This is use for update user password.
     *
     * @return \Illuminate\Http\Response
    */

  public function updatePassword(Request $request)
  {
    $user = Auth::user();

    $data = request()->validate([ 
      'password' => ['required','required_with:old_password', 'string', 'min:8','different:old_password'],
      'password_confirmation' => ['required', 'required_with:password', 'same:password'],
      'old_password' => ['required','required_with:password', function ($attribute, $value, $fail) {

          if (!\Hash::check($value, Auth::user()->password)) {
              return $fail(__('Old  password does not match please try again!'));
          }
      }]
      ],['old_password.required_with'=>'Please enter your current password.']);

    


    if (!empty($request->old_password) && !empty($user)) 
    {
        
      if (\Hash::check($request->old_password, $user->password)) 
      {
          $input['password'] = \Hash::make($request->password);
           
      }
      else 
      {
          return redirect()->back()->with('error', 'Current  password does not match please try again!');
      }
    }  

    User::where('id', $user->id)->update($input);

    return redirect()->route('profile')->with('success','Your password updated successfully!!');
  }
}
