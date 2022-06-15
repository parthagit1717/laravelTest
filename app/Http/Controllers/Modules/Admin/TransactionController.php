<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Auth;
use Redirect,Response;
use Mail;
use DateTime;
use DB;
use Carbon\Carbon;
use File; 
use Storage; 


class TransactionController extends Controller
{
    /**
     * This is for show transaction list.
     *
     * @return \Illuminate\Http\Response
    */
    public function Transaction()
    {  
    	return view('modules.admin.transaction.manage_transaction');
    }

     /**
	 * Display user list in datatable .
	 *
	 * @return \Illuminate\Http\Response
	*/

    public function allTransaction(Request $request)
    {
        
       $columns = array( 
                            0 =>'id', 
                            1 =>'payment_id', 
                            2=> 'user_email',
                            3=> 'amount', 
                            4=> 'card_brand',
                            5=> 'created_at',
                            6=> 'payment_status', 
                            7=> 'id',
                        );
  		$userid = Auth::User()->id;

        $totalData = Payment::count();
            
        $totalFiltered = $totalData; 

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
            
        if(empty($request->input('search.value')))
        {            
            $posts = Payment::offset($start) 
                        ->limit($limit)
                        ->orderBy($order,$dir)
                        ->get();
        }
        else {
            $search = $request->input('search.value'); 

            $posts =  Payment::where('id','LIKE',"%{$search}%")
                            ->orWhere('payment_id', 'LIKE',"%{$search}%") 
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)
                            ->get();

            $totalFiltered = Payment::where('id','LIKE',"%{$search}%")
                             ->orWhere('payment_id', 'LIKE',"%{$search}%")
                             ->count();
        }

        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                 

                $nestedData['id'] = $post->id;
                $nestedData['payment_id'] = $post->payment_id;  
                $nestedData['user_email'] = $post->user_email;
                $nestedData['amount'] = $post->amount;
                $nestedData['card_brand'] = $post->card_brand;
                $nestedData['created_at'] = date('d-M-Y - H:i:s A',strtotime($post->created_at)); 
                if($post->payment_status == 0)
                {
                    $nestedData['payment_status'] = '<span class="badge rounded-pill bg-primary badge-sm me-1 mb-1 mt-1">Pending</span>';
                }
                else if($post->payment_status == 1)
                { 
                    $nestedData['payment_status'] = '<span class="badge rounded-pill bg-success badge-sm me-1 mb-1 mt-1">Success</span>';
                }
                else
                {
                    $nestedData['payment_status'] = '<span class="badge rounded-pill bg-danger badge-sm me-1 mb-1 mt-1">Failed</span>';   
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
}
