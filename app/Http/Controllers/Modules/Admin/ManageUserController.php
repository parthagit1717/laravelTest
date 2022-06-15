<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Auth;
use Redirect,Response;
use Mail;
use DateTime;
use DB;
use Carbon\Carbon;
use File; 
use Storage; 


class ManageUserController extends Controller
{
    /**
     * Display user list .
     *
     * @return \Illuminate\Http\Response
    */
    public function manageUser()
    { 
        $userid = Auth::User()->id; 
        // dd($userid);
        $data['user'] = User::get();
        
        return view('modules.admin.user.manage_user')->with(@$data);
    }

    /**
	 * Display user list in datatable .
	 *
	 * @return \Illuminate\Http\Response
	*/

    public function allUser(Request $request)
    {
        
       $columns = array( 
                            0 =>'id', 
                            1 =>'name', 
                            2=> 'email', 
                            3=> 'id',
                        );
  		$userid = Auth::User()->id;

        $totalData = User::whereNotIn('id',[$userid])->count();
            
        $totalFiltered = $totalData; 

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
            
        if(empty($request->input('search.value')))
        {            
            $posts = User::offset($start)
            			->whereNotIn('id',[$userid])
                        ->limit($limit)
                        ->orderBy($order,$dir)
                        ->get();
        }
        else {
            $search = $request->input('search.value'); 

            $posts =  User::where('id','LIKE',"%{$search}%")
                            ->orWhere('name', 'LIKE',"%{$search}%")
                            ->whereNotIn('id',[$userid])
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)
                            ->get();

            $totalFiltered = User::where('id','LIKE',"%{$search}%")
                             ->orWhere('name', 'LIKE',"%{$search}%")
                             ->count();
        }

        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                 

                $nestedData['id'] = $post->id;
                $nestedData['name'] = $post->name;  
                $nestedData['email'] = $post->email; 
                if($post->status == 1)
                {
                    $nestedData['options'] = "&emsp;<a href='".url('/viewuser/'.$post->id)."' title='View' id='view' class='btn btn-primary' style=' padding:4px;'> View <i class='bi bi-eye'></i> </a>

                      <a href='javascript:void(0)' data-toggle='tooltip' data-id=".$post->id." title='Edit' class='edit btn btn-success edit-post' style=' padding:4px;'> Edit <i class='bi bi-pencil-square'></i></a> 

                      <a href='javascript:void(0)' onclick='inactive(".$post->id.")' title='Click To Inactive User' id='inactive' class='edit btn btn-danger' style='padding:4px;'>Inactive <i class='bi bi-hand-thumbs-down'></i></a>  &emsp;";
                }
                else
                {
                    $nestedData['options'] = "&emsp;<a href='".url('/viewuser/'.$post->id)."' title='View' id='view' class='btn btn-primary'>View <i class='bi bi-eye'></i></a>

                     <a href='javascript:void(0)' data-toggle='tooltip' data-id=".$post->id." title='Edit' class='edit btn btn-success edit-post' '> Edit <i class='bi bi-pencil-square'></i></a>

                    <a href='javascript:void(0)' onclick='active(".$post->id.")' title='Click To Active User' id='active' class='edit btn btn-success'> Active <i class='bi bi bi-hand-thumbs-up'></i></a>&emsp;";   
                }
                $data[] = $nestedData;

            }
        }
          
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data);  
        
    }

    /**
     * This is for store new user.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function storeUser(Request $request)
    {
        // dd($request->all());
       
        if (@$request->user_id) 
        {
            $validation = \Validator::make($request->all(),[ 
                "name" => "required",  
                'email' => 'required|string|max:255|email|unique:users,email,'.$request->user_id,
                'image' => 'mimes:jpeg,jpg,png|max:10000|dimensions:min_width=100,min_height=100',
                'password' => ['nullable','required_with:old_password', 'string', 'min:8'], 
                'password_confirmation' =>['nullable', 'required_with:password', 'same:password'],
            ]);

            if ($validation->fails()) {
                return response()->json(['errors'=>$validation->errors()],200);
            } 


            $updateuser = User::find($request->user_id);
            $update['name'] = $request->name;
            $update['email'] = $request->email;

            if (!empty($request->password) && !empty($updateuser)) 
                { 
                    $update['password'] = \Hash::make($request->password); 
                }  
                
            if(@$request->hasFile('image'))
            { 

                @unlink(storage_path('app/public/images/user_image/'.$updateuser->image));  

                $image = $request->image;
                $filename = time().'-'.rand(1000,9999).'.'.$image->getClientOriginalExtension();
                Storage::putFileAs('public/images/user_image/', $image, $filename);
               
                $update['image'] =  $filename;
            }

            User::where('id',$request->user_id)->update($update);

            return response()->json(['sucs'=>'User profile updated successfully !'],200);
        }
        else
        {
            $validation = \Validator::make($request->all(),[ 
                "name" => "required",  
                'email' => 'required|string|max:255|email|unique:users,email,'.$request->user_id,
                'image' => 'mimes:jpeg,jpg,png|max:10000|dimensions:min_width=100,min_height=100',
                'password' => ['required','required_with:old_password', 'string', 'min:8'], 
                'password_confirmation' =>['required', 'required_with:password', 'same:password'],
            ]);

            if ($validation->fails()) {
                return response()->json(['errors'=>$validation->errors()],200);
            } 
          
            $input['name'] = $request->name;
            $input['email'] = $request->email;
            $input['email_vcode'] = rand(10000,99999); 
            $input['password'] = Hash::make($request->password); 
            $input['status'] = 0; 

            if(@$request->hasFile('image'))
            {   

                $image = $request->image;
                $filename = time().'-'.rand(1000,9999).'.'.$image->getClientOriginalExtension();
                Storage::putFileAs('public/images/user_image/', $image, $filename);
               
                $input['image'] =  $filename;
            }

            $user = User::create($input); 

            // dd($user);

            // $inputs['user_id'] = $user->id;
            // $inputs['role_id'] = 2; 
            // $role = RoleUser::create($inputs);

            $maildata['name'] = $request->name;
            $maildata['email'] = $request->email;
            $maildata['password'] = $request->password;
            $maildata['email_vcode'] =$user->email_vcode;
            $maildata['id'] = $user->id;

            // Mail::send(new CreateNewUserMail($maildata));

            return response()->json(['sucs'=>'New user added successfully. And verification mail send.'],200);

        }
                
        // return Response::json($post);
        // return response()->json(['sucs'=>'Data added successfully'],200);

    }

    /**
     * This is for inactive user.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function inactiveUser($id)
    {   
         
        $input['status'] = 2; //2=> Inactive/1=>Active.

        $updateuser = User::where('id',$id)->update($input);
     
        return response()->json(['success'=>'User status inactive successfully.']);
    }

    /**
     * This is for active user.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function activeUser($id)
    {   
        $input['status'] = 1; //2=> Inactive/1=>Active.

        $updateuser = User::where('id',$id)->update($input);
     
        return response()->json(['success'=>'User status active successfully.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function editEmp($id)
    {   
        $where = array('id' => $id);
        $post  = User::where($where)->first();
     
        return Response::json($post);
    }

    /**
     * Display user details .
     *
     * @return \Illuminate\Http\Response
    */

    public function viewUser($id)
    {
         
        $data['user'] = User::where('id',$id)->first();
         

        // $sitedata = Site::where('user_id',$id)->where('is_delete','=',1)->get();

        // $data['totalavtiveitsite'] = 0;
        // $data['totaldownsitesite'] = 0;

        // $data['totalsite'] = count($sitedata);

        // foreach ($sitedata as  $value) {

        //     if($value->status ==1 )
        //     {
        //         $data['totalavtiveitsite']+= 1;
        //     }
        //     else if($value->status ==2)
        //     {
        //         $data['totaldownsitesite']+=1;
        //     }
        // }
         
        return view('modules.Admin.user.user_details')->with(@$data);
        
    }
}
