<?php

namespace App\Http\Controllers\Modules\User\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use App\Mail\SubscriptionMail;
use Auth;
use Redirect,Response;
use Mail;
use DateTime;
use DB;
use Carbon\Carbon;
use File; 
use Storage; 
use Session;
use Stripe;

class SubscriptionController extends Controller
{
    public function __construct()
    {
         \Stripe\Stripe::setApiKey('sk_test_51JWap1SCy44BcbLFwuE9DTWPvy69jeFeDGb29FNw9L7DJTx4Zlg0ukyCVNQssI4VsoXa8FWBL0D15LKSsXn5dSUt000bjep3j5');
    }

    /**
     * This is for show Subscription plans .
     *
     * @return \Illuminate\Http\Response
    */
    public function subPlanList()
    {
    	$data['user'] =  User::find(Auth::User()->id);
    	$data['subdata'] = Subscription::where('status','=',1)->get();
    	$data['today'] = date('Y-m-d');

    	$enddate = date('Y-m-d', strtotime($data['user']->subs_end)); 

    	$data['subsend'] = $enddate;
    	 

    	$data['subendtate'] =  \Carbon\Carbon::now()->diff( \Carbon\Carbon::parse($data['user']->subs_end) ) ;
    	return view('modules.user.subscription.subscription_list')->with(@$data);
    }

    /**
     * This is for add Subscription plans for user.
     *
     * @return \Illuminate\Http\Response
    */
    public function addSubscription($user_id,$sub_id)
    {
        \Stripe\Stripe::setApiKey('sk_test_51JWap1SCy44BcbLFwuE9DTWPvy69jeFeDGb29FNw9L7DJTx4Zlg0ukyCVNQssI4VsoXa8FWBL0D15LKSsXn5dSUt000bjep3j5');
        

        $user = User::where('id',$user_id)->first();
        $subsdata = Subscription::where('id',$sub_id)->first();

        $amount = $subsdata->sub_price;

        // $orderID = strtoupper(str_replace('.','',uniqid('', true)));

     //    $customer = \Stripe\Customer::create(array( 
     //        'name' => $user->name, 
     //        'email' => $user->email,  
     //        ));
        
         
     //    $intent = \Stripe\PaymentIntent::create([
     //        'customer' => $customer->id,
     //        'amount' => ($amount)*100,
     //        'description' => $subsdata->sub_title,
     //        'currency' => 'INR',
     //        'metadata' => ['order_id' => $orderID],
     //    ]);

     // //    // dd($intent);

     //    $savepay['user_id'] = $user->id; 
     //    $savepay['user_email'] = $user->email;
     //    $savepay['amount'] = $subsdata->sub_price;
     //    $savepay['currency'] = $intent->currency;
     //    $savepay['payment_id'] = $intent->id;
     //    $savepay['payment_status'] =0;  //0=>Payment Pending....

     //    $savepayment = Payment::create($savepay);

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

        $data['user'] = $user;

        $datas = array(
                'name'=> $user->name,
                'userid' => $user->id,
                'subsid' => $sub_id,
                'email'=> $user->email,
                'amount'=> $amount, 
                );

        $data['data'] = $datas;
        
        return view('modules.user.subscription.stripe')->with($data);
         
         
    }

    /**
     * This is for add Subscription plans for user.
     *
     * @return \Illuminate\Http\Response
    */
    public function createPaymentIntent(Request $request)
    {
        // dd($request->all());
        // dd('ok');

        $user = User::where('id',$request->user_id)->first();
        $subsdata = Subscription::where('id',$request->sub_id)->first();

        $amount = $subsdata->sub_price;

        $orderID = strtoupper(str_replace('.','',uniqid('', true)));

        $customer = \Stripe\Customer::create(array( 
            'name' => $user->name, 
            'email' => $user->email,  
            ));
        
         
        $intent = \Stripe\PaymentIntent::create([
            'customer' => $customer->id,
            'amount' => ($amount)*100,
            'description' => $subsdata->sub_title,
            'currency' => 'INR',
            'metadata' => ['order_id' => $orderID],
        ]);

        // dd($intent);

        $savepay['user_id'] = $user->id; 
        $savepay['user_email'] = $user->email;
        $savepay['amount'] = $subsdata->sub_price;
        $savepay['currency'] = $intent->currency;
        $savepay['payment_id'] = $intent->id;
        $savepay['payment_status'] = 0;  //0=>Payment Pending....

         

        $savepayment = Payment::create($savepay);

        // dd($savepayment);

        return response()->json(['status' => 200, 'client_secret' => $intent->client_secret]);

    }

    public function success(Request $request)
    {
        // dd('success');
        \Stripe\Stripe::setApiKey('sk_test_51JWap1SCy44BcbLFwuE9DTWPvy69jeFeDGb29FNw9L7DJTx4Zlg0ukyCVNQssI4VsoXa8FWBL0D15LKSsXn5dSUt000bjep3j5');

        $piayID = $request->pid; 

        $rt = \Stripe\PaymentIntent::retrieve(
              $piayID,
              []
            ); 
        // echo "<pre>";
        // print_r($rt);
        // die();
        // dd($rt);

        $paymentid = $rt->id; 

        $cbrand = isset($rt->charges->data[0]) ? $rt->charges->data[0]->payment_method_details->card->brand : '';
        $funding = isset($rt->charges->data[0]) ? $rt->charges->data[0]->payment_method_details->card->funding : '';
        $type = isset($rt->charges->data[0]) ? $rt->charges->data[0]->payment_method_details->type : '';
         

        // dd($rt->status);
        
        if ($rt->status == 'succeeded')
        {
          // dd($paymentid);
        $savepay['payment_status'] = 1;
        $savepay['card_brand'] = $cbrand.' '.$funding.' '.$type;

        $updatepayment = Payment::where('payment_id',$paymentid)->update($savepay); 

        $subsdata = Subscription::where('id',$request->subsis)->first();

        $subvali = $subsdata->sub_vali;
        $today = date('Y-m-d h:i:s');        
        $sub_end_date = date('Y-m-d h:i:s', strtotime($today. ' +'. $subvali .'days'));


        $userinput['subs_id'] = $subsdata->id;
        $userinput['subs_start'] = $today;
        $userinput['subs_end'] = $sub_end_date; 
                
        $upusersub = User::where('id',$request->userid)->update($userinput);

        // dd($subsdata);



        $user = User::where('id',$request->userid)->first();
        
        $currency = $rt->charges->data[0]->currency;
        $receipt_url = $rt->charges->data[0]->receipt_url;


        $maildata['email'] = $user->email;
        $maildata['name'] = $user->name;
        $maildata['subname'] = $subsdata->sub_title; 
        $maildata['subs_end'] = date("d-m-Y",strtotime($sub_end_date));
        $maildata['currency'] = $currency;
        $maildata['amount'] = $subsdata->sub_price;
        $maildata['payment_id'] = $paymentid;
        $maildata['receipt_url'] = $receipt_url;
        $maildata['today'] = date('d-M-Y');

        // dd($maildata);
        // dd(env('MAIL_FROM_ADDRESS'));
        // Mail::send(new SubscriptionMail($maildata));
        
        return redirect()->route('subPlanList')->with('success','You have subscribe successfully');

        

        }
        else
        {
          // dd($rt->charges->data[0]->status);
          // dd($rt->charges->data[0]->payment_method_details->card->brand);
          if(isset($rt->charges->data[0]->status ) && $rt->charges->data[0]->status == 'failed'){
            // dd($type);
            $msg = $rt->charges->data[0]->failure_message;
            $updatepay['payment_status'] =2;
            $updatepay['card_brand'] = $cbrand.' '.$funding.' '.$type; 
            $updatepayment = Payment::where('payment_id',$paymentid)->update($updatepay);

            return redirect()->route('subPlanList')->with('error','Payment failed.'.' '.$msg);
          }
          else{
            $msg = $rt->last_payment_error->message;
            $updatepay['payment_status'] =2;
            $updatepay['card_brand'] = $cbrand.' '.$funding.' '.$type; 
            $updatepayment = Payment::where('payment_id',$paymentid)->update($updatepay);


            return redirect()->route('subPlanList')->with('error','Payment failed.'.' '.$msg);


          }
            
        } 
        

        
         
       
    }

    /**
     * This is for subscription plans error redirect page.
     *
     * @return \Illuminate\Http\Response
    */
    public function subPlanError(Request $request)
    {
        return view('modules.user.subscription.subscription_error');
    }
}
