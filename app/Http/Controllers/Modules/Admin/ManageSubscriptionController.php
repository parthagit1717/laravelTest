<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Subscription;
use Auth;
use Redirect,Response;
use Mail;
use DateTime;
use DB;
use Carbon\Carbon;
use File; 
use Storage; 

class ManageSubscriptionController extends Controller
{
    /**
     * This is for show Subscription plans list.
     *
     * @return \Illuminate\Http\Response
    */
    public function manageSubscription()
    { 

    	return view('modules.admin.subscription.manage_subscription')->with(@$data);
    }

    /**
	 * Display user list in datatable .
	 *
	 * @return \Illuminate\Http\Response
	*/

    public function allSubscription(Request $request)
    {
        
       $columns = array( 
                            0 =>'id', 
                            1 =>'sub_title', 
                            2=> 'sub_price',
                            3=> 'sub_vali', 
                            4=> 'sub_desc',
                            5=> 'id',
                        );
  		$userid = Auth::User()->id;

        $totalData = Subscription::count();
            
        $totalFiltered = $totalData; 

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
            
        if(empty($request->input('search.value')))
        {            
            $posts = Subscription::offset($start) 
                        ->limit($limit)
                        ->orderBy($order,$dir)
                        ->get();
        }
        else {
            $search = $request->input('search.value'); 

            $posts =  Subscription::where('id','LIKE',"%{$search}%")
                            ->orWhere('sub_title', 'LIKE',"%{$search}%") 
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)
                            ->get();

            $totalFiltered = Subscription::where('id','LIKE',"%{$search}%")
                             ->orWhere('sub_title', 'LIKE',"%{$search}%")
                             ->count();
        }

        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                 

                $nestedData['id'] = $post->id;
                $nestedData['sub_title'] = $post->sub_title;  
                $nestedData['sub_price'] = $post->sub_price;
                $nestedData['sub_vali'] = $post->sub_vali;
                $nestedData['sub_desc'] = $post->sub_desc; 
                if($post->status == 1)
                {
                    $nestedData['options'] = " 

                      <a href='javascript:void(0)' data-toggle='tooltip' data-id=".$post->id." title='Edit' class='edit btn btn-success edit-post' style=' padding:4px;'> Edit <i class='bi bi-pencil-square'></i></a> 

                      <a href='javascript:void(0)' onclick='inactive(".$post->id.")' title='Click To Inactive Subscription' id='inactive' class='edit btn btn-success' style='padding:4px;'>Inactive <i class='bi bi-hand-thumbs-up'></i></a>  &emsp;";
                }
                else
                {
                    $nestedData['options'] = " 

                     <a href='javascript:void(0)' data-toggle='tooltip' data-id=".$post->id." title='Edit' class='edit btn btn-success edit-post' '> Edit <i class='bi bi-pencil-square'></i></a>

                    <a href='javascript:void(0)' onclick='active(".$post->id.")' title='Click To Active Subscription' id='active' class='edit btn btn-danger'> Inactive <i class='bi bi-hand-thumbs-down'></i></a>&emsp;";   
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
     * This is for store new Subscription.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function storeSubscription(Request $request)
    {
        // dd($request->all());
       
        if (@$request->subs_id) 
        { 

            $validation = \Validator::make($request->all(),[ 
                "name" => "required",
                "sub_desc" => "required",  
                "sub_vali" => "required|numeric|min:1",
                "sub_price" => "required|numeric|min:1",
            ],[ 'name.required'=>'Subscription name is required.',
                'sub_desc.required'=>'Subscription description required.',
                'sub_vali.required'=>'Subscription validity required.',
                'min.required'=>'Subscription validity must be positive number.',
                'sub_price.required'=>'Subscription price must be positive number.',
            ]);

            if ($validation->fails()) {
                return response()->json(['errors'=>$validation->errors()],200);
            }


            $updateuser = Subscription::find($request->subs_id);
            

            $update['sub_title'] = $request->name;
            $update['sub_desc'] = $request->sub_desc;
            $update['sub_vali'] = $request->sub_vali;
            $update['sub_price'] = $request->sub_price;
 

            Subscription::where('id',$request->subs_id)->update($update);

            return response()->json(['sucs'=>'Subscription data updated successfully !'],200);
        }
        else
        { 

            $validation = \Validator::make($request->all(),[ 
                "name" => "required",
                "sub_desc" => "required",  
                "sub_vali" => "required|numeric|min:1",
                "sub_price" => "required|numeric|min:1",
            ],[ 'name.required'=>'Subscription name is required.',
                'sub_desc.required'=>'Subscription description required.',
                'sub_vali.required'=>'Subscription validity required.',
                'sub_vali.min'=>'Subscription validity must be positive number.',
                'sub_price.required'=>'Subscription price required.',
                'sub_price.min'=>'Subscription price must be positive number.',
            ]);

            if ($validation->fails()) {
                return response()->json(['errors'=>$validation->errors()],200);
            } 
          
            $input['sub_title'] = $request->name;
            $input['sub_desc'] = $request->sub_desc;
            $input['sub_vali'] = $request->sub_vali;
            $input['sub_price'] = $request->sub_price;
             
            // dd($input);
            $user = Subscription::create($input);  
            

            return response()->json(['sucs'=>'New subscription added successfully.'],200);

        }
                
        // return Response::json($post);
        // return response()->json(['sucs'=>'Data added successfully'],200);

    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function editSubscription($id)
    {   

        $where = array('id' => $id);
        $post  = Subscription::where($where)->first();
     
        return Response::json($post);
    }


    /**
     * This is for inactive user.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function inactiveSubscription($id)
    {  
        $chk = User::where('subs_id',$id)->first();  

        if(!empty($chk))
        {

            return response()->json(['success'=>'You can not inactive this subscription it is in use !!!']);
        }
        else
        {
            $input['status'] = 2; //2=> Inactive/1=>Active.

            $updateuser = Subscription::where('id',$id)->update($input);
     
            return response()->json(['success'=>'Subscription status inactive successfully.']);
        }
        
    }

    /**
     * This is for active user.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function activeSubscription($id)
    {   
        $input['status'] = 1; //2=> Inactive/1=>Active.

        $updateuser = Subscription::where('id',$id)->update($input);
     
        return response()->json(['success'=>'Subscription status active successfully.']);
    }
}
