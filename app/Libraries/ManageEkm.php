<?php

 
namespace App\Libraries;

use Illuminate\Support\Facades\Auth;
use LMS\Http\Controllers\AccountController;
use LMS\OpTaskRecord;
use LMS\Order;
use LMS\Orderitems;
use LMS\Orderstatus;
use LMS\Billingaddress;
use LMS\Deliveryaddress;
use LMS\Inventory;
use LMS\Inventorymeta;
use LMS\Inventoryimages;
use LMS\InventoryImagesTemp;
use LMS\Ibeinventoryimages;
use LMS\Accountservice;
use LMS\Ordermetadata;
use LMS\InventoryQueue;
use LMS\OrderNote;
use LMS\User;
use GuzzleHttp\Client;
use DB;
use LMS\Notifications;
use LMS\OnepatchGoogleCategories;
use LMS\Helpers\Helper;
use LMS\EbayCategories;
use LMS\Storemeta;
class ManageEkm {

    private $url='https://api.ekm.net/api/v1/';
    private $urlv2='https://api.ekm.net/api/v2/';
    private $secret_key;
    private $consumer_key;
    private $redirect_uri;
    private $access_token;
    private $refresh_token;

    public function __construct() {
        $this->redirect_uri = route('ekmauthreturn');
    }

    function fatchEkmOrderData() {

        $ebayDataConf = Accountservice::where(['status' => 1, 'service_id' => 9,'validate'=>1])->where('data', '!=', '')->get();
        if (!empty($ebayDataConf)) {
            foreach ($ebayDataConf as $convalue) {
                $account_id = $convalue->account_id;
                $confData = json_decode($convalue->data);
                if (isset($confData->access_token)) {
                    if (isset($confData->refresh_token)) {
                            $expire_time  = strtotime($confData->expires_in);
                            $current_time = strtotime(date('Y-m-d H:i:s'));
                            if($current_time>$expire_time){
                                $url = 'https://api.ekm.net/connect/token';
                                $api_key = env('EKM_CLIENT_ID');
                                $SECRET_key = env('EKM_CLIENT_SECRET');
                                $responce = $this->GetTokenByRefreshToken($url, $api_key, $SECRET_key, $confData->refresh_token);
                                //dd($responce);
                                if (isset($responce->access_token)) {
                                    $dbdata = array();
                                    $formData = Accountservice::where(['id' => $convalue->id])->orderBy('id', 'desc')->first();
                                    $dbdata['service_name'] = $confData->service_name;
                                    //$dbdata['ekm_store_url'] = $confData->ekm_store_url;
                                    $dbdata['ekm_currency'] = isset($confData->ekm_currency) ? $confData->ekm_currency : 'GBP';
                                    $dbdata['access_token'] = $responce->access_token;
                                    $dbdata['expires_in'] = date('Y-m-d H:i:s',strtotime("+3000 seconds"));//$responce->expires_in;
                                    $dbdata['token_type'] = $responce->token_type;
                                    if (isset($responce->refresh_token)) {
                                        $dbdata['refresh_token'] = $responce->refresh_token;
                                        $dbdata['refresh_token_expires_in'] = date('Y-m-d H:i:s',strtotime(" + 13 days"));//$responce->expires_in;
                                    }
                                    $formData->data = json_encode($dbdata);
                                    $formData->save();
                                    $confData->access_token = $responce->access_token;
                                }
                               // echo $confData->access_token;die;
                            }
                    }
                    $order_url=$this->url.'orders';
                    $order_data = array();
                    if (isset($confData->access_token)) {
                        $params =[
                          'page'=>1,
                          'limit'=>20  
                        ];
                        $order_data_response = $this->call($order_url, $confData->access_token, 'GET',$params);
                        if($order_data_response && $this->isJson($order_data_response)) {
                            $order_data=json_decode($order_data_response);
                        }
                        if(isset($order_data->code) && $order_data->code == 401){
                            $account_id = $convalue->account_id;
                            $config_id = $convalue->id;
                            $message=$order_data->message;
                            $order_notification = new Notifications();
                            $order_notification->account_id = $account_id;
                            $order_notification->type = 'error';
                            $order_notification->link = 'account/viewService/'. $account_id.'/ekm/'.$config_id;
                            $order_notification->icon = 'glyphicon glyphicon-exclamation-sign';
                            $order_notification->text = $message;
                            $order_notification->save();
                            
                            $formData = Accountservice::where(['id' => $convalue->id])->orderBy('id', 'desc')->first();
                            $formData->validate = 0;
                            $formData->save();
                            
                            continue;
                        }
                    }
                }
                /*echo '<pre>';
                print_r($order_data);die;*/
                if (!empty($order_data->data)) {
                    //dd($order_data->data);
                    $i = 0;
                    foreach ($order_data->data as $value) {

                        $exchk = Order::where('account_id', $account_id)->where('SupplierOrderNumber', '=', $value->order_number)->where('service_config_id', $convalue->id)->get()->count();
                        //for account id 610 only complete order import 
                        if($account_id==610 && $value->status=='PENDING'){
                            continue;
                        }
                        //for account id 1210,1492  only complete with paid order import 
                        if(($account_id==1210 || $account_id==1492) && $value->payment_status!='SUCCESS'){
                            continue;
                        }
                        //if ($exchk == 0 && !empty($value->items) && ($value->status!='PENDING' && $value->status!='SYSTEMHIDDEN')) {
                        if ($exchk == 0 && !empty($value->items) &&  $value->status!='SYSTEMHIDDEN' && $value->order_type) {
                            $order = new Order;
                            $order->SupplierOrderNumber = $value->order_number;
                            $order->CustomerOrderNumber = $value->id;
                            $order->OrderDate = date("Y-m-d H:i:s", strtotime($value->order_date));
                            $order->order_type = 'ekm';
                            $order->ordertax = $value->total_tax;
                            $order->OrderCurrency = isset($confData->ekm_currency) ? $confData->ekm_currency : 'GBP';
                            //$order->OrderCurrency = $value->currency_code;
                            $order->OrderTotal = $value->total_cost;
                            if (isset($value->customer_details->customer_id)) {
                                $order->CustomerID = $value->customer_details->customer_id;
                            }
                            $order->service_config_id = $convalue->id;
                            $order->order_status = ucwords($value->status);
                            $order->delivery_method = $value->delivery_method;
                            $order->order_type_ekm = $value->order_type;
                            $order->account_id = $account_id;
                            $user = User::where('account_id', $account_id)->first();
                            //$ordercount = Order::where('account_id', $account_id)->where('OrderDate','>=',date('Y-m-').'01 00:00;00')->where('display_status', 1)->count();
                            $subc = new \LMS\Http\Controllers\SubscriptionController();
                            $display_status=1;
                            $ordercount =$subc->getBillingCycleOrderCountByAccountId($account_id,$display_status);
                            $limitation = $subc->getLimitationDetailsByAccountId($account_id);
                            //dd($limitation);
                            if ($limitation['order']!='' && $limitation['order'] <= $ordercount) {
                                $order->display_status = 0;
                            }
                            $order->save();
                            $order_id = $order->id;
                            if ($order_id != '') {
                                if (isset($value->customer_facing_notes) && $value->customer_facing_notes != "") {
                                    $OrderNote = new OrderNote();
                                    $OrderNote->order_id = $order_id;
                                    $OrderNote->user_id = $user->id;
                                    $OrderNote->note = $value->customer_facing_notes;
                                    $OrderNote->save();
                                }
                                if (isset($value->internal_notes) && $value->internal_notes != "") {
                                    $OrderNote = new OrderNote();
                                    $OrderNote->order_id = $order_id;
                                    $OrderNote->user_id = $user->id;
                                    $OrderNote->note = $value->internal_notes;
                                    $OrderNote->save();
                                }
                                if (!empty($value->items)) {
                                    foreach ($value->items as $itemvalue) {
                                        $orderItem = new Orderitems();
                                        $orderItem->order_id = $order_id;
                                        $orderItem->SupplierOrderNumber = $value->order_number;
                                        if (isset($itemvalue->product->id)) {
                                            $orderItem->ProductItemID = $itemvalue->product->id;
                                        }
                                        if (isset($itemvalue->product->product_code)) {
                                            $orderItem->SupplierOrderlineNumber = $itemvalue->product->product_code;
                                        }
                                        $orderItem->Name = $itemvalue->item_name;
                                        if (isset($itemvalue->product->description)) {
                                            $orderItem->ProductDescription = $itemvalue->product->description;
                                        }
                                        $orderItem->Quantity = $itemvalue->quantity;
                                        if (isset($itemvalue->product->mpn)) {
                                            $orderItem->ASIN = $itemvalue->product->mpn;
                                        }
                                        $orderItem->ProductUnitPrice = $itemvalue->item_price;
                                        $orderItem->lineItemtax = (double)$itemvalue->item_tax;
                                        $orderItem->lineItemtaxRate = $itemvalue->item_tax_rate;
                                        $orderItem->lineItemDiscount = $itemvalue->item_discount;

                                        $orderItem->save();
                                        if (isset($orderItem->ProductItemID)) {
                                            $invhelper = new InventoryHelper();
                                            $invhelper->updateQtyInOnepatch($convalue->id, $account_id,$orderItem->Quantity,'ekm_product_item_id',$orderItem->ProductItemID);
                                        }
                                        else if (isset($orderItem->SupplierOrderlineNumber)) {
                                            $invhelper = new InventoryHelper();
                                            $invhelper->updateQtyInOnepatch($convalue->id, $account_id,$orderItem->Quantity,'sku',$orderItem->SupplierOrderlineNumber);
                                        } else {
                                            $invhelper = new InventoryHelper();
                                            $invhelper->updateQtyInOnepatch($convalue->id, $account_id,$orderItem->Quantity,'name',$orderItem->Name);
                                        }
                                        //End //
                                    }
                                }
                                $customer_data_Arr = array();
                                if (isset($value->customer_details) && $value->customer_details) {
                                    $company =isset($value->customer_details->company) ? $value->customer_details->company : '';
                                    if($company){
                                        $company=$company.',';
                                    }
                                    $billing_address = new Billingaddress();

                                    $billing_address->order_id = $order_id;
                                    $billing_address->SupplierOrderNumber = $value->order_number;

                                    $billing_address->FirstName = $value->customer_details->first_name;
                                    $billing_address->LastName = $value->customer_details->last_name;


                                    $billing_address->Address1 = $company .' '.$value->customer_details->address;

                                    $billing_address->Address2 = $value->customer_details->address2;

                                    $billing_address->Address5 = isset($value->customer_details->county) ? $value->customer_details->county : '';

                                    $billing_address->City = $value->customer_details->town;

                                    $billing_address->Country = $value->customer_details->country;

                                    $billing_address->Postcode = $value->customer_details->post_code;
                                    $billing_address->Phone = $value->customer_details->telephone;
                                    $billing_address->EmailAddress = $value->customer_details->email_address;

                                    $billing_address->save();
                                    
                                    //customer data store
                                    $customer_data_Arr = [
                                        'first_name' => $value->customer_details->first_name,
                                        'last_name' => $value->customer_details->last_name,
                                        'email' => $value->customer_details->email_address,
                                        'phone_number' => $value->customer_details->telephone,
                                        'post_code' => $value->customer_details->post_code
                                    ];
                                    // End customer data store
                                }else if (isset($value->shipping_address) && $value->shipping_address) {
                                    $company =isset($value->shipping_address->company) ? $value->shipping_address->company : '';
                                    if($company){
                                        $company=$company.',';
                                    }
                                    $billing_address = new Billingaddress();

                                    $billing_address->order_id = $order_id;
                                    $billing_address->SupplierOrderNumber = $value->order_number;

                                    $billing_address->FirstName = $value->shipping_address->first_name;
                                    $billing_address->LastName = $value->shipping_address->last_name;

                                    $billing_address->Address1 = $company .' '.$value->shipping_address->address;

                                    $billing_address->Address2 = $value->shipping_address->address2;


                                    $billing_address->Address5 = isset($value->shipping_address->county) ? $value->shipping_address->county : '';

                                    $billing_address->City = $value->shipping_address->town;

                                    $billing_address->Country = $value->shipping_address->country;

                                    $billing_address->Postcode = $value->shipping_address->post_code;
                                    $billing_address->Phone = $value->shipping_address->telephone;
                                    //$delievery_address->EmailAddress = $value->shipping_address->email;

                                    $billing_address->save();
                                    
                                    //customer data store
                                    $customer_data_Arr = [
                                        'first_name' => $value->shipping_address->first_name,
                                        'last_name' => $value->shipping_address->last_name,
                                        'email' => '',
                                        'phone_number' => $value->shipping_address->telephone,
                                        'post_code' => $value->shipping_address->post_code
                                    ];
                                    // End customer data store
                                }
                                if (isset($value->shipping_address) && $value->shipping_address) {
                                    $company =isset($value->shipping_address->company) ? $value->shipping_address->company : '';
                                    if($company){
                                        $company=$company.',';
                                    }
                                    $delievery_address = new Deliveryaddress();

                                    $delievery_address->order_id = $order_id;
                                    $delievery_address->SupplierOrderNumber = $value->order_number;

                                    $delievery_address->FirstName = $value->shipping_address->first_name;
                                    $delievery_address->LastName = $value->shipping_address->last_name;

                                    $delievery_address->Address1 =  $company.' '.$value->shipping_address->address;

                                    $delievery_address->Address2 = $value->shipping_address->address2;

                                    $delievery_address->Address5 = isset($value->shipping_address->county) ? $value->shipping_address->county : '';

                                    $delievery_address->City = $value->shipping_address->town;

                                    $delievery_address->Country = $value->shipping_address->country;

                                    $delievery_address->Postcode = $value->shipping_address->post_code;
                                    $delievery_address->Phone = $value->shipping_address->telephone;
                                    //$delievery_address->EmailAddress = $value->shipping_address->email;

                                    $delievery_address->save();
                                }else if (isset($value->customer_details) && $value->customer_details) {
                                    $company =isset($value->customer_details->company) ? $value->customer_details->company : '';
                                    if($company){
                                        $company=$company.',';
                                    }
                                    $delievery_address = new Deliveryaddress();

                                    $delievery_address->order_id = $order_id;
                                    $delievery_address->SupplierOrderNumber = $value->order_number;

                                    $delievery_address->FirstName = $value->customer_details->first_name;
                                    $delievery_address->LastName = $value->customer_details->last_name;



                                    $delievery_address->Address1 =  $company.' '.$value->customer_details->address;

                                    $delievery_address->Address2 = $value->customer_details->address2;

                                    $delievery_address->Address5 = isset($value->customer_details->county) ? $value->customer_details->county : '';

                                    $delievery_address->City = $value->customer_details->town;

                                    $delievery_address->Country = $value->customer_details->country;

                                    $delievery_address->Postcode = $value->customer_details->post_code;
                                    $delievery_address->Phone = $value->customer_details->telephone;
                                    $delievery_address->EmailAddress = $value->customer_details->email_address;

                                    $delievery_address->save();
                                }
                                //order data 
                                $order_data_details = [
                                    'order_id' => $order_id,
                                    'account_id' => $account_id
                                ];
                                $add_or_update_customer = Helper::StoreCustomer($order_data_details);
                                $order_status = new Orderstatus();
                                $order_status->order_id = $order_id;
                                $order_status->SupplierOrderNumber = $value->order_number;
                                $order_status->ReferenceNumber = '';
                                $order_status->OrderType = '';
                                if ($value->status == 'DISPATCHED') {
                                    $order_status->Status = 'DES';
                                } else {
                                    $order_status->Status = 'Imported';
                                }

                                $order_status->Reason = '';
                                $order_status->ConsignmentID = '';
                                $order_status->CarrierName = $value->shipping_company;
                                $order_status->CreateDate = '';
                                $order_status->save();

                                $order_meta_sp = new Ordermetadata();
                                $order_meta_sp->order_id = $order_id;
                                $order_meta_sp->meta_key = '_shipping_price';
                                $order_meta_sp->meta_value = $value->total_delivery;
                                $order_meta_sp->save();
                                
                                //shipping tax rate
                                if(isset($value->delivery_tax_rate)){
                                    $order_meta_str = new Ordermetadata();
                                    $order_meta_str->order_id = $order_id;
                                    $order_meta_str->meta_key = '_shipping_tax_rate';
                                    $order_meta_str->meta_value = $value->delivery_tax_rate;
                                    $order_meta_str->save();
                                }
                                
                                //sub total price
                                if(isset($value->sub_total)){

                                    $order_meta_st = new Ordermetadata();
                                    $order_meta_st->order_id = $order_id;
                                    $order_meta_st->meta_key = '_order_subtotal_price';
                                    $order_meta_st->meta_value = $value->sub_total;
                                    $order_meta_st->save();
                                }
                                //End sub total price
                                
                                //total discount total_discounts
                                if(isset($value->discounts_total)){

                                    $order_meta = new Ordermetadata();
                                    $order_meta->order_id = $order_id;
                                    $order_meta->meta_key = '_order_total_discounts';
                                    $order_meta->meta_value = $value->discounts_total;
                                    $order_meta->save();
                                }
                                //end total discount
                                // insert order response
                                $order_meta_resp = new Ordermetadata();
                                $order_meta_resp->order_id = $order_id;
                                $order_meta_resp->meta_key = '_order_response';
                                $order_meta_resp->meta_value = json_encode($value);
                                $order_meta_resp->save();
                                //End order response

                                if (isset($value->custom_fields) && $value->custom_fields != '') {
                                    $order_meta_cf = new Ordermetadata();
                                    $order_meta_cf->order_id = $order_id;
                                    $order_meta_cf->meta_key = '_custom_fields';
                                    $order_meta_cf->meta_value = $value->custom_fields;
                                    $order_meta_cf->save();
                                }

                                if (isset($value->shipping_company) && $value->shipping_company != '') {
                                    $order_meta_si = new Ordermetadata();
                                    $order_meta_si->order_id = $order_id;
                                    $order_meta_si->meta_key = '_shipping_info';
                                    $order_meta_si->meta_value = $value->shipping_company;
                                    $order_meta_si->save();
                                }

                                if ($i == 0) {
                                    $order_notification = new Notifications();
                                    $order_notification->account_id = $account_id;
                                    $order_notification->type = 'order';
                                    $order_notification->link = 'orders';
                                    $order_notification->icon = 'glyphicon glyphicon-sort-by-order-alt';
                                    $order_notification->text = 'New EKM Order';
                                    $order_notification->save();
                                    $noti = new \LMS\Http\Controllers\AccountController();
                                    $limitation = $noti->sentnotificationtodevice($account_id, 'order', 'New EKM Order');
                                }
                                $i++;
                                
                                //order data 
                                $order_data_details = [
                                    'order_id' => $order_id,
                                    'account_id' => $account_id
                                ];
                                $add_or_update_customer = Helper::StoreCustomer($order_data_details);
                                // End order data
                                //insert or update customer record
                                //$add_or_update_customer = Helper::StoreCustomer($customer_data_Arr, $order_data);
                                //End insert or update customer record
                                // update stock to all intigration //
                                $setting = DB::table('setting')->where('account_id', $account_id)->first();
                                if(isset($setting->stock_sync_orders) && $setting->stock_sync_orders==1){
                                    $invhelper = new InventoryHelper();
                                    $invhelper->updateQtyInAllService($order_id, $account_id);
                                }
                                //auto generate invoice xero
                                $invhelper = new InventoryHelper();
                                $invhelper->autogenerateinvoice($order_id, $account_id);
                                //Send Notification to app users
                                $user_notification = new ManageNotifications();
                                $send_notification = $user_notification->sendAppNotification($account_id, $order_id);
                                if(file_exists(base_path().'/app/Libraries/CustomLibraryCode/'.$account_id.'/ordercustomcode.php')){
                                    //custom code indiviual customer if require
                                    include base_path().'/app/Libraries/CustomLibraryCode/'.$account_id.'/ordercustomcode.php';
                                }
                            }
                        }
                    }
                }
            }
        }
    } 

    function UpdateOrderStatus($order_id,$orderstatus) {
        $orderdata = Order::find($order_id);
        if ($orderdata->service_config_id != "") {
            $ebayDataConf = Accountservice::where(['service_id' => 9, 'id' => $orderdata->service_config_id,'status'=>1])->first();
            //dd($ebayDataConf);
            if (!empty($ebayDataConf)) {
                $confData = json_decode($ebayDataConf->data);
                if (isset($confData->access_token)) {
                    try {
                        $url=$this->url.'orders/'.$orderdata->CustomerOrderNumber.'/status';
                        $putdata = array(
                            'status' => $orderstatus,
                            'email_customer' => TRUE,
                        );
                        $productdata = $this->call($url, $confData->access_token, 'PUT', $putdata);
                        if($productdata && $this->isJson($productdata)){
                            $productdata=json_decode($productdata);
                            //print_r($productdata->validation_result[0]->error_message);die;
                           // dd($productdata->validation_result->error_message);
                            //print_r($productdata);die;
                            if(isset($productdata->data)){
                                return json_encode(array('status'=>1,'message'=>'Ekm status changed successfully.'));die; 
                            }else{
                                if(isset($productdata->validation_result[0]->error_message)){
                                   return json_encode(array('status'=>0,'message'=>$productdata->validation_result[0]->error_message));die; 
                                }else{
                                   return json_encode(array('status'=>0,'message'=>'Something went wrong to update status to Ekm.'));die;    
                                }
                            }
                        }else{
                          return json_encode(array('status'=>0,'message'=>'Something went wrong in your Ekm integration.'));die;  
                        }
                        
                    } catch (\Exception $ex) {
                        return json_encode(array('status'=>0,'message'=>$ex->getMessage()));die;
                    }
                }else{
                   return json_encode(array('status'=>0,'message'=>'Something went wrong in your Ekm integration.'));die;
                }
               // dd($ebayDataConf);
            }
            
            return json_encode(array('status'=>0,'message'=>'Ekm integration id not found because you deleted it.'));die;
        }
        return json_encode(array('status'=>0,'message'=>'Ekm integration id not found.'));die;
    }

    function UpdateTrakingNumber($order_id, $trackingid,$CarrierName='') {
        $orderdata = Order::find($order_id);
        if ($orderdata->service_config_id != "") {
            $ebayDataConf = Accountservice::where(['service_id' => 9, 'id' => $orderdata->service_config_id])->first();
            if (!empty($ebayDataConf)) {
                $confData = json_decode($ebayDataConf->data);
                if (isset($confData->access_token)) {
                    try {
                        $url=$this->url.'orders/' . $orderdata->CustomerOrderNumber . '/status';
                        $putdata = array(
                            'status' => 'DISPATCHED',
                            'email_customer' => TRUE,
                        );
                        $productdata = $this->call($url, $confData->access_token, 'PUT', $putdata);
                        if($trackingid){
                            $tracking_url = '';
                            if($CarrierName == 'DpdUK'){
                                $CarrierName = 'DPD UK';
                                $tracking_url = "https://www.dpd.co.uk/apps/tracking/?reference=$trackingid&nav=1";
                            }elseif($CarrierName == 'DpdLocal'){
                                $CarrierName = 'DPD LOCAL';
                                $tracking_url = 'https://www.dpdlocal-online.co.uk/tracking/'.$trackingid;
                            }elseif($CarrierName == 'RoyalMail'){
                                $CarrierName = 'Royal Mail';
                                $tracking_url = 'https://www3.royalmail.com/track-your-item#/tracking-results/'.$trackingid;
                            }
                            //update tracking number
                            $deliveryTrackingurl=$this->urlv2.'orders/' . $orderdata->CustomerOrderNumber . '/deliveryTracking';
                            $putdatadeliveryTracking = array(
                                'delivery_tracking_number' => $trackingid,
                                'delivery_tracking_company' => $CarrierName,
                                'delivery_tracking_url' => $tracking_url
                            );
                            $productdatadeliveryTracking = $this->call($deliveryTrackingurl, $confData->access_token, 'PUT', $putdatadeliveryTracking);
                            //dd($productdatadeliveryTracking);
                        }
                    } catch (\Exception $ex) {
                        print_r($ex->getMessage());
                    }
                }
            }
        }
    }
  

    public function importproducts($config_id, $queuetaskid, $offset = 0, $limit = 20) {
        //echo 123;die;
        $response=array();
        $config_details = Accountservice::find($config_id);
        $InventoryQueue=InventoryQueue::find($queuetaskid);
        $storeurl="https://youraccount.15.ekm.net" ;
        if ($config_details && $InventoryQueue) {
            $account_id = $config_details->account_id;
            $service_id = $config_details->service_id;
            $config = json_decode($config_details->data);
            //start the task//
            $InventoryQueue->status=1; //started
            $InventoryQueue->save();
            //end//
            if($config){
                if(isset($config->ekm_store_url)){
                    $ekm_store_url=explode("/",$config->ekm_store_url);
                    if(isset($ekm_store_url[0]) && isset($ekm_store_url[2])){
                       $storeurl=$ekm_store_url[0].'//'.$ekm_store_url[2];
                    }
                }
                $params =[
                  'page'=>$offset,
                  'limit'=>$limit  
                ];
                $url = $this->url.'products';
                $token = $config->access_token;
                $response_one = microtime(1);
                $response=$this->call($url,$token,'GET',$params);
                /*echo '<pre>';
                print_r($response);die;*/
                if($response != '' && $this->isJson($response)) {
                    $store_meta_domain = Storemeta::where([
                        ['account_id', $account_id],
                        ['config_id', $config_id],
                        ['type', 'ekm_domain']
                    ])->first();
                    if(isset($store_meta_domain->value)){
                        $domain_name=$store_meta_domain->value;
                    }else{
                        $domain_name='';
                        $domain_api_url = $this->url.'settings/domains';
                        $domain_api_response=$this->call($domain_api_url,$token,'GET');
                        //dd($domain_api_response);
                        if($domain_api_response != '' && $this->isJson($domain_api_response)) {
                            $dresp = json_decode($domain_api_response);
                            if (isset($dresp->data->primary_domain_name)) {
                                $domain_name=$dresp->data->primary_domain_name;
                                $storemeta = new Storemeta();
                                $storemeta->account_id = $account_id;
                                $storemeta->config_id = $config_id;
                                $storemeta->type = 'ekm_domain';
                                $storemeta->name = 'domain';
                                $storemeta->value = $domain_name;
                                $storemeta->save(); 
                            }
                        }
                    }
                    $resp = json_decode($response);
                    /*if($account_id==781){
                        dd($resp);
                    }*/
                    if (isset($resp->data) && !empty($resp->data)) {
                        if(isset($resp->meta->items_total)){
                            $total_records=$resp->meta->items_total;
                        }else{
                            $total_records=0;
                        }
                        if(isset($resp->meta->pages_total)){
                            $total_pages=$resp->meta->pages_total;
                        }else{
                            $total_pages=0;
                        }
                        if (!empty($resp->data)) {
                            $setting = DB::table('setting')->where('account_id', $account_id)->first();
                            $direct_import=0;
                            if(isset($setting->direct_import) && $setting->direct_import == 1){ 
                                $direct_import=1;
                            }
                            foreach ($resp->data as $product) {
                                $this->addNewProduct($account_id, $product,$config_id,$token,$storeurl,$service_id,$domain_name,$direct_import);
                            }
                            if($InventoryQueue){
                                $InventoryQueue->api_status=1;
                                $InventoryQueue->api_response=$response;
                                $InventoryQueue->status=2; //finished
                                $InventoryQueue->save();
                            }
                            $response=array('status'=>1,'message'=>'successfully imported.','total_records'=>$total_records,'total_pages'=>$total_pages);
                        }else{
                            if($InventoryQueue){
                                $InventoryQueue->api_status=1;
                                $InventoryQueue->api_response=$response;
                                $InventoryQueue->status=2; //finished
                                $InventoryQueue->save();
                            }
                            $response=array('status'=>1,'message'=>'successfully imported.','total_records'=>$total_records); 
                        }
                    }else{
                        //dd($resp);
                        if($InventoryQueue){
                            $InventoryQueue->api_status=0;
                            $InventoryQueue->api_response=$response;
                            $InventoryQueue->status=2; //finished
                            $InventoryQueue->save();
                        }
                        if(isset($resp->code) && $resp->code==401){
                            InventoryQueue::where('task_id', $InventoryQueue->task_id)->update(['status' => 3,'api_response'=>$response]); 
                            $formData = Accountservice::where(['id' => $config_id])->orderBy('id', 'desc')->first();
                            $formData->validate = 0;
                            $formData->save();
                            $message=$resp->message;
                        }else{
                            $message="Something went wrong in your ekm intregation";
                        }
                        //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                        $response=array('status'=>0,'message'=> $message);
                    }
                }else{           
                    if($InventoryQueue){
                        $InventoryQueue->api_status=0;
                        $InventoryQueue->api_response=json_encode(array('status'=>'error','message'=>'Something went wrong in api response.'));
                        $InventoryQueue->status=2;//finished
                        $InventoryQueue->save();
                    }
                    $response=array('status'=>0,'message'=>'Something went wrong in api response.');
                }
             $response_two = microtime(1);
             $response_time=( $response_two - $response_one );   
             $response['response_time']=ceil($response_time);
                
            }else{
                if($InventoryQueue){
                    $InventoryQueue->api_status=0;
                    $InventoryQueue->api_response=json_encode(array('status'=>'error','message'=>'Config data not found.'));
                    $InventoryQueue->status=2; //finished
                    $InventoryQueue->save();
                }
                $response=array('status'=>0,'message'=>'Config data not found.','response_time'=>0);
            }
        }
        return json_encode($response);
    }
    public function addNewProduct($account_id, $product_data,$config_id,$access_token,$storeurl,$service_id,$domain_name,$direct_import) {
        //$setting = DB::table('setting')->where('account_id', $account_id)->first();
        if(!empty($product_data->variants)) {
            $type=2;
        }else{
            $type=0;
        }
        /*echo '<pre>';
        print_r($product_data);*/
        /*if(isset($product_data->product_code) && $product_data->product_code!='' && $product_data->product_code != "Identifier doesn't exist"){
            $already_exist_chk = Inventory::where(['account_id' => $account_id, 'sku' => $product_data->product_code])->first();
            if ($already_exist_chk){
                $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_data->id;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $already_exist_chk->id;
                    $invmeta3->sku = $already_exist_chk->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_product_item_id";
                    $invmeta3->meta_value = $product_data->id;
                    $invmeta3->save();
                }
                return;
            }
        }
        if(isset($product_data->gtin) && $product_data->gtin!='' && $product_data->gtin != "Identifier doesn't exist"){
            $already_exist_chk = Inventory::where(['account_id' => $account_id, 'barcode' => $product_data->gtin])->first();
            if ($already_exist_chk){
                $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_data->id;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $already_exist_chk->id;
                    $invmeta3->sku = $already_exist_chk->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_product_item_id";
                    $invmeta3->meta_value = $product_data->id;
                    $invmeta3->save();
                }
                return;
            }
        }
        if((isset($product_data->product_code) && $product_data->product_code == '' && isset($product_data->gtin) && $product_data->gtin=='')){
            //echo 123;die;
            $already_exist_chk = Inventory::where(['account_id' => $account_id, 'name' => $product_data->name,'type'=>$type])->first();
            if ($already_exist_chk){
                $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_data->id;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $already_exist_chk->id;
                    $invmeta3->sku = $already_exist_chk->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_product_item_id";
                    $invmeta3->meta_value = $product_data->id;
                    $invmeta3->save();
                }
                return;
            }
        }*/
        $inv = new Inventory();
        $inv->account_id = $account_id;
        if (isset($product_data->product_code) && $product_data->product_code != '' && $product_data->product_code != "Identifier doesn't exist" && strtolower($product_data->product_code) != 'does not apply'){
            $inv->sku = $product_data->product_code;
        }
        
        if(isset($product_data->attribute_items) && is_array($product_data->attribute_items) && !empty($product_data->attribute_items)){
            foreach($product_data->attribute_items as $attributes){
                if(isset($attributes->attribute_key)){
                    if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='SKU' && strtolower($attributes->attribute_value) != 'does not apply'){
                        $inv->sku = $attributes->attribute_value;
                    }
                    
                    if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='EAN' && strtolower($attributes->attribute_value) != 'does not apply'){
                        $inv->barcode = $attributes->attribute_value;
                        $inv->barcode_type='EAN';
                    }else if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='UPC' && strtolower($attributes->attribute_value) != 'does not apply'){
                        $inv->barcode = $attributes->attribute_value;
                        $inv->barcode_type='UPC';
                    }else if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='MPN' && strtolower($attributes->attribute_value) != 'does not apply'){
                        $inv->barcode = $attributes->attribute_value;
                        $inv->barcode_type='MPN';
                    }
                }
            }
        }
        if (isset($product_data->gtin) && $product_data->gtin != '' && $product_data->gtin != "Identifier doesn't exist" && strtolower($product_data->gtin) != 'does not apply'){
            $inv->barcode = $product_data->gtin;
            $inv->barcode_type='GTIN';
        }
        //already exist check
        if(isset($inv->sku) && $inv->sku!='' && $inv->sku != "Identifier doesn't exist"){
            /*echo $inv->sku;
            echo '<br>';*/
            $already_exist_chk = Inventory::where(['account_id' => $account_id, 'sku' => $inv->sku])->first();
            if ($already_exist_chk){
                if($account_id == 1084){
                    $already_exist_chk->barcode = $inv->barcode;
                    $already_exist_chk->barcode_type=$inv->barcode_type;
                    $already_exist_chk->save();
                }
                
                $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_data->id;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $already_exist_chk->id;
                    $invmeta3->sku = $already_exist_chk->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_product_item_id";
                    $invmeta3->meta_value = $product_data->id;
                    $invmeta3->save();
                }
                if(isset($product_data->seo_friendly_url)){
                    $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','live_product_url_'.$service_id)->where('config_id',$config_id)->first();
                    if($inventorymeta){
                        $inventorymeta->meta_value=$domain_name.'/'.$product_data->seo_friendly_url;
                        $inventorymeta->save();
                    }else{
                        $invmeta = new Inventorymeta();
                        $invmeta->account_id = $account_id;
                        $invmeta->sku = $already_exist_chk->sku;
                        $invmeta->inventory_id = $already_exist_chk->id;
                        $invmeta->config_id = $config_id;
                        $invmeta->meta_key = 'live_product_url_'.$service_id;
                        $invmeta->meta_value =  $domain_name.'/'.$product_data->seo_friendly_url;
                        $invmeta->save();
                    }
                }
                if (!empty($product_data->categories)) {
                    $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_category_id')->where('config_id',$config_id)->first();
                    $product_categorie_ids=implode(",",(array_column((array)$product_data->categories, 'category_id')));
                    if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                        $inventorymeta->meta_value=$product_categorie_ids;
                        $inventorymeta->save();
                    }else{
                        $invmeta3 = new Inventorymeta();
                        $invmeta3->account_id = $account_id;
                        $invmeta3->inventory_id = $already_exist_chk->id;
                        $invmeta3->sku = $already_exist_chk->sku;
                        $invmeta3->config_id = $config_id;
                        $invmeta3->meta_key = "ekm_category_id";
                        $invmeta3->meta_value = $product_categorie_ids;
                        $invmeta3->save();
                    }
                }
                return;
            }
        }
        if(isset($inv->barcode) && $inv->barcode!='' && $inv->barcode != "Identifier doesn't exist"){
           /* echo $inv->barcode.'barcode';
            echo '<br>';*/
            $already_exist_chk = Inventory::where(['account_id' => $account_id, 'barcode' => $inv->barcode])->first();
            if ($already_exist_chk){
                $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_data->id;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $already_exist_chk->id;
                    $invmeta3->sku = $already_exist_chk->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_product_item_id";
                    $invmeta3->meta_value = $product_data->id;
                    $invmeta3->save();
                }
                if(isset($product_data->seo_friendly_url)){
                    $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','live_product_url_'.$service_id)->where('config_id',$config_id)->first();
                    if($inventorymeta){
                        $inventorymeta->meta_value=$domain_name.'/'.$product_data->seo_friendly_url;
                        $inventorymeta->save();
                    }else{
                        $invmeta = new Inventorymeta();
                        $invmeta->account_id = $account_id;
                        $invmeta->sku = $already_exist_chk->sku;
                        $invmeta->inventory_id = $already_exist_chk->id;
                        $invmeta->config_id = $config_id;
                        $invmeta->meta_key = 'live_product_url_'.$service_id;
                        $invmeta->meta_value =  $domain_name.'/'.$product_data->seo_friendly_url;
                        $invmeta->save();
                    }
                }
                if (!empty($product_data->categories)) {
                    $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_category_id')->where('config_id',$config_id)->first();
                    $product_categorie_ids=implode(",",(array_column((array)$product_data->categories, 'category_id')));
                    if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                        $inventorymeta->meta_value=$product_categorie_ids;
                        $inventorymeta->save();
                    }else{
                        $invmeta3 = new Inventorymeta();
                        $invmeta3->account_id = $account_id;
                        $invmeta3->inventory_id = $already_exist_chk->id;
                        $invmeta3->sku = $already_exist_chk->sku;
                        $invmeta3->config_id = $config_id;
                        $invmeta3->meta_key = "ekm_category_id";
                        $invmeta3->meta_value = $product_categorie_ids;
                        $invmeta3->save();
                    }
                }
                return;
            }
        }
        if((isset($inv->barcode) && $inv->barcode == '' && isset($inv->sku) && $inv->sku=='')){
            //echo 123;die;
            $already_exist_chk = Inventory::where(['account_id' => $account_id, 'name' => $product_data->name,'type'=>$type])->first();
            if ($already_exist_chk){
                $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_data->id;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $already_exist_chk->id;
                    $invmeta3->sku = $already_exist_chk->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_product_item_id";
                    $invmeta3->meta_value = $product_data->id;
                    $invmeta3->save();
                }
                if(isset($product_data->seo_friendly_url)){
                    $inventorymeta=Inventorymeta::where('inventory_id',$already_exist_chk->id)->where('meta_key','live_product_url_'.$service_id)->where('config_id',$config_id)->first();
                    if($inventorymeta){
                        $inventorymeta->meta_value=$domain_name.'/'.$product_data->seo_friendly_url;
                        $inventorymeta->save();
                    }else{
                        $invmeta = new Inventorymeta();
                        $invmeta->account_id = $account_id;
                        $invmeta->sku = $already_exist_chk->sku;
                        $invmeta->inventory_id = $already_exist_chk->id;
                        $invmeta->config_id = $config_id;
                        $invmeta->meta_key = 'live_product_url_'.$service_id;
                        $invmeta->meta_value =  $domain_name.'/'.$product_data->seo_friendly_url;
                        $invmeta->save();
                    }
                }
                return;
            }
        }
        $inv->name = $product_data->name;
        $inv->type = $type;
        $inv->parent_id = 0;
        $inv->short_description = $product_data->short_description;
        $inv->long_description = $product_data->description;
        $inv->brand = $product_data->brand;
        if (isset($product_data->total_product_stock) && $product_data->total_product_stock != '') {
            $inv->quantity = $product_data->total_product_stock;
            if($product_data->total_product_stock==0){
               $inv->stock_status=0; 
           }else{
                $inv->stock_status=1;
           }
        }else{
            $inv->quantity = 0;
            $inv->stock_status=0;
        }

        if (isset($product_data->price) && $product_data->price != ''){
            $inv->price = $product_data->price;
        }
        if(isset($direct_import) && $direct_import == 1){ 
            $inv->status=1;
        }else{
            $inv->status=0; 
        }
        $inv->save();
        //dd($inv);
        $inventory_id = $inv->id;
        if (is_numeric($inventory_id)) {
            $temp_images=array();
            $product_images_temp=array();
            $url = $this->url.'products/'.$product_data->id.'/images';
            $productimagedata=$this->call($url,$access_token,'GET',$params=array());
            if (isset($productimagedata) && $this->isJson($productimagedata)) {
                $productimagedata=json_decode($productimagedata);
                if(isset($productimagedata->data)){
                    foreach ($productimagedata->data as $imgvalue) {
                        if (isset($imgvalue->location) && $imgvalue->location) {
                            if($domain_name){
                                $image_url = $domain_name. $imgvalue->location . $imgvalue->local;
                            }else{
                                $image_url = $storeurl . $imgvalue->location . $imgvalue->local;
                            }
                            $temp_images[]=array('inventory_id'=>$inventory_id,'image_url'=>$image_url);
                            $product_images_temp[]=$image_url;
                        }
                    }
                }
            }
            if(isset($temp_images) && !empty($temp_images)){
                InventoryImagesTemp::insert($temp_images);
                $product_images=json_encode($product_images_temp);
                $invmeta = new Inventorymeta();
                $invmeta->account_id = $account_id; 
                $invmeta->sku = $inv->sku;
                $invmeta->inventory_id = $inventory_id;
                $invmeta->config_id = $config_id;
                $invmeta->meta_key = 'live_product_images_'.$service_id;
                $invmeta->meta_value =  $product_images;
                $invmeta->save();  
            }
            if(isset($product_data->seo_friendly_url)){
                $invmeta = new Inventorymeta();
                $invmeta->account_id = $account_id;
                $invmeta->sku = $inv->sku;
                $invmeta->inventory_id = $inventory_id;
                $invmeta->config_id = $config_id;
                $invmeta->meta_key = 'live_product_url_'.$service_id;
                $invmeta->meta_value =  $domain_name.'/'.$product_data->seo_friendly_url;
                $invmeta->save();
            }
            $category = '';
            if ($product_data->category_id != '') {
                $url = $this->url.'categories/'.$product_data->category_id;
                $produccatdata=$this->call($url,$access_token,'GET',$params=array());
                if (isset($produccatdata) && $this->isJson($produccatdata)) {
                    $produccatdata=json_decode($produccatdata);
                    if (isset($produccatdata->data->name)) {
                        $category = $produccatdata->data->name;
                    }
                }
            }
            //dd($temp_images);
            if($category){
                $product_categories=json_encode(array($category)); 
                $invmeta = new Inventorymeta();
                $invmeta->account_id = $account_id;
                $invmeta->sku = $inv->sku;
                $invmeta->inventory_id = $inventory_id;
                $invmeta->meta_key = 'product_category_info';
                $invmeta->meta_value =  $product_categories;
                $invmeta->save();        
            }
            if (!empty($product_data->categories)) {
                $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','ekm_category_id')->where('config_id',$config_id)->first();
                $product_categorie_ids=implode(",",(array_column((array)$product_data->categories, 'category_id')));
                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                    $inventorymeta->meta_value=$product_categorie_ids;
                    $inventorymeta->save();
                }else{
                    $invmeta3 = new Inventorymeta();
                    $invmeta3->account_id = $account_id;
                    $invmeta3->inventory_id = $inventory_id;
                    $invmeta3->sku = $inv->sku;
                    $invmeta3->config_id = $config_id;
                    $invmeta3->meta_key = "ekm_category_id";
                    $invmeta3->meta_value = $product_categorie_ids;
                    $invmeta3->save();
                }
            }
            
            if (!empty($product_data->options)) {
                $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_options')->first();
                $product_options=json_encode($product_data->options);
                if(isset($inventorymeta->id)){
                    $inventorymeta->meta_value=$product_options;
                    $inventorymeta->save();
                }else{
                    $invmeta4 = new Inventorymeta();
                    $invmeta4->account_id = $account_id;
                    $invmeta4->inventory_id = $inventory_id;
                    $invmeta4->sku = $inv->sku;
                    $invmeta4->config_id = $config_id;
                    $invmeta4->meta_key = "product_options";
                    $invmeta4->meta_value = $product_options;
                    $invmeta4->save();
                }
            }
            $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
            if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                $inventorymeta->meta_value=$product_data->id;
                $inventorymeta->save();
            }else{
                $invmeta5 = new Inventorymeta();
                $invmeta5->account_id = $account_id;
                $invmeta5->inventory_id = $inventory_id;
                $invmeta5->sku = $inv->sku;
                $invmeta5->config_id = $config_id;
                $invmeta5->meta_key = "ekm_product_item_id";
                $invmeta5->meta_value = $product_data->id;
                $invmeta5->save();
            }
            if($product_data->condition && $product_data->condition!='NotApplicable'){
                $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_condition')->first();
                if($inventorymeta){
                    $inventorymeta->meta_value=$product_data->condition;
                    $inventorymeta->save();
                }else{
                    $invmeta6 = new Inventorymeta();
                    $invmeta6->account_id = $account_id;
                    $invmeta6->inventory_id = $inventory_id;
                    $invmeta6->sku = $inv->sku;
                    $invmeta6->config_id = $config_id;
                    $invmeta6->meta_key = "product_condition";
                    $invmeta6->meta_value = $product_data->condition;
                    $invmeta6->save();
                }
            }
            if(isset($product_data->product_weight) && $product_data->product_weight!=''){
                $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_weight')->first();
                if($inventorymeta){
                    $inventorymeta->meta_value=$product_data->product_weight;
                    $inventorymeta->save();
                }else{
                    $invmeta6 = new Inventorymeta();
                    $invmeta6->account_id = $account_id;
                    $invmeta6->inventory_id = $inventory_id;
                    $invmeta6->sku = $inv->sku;
                    $invmeta6->config_id = $config_id;
                    $invmeta6->meta_key = "product_weight";
                    $invmeta6->meta_value = $product_data->product_weight;
                    $invmeta6->save();
                }
            }
            //dd($product_data);
        }
        if (isset($product_data->variants) && !empty($product_data->variants)) {
            $products = $product_data->variants;
            $attributrlist = []; 
            foreach ($products as $product) {
                //dd($product);
                 $already_exist_chk = Inventory::where(['account_id' => $account_id, 'sku' => $product->product_code,'type'=>1])->first();
                // if (empty($already_exist_chk)) {
                        $variatiobname = '';
                                      
                        foreach ($product->variant_combinations as $key => $value_variant_combinations) {
                            if ($value_variant_combinations != '') {
                                if(strtolower($value_variant_combinations->variant_name)=='colour'){
                                   $name='Color';  
                                }else{
                                   $name=$value_variant_combinations->variant_name; 
                                }
                                $variant_choice=$value_variant_combinations->variant_choice;
                                $attributrlist[$name][] = $variant_choice;
                                $variatiobname .= $variant_choice . ' ';
                            }
                        }
                           

                        $child_inv = new Inventory();
                        $child_inv->account_id = $account_id;
                        if (isset($product->product_code) && $product->product_code != ''){
                            $child_inv->sku = $product->product_code;
                        }
                        if (isset($product->gtin) && $product->gtin != '' && $product->gtin != "Identifier doesn't exist"){
                            $child_inv->barcode = $product->gtin;
                            $child_inv->barcode_type='GTIN';
                        }
                        $child_inv->name = $product_data->name. '-' . $variatiobname;
                        $child_inv->type = 1;
                        $child_inv->parent_id = $inventory_id;
                        $child_inv->short_description = $product_data->short_description;
                        $child_inv->long_description = $product_data->description;
                        $child_inv->brand = $product_data->brand;
                        if (isset($product->number_in_stock) && $product->number_in_stock != '') {
                            $child_inv->quantity = $product->number_in_stock;
                            if($product->number_in_stock==0){
                               $child_inv->stock_status=0; 
                           }else{
                                $child_inv->stock_status=1;
                           }
                        }else{
                            $child_inv->quantity = 0;
                            $child_inv->stock_status=0;
                        }

                        if (isset($product->price) && $product->price != ''){
                            $child_inv->price = $product->price;
                        }
                        if(isset($direct_import) && $direct_import == 1){
                            $child_inv->status=1;
                        }else{
                            $child_inv->status=0; 
                        }
                        //dd($child_inv);
                        $child_inv->save();
                        $child_inventory_id = $child_inv->id;
                       /* echo '<pre>';
                        print_r($product);*/
                        if (is_numeric($child_inventory_id)) {
                            $temp_child_images=array();
                            $product_child_images_temp=array();
                            $url = $this->url.'products/'.$product_data->id. '/variants/' . $product->id . '/images';
                            $productimagedata=$this->call($url,$access_token,'GET',$params=array());
                            if (isset($productimagedata) && $this->isJson($productimagedata)) {
                                $productimagedata=json_decode($productimagedata);
                                if(isset($productimagedata->data)){
                                    foreach ($productimagedata->data as $imgvalue) {
                                        if (isset($imgvalue->location)) {
                                            if($domain_name){
                                                $image_url = $domain_name. $imgvalue->location . $imgvalue->local;
                                            }else{
                                                $image_url = $storeurl . $imgvalue->location . $imgvalue->local;
                                            }
                                            $temp_child_images[]=array('inventory_id'=>$child_inventory_id,'image_url'=>$image_url);
                                            $product_child_images_temp[]=$image_url;
                                        }
                                    }
                                }
                            }
                            if(isset($temp_child_images) && !empty($temp_child_images)){
                                InventoryImagesTemp::insert($temp_child_images);
                                $product_child__images=json_encode($product_child_images_temp);
                                $invmeta = new Inventorymeta();
                                $invmeta->account_id = $account_id;
                                $invmeta->sku = $child_inv->sku;
                                $invmeta->inventory_id = $child_inventory_id;
                                $invmeta->config_id = $config_id;
                                $invmeta->meta_key = 'live_product_images_'.$service_id;
                                $invmeta->meta_value =  $product_child__images;
                                $invmeta->save(); 
                            }
                            if($category){
                                $child_product_categories=json_encode(array($category)); 
                                $invmeta = new Inventorymeta();
                                $invmeta->account_id = $account_id;
                                $invmeta->sku = $child_inv->sku;
                                $invmeta->inventory_id = $child_inventory_id;
                                $invmeta->meta_key = 'product_category_info';
                                $invmeta->meta_value =  $child_product_categories;
                                $invmeta->save();        
                            }
                            
                            $invmeta3 = new Inventorymeta();
                            $invmeta3->account_id = $account_id;
                            $invmeta3->inventory_id = $child_inventory_id;
                            $invmeta3->sku = $child_inv->sku;
                            $invmeta3->config_id = $config_id;
                            $invmeta3->meta_key = "ekm_variation_id";
                            $invmeta3->meta_value = $product->id;
                            $invmeta3->save();
                            if($product_data->condition && $product_data->condition!='NotApplicable'){
                                $inventorymeta=Inventorymeta::where('inventory_id',$child_inventory_id)->where('meta_key','product_condition')->first();
                                if($inventorymeta){
                                    $inventorymeta->meta_value=$product_data->condition;
                                    $inventorymeta->save();
                                }else{
                                    $invmeta3 = new Inventorymeta();
                                    $invmeta3->account_id = $account_id;
                                    $invmeta3->inventory_id = $child_inventory_id;
                                    $invmeta3->sku = $child_inv->sku;
                                    $invmeta3->config_id = $config_id;
                                    $invmeta3->meta_key = "product_condition";
                                    $invmeta3->meta_value = $product_data->condition;
                                    $invmeta3->save();
                                }
                            }
                            if(isset($product->product_weight) && $product->product_weight!=''){
                                $inventorymeta=Inventorymeta::where('inventory_id',$child_inventory_id)->where('meta_key','product_weight')->first();
                                if($inventorymeta){
                                    $inventorymeta->meta_value=$product->product_weight;
                                    $inventorymeta->save();
                                }else{
                                    $invmeta6 = new Inventorymeta();
                                    $invmeta6->account_id = $account_id;
                                    $invmeta6->inventory_id = $child_inventory_id;
                                    $invmeta6->sku = $child_inv->sku;
                                    $invmeta6->config_id = $config_id;
                                    $invmeta6->meta_key = "product_weight";
                                    $invmeta6->meta_value = $product->product_weight;
                                    $invmeta6->save();
                                }
                            }

                            foreach ($product->variant_combinations as $key => $value_variant_combinations) {
                                if ($value_variant_combinations != '') {
                                    if(strtolower($value_variant_combinations->variant_name)=='colour'){
                                       $name='Color';  
                                    }else{
                                       $name=$value_variant_combinations->variant_name; 
                                    }
                                    $variant_choice=$value_variant_combinations->variant_choice;
                                    $invmeta3 = new Inventorymeta();
                                    $invmeta3->account_id = $account_id;
                                    $invmeta3->inventory_id = $child_inventory_id;
                                    $invmeta3->sku = $child_inv->sku;
                                    $invmeta3->meta_key = 'attribute_'.strtolower($name);
                                    $invmeta3->meta_value = $variant_choice;
                                    $invmeta3->save();
                                }
                            }
               
                        }
                     //}else{
                        //$already_exist_chk->parent_id=$inventory_id;
                        //$already_exist_chk->save();
                    // }
            }
            $product_attributes=array();
            if(!empty($attributrlist)){
                foreach($attributrlist as $keyatt=>$valueatt){
                    $product_attributes[]=array('key'=>'attribute_'.strtolower($keyatt),'name'=>ucwords($keyatt),'value'=>implode('|',array_unique($valueatt)),'use_for_variation'=> true);
                }  
            }

            if(!empty($product_attributes)){
                $invmeta1 = new Inventorymeta();
                $invmeta1->account_id = $account_id;
                $invmeta1->sku = $inv->sku;
                $invmeta1->inventory_id = $inventory_id;
                $invmeta1->meta_key = 'product_attributes';
                $invmeta1->meta_value = json_encode($product_attributes);
                $invmeta1->save();
            }
        }
               
        
        
    }

     //=====Upload to Ekm============//
    public function uploadsingleproduct($inventory_id,$account_id,$config_id){
        //dd($this->deleteekmcategory(332,$config_id));
        ini_set('max_execution_time', 0);
        $response=array();
        $config_details = Accountservice::find($config_id);
        if ($config_details) {
            $account_id = $config_details->account_id;
            $service_id = $config_details->service_id;
            $config = json_decode($config_details->data);
            if($config){
                $url = $this->url.'products';
                $token = $config->access_token;
                $products = Inventory::where('id',$inventory_id)->get();
                // $this->getcategory($token);die;
                if(count($products)>0){
                    foreach ($products as $product){
                        
                        $key = '';
                        $temp = [];
                        $temp['live'] = true;
                        $temp['can_be_added_to_cart'] = true;
                        if($account_id==786 || $account_id==1284 || $account_id==1492){
                            if($product->sku){
                                $temp['product_code'] = $product->sku;
                            }else if($product->barcode && $product->barcode !='Does Not Apply'){
                                $temp['product_code'] = $product->barcode;
                            }
                        }else{
                            if($product->barcode && $product->barcode !='Does Not Apply'){
                                $temp['product_code'] = $product->barcode;
                            }
                            else if($product->sku){
                                $temp['product_code'] = $product->sku;
                            }
                        }
                        
                        if(isset($product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']!=''){
                            $temp['name'] = $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value'];
                        }else{
                            $temp['name'] = $product->name;
                        }
                        if(isset($product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                            $temp['price'] = (double)$product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                        }elseif(isset($product->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                             $temp['price'] = (double)$product->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                        }elseif($product->sale_price && $product->sale_price!=0){
                            $temp['price']=(double)$product->sale_price;
                        }else{
                            $temp['price']=(double)$product->price;
                        }
                        if(isset($product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                            $temp['number_in_stock'] = $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                        }else{
                             if($product->quantity || $product->quantity===0){
                                $temp['number_in_stock'] = $product->quantity;
                            }else{
                                if($product->stock_status==1){
                                    $temp['number_in_stock'] = 999;
                                }
                            }
                        }

                        //Bundle product quantity
                        if($product->type==3){
                            $bundlequantitydetails= \LMS\Libraries\CustomModules\BundleProducts\Controllers\BundleProductController::isInStockByBundleProductId($product->id);
                            $bundlequantitydetailsres=json_decode($bundlequantitydetails->getContent());
                            if(isset($bundlequantitydetailsres->stock) || $bundlequantitydetailsres->stock===null){
                                $bundlequantity=$bundlequantitydetailsres->stock;
                            }else{
                                $bundlequantity=0;
                            }
                            if($bundlequantity === null){   
                                $quantity = 999;
                            }
                            elseif($bundlequantity === 0 || $bundlequantity < 0){
                                $quantity = $bundlequantity;
                            }else{
                                $quantity=$bundlequantity;
                            }
                            $temp['number_in_stock'] = $quantity;
                        }
                        
                        if(isset($product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']!=''){
                            $temp['short_description'] = $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value'];
                        }else{
                             $temp['short_description'] = $product->short_description;
                        }
                        if(isset($product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']!=''){
                            $temp['description'] = $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value'];
                        }else{
                            $temp['description'] = ($product->long_description != '') ? $product->long_description : $product->short_description;
                            $temp['description'] = $temp['description'];
                        }
                        if($temp['description']==$temp['short_description']){
                            unset($temp['short_description']);
                        }
                        //Remove Inline css form Description
                        if(isset($temp['description'])){
                        
                            $temp['description']=$this->striphtmlcss($temp['description']);
                        }
                        if(isset($temp['short_description'])){
                            $temp['short_description']=$this->striphtmlcss($temp['short_description']);
                        }
                        if($product->brand){
                            $temp['brand'] = $product->brand;
                        }
                        if(isset($product->metadata('product_condition')['meta_value'])){
                            $temp['condition']=$product->metadata('product_condition')['meta_value'];
                        }
                        if(isset($product->metadata('product_weight')['meta_value'])){
                            $temp['product_weight']=(double)$product->metadata('product_weight')['meta_value'];
                        }
                        if(isset($product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']!=''){
                            $categories=explode(",",$product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']);
                            if(count($categories)>0){
                                $temp['category_id']=$categories[0];
                                unset($categories[0]);
                            }
                        }

                        /*if(isset($product->category()->title) && $product->category()->title!=''){
                            $category_id=$this->addEkmCategory($token, $product->category()->title);
                            if($category_id){
                                $temp['category_id']=$category_id;
                            }
                        }else if(isset($product->metadata('ebay_category_id')['meta_value'])){
                            $ebay_category=EbayCategories::where('cat_id',$product->metadata('ebay_category_id')['meta_value'])->first();
                            if($ebay_category){
                                    $category_id=$this->addEkmCategory($token, $ebay_category->name);
                                    if($category_id){
                                        $temp['category_id']=$category_id;
                                    }
                            }
                        }*/
                       //echo json_encode($temp);die;
                        //dd($temp);
                       /* if($inventory_id==2323240){
                            dd($temp);
                        }*/
                        $product_code = '';
                        $searchresponse = '';
                        if(isset($temp['product_code'])){
                            $product_code = $temp['product_code'];
                            $params =[
                              'query'=>"product_code eq '".$product_code."'",
                              'orderby'=>'-id' 
                            ];
                            $searchurl = $this->url.'products/search';
                            $searchresponse=$this->call($searchurl,$token,'GET',$params);
                        }
                        
                        /*echo '<pre>';
                        print_r($searchresponse);
                        dd(123);*/
                        if($product_code == '' || ($searchresponse != '' && $this->isJson($searchresponse))) {
                            $store_meta_domain = Storemeta::where([
                                ['account_id', $account_id],
                                ['config_id', $config_id],
                                ['type', 'ekm_domain']
                            ])->first();
                            if(isset($store_meta_domain->value)){
                                $domain_name=$store_meta_domain->value;
                            }else{
                                $domain_name='';
                                $domain_api_url = $this->url.'settings/domains';
                                $domain_api_response=$this->call($domain_api_url,$token,'GET');
                                //dd($domain_api_response);
                                if($domain_api_response != '' && $this->isJson($domain_api_response)) {
                                    $dresp = json_decode($domain_api_response);
                                    if (isset($dresp->data->primary_domain_name)) {
                                        $domain_name=$dresp->data->primary_domain_name;
                                        $storemeta = new Storemeta();
                                        $storemeta->account_id = $account_id;
                                        $storemeta->config_id = $config_id;
                                        $storemeta->type = 'ekm_domain';
                                        $storemeta->name = 'domain';
                                        $storemeta->value = $domain_name;
                                        $storemeta->save(); 
                                    }
                                }
                            }
                            $searchresp = json_decode($searchresponse);
                            //dd($resp);
                            if (isset($searchresp->data) && !empty($searchresp->data)) {
                                $response=array('status'=>0,'message'=>'Product already exists in Ekm.');
                            }else{
                                //dd($searchresp);
                                if(isset($searchresp->code) && $searchresp->code==401){
                                    $formData = Accountservice::where(['id' => $config_id])->orderBy('id', 'desc')->first();
                                    $formData->validate = 0;
                                    $formData->save();
                                    $message=$searchresp->message;
                                    $response=array('status'=>0,'message'=> $message);
                                }else{
                                    try{
                                        $productuploaddata = $this->call($url,$token, 'POST', $temp);
                                        
                                        if($productuploaddata && $this->isJson($productuploaddata)) {
                                            $productuploaddata=json_decode($productuploaddata);
                                            if (isset($productuploaddata->data)) {
                                                $product_id = $productuploaddata->data->id;

                                                //add multiple category to a product
                                                if(isset($categories) && count($categories)>0){
                                                    foreach($categories as $category_id){
                                                        $cat_product_url = $this->url."products/$product_id/categorymanaged/$category_id";
                                                        $category_maped = $this->call($cat_product_url,$token, 'POST');
                                                    }
                                                    
                                                }
                                                $imgdbdata = Ibeinventoryimages::where('inventory_id', $product->id)->where('config_id', $config_id)->where('deleted', 0)->orderBy('sort_order', 'asc')->get();
                                                if(count($imgdbdata)>0){
                                                }else{
                                                    $imgdbdata = Inventoryimages::where('inventory_id', $product->id)->where('deleted', 0)->orderBy('sort_order', 'asc')->orderBy('inventory_images_temp_id', 'asc')->get();
                                                }
                                                $i = 1;
                                                if (!empty($imgdbdata)) {
                                                    foreach ($imgdbdata as $imgdb) {
                                                        $productimages3url=Helper::gets3ImageUrl($product->id,$product->account_id,$imgdb->filename);
                                                        if($productimages3url){
                                                            $productimageurl=$this->url.'products/'.$product_id.'/images/'.$i.'?imageUrl=' . $productimages3url;
                                                            $productimagedata = $this->call($productimageurl, $token, 'POST', array());
                                                            $i++;
                                                        }
                                                        /*$disk = \Storage::disk('s3');
                                                        $filename = $product->account_id . '/' . $product->id . '/' . $imgdb->filename;
                                                        if ($disk->exists($filename)) {
                                                            $command = $disk->getDriver()->getAdapter()->getClient()->getCommand('GetObject', [
                                                                'Bucket' => \Config::get('filesystems.disks.s3.bucket'),
                                                                'Key' => $filename,
                                                            ]);
                
                                                            $request_images = $disk->getDriver()->getAdapter()->getClient()->createPresignedRequest($command, '+10 minutes');
                                                            $image = $request_images->getUri();
                                                            $img = base_path() . '/public/assets/listingimages/' . $imgdb->filename;
                                                            file_put_contents($img, file_get_contents($image));
                                                            $filepath = asset('/assets/listingimages/' . $imgdb->filename);
                                                           // $filepath = 'http://app.onepatch.co.uk/assets/listingimages/' . $imgdb->filename;
                                                            $productimageurl=$this->url.'products/'.$product_id.'/images/'.$i.'?imageUrl=' . $filepath;
                                                            $productimagedata = $this->call($productimageurl, $token, 'POST', array());
                
                                                            $i++;
                                                        }*/
                                                    }
                                                }
                
                                                $inventorymeta=Inventorymeta::where('inventory_id',$product->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                                                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                                                    $inventorymeta->meta_value=$product_id;
                                                    $inventorymeta->config_id = $config_id;
                                                    $inventorymeta->save();
                                                }else{
                                                    $invmeta3 = new Inventorymeta();
                                                    $invmeta3->account_id = $account_id;
                                                    $invmeta3->inventory_id = $product->id;
                                                    $invmeta3->sku = $product->sku;
                                                    $invmeta3->config_id = $config_id;
                                                    $invmeta3->meta_key = "ekm_product_item_id";
                                                    $invmeta3->meta_value = $product_id;
                                                    $invmeta3->save();
                                                }

                                                if(isset($productuploaddata->data->seo_friendly_url)){
                                                    $inventorymeta=Inventorymeta::where('inventory_id',$product->id)->where('meta_key','live_product_url_'.$service_id)->where('config_id',$config_id)->first();
                                                    if($inventorymeta){
                                                        $inventorymeta->meta_value=$domain_name.'/'.$productuploaddata->data->seo_friendly_url;
                                                        $inventorymeta->save();
                                                    }else{
                                                        $invmeta = new Inventorymeta();
                                                        $invmeta->account_id = $account_id;
                                                        $invmeta->sku = $product->sku;
                                                        $invmeta->inventory_id = $product->id;
                                                        $invmeta->config_id = $config_id;
                                                        $invmeta->meta_key = 'live_product_url_'.$service_id;
                                                        $invmeta->meta_value =  $domain_name.'/'.$productuploaddata->data->seo_friendly_url;
                                                        $invmeta->save();
                                                    }
                                                }
                                                
                                                if($product->type==2){ //type 0=>simple product 2=> variable
                                                    $childproducts=Inventory::where('account_id',$account_id)->where('parent_id',$product->id)->where('status','!=',0)->get();
                                                    if($childproducts){
                                                        foreach ($childproducts as $vkey=>$childproduct){
                                                            $tempchild['parent_product_id']=$product_id;
                                                            $tempchild['live'] = true;
                                                            $tempchild['product_code'] = $childproduct->sku;
                                                            if(isset($childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                                                                $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                                            }elseif(isset($childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                                                                 $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                                                            }elseif($childproduct->sale_price && $childproduct->sale_price!=0){
                                                                $tempchild['price']=(double)$childproduct->sale_price;
                                                            }else{
                                                                $tempchild['price']=(double)$childproduct->price;
                                                            }
                                                            if(isset($childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                                                                $tempchild['number_in_stock'] = $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                                                            }else{
                                                                 if($childproduct->quantity || $childproduct->quantity===0){
                                                                    $tempchild['number_in_stock'] = $childproduct->quantity;
                                                                }else{
                                                                    if($childproduct->stock_status==1){
                                                                        $tempchild['number_in_stock'] = 999;
                                                                    }
                                                                }
                                                            }
                                                            if($vkey==0){
                                                                $tempchild['is_default_variant']=true;
                                                            }else{
                                                                $tempchild['is_default_variant']=false;
                                                            }
                                                            $allattributedata = array();
                                                            $childproductattributes=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','like','attribute_%')->get();
                                                            if($childproductattributes){
                                                                foreach($childproductattributes as $key=>$value){
                                                                    $meta_key=strtolower(str_replace('attribute_','',str_replace('pa_', '', $value->meta_key)));
                                                                    if($meta_key=='color'){
                                                                        $meta_key='colour';
                                                                    }
                                                                    //$tempchild['attributes'][$meta_key]=$value->meta_value;
                                                                    $allattributedata[] = array(
                                                                        'variant_name' => ucwords($meta_key),
                                                                        'variant_choice' => $value->meta_value,
                                                                    );
                                                                }
                                                            }
                                                            if(!empty($allattributedata)){
                                                                $tempchild['variant_combinations']=$allattributedata;
                                                            }
                                                            $productvarienturl=$this->url.'products/'.$product_id . '/variants';
                                                            $productuploadvdata = $this->call($productvarienturl,$token, 'POST', $tempchild);
                                                            if($productuploadvdata && $this->isJson($productuploadvdata)) {
                                                                $productuploadvdata=json_decode($productuploadvdata);
                                                                /*echo '<pre>';
                                                                print_r($productuploadvdata);*/
                                                                if (isset($productuploadvdata->data)) {
                                                                    $imgdb = Ibeinventoryimages::where('inventory_id', $childproduct->id)->where('config_id', $config_id)->where('deleted', 0)->orderBy('sort_order', 'asc')->first();
                                                                    if($imgdb){
                                                                        
                                                                    }else{
                                                                        $imgdb = Inventoryimages::where('inventory_id', $childproduct->id)->where('deleted', 0)->orderBy('sort_order', 'asc')->orderBy('inventory_images_temp_id', 'asc')->first();
                                                                    }
                                                                    if ($imgdb) {
                                                                        $productimages3url=Helper::gets3ImageUrl($childproduct->id,$childproduct->account_id,$imgdb->filename);
                                                                        if($productimages3url){
                                                                            $childproduct_image_url=$this->url.'products/' . $product_id . '/variants/' . $productuploadvdata->data[$vkey]->id . '/images/1?imageUrl=' . $productimages3url;
                                                                            $productimagedata = $this->call($childproduct_image_url, $token, 'POST', array());
                                                                        }
                                                                        
                                                                    }
                                                                }
                                                                $inventorymeta=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','ekm_variation_id')->where('config_id',$config_id)->first();
                                                                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                                                                    $inventorymeta->meta_value=isset($productuploadvdata->data[$vkey]->id) ? $productuploadvdata->data[$vkey]->id : 0;
                                                                    $inventorymeta->config_id = $config_id;
                                                                    $inventorymeta->save();
                                                                }else{
                                                                    $invmeta3 = new Inventorymeta();
                                                                    $invmeta3->account_id = $account_id;
                                                                    $invmeta3->inventory_id = $childproduct->id;
                                                                    $invmeta3->sku = $childproduct->sku;
                                                                    $invmeta3->config_id = $config_id;
                                                                    $invmeta3->meta_key = "ekm_variation_id";
                                                                    $invmeta3->meta_value = isset($productuploadvdata->data[$vkey]->id) ? $productuploadvdata->data[$vkey]->id : 0;
                                                                    $invmeta3->save();
                                                                }
                                                            }
                                                            /*if($vkey==8){
                                                               break; 
                                                            }
                                                            $vres[]=$tempchild;*/
                                                            
                                                        }
                                                    }
                                                    $this->updatesingleproduct($inventory_id, $config_id);
                                                }
                                                //$response=array('status'=>0,'message'=>json_encode($tempchild));
                                               // dd($vres);
                                                
                                                $response=array('status'=>1,'message'=>'Product Upload Successfully.');
                                            }else{
                                                if(isset($productuploaddata->message)){
                                                    $message=$productuploaddata->message;
                                                }else{
                                                    $message="Something went wrong in your ekm intregation";
                                                }
                                                //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                                $response=array('status'=>0,'message'=> $message);
                                            }
                                        }else{
                
                                            $response=array('status'=>0,'message'=>'Something went wrong in api response.');
                                        }
                                    }catch (\Exception $ex) {
                                        $response=array('status'=>0,'message'=>$ex->getMessage());
                                        //echo json_encode($response);
                                        //echo 'There was a problem with the Amazon library. Error1: ' . $ex->getMessage();
                                        //exit;
                                    }
                                }
                                //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                
                            }
                        }else{  
                            $response=array('status'=>0,'message'=>'Something went wrong in api response.');
                        }
                        
                    }
                }
                //dd($products);
            }else{
                
                $response=array('status'=>0,'message'=>'Config data not found.','response_time'=>0);
            }
        }
        return json_encode($response);
    }
    //=====Upload to EKM============//
    public function uploadproducts($config_id, $queuetaskid, $offset = 0, $limit = 10){
        ini_set('max_execution_time', 0);
        $response=array();
        $config_details = Accountservice::find($config_id);
        $InventoryQueue=InventoryQueue::find($queuetaskid);
        if ($config_details && $InventoryQueue) {
            $account_id = $config_details->account_id;
            $service_id = $config_details->service_id;
            $config = json_decode($config_details->data);
            //start the task//
            $InventoryQueue->status=1; //started
            $InventoryQueue->save();
            if($config){
                $url = $this->url.'products';
                $token = $config->access_token;
                /*if($account_id==417){
                    $app_con_url='https://app.onepatch.co.uk/get-config-info';
                    $config_details_app = $this->call($app_con_url,'', 'GET');
                    if($config_details_app){
                        $token=json_decode($config_details_app)->access_token;
                    }
                }*/
                $products = Inventory::where('account_id',$account_id)->where('parent_id',0)->where('status','!=',0)->skip($offset)->take($limit)->get();
                if(count($products)>0){
                    foreach ($products as $product){
                        if(isset($product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value']!='') {
                            $response[]=array('status'=>0,'message'=>'Product already exists in Ekm.','name'=>$product->name,'sku'=>$product->sku,'inventory_id'=>$product->id);
                            continue;
                        }
                        $key = '';
                        $temp = [];
                        $temp['live'] = true;
                        $temp['can_be_added_to_cart'] = true;
                        if($account_id==786 || $account_id==1284 || $account_id==1492){
                            if($product->sku){
                                $temp['product_code'] = $product->sku;
                            }else if($product->barcode && $product->barcode !='Does Not Apply'){
                                $temp['product_code'] = $product->barcode;
                            }
                        }else{
                            if($product->barcode && $product->barcode !='Does Not Apply'){
                                $temp['product_code'] = $product->barcode;
                            }
                            else if($product->sku){
                                $temp['product_code'] = $product->sku;
                            }
                        }
                        if(isset($product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']!=''){
                            $temp['name'] = $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value'];
                        }else{
                            $temp['name'] = $product->name;
                        }
                        if(isset($product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                            $temp['price'] = (double)$product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                        }elseif(isset($product->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                             $temp['price'] = (double)$product->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                        }elseif($product->sale_price && $product->sale_price!=0){
                            $temp['price']=(double)$product->sale_price;
                        }else{
                            $temp['price']=(double)$product->price;
                        }
                        if(isset($product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                            $temp['number_in_stock'] = $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                        }else{
                             if($product->quantity || $product->quantity===0){
                                $temp['number_in_stock'] = $product->quantity;
                            }else{
                                if($product->stock_status==1){
                                    $temp['number_in_stock'] = 999;
                                }
                            }
                        }

                        //Bundle product quantity
                        if($product->type==3){
                            $bundlequantitydetails= \LMS\Libraries\CustomModules\BundleProducts\Controllers\BundleProductController::isInStockByBundleProductId($product->id);
                            $bundlequantitydetailsres=json_decode($bundlequantitydetails->getContent());
                            if(isset($bundlequantitydetailsres->stock) || $bundlequantitydetailsres->stock===null){
                                $bundlequantity=$bundlequantitydetailsres->stock;
                            }else{
                                $bundlequantity=0;
                            }
                            if($bundlequantity === null){   
                                $quantity = 999;
                            }
                            elseif($bundlequantity === 0 || $bundlequantity < 0){
                                $quantity = $bundlequantity;
                            }else{
                                $quantity=$bundlequantity;
                            }
                            $temp['number_in_stock'] = $quantity;
                        }
                        
                        if(isset($product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']!=''){
                            $temp['short_description'] = $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value'];
                        }else{
                             $temp['short_description'] = $product->short_description;
                        }
                        if(isset($product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']!=''){
                            $temp['description'] = $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value'];
                        }else{
                            $temp['description'] = ($product->long_description != '') ? $product->long_description : $product->short_description;
                            $temp['description'] = $temp['description'];
                        }
                        if($temp['description']==$temp['short_description']){
                            unset($temp['short_description']);
                        }
                        //Remove Inline css form Description
                        if(isset($temp['description'])){
                        
                            $temp['description']=$this->striphtmlcss($temp['description']);
                        }
                        if(isset($temp['short_description'])){
                            $temp['short_description']=$this->striphtmlcss($temp['short_description']);
                        }
                        if($product->brand){
                            $temp['brand'] = $product->brand;
                        }
                        if(isset($product->metadata('product_condition')['meta_value'])){
                            $temp['condition']=$product->metadata('product_condition')['meta_value'];
                        }
                        if(isset($product->metadata('product_weight')['meta_value'])){
                            $temp['product_weight']=(double)$product->metadata('product_weight')['meta_value'];
                        }

                        /*if(isset($product->category()->title) && $product->category()->title!=''){
                            $category_id=$this->addEkmCategory($token, $product->category()->title);
                            if($category_id){
                                $temp['category_id']=$category_id;
                            }
                        }else if(isset($product->metadata('ebay_category_id')['meta_value'])){
                            $ebay_category=EbayCategories::where('cat_id',$product->metadata('ebay_category_id')['meta_value'])->first();
                            if($ebay_category){
                                    $category_id=$this->addEkmCategory($token, $ebay_category->name);
                                    if($category_id){
                                        $temp['category_id']=$category_id;
                                    }
                            }
                        }*/
                        if(isset($product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']!=''){
                            $categories=explode(",",$product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']);
                            if(count($categories)>0){
                                $temp['category_id']=$categories[0];
                                unset($categories[0]);
                            }
                        }
                        $product_code = '';
                        $searchresponse = '';
                        if(isset($temp['product_code'])){
                            $product_code = $temp['product_code'];
                            $params =[
                              'query'=>"product_code eq '".$product_code."'",
                              'orderby'=>'-id' 
                            ];
                            $searchurl = $this->url.'products/search';
                            $searchresponse=$this->call($searchurl,$token,'GET',$params);
                        }
                        
                        /*echo '<pre>';
                        print_r($searchresponse);
                        dd(123);*/
                        if($product_code == '' || ($searchresponse != '' && $this->isJson($searchresponse))) {
                            $store_meta_domain = Storemeta::where([
                                ['account_id', $account_id],
                                ['config_id', $config_id],
                                ['type', 'ekm_domain']
                            ])->first();
                            if(isset($store_meta_domain->value)){
                                $domain_name=$store_meta_domain->value;
                            }else{
                                $domain_name='';
                                $domain_api_url = $this->url.'settings/domains';
                                $domain_api_response=$this->call($domain_api_url,$token,'GET');
                                //dd($domain_api_response);
                                if($domain_api_response != '' && $this->isJson($domain_api_response)) {
                                    $dresp = json_decode($domain_api_response);
                                    if (isset($dresp->data->primary_domain_name)) {
                                        $domain_name=$dresp->data->primary_domain_name;
                                        $storemeta = new Storemeta();
                                        $storemeta->account_id = $account_id;
                                        $storemeta->config_id = $config_id;
                                        $storemeta->type = 'ekm_domain';
                                        $storemeta->name = 'domain';
                                        $storemeta->value = $domain_name;
                                        $storemeta->save(); 
                                    }
                                }
                            }
                            $searchresp = json_decode($searchresponse);
                            //dd($resp);
                            if (isset($searchresp->data) && !empty($searchresp->data)) {
                                $response[]=array('status'=>0,'message'=>'Product already exists in Ekm.','name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                            }else{
                                //dd($searchresp);
                                if(isset($searchresp->code) && $searchresp->code==401){
                                    $formData = Accountservice::where(['id' => $config_id])->orderBy('id', 'desc')->first();
                                    $formData->validate = 0;
                                    $formData->save();
                                    $message=$searchresp->message;
                                    $response[]=array('status'=>0,'message'=> $message,'name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                                }else{
                                    sleep(1);
                                    try{
                                        $productuploaddata = $this->call($url,$token, 'POST', $temp);
                                        
                                        if($productuploaddata && $this->isJson($productuploaddata)) {
                                            $productuploaddata=json_decode($productuploaddata);
                                            if (isset($productuploaddata->data)) {
                                                $product_id = $productuploaddata->data->id;

                                                //add multiple category to a product
                                                if(isset($categories) && count($categories)>0){
                                                    foreach($categories as $category_id){
                                                        $cat_product_url = $this->url."products/$product_id/categorymanaged/$category_id";
                                                        $category_maped = $this->call($cat_product_url,$token, 'POST');
                                                    }
                                                    
                                                }
                                                $imgdbdata = Ibeinventoryimages::where('inventory_id', $product->id)->where('config_id', $config_id)->where('deleted', 0)->orderBy('sort_order', 'asc')->get();
                                                if(count($imgdbdata)>0){
                                                }else{
                                                    $imgdbdata = Inventoryimages::where('inventory_id', $product->id)->where('deleted', 0)->orderBy('sort_order', 'asc')->orderBy('inventory_images_temp_id', 'asc')->get();
                                                }
                                                $i = 1;
                                                if (!empty($imgdbdata)) {
                                                    foreach ($imgdbdata as $imgdb) {
                                                        $productimages3url=Helper::gets3ImageUrl($product->id,$product->account_id,$imgdb->filename);
                                                        if($productimages3url){
                                                            $productimageurl=$this->url.'products/'.$product_id.'/images/'.$i.'?imageUrl=' . $productimages3url;
                                                            $productimagedata = $this->call($productimageurl, $token, 'POST', array());
                                                            $i++;
                                                        }
                                                        /*$disk = \Storage::disk('s3');
                                                        $filename = $product->account_id . '/' . $product->id . '/' . $imgdb->filename;
                                                        if ($disk->exists($filename)) {
                                                            $command = $disk->getDriver()->getAdapter()->getClient()->getCommand('GetObject', [
                                                                'Bucket' => \Config::get('filesystems.disks.s3.bucket'),
                                                                'Key' => $filename,
                                                            ]);
                
                                                            $request_images = $disk->getDriver()->getAdapter()->getClient()->createPresignedRequest($command, '+10 minutes');
                                                            $image = $request_images->getUri();
                                                            $img = base_path() . '/public/assets/listingimages/' . $imgdb->filename;
                                                            file_put_contents($img, file_get_contents($image));
                                                            $filepath = asset('/assets/listingimages/' . $imgdb->filename);
                                                           // $filepath = 'http://app.onepatch.co.uk/assets/listingimages/' . $imgdb->filename;
                                                            $productimageurl=$this->url.'products/'.$product_id.'/images/'.$i.'?imageUrl=' . $filepath;
                                                            $productimagedata = $this->call($productimageurl, $token, 'POST', array());
                
                                                            $i++;
                                                        }*/
                                                    }
                                                }
                
                                                $inventorymeta=Inventorymeta::where('inventory_id',$product->id)->where('meta_key','ekm_product_item_id')->where('config_id',$config_id)->first();
                                                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                                                    $inventorymeta->meta_value=$product_id;
                                                    $inventorymeta->config_id = $config_id;
                                                    $inventorymeta->save();
                                                }else{
                                                    $invmeta3 = new Inventorymeta();
                                                    $invmeta3->account_id = $account_id;
                                                    $invmeta3->inventory_id = $product->id;
                                                    $invmeta3->sku = $product->sku;
                                                    $invmeta3->config_id = $config_id;
                                                    $invmeta3->meta_key = "ekm_product_item_id";
                                                    $invmeta3->meta_value = $product_id;
                                                    $invmeta3->save();
                                                }

                                                if(isset($productuploaddata->data->seo_friendly_url)){
                                                    $inventorymeta=Inventorymeta::where('inventory_id',$product->id)->where('meta_key','live_product_url_'.$service_id)->where('config_id',$config_id)->first();
                                                    if($inventorymeta){
                                                        $inventorymeta->meta_value=$domain_name.'/'.$productuploaddata->data->seo_friendly_url;
                                                        $inventorymeta->save();
                                                    }else{
                                                        $invmeta = new Inventorymeta();
                                                        $invmeta->account_id = $account_id;
                                                        $invmeta->sku = $product->sku;
                                                        $invmeta->inventory_id = $product->id;
                                                        $invmeta->config_id = $config_id;
                                                        $invmeta->meta_key = 'live_product_url_'.$service_id;
                                                        $invmeta->meta_value =  $domain_name.'/'.$productuploaddata->data->seo_friendly_url;
                                                        $invmeta->save();
                                                    }
                                                }
                                                
                                                if($product->type==2){ //type 0=>simple product 2=> variable
                                                    $childproducts=Inventory::where('account_id',$account_id)->where('parent_id',$product->id)->where('status','!=',0)->get();
                                                    if($childproducts){
                                                        foreach ($childproducts as $vkey=>$childproduct){
                                                            $tempchild['parent_product_id']=$product_id;
                                                            $tempchild['live'] = true;
                                                            $tempchild['product_code'] = $childproduct->sku;
                                                            if(isset($childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                                                                $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                                            }elseif(isset($childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                                                                 $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                                                            }elseif($childproduct->sale_price && $childproduct->sale_price!=0){
                                                                $tempchild['price']=(double)$childproduct->sale_price;
                                                            }else{
                                                                $tempchild['price']=(double)$childproduct->price;
                                                            }
                                                            if(isset($childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                                                                $tempchild['number_in_stock'] = $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                                                            }else{
                                                                 if($childproduct->quantity || $childproduct->quantity===0){
                                                                    $tempchild['number_in_stock'] = $childproduct->quantity;
                                                                }else{
                                                                    if($childproduct->stock_status==1){
                                                                        $tempchild['number_in_stock'] = 999;
                                                                    }
                                                                }
                                                            }
                                                            if($vkey==0){
                                                                $tempchild['is_default_variant']=true;
                                                            }else{
                                                                $tempchild['is_default_variant']=false;
                                                            }
                                                            $allattributedata = array();
                                                            $childproductattributes=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','like','attribute_%')->get();
                                                            if($childproductattributes){
                                                                foreach($childproductattributes as $key=>$value){
                                                                    $meta_key=strtolower(str_replace('attribute_','',str_replace('pa_', '', $value->meta_key)));
                                                                    if($meta_key=='color'){
                                                                        $meta_key='colour';
                                                                    }
                                                                    //$tempchild['attributes'][$meta_key]=$value->meta_value;
                                                                    $allattributedata[] = array(
                                                                        'variant_name' => ucwords($meta_key),
                                                                        'variant_choice' => $value->meta_value,
                                                                    );
                                                                }
                                                            }
                                                            if(!empty($allattributedata)){
                                                                $tempchild['variant_combinations']=$allattributedata;
                                                            }
                                                            $productvarienturl=$this->url.'products/'.$product_id . '/variants';
                                                            $productuploadvdata = $this->call($productvarienturl,$token, 'POST', $tempchild);
                                                            if($productuploadvdata && $this->isJson($productuploadvdata)) {
                                                                $productuploadvdata=json_decode($productuploadvdata);
                                                                /*echo '<pre>';
                                                                print_r($productuploadvdata);*/
                                                                if (isset($productuploadvdata->data)) {
                                                                    $imgdb = Ibeinventoryimages::where('inventory_id', $childproduct->id)->where('config_id', $config_id)->where('deleted', 0)->orderBy('sort_order', 'asc')->first();
                                                                    if($imgdb){
                                                                        
                                                                    }else{
                                                                        $imgdb = Inventoryimages::where('inventory_id', $childproduct->id)->where('deleted', 0)->orderBy('sort_order', 'asc')->orderBy('inventory_images_temp_id', 'asc')->first();
                                                                    }
                                                                    if ($imgdb) {
                                                                        $productimages3url=Helper::gets3ImageUrl($childproduct->id,$childproduct->account_id,$imgdb->filename);
                                                                        if($productimages3url){
                                                                            $childproduct_image_url=$this->url.'products/' . $product_id . '/variants/' . $productuploadvdata->data[$vkey]->id . '/images/1?imageUrl=' . $productimages3url;
                                                                            $productimagedata = $this->call($childproduct_image_url, $token, 'POST', array());
                                                                        }
                                                                        
                                                                    }
                                                                }
                                                                $inventorymeta=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','ekm_variation_id')->where('config_id',$config_id)->first();
                                                                if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                                                                    $inventorymeta->meta_value=isset($productuploadvdata->data[$vkey]->id) ? $productuploadvdata->data[$vkey]->id : 0;
                                                                    $inventorymeta->config_id = $config_id;
                                                                    $inventorymeta->save();
                                                                }else{
                                                                    $invmeta3 = new Inventorymeta();
                                                                    $invmeta3->account_id = $account_id;
                                                                    $invmeta3->inventory_id = $childproduct->id;
                                                                    $invmeta3->sku = $childproduct->sku;
                                                                    $invmeta3->config_id = $config_id;
                                                                    $invmeta3->meta_key = "ekm_variation_id";
                                                                    $invmeta3->meta_value = isset($productuploadvdata->data[$vkey]->id) ? $productuploadvdata->data[$vkey]->id : 0;
                                                                    $invmeta3->save();
                                                                }
                                                            }
                                                            /*if($vkey==8){
                                                               break; 
                                                            }
                                                            $vres[]=$tempchild;*/
                                                            
                                                        }
                                                    }
                                                    $this->updatesingleproduct($product->id, $config_id);
                                                }
                                                //$response=array('status'=>0,'message'=>json_encode($tempchild));
                                               // dd($vres);
                                                
                                                $response[]=array('status'=>1,'message'=>'Product Upload Successfully.','name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                                            }else{
                                                if(isset($productuploaddata->message)){
                                                    $message=$productuploaddata->message;
                                                }else{
                                                    $message="Something went wrong in your ekm intregation";
                                                }
                                                //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                                $response[]=array('status'=>0,'message'=> $message,'name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                                            }
                                        }else{
                
                                            $response[]=array('status'=>0,'message'=>'Something went wrong in api response.','name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                                        }
                                    }catch (\Exception $ex) {
                                        $response=array('status'=>0,'message'=>$ex->getMessage(),'name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                                        //echo json_encode($response);
                                        //echo 'There was a problem with the Amazon library. Error1: ' . $ex->getMessage();
                                        //exit;
                                    }
                                }
                                //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                
                            }
                        }else{  
                            $response=array('status'=>0,'message'=>'Something went wrong in api response.','name'=>$temp['name'],'sku'=>isset($temp['product_code'])?$temp['product_code']:'','inventory_id'=>$product->id);
                        }
                        
                    }
                    if($InventoryQueue){
                        $InventoryQueue->api_status=1;
                        $InventoryQueue->api_response=json_encode($response);
                        $InventoryQueue->status=2;//finished
                        $InventoryQueue->save();
                    }
                }else{
                    if($InventoryQueue){
                        $InventoryQueue->api_status=0;
                        $InventoryQueue->api_response=json_encode(array('status'=>'error','message'=>'No product found.'));
                        $InventoryQueue->status=2; //finished
                        $InventoryQueue->save();
                    }
                    $response=array('status'=>0,'message'=>'No product found.','response_time'=>0); 
                }
                //dd($products);
            }else{
                if($InventoryQueue){
                    $InventoryQueue->api_status=0;
                    $InventoryQueue->api_response=json_encode(array('status'=>'error','message'=>'Config data not found.'));
                    $InventoryQueue->status=2; //finished
                    $InventoryQueue->save();
                }
                $response=array('status'=>0,'message'=>'Config data not found.','response_time'=>0);
            }
        }
        return json_encode($response);
    }

    public function updatesingleproduct($inventory_id, $config_id){
       /* if($inventory_id==42774){
            /*$res = $this->updatesingleproductOnlyQtyPrice($inventory_id, $config_id);
            dd($res);*/
            /*$this->QuantityUpdate($inventory_id);die;
        }*/
        //$this->QuantityUpdate($inventory_id);die;
        $config_details = Accountservice::find($config_id);
        if ($config_details) {
            $account_id = $config_details->account_id;
            $config = json_decode($config_details->data);
            if($config){
                $token = $config->access_token;
                /*if($account_id==417){
                    $app_con_url='https://app.onepatch.co.uk/get-config-info';
                    $config_details_app = $this->call($app_con_url,'', 'GET');
                    if($config_details_app){
                        $token=json_decode($config_details_app)->access_token;
                    }
                }*/
               //$this->getcategory($token);die;
               //dd($this->deleteekmitem($inventory_id));
                $product=Inventory::find($inventory_id);
                if($product){
                    $ekm_item_id=isset($product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value'])?$product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value']:'';
                    if($ekm_item_id){
                        $ekmvarients=array();
                        $params =[
                          'page'=>1,
                          'limit'=>20  
                        ];
                        $productvarienturl=$this->urlv2.'products/'.$ekm_item_id . '/variant_combinations';
                        $getproductvarient = $this->call($productvarienturl, $token, 'GET',$params); 
                        if($getproductvarient && $this->isJson($getproductvarient)) {
                            $productvarient=json_decode($getproductvarient);
                            //dd($productvarient);
                            if(isset($productvarient->data)){
                                $total_page=$productvarient->meta->pages_total;
                                foreach($productvarient->data as $ekmvarient){
                                    $ekmvarients[]=array('id'=>$ekmvarient->id);
                                }
                                //dd($ekmcategories); 
                                $page=2;
                                for($page=2;$page<=$total_page;$page++){
                                   $params =[
                                      'page'=>$page,
                                      'limit'=>20  
                                    ];
                                    $getproductvarient = $this->call($productvarienturl, $token, 'GET',$params);
                                    if($getproductvarient && $this->isJson($getproductvarient)) {
                                        $productvarient=json_decode($getproductvarient);
                                        if(isset($productvarient->data)){
                                           foreach($productvarient->data as $ekmvarient){
                                                $ekmvarients[]=array('id'=>$ekmvarient->id);
                                           }
                                        }
                                    }
                                }
                            }
                        }
                        //dd($ekmvarients);
                        $temp['live'] = true;
                        $temp['can_be_added_to_cart'] = true;
                        if($account_id==786 || $account_id==1284 || $account_id==1492){
                            if($product->sku){
                                $temp['product_code'] = $product->sku;
                            }else if($product->barcode && $product->barcode !='Does Not Apply'){
                                $temp['product_code'] = $product->barcode;
                            }
                        }else{
                            if($product->barcode && $product->barcode !='Does Not Apply'){
                                $temp['product_code'] = $product->barcode;
                            }
                            else if($product->sku){
                                $temp['product_code'] = $product->sku;
                            }
                        }
                        if(isset($product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']!=''){
                            $temp['name'] = $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value'];
                        }else{
                            $temp['name'] = $product->name;
                        }
                        if(isset($product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                            $temp['price'] = (double)$product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                        }elseif(isset($product->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                             $temp['price'] = (double)$product->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                        }elseif($product->sale_price && $product->sale_price!=0){
                            $temp['price']=(double)$product->sale_price;
                        }else{
                            $temp['price']=(double)$product->price;
                        }
                        if(isset($product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                            $temp['number_in_stock'] = $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                        }else{
                             if($product->quantity || $product->quantity===0){
                                $temp['number_in_stock'] = $product->quantity;
                            }else{
                                if($product->stock_status==1){
                                    $temp['number_in_stock'] = 999;
                                }
                            }
                        }

                        //Bundle product quantity
                        if($product->type==3){
                            $bundlequantitydetails= \LMS\Libraries\CustomModules\BundleProducts\Controllers\BundleProductController::isInStockByBundleProductId($product->id);
                            $bundlequantitydetailsres=json_decode($bundlequantitydetails->getContent());
                            if(isset($bundlequantitydetailsres->stock) || $bundlequantitydetailsres->stock===null){
                                $bundlequantity=$bundlequantitydetailsres->stock;
                            }else{
                                $bundlequantity=0;
                            }
                            if($bundlequantity === null){   
                                $quantity = 999;
                            }
                            elseif($bundlequantity === 0 || $bundlequantity < 0){
                                $quantity = $bundlequantity;
                            }else{
                                $quantity=$bundlequantity;
                            }
                            $temp['number_in_stock'] = $quantity;
                        }
                        
                        if(isset($product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']!=''){
                            $temp['short_description'] = $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value'];
                        }else{
                             $temp['short_description'] = $product->short_description;
                        }
                        if(isset($product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']!=''){
                            $temp['description'] = $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value'];
                        }else{
                            $temp['description'] = ($product->long_description != '') ? $product->long_description : $product->short_description;
                            $temp['description'] = $temp['description'];
                        }
                        if($temp['description']==$temp['short_description']){
                            unset($temp['short_description']);
                        }
                        //Remove Inline css form Description
                        if(isset($temp['description'])){
                        
                            $temp['description']=$this->striphtmlcss($temp['description']);
                        }
                        if(isset($temp['short_description'])){
                            $temp['short_description']=$this->striphtmlcss($temp['short_description']);
                        }
                        if($product->brand){
                            $temp['brand'] = $product->brand;
                        }
                        if(isset($product->metadata('product_condition')['meta_value'])){
                            $temp['condition']=$product->metadata('product_condition')['meta_value'];
                        }
                        if(isset($product->metadata('product_weight')['meta_value'])){
                            $temp['product_weight']=(double)$product->metadata('product_weight')['meta_value'];
                        }
                        if(isset($product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']!=''){
                            $categories=explode(",",$product->metadataWithExtra('ekm_category_id',$config_id)['meta_value']);
                            if(count($categories)>0){
                                $temp['category_id']=$categories[0];
                                
                                // remove category form catagory manage 
                                $cat_delete_product_url = $this->url."products/$ekm_item_id/categorymanaged/".$categories[0];
                                $cat_delete_product = $this->call($cat_delete_product_url,$token, 'DELETE');
                                
                                //end//
                                unset($categories[0]);
                            }
                        }
                        /*if(isset($product->category()->title) && $product->category()->title!=''){
                            $category_id=$this->addEkmCategory($token, $product->category()->title);
                            if($category_id){
                                $temp['category_id']=$category_id;
                            }
                        }else if(isset($product->metadata('ebay_category_id')['meta_value'])){
                            $ebay_category=EbayCategories::where('cat_id',$product->metadata('ebay_category_id')['meta_value'])->first();
                            if($ebay_category){
                                    $category_id=$this->addEkmCategory($token, $ebay_category->name);
                                    if($category_id){
                                        $temp['category_id']=$category_id;
                                    }
                            }
                        }*/
                        //dd($temp);
                        sleep(1);
                        $url = $this->url.'products/'.$ekm_item_id;
                        $productuploaddata = $this->call($url,$token, 'PUT', $temp);
                        if($productuploaddata && $this->isJson($productuploaddata)) {
                            $productuploaddata=json_decode($productuploaddata);
                            if (isset($productuploaddata->data)) {
                                $product_id = $productuploaddata->data->id;
                                //add multiple category to a product
                                if(isset($categories) && count($categories)>0){
                                    foreach($categories as $category_id){
                                        $cat_product_url = $this->url."products/$product_id/categorymanaged/$category_id";
                                        $category_maped = $this->call($cat_product_url,$token, 'POST');
                                    }
                                    
                                }
                                if($product->type==2){ //type 0=>simple product 2=> variable
                                    $childproducts=Inventory::where('account_id',$account_id)->where('parent_id',$product->id)->where('status','!=',0)->get();
                                    if($childproducts){
                                        foreach ($childproducts as $vkey=>$childproduct){
                                            if(isset($ekmvarients[$vkey]['id'])){
                                                $ekm_variation_id=$ekmvarients[$vkey]['id'];
                                            }else{
                                                $ekm_variation_id=0;
                                            }
                                            
                                            $update_varition_id = Inventorymeta::where('inventory_id', $childproduct->id)->where('meta_key', 'ekm_variation_id')->update(['meta_value' => $ekm_variation_id]);
                                            
                                            $ekm_variation_id=isset($childproduct->metadataWithExtra('ekm_variation_id',$config_id)['meta_value'])?$childproduct->metadataWithExtra('ekm_variation_id',$config_id)['meta_value']:'';
                                            //$variation_ids[]=array('key'=>$vkey,'id'=>$ekm_variation_id);
                                            if($ekm_variation_id){
                                                $tempchild['live'] = true;
                                                $tempchild['product_code'] = $childproduct->sku;
                                                if(isset($childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                                                    $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                                }elseif(isset($childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                                                     $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                                                }elseif($childproduct->sale_price && $childproduct->sale_price!=0){
                                                    $tempchild['price']=(double)$childproduct->sale_price;
                                                }else{
                                                    $tempchild['price']=(double)$childproduct->price;
                                                }
                                                if(isset($childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                                                    $tempchild['number_in_stock'] = $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                                                }else{
                                                     if($childproduct->quantity || $childproduct->quantity===0){
                                                        $tempchild['number_in_stock'] = $childproduct->quantity;
                                                    }else{
                                                        if($childproduct->stock_status==1){
                                                            $tempchild['number_in_stock'] = 999;
                                                        }
                                                    }
                                                }
                                                if($vkey==0){
                                                    $tempchild['is_default_variant']=true;
                                                }else{
                                                    $tempchild['is_default_variant']=false;
                                                }
                                                $allattributedata = array();
                                                $childproductattributes=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','like','attribute_%')->get();
                                                if($childproductattributes){
                                                    foreach($childproductattributes as $key=>$value){
                                                        $meta_key=strtolower(str_replace('attribute_','',str_replace('pa_', '', $value->meta_key)));
                                                        if($meta_key=='color'){
                                                            $meta_key='colour';
                                                        }
                                                        $tempchild['attributes'][$meta_key]=$value->meta_value;
                                                        $allattributedata[] = array(
                                                            'variant_name' => ucwords($meta_key),
                                                            'variant_choice' => $value->meta_value,
                                                        );
                                                    }
                                                }
                                                if(!empty($allattributedata)){
                                                    $tempchild['variant_combinations']=$allattributedata;
                                                }
                                                //dd(json_encode($tempchild));
                                                $productvarienturl=$this->url.'products/'.$product_id . '/variants/'.$ekm_variation_id;
                                                $productuploadvdata = $this->call($productvarienturl,$token, 'PUT', $tempchild);
                                            }
                                            
                                        }
                                    }
                                    //dd($variation_ids);

                                }
                                $response=array('status'=>1,'message'=>'Product Update Successfully.');
                            }else{
                                if(isset($productuploaddata->message)){
                                    $message=$productuploaddata->message;
                                }else{
                                    $message="Something went wrong in your ekm intregation";
                                }
                                //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                $response=array('status'=>0,'message'=> $message);
                            }
                        }else{
                           $response=array('status'=>0,'message'=>'Something went wrong in api response.');  
                        }
                    }else{
                       $response=array('status'=>0,'message'=>'Ekm item id not found.'); 
                    }
                }else{
                    $response=array('status'=>0,'message'=>'Inventory not found.');
                }
            }else{
                $response=array('status'=>0,'message'=>'Config data not found.');
            }
        }else{       
            $response=array('status'=>0,'message'=>'Config data not found.');
        }
        return json_encode($response);
    }

    public function updatesingleproductOnlyQtyPrice($inventory_id, $config_id){
        //$this->QuantityUpdate($inventory_id);die;
        $config_details = Accountservice::find($config_id);
        if ($config_details) {
            $account_id = $config_details->account_id;
            $config = json_decode($config_details->data);
            if($config){
                $token = $config->access_token;
                /*if($account_id==417){
                    $app_con_url='https://app.onepatch.co.uk/get-config-info';
                    $config_details_app = $this->call($app_con_url,'', 'GET');
                    if($config_details_app){
                        $token=json_decode($config_details_app)->access_token;
                    }
                }*/
               //$this->getcategory($token);die;
               //dd($this->deleteekmitem($inventory_id));
                $product=Inventory::find($inventory_id);
                if($product){
                    $ekm_item_id=isset($product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value'])?$product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value']:'';
                    if($ekm_item_id){
                        $ekmvarients=array();
                        $params =[
                          'page'=>1,
                          'limit'=>20  
                        ];
                        $productvarienturl=$this->urlv2.'products/'.$ekm_item_id . '/variant_combinations';
                        $getproductvarient = $this->call($productvarienturl, $token, 'GET',$params); 
                        if($getproductvarient && $this->isJson($getproductvarient)) {
                            $productvarient=json_decode($getproductvarient);
                            //dd($productvarient);
                            if(isset($productvarient->data)){
                                $total_page=$productvarient->meta->pages_total;
                                foreach($productvarient->data as $ekmvarient){
                                    $ekmvarients[]=array('id'=>$ekmvarient->id);
                                }
                                //dd($ekmcategories); 
                                $page=2;
                                for($page=2;$page<=$total_page;$page++){
                                   $params =[
                                      'page'=>$page,
                                      'limit'=>20  
                                    ];
                                    $getproductvarient = $this->call($productvarienturl, $token, 'GET',$params);
                                    if($getproductvarient && $this->isJson($getproductvarient)) {
                                        $productvarient=json_decode($getproductvarient);
                                        if(isset($productvarient->data)){
                                           foreach($productvarient->data as $ekmvarient){
                                                $ekmvarients[]=array('id'=>$ekmvarient->id);
                                           }
                                        }
                                    }
                                }
                            }
                        }
                        //dd($ekmvarients);
                        $temp['live'] = true;
                        $temp['can_be_added_to_cart'] = true;
                        
                        if(isset($product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']!=''){
                            $temp['name'] = $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value'];
                        }else{
                            $temp['name'] = $product->name;
                        }
                        if(isset($product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                            $temp['price'] = (double)$product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                        }elseif(isset($product->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                             $temp['price'] = (double)$product->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                        }elseif($product->sale_price && $product->sale_price!=0){
                            $temp['price']=(double)$product->sale_price;
                        }else{
                            $temp['price']=(double)$product->price;
                        }
                        if(isset($product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                            $temp['number_in_stock'] = $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                        }else{
                             if($product->quantity || $product->quantity===0){
                                $temp['number_in_stock'] = $product->quantity;
                            }else{
                                if($product->stock_status==1){
                                    $temp['number_in_stock'] = 999;
                                }
                            }
                        }
    
                        //Bundle product quantity
                        if($product->type==3){
                            $bundlequantitydetails= \LMS\Libraries\CustomModules\BundleProducts\Controllers\BundleProductController::isInStockByBundleProductId($product->id);
                            $bundlequantitydetailsres=json_decode($bundlequantitydetails->getContent());
                            if(isset($bundlequantitydetailsres->stock) || $bundlequantitydetailsres->stock===null){
                                $bundlequantity=$bundlequantitydetailsres->stock;
                            }else{
                                $bundlequantity=0;
                            }
                            if($bundlequantity === null){   
                                $quantity = 999;
                            }
                            elseif($bundlequantity === 0 || $bundlequantity < 0){
                                $quantity = $bundlequantity;
                            }else{
                                $quantity=$bundlequantity;
                            }
                            $temp['number_in_stock'] = $quantity;
                        }
                        
                        if(isset($product->metadata('product_weight')['meta_value'])){
                            $temp['product_weight']=(double)$product->metadata('product_weight')['meta_value'];
                        }
                        //dd($temp);
                        $url = $this->url.'products/'.$ekm_item_id;
                        $productuploaddata = $this->call($url,$token, 'PUT', $temp);
                        if($productuploaddata && $this->isJson($productuploaddata)) {
                            $productuploaddata=json_decode($productuploaddata);
                            if (isset($productuploaddata->data)) {
                                $product_id = $productuploaddata->data->id;
                                //add multiple category to a product
                                if(isset($categories) && count($categories)>0){
                                    foreach($categories as $category_id){
                                        $cat_product_url = $this->url."products/$product_id/categorymanaged/$category_id";
                                        $category_maped = $this->call($cat_product_url,$token, 'POST');
                                    }
                                    
                                }
                                if($product->type==2){ //type 0=>simple product 2=> variable
                                    $childproducts=Inventory::where('account_id',$account_id)->where('parent_id',$product->id)->where('status','!=',0)->get();
                                    if($childproducts){
                                        foreach ($childproducts as $vkey=>$childproduct){
                                            if(isset($ekmvarients[$vkey]['id'])){
                                                $ekm_variation_id=$ekmvarients[$vkey]['id'];
                                            }else{
                                                $ekm_variation_id=0;
                                            }
                                            
                                            $update_varition_id = Inventorymeta::where('inventory_id', $childproduct->id)->where('meta_key', 'ekm_variation_id')->update(['meta_value' => $ekm_variation_id]);
                                            
                                            $ekm_variation_id=isset($childproduct->metadataWithExtra('ekm_variation_id',$config_id)['meta_value'])?$childproduct->metadataWithExtra('ekm_variation_id',$config_id)['meta_value']:'';
                                            //$variation_ids[]=array('key'=>$vkey,'id'=>$ekm_variation_id);
                                            if($ekm_variation_id){
                                                $tempchild['live'] = true;
                                                $tempchild['product_code'] = $childproduct->sku;
                                                if(isset($childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                                                    $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                                }elseif(isset($childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                                                     $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                                                }elseif($childproduct->sale_price && $childproduct->sale_price!=0){
                                                    $tempchild['price']=(double)$childproduct->sale_price;
                                                }else{
                                                    $tempchild['price']=(double)$childproduct->price;
                                                }
                                                if(isset($childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                                                    $tempchild['number_in_stock'] = $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                                                }else{
                                                     if($childproduct->quantity || $childproduct->quantity===0){
                                                        $tempchild['number_in_stock'] = $childproduct->quantity;
                                                    }else{
                                                        if($childproduct->stock_status==1){
                                                            $tempchild['number_in_stock'] = 999;
                                                        }
                                                    }
                                                }
                                                if($vkey==0){
                                                    $tempchild['is_default_variant']=true;
                                                }else{
                                                    $tempchild['is_default_variant']=false;
                                                }
                                                $allattributedata = array();
                                                $childproductattributes=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','like','attribute_%')->get();
                                                if($childproductattributes){
                                                    foreach($childproductattributes as $key=>$value){
                                                        $meta_key=strtolower(str_replace('attribute_','',str_replace('pa_', '', $value->meta_key)));
                                                        if($meta_key=='color'){
                                                            $meta_key='colour';
                                                        }
                                                        $tempchild['attributes'][$meta_key]=$value->meta_value;
                                                        $allattributedata[] = array(
                                                            'variant_name' => ucwords($meta_key),
                                                            'variant_choice' => $value->meta_value,
                                                        );
                                                    }
                                                }
                                                if(!empty($allattributedata)){
                                                    $tempchild['variant_combinations']=$allattributedata;
                                                }
                                                //dd(json_encode($tempchild));
                                                $productvarienturl=$this->url.'products/'.$product_id . '/variants/'.$ekm_variation_id;
                                                $productuploadvdata = $this->call($productvarienturl,$token, 'PUT', $tempchild);
                                            }
                                            
                                        }
                                    }
                                    //dd($variation_ids);

                                }
                                $response=array('status'=>1,'message'=>'Product Update Successfully.');
                            }else{
                                if(isset($productuploaddata->message)){
                                    $message=$productuploaddata->message;
                                }else{
                                    $message="Something went wrong in your ekm intregation";
                                }
                                //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                $response=array('status'=>0,'message'=> $message);
                            }
                        }else{
                           $response=array('status'=>0,'message'=>'Something went wrong in api response.');  
                        }
                    }else{
                       $response=array('status'=>0,'message'=>'Ekm item id not found.'); 
                    }
                }else{
                    $response=array('status'=>0,'message'=>'Inventory not found.');
                }
            }else{
                $response=array('status'=>0,'message'=>'Config data not found.');
            }
        }else{       
            $response=array('status'=>0,'message'=>'Config data not found.');
        }
        return json_encode($response);
    }

    public function QuantityUpdate($inventory_id){
        $response=array();
        $inventorymetamultiple = Inventorymeta::where('meta_key','ekm_product_item_id')->where('inventory_id',$inventory_id)->get();
        if($inventorymetamultiple){
            foreach($inventorymetamultiple as $key=>$value){
                $config_id=$value->config_id;
                $config_details = Accountservice::where(['status' => 1, 'id' =>$config_id])->where('data', '!=', '')->first();
                if ($config_details) {
                    // return $config_details;
                    $account_id = $config_details->account_id;
                    $config = json_decode($config_details->data);
                    if($config){
                        $token = $config->access_token;
                        $product=Inventory::find($inventory_id);
                        if($product){
                            $ekm_item_id=isset($product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value'])?$product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value']:'';
                            if($ekm_item_id){
                                /*if($product->sku){
                                    $temp['product_code'] = $product->sku;
                                 }*/
                                if(isset($product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value']!=''){
                                    $temp['name'] = $product->metadataWithExtra('ekm_product_name',$config_id)['meta_value'];
                                }else{
                                    $temp['name'] = $product->name;
                                }
                                if(isset($product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                                    $temp['price'] = (double)$product->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                }elseif(isset($product->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                                     $temp['price'] = (double)$product->metadataWithExtra('ekm_price',$config_id)['meta_value'];
                                }elseif($product->sale_price && $product->sale_price!=0){
                                    $temp['price']=(double)$product->sale_price;
                                }else{
                                    $temp['price']=(double)$product->price;
                                }
                                $temp['live'] = true;
                                $temp['can_be_added_to_cart'] = true;
                                if(isset($product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                                    $temp['number_in_stock'] = $product->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                                }else{
                                     if($product->quantity || $product->quantity===0){
                                        $temp['number_in_stock'] = $product->quantity;
                                    }else{
                                            if($product->stock_status==1){
                                                $temp['number_in_stock'] = 999;
                                            }
                                        }
                                }

                                //Bundle product quantity
                                if($product->type==3){
                                    $bundlequantitydetails= \LMS\Libraries\CustomModules\BundleProducts\Controllers\BundleProductController::isInStockByBundleProductId($product->id);
                                    $bundlequantitydetailsres=json_decode($bundlequantitydetails->getContent());
                                    if(isset($bundlequantitydetailsres->stock) || $bundlequantitydetailsres->stock===null){
                                        $bundlequantity=$bundlequantitydetailsres->stock;
                                    }else{
                                        $bundlequantity=0;
                                    }
                                    if($bundlequantity === null){   
                                        $quantity = 999;
                                    }
                                    elseif($bundlequantity === 0 || $bundlequantity < 0){
                                        $quantity = $bundlequantity;
                                    }else{
                                        $quantity=$bundlequantity;
                                    }
                                    $temp['number_in_stock'] = $quantity;
                                }
                                if(isset($product->metadata('product_weight')['meta_value'])){
                                    $temp['product_weight']=(double)$product->metadata('product_weight')['meta_value'];
                                }
                                /*if(isset($product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value']!=''){
                                    $temp['short_description'] = $product->metadataWithExtra('ekm_shortDescription',$config_id)['meta_value'];
                                }else{
                                     $temp['short_description'] = $product->short_description;
                                }
                                if(isset($product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']) && $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value']!=''){
                                    $temp['description'] = $product->metadataWithExtra('ekm_longDescription',$config_id)['meta_value'];
                                }else{
                                    $temp['description'] = ($product->long_description != '') ? $product->long_description : $product->short_description;
                                    $temp['description'] = $temp['description'];
                                }
                                if($product->brand){
                                    $temp['brand'] = $product->brand;
                                }
                                if(isset($product->metadata('product_condition')['meta_value'])){
                                    $temp['condition']=$product->metadata('product_condition')['meta_value'];
                                }
                                if(isset($product->metadata('product_weight')['meta_value'])){
                                    $temp['product_weight']=$product->metadata('product_weight')['meta_value'];
                                }*/
                                $url = $this->url.'products/'.$ekm_item_id;
                                $productuploaddata = $this->call($url,$token, 'PUT', $temp);
                                if($productuploaddata && $this->isJson($productuploaddata)) {
                                    $productuploaddata=json_decode($productuploaddata);
                                    //dd($productuploaddata);
                                    /*if (isset($productuploaddata->data)) {
                                        $product_id = $productuploaddata->data->id;
                                        if($product->type==2){ //type 0=>simple product 2=> variable
                                            $childproducts=Inventory::where('account_id',$account_id)->where('parent_id',$product->id)->where('status','!=',0)->get();
                                            if($childproducts){
                                                foreach ($childproducts as $childproduct){
                                                    $ekm_variation_id=isset($childproduct->metadataWithExtra('ekm_variation_id',$config_id)['meta_value'])?$childproduct->metadataWithExtra('ekm_variation_id',$config_id)['meta_value']:'';
                                                    if($ekm_variation_id){
                                                        $tempchild['live'] = true;
                                                        $tempchild['product_code'] = $childproduct->sku;
                                                        if(isset($childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value']!=''){
                                                            $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                                        }elseif(isset($childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_price',$config_id)['meta_value']!=''){
                                                             $tempchild['price'] = (double)$childproduct->metadataWithExtra('ekm_selling_price',$config_id)['meta_value'];
                                                        }elseif($childproduct->sale_price){
                                                            $tempchild['price']=(double)$childproduct->sale_price;
                                                        }else{
                                                            $tempchild['price']=(double)$childproduct->price;
                                                        }
                                                        if(isset($childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']) && $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value']!=''){
                                                            $tempchild['number_in_stock'] = $childproduct->metadataWithExtra('ekm_product_quantity',$config_id)['meta_value'];
                                                        }else{
                                                             if($childproduct->quantity){
                                                                $tempchild['number_in_stock'] = $childproduct->quantity;
                                                            }
                                                        }
                                                        $allattributedata = array();
                                                        $childproductattributes=Inventorymeta::where('inventory_id',$childproduct->id)->where('meta_key','like','attribute_%')->get();
                                                        if($childproductattributes){
                                                            foreach($childproductattributes as $key=>$value){
                                                                $meta_key=strtolower(str_replace('attribute_','',str_replace('pa_', '', $value->meta_key)));
                                                                if($meta_key=='color'){
                                                                    $meta_key='colour';
                                                                }
                                                                $tempchild['attributes'][$meta_key]=$value->meta_value;
                                                                $allattributedata[] = array(
                                                                    'variant_name' => ucwords($meta_key),
                                                                    'variant_choice' => $value->meta_value,
                                                                );
                                                            }
                                                        }
                                                        if(!empty($allattributedata)){
                                                            $tempchild['variant_combinations']=$allattributedata;
                                                        }

                                                        $productvarienturl=$this->url.'products/'.$product_id . '/variants/'.$ekm_variation_id;
                                                        $productuploadvdata = $this->call($productvarienturl,$token, 'PUT', $tempchild);
                                                    }
                                                    
                                                }
                                            }

                                        }
                                        $response=array('status'=>1,'message'=>'Product Update Successfully.');
                                    }else{
                                        if(isset($productuploaddata->message)){
                                            $message=$productuploaddata->message;
                                        }else{
                                            $message="Something went wrong in your ekm intregation";
                                        }
                                        //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                                        $response=array('status'=>0,'message'=> $message);
                                    }*/
                                }else{
                                   $response=array('status'=>0,'message'=>'Something went wrong in api response.');  
                                }
                            }else{
                               $response=array('status'=>0,'message'=>'Ekm item id not found.'); 
                            }
                        }else{
                            $response=array('status'=>0,'message'=>'Inventory not found.');
                        }
                    }else{
                        $response=array('status'=>0,'message'=>'Config data not found.');
                    }
                }else{
                    $response = array("status"=>0, "message"=>"Config data not found.");
                }
            }
        }else{
            $response = array("status"=>0, "message"=>"Can not find Inventory!");
        }
        return json_encode($response);

    }

    public function syncproduct($ekm_product_id,$inventory_id,$config_id){
        /*$this->CheckNewProduct(411); 
        dd(123);*/
        $response=array();
        $inv = Inventory::find($inventory_id);
        if ($inv != '') {
            if (is_numeric($config_id)) {
                $accountservice=Accountservice::find($config_id);
                if($accountservice){
                    $account_id=$accountservice->account_id;
                    $service_id = $accountservice->service_id;
                    $config=json_decode($accountservice->data);
                    if($config){
                        $url = $this->url.'products/'.$ekm_product_id;
                        $token = $config->access_token;
                        $response_one = microtime(1);
                        $response=$this->call($url,$token,'GET',$params=array());
                        
                        //dd($response);
                        if($response && $this->isJson($response)) {
                            $resp = json_decode($response);
                            if(isset($resp->data) && !empty($resp->data)) {
                                $store_meta_domain = Storemeta::where([
                                    ['account_id', $account_id],
                                    ['config_id', $config_id],
                                    ['type', 'ekm_domain']
                                ])->first();
                                if(isset($store_meta_domain->value)){
                                    $domain_name=$store_meta_domain->value;
                                }else{
                                    $domain_name='';
                                    $domain_api_url = $this->url.'settings/domains';
                                    $domain_api_response=$this->call($domain_api_url,$token,'GET');
                                    //dd($domain_api_response);
                                    if($domain_api_response != '' && $this->isJson($domain_api_response)) {
                                        $dresp = json_decode($domain_api_response);
                                        if (isset($dresp->data->primary_domain_name)) {
                                            $domain_name=$dresp->data->primary_domain_name;
                                            $storemeta = new Storemeta();
                                            $storemeta->account_id = $account_id;
                                            $storemeta->config_id = $config_id;
                                            $storemeta->type = 'ekm_domain';
                                            $storemeta->name = 'domain';
                                            $storemeta->value = $domain_name;
                                            $storemeta->save(); 
                                        }
                                    }
                                }
                                $product_data=$resp->data;
                                if (!empty($inv)) {
                                    
                                    if (isset($product_data->product_code) && $product_data->product_code != '' && $product_data->product_code != "Identifier doesn't exist"){
                                        $inv->sku = $product_data->product_code;
                                    }
                                    
                                    if(isset($product_data->attribute_items) && is_array($product_data->attribute_items) && !empty($product_data->attribute_items)){
                                        foreach($product_data->attribute_items as $attributes){
                                            if(isset($attributes->attribute_key)){
                                                if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='SKU'){
                                                    $inv->sku = $attributes->attribute_value;
                                                }
                                                
                                                if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='EAN'){
                                                    $inv->barcode = $attributes->attribute_value;
                                                    $inv->barcode_type='EAN';
                                                }else if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='UPC' && strtolower($attributes->attribute_value) != 'does not apply'){
                                                    $inv->barcode = $attributes->attribute_value;
                                                    $inv->barcode_type='UPC';
                                                }
                                                else if(isset($attributes->attribute_value) && $attributes->attribute_value && trim($attributes->attribute_key)=='MPN' && strtolower($attributes->attribute_value) != 'does not apply'){
                                                    $inv->barcode = $attributes->attribute_value;
                                                    $inv->barcode_type='MPN';
                                                }
                                            }
                                        }
                                    }
                                    if (isset($product_data->gtin) && $product_data->gtin != '' && $product_data->gtin != "Identifier doesn't exist"){
                                        $inv->barcode = $product_data->gtin;
                                        $inv->barcode_type='GTIN';
                                    }
                                    $inv->name = $product_data->name;
                                    $inv->short_description = $product_data->short_description;
                                    $inv->long_description = $product_data->description;
                                    $inv->brand = $product_data->brand;
                                    if (isset($product_data->total_product_stock) && $product_data->total_product_stock != '') {
                                        $inv->quantity = $product_data->total_product_stock;
                                        if($product_data->total_product_stock==0){
                                           $inv->stock_status=0; 
                                       }else{
                                            $inv->stock_status=1;
                                       }
                                    }else{
                                        $inv->quantity = 0;
                                        $inv->stock_status=0;
                                    }

                                    if (isset($product_data->price) && $product_data->price != ''){
                                        $inv->price = $product_data->price;
                                    }
                                    $inv->save();
                                    $category = '';
                                    if ($product_data->category_id != '') {
                                        $url = $this->url.'categories/'.$product_data->category_id;
                                        $produccatdata=$this->call($url,$token,'GET',$params=array());
                                        if (isset($produccatdata) && $this->isJson($produccatdata)) {
                                            $produccatdata=json_decode($produccatdata);
                                            if (isset($produccatdata->data->name)) {
                                                $category = $produccatdata->data->name;
                                            }
                                        }
                                    }
                                    //dd($temp_images);
                                    if($category){
                                        $product_categories=json_encode(array($category)); 
                                        $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_category_info')->first();
                                        if($inventorymeta){
                                            $inventorymeta->meta_value=$product_categories;
                                            $inventorymeta->save();
                                        }else{

                                            $invmeta = new Inventorymeta();
                                            $invmeta->account_id = $account_id;
                                            $invmeta->sku = $inv->sku;
                                            $invmeta->inventory_id = $inventory_id;
                                            $invmeta->meta_key = 'product_category_info';
                                            $invmeta->meta_value =  $product_categories;
                                            $invmeta->save(); 
                                        }

                                    }

                                    if (!empty($product_data->categories)) {
                                        $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','ekm_category_id')->where('config_id',$config_id)->first();
                                        $product_categorie_ids=implode(",",(array_column((array)$product_data->categories, 'category_id')));
                                        if($inventorymeta && ($inventorymeta->config_id==$config_id)){
                                            $inventorymeta->meta_value=$product_categorie_ids;
                                            $inventorymeta->save();
                                        }else{
                                            $invmeta3 = new Inventorymeta();
                                            $invmeta3->account_id = $account_id;
                                            $invmeta3->inventory_id = $inventory_id;
                                            $invmeta3->sku = $inv->sku;
                                            $invmeta3->config_id = $config_id;
                                            $invmeta3->meta_key = "ekm_category_id";
                                            $invmeta3->meta_value = $product_categorie_ids;
                                            $invmeta3->save();
                                        }
                                    }

                                    if (!empty($product_data->options)) {
                                        $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_options')->first();
                                        $product_options=json_encode($product_data->options);
                                        if(isset($inventorymeta->id)){
                                            $inventorymeta->meta_value=$product_options;
                                            $inventorymeta->save();
                                        }else{
                                            $invmeta3 = new Inventorymeta();
                                            $invmeta3->account_id = $account_id;
                                            $invmeta3->inventory_id = $inventory_id;
                                            $invmeta3->sku = $inv->sku;
                                            $invmeta3->config_id = $config_id;
                                            $invmeta3->meta_key = "product_options";
                                            $invmeta3->meta_value = $product_options;
                                            $invmeta3->save();
                                        }
                                    }
                                    if($product_data->condition && $product_data->condition!='NotApplicable'){
                                        $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_condition')->first();
                                        if($inventorymeta){
                                            $inventorymeta->meta_value=$product_data->condition;
                                            $inventorymeta->save();
                                        }else{
                                            $invmeta3 = new Inventorymeta();
                                            $invmeta3->account_id = $account_id;
                                            $invmeta3->inventory_id = $inventory_id;
                                            $invmeta3->sku = $inv->sku;
                                            $invmeta3->config_id = $config_id;
                                            $invmeta3->meta_key = "product_condition";
                                            $invmeta3->meta_value = $product_data->condition;
                                            $invmeta3->save();
                                        }
                                    }
                                    if(isset($product_data->product_weight) && $product_data->product_weight!=''){
                                        $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','product_weight')->first();
                                        if($inventorymeta){
                                            $inventorymeta->meta_value=$product_data->product_weight;
                                            $inventorymeta->save();
                                        }else{
                                            $invmeta6 = new Inventorymeta();
                                            $invmeta6->account_id = $account_id;
                                            $invmeta6->inventory_id = $inventory_id;
                                            $invmeta6->sku = $inv->sku;
                                            $invmeta6->config_id = $config_id;
                                            $invmeta6->meta_key = "product_weight";
                                            $invmeta6->meta_value = $product_data->product_weight;
                                            $invmeta6->save();
                                        }
                                    }
                                    if(isset($product_data->seo_friendly_url)){
                                        $inventorymeta=Inventorymeta::where('inventory_id',$inventory_id)->where('meta_key','live_product_url_'.$service_id)->where('config_id',$config_id)->first();
                                        if($inventorymeta){
                                            $inventorymeta->meta_value=$domain_name.'/'.$product_data->seo_friendly_url;
                                            $inventorymeta->save();
                                        }else{
                                            $invmeta = new Inventorymeta();
                                            $invmeta->account_id = $account_id;
                                            $invmeta->sku = $inv->sku;
                                            $invmeta->inventory_id = $inventory_id;
                                            $invmeta->config_id = $config_id;
                                            $invmeta->meta_key = 'live_product_url_'.$service_id;
                                            $invmeta->meta_value =  $domain_name.'/'.$product_data->seo_friendly_url;
                                            $invmeta->save();
                                        }
                                    }
                                    if (isset($product_data->variants) && !empty($product_data->variants)) {
                                        $products = $product_data->variants;
                                        
                                        foreach ($products as $product) {
                                            //dd($product);
                                            $already_exist_chk = Inventorymeta::where(['account_id' => $account_id, 'config_id' => $config_id,'meta_value'=>$product->id])->first();
                                            
                                            if($already_exist_chk){
                                                $child_inventory_id=$already_exist_chk->inventory_id;
                                                $child_inv=Inventory::find($child_inventory_id);
                                                if (!empty($child_inv)) {
                                                    if (isset($product->product_code) && $product->product_code != ''){
                                                        $child_inv->sku = $product->product_code;
                                                    }
                                                    if (isset($product->gtin) && $product->gtin != '' && $product->gtin != "Identifier doesn't exist"){
                                                        $child_inv->barcode = $product->gtin;
                                                        $child_inv->barcode_type='GTIN';
                                                    }
                                                    if (isset($product->number_in_stock) && $product->number_in_stock != '') {
                                                        $child_inv->quantity = $product->number_in_stock;
                                                        if($product->number_in_stock==0){
                                                           $child_inv->stock_status=0; 
                                                       }else{
                                                            $child_inv->stock_status=1;
                                                       }
                                                    }else{
                                                        $child_inv->quantity = 0;
                                                        $child_inv->stock_status=0;
                                                    }

                                                    if (isset($product->price) && $product->price != ''){
                                                        $child_inv->price = $product->price;
                                                    }
                                                    $child_inv->save();
                                                    if($product->condition && $product->condition!='NotApplicable'){
                                                        $inventorymeta=Inventorymeta::where('inventory_id',$child_inventory_id)->where('meta_key','product_condition')->first();
                                                        if($inventorymeta){
                                                            $inventorymeta->meta_value=$product->condition;
                                                            $inventorymeta->save();
                                                        }else{
                                                            $invmeta3 = new Inventorymeta();
                                                            $invmeta3->account_id = $account_id;
                                                            $invmeta3->inventory_id = $child_inventory_id;
                                                            $invmeta3->sku = $child_inv->sku;
                                                            $invmeta3->config_id = $config_id;
                                                            $invmeta3->meta_key = "product_condition";
                                                            $invmeta3->meta_value = $product->condition;
                                                            $invmeta3->save();
                                                        }
                                                    }
                                                    if(isset($product->product_weight) && $product->product_weight!=''){
                                                        $inventorymeta=Inventorymeta::where('inventory_id',$child_inventory_id)->where('meta_key','product_weight')->first();
                                                        if($inventorymeta){
                                                            $inventorymeta->meta_value=$product->product_weight;
                                                            $inventorymeta->save();
                                                        }else{
                                                            $invmeta6 = new Inventorymeta();
                                                            $invmeta6->account_id = $account_id;
                                                            $invmeta6->inventory_id = $inventory_id;
                                                            $invmeta6->sku = $child_inv->sku;
                                                            $invmeta6->config_id = $config_id;
                                                            $invmeta6->meta_key = "product_weight";
                                                            $invmeta6->meta_value = $product->product_weight;
                                                            $invmeta6->save();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                //dd(123);
                                $response=array('status'=>1,'message'=>'Product Sync Successfully.');
                            }else{
                                if(isset($resp->code) && $resp->code==401){
                                    $formData = Accountservice::where(['id' => $config_id])->orderBy('id', 'desc')->first();
                                    $formData->validate = 0;
                                    $formData->save();
                                    $message=$resp->message;
                                }else{
                                    //$message="Something went wrong in your ekm intregation";
                                    $message=json_encode($resp);
                                }
                                $response=array('status'=>0,'message'=>$message); 
                            }
                            
                        }else{
                            $response=array('status'=>0,'message'=>'Something went wrong in api response.');
                        }
                        
                    }
                }else{
                
                    $response=array('status'=>0,'message'=>'Config data not found.','response_time'=>0);
                }
            }
        }else{
            $response=array('status'=>0,'message'=>'Not a valid product');
        }
        //dd($response);
        return json_encode($response);
    }

    private function addEkmCategory($token, $category_text = "Computers/Tablets & Networking > Laptop & Desktop Accessories > Anti-Theft Locks & Kits")
    {
        //$manageekm = new ManageEkm();
        $cat_format = explode('>', $category_text);
       // dd($cat_format);
        $parent_cat = 0;
        if(!empty($cat_format))
        {
            foreach($cat_format as $ecat)
            {
                $catname = ucwords(strtolower(trim($ecat)));
                $catname = str_replace(",", " ",$catname);
                $catname = str_replace("  "," ",$catname);
                //$catname='N Gauge';
                
                $existing_cat_chk = $this->searchEkmCategory($token, $catname,$parent_cat);
                //continue;
                //dd($existing_cat_chk);
                
                $test[]=array('name'=>$catname,'parent'=>$parent_cat,'ex'=>$existing_cat_chk);
                
                if(is_numeric($existing_cat_chk))
                {
                    $parent_cat = $existing_cat_chk;
                }
                else
                {
                    $cat_data = [];
                    $cat_data['name'] = $catname;
                    $cat_data['description'] = $catname;
                    $cat_data['in_category_description'] = $catname;
                    $cat_data['meta_description'] = $catname;
                    $cat_data['meta_keywords'] = $catname;
                    $cat_data['meta_title'] = $catname;
                    $cat_data['live'] = true;
                    $cat_data['parent_category_id'] = $parent_cat;
                    //echo $token;die;
                    $url = $this->url."categories";
                    $order_data = $this->call($url, $token, 'POST', $cat_data);
                    if($order_data && $this->isJson($order_data)){
                        $order_data=json_decode($order_data);
                        if(isset($order_data->data) && !empty($order_data->data) && $order_data->errors == null)
                        {
                            if(isset($order_data->data->id) && is_numeric($order_data->data->id))
                            {
                                $parent_cat  = $order_data->data->id;
                            }
                        }
                    }
                    //echo '<pre>'; print_r($order_data);die;
                        
                }
                
            }
        }
       /* echo $parent_cat;
        dd($test);*/
        return $parent_cat;
        
        
    }
    private function searchEkmCategory($token, $category_text = "Keyboard",$parent_cat){
        $category_text=trim(strtolower($category_text));
        $params =[
                  'query'=> "contains(tolower(name), '$category_text')",
                ];
        $url = $this->url."categories/search";
        $order_data = $this->call($url,$token,'GET',$params);
        //dd($order_data);
        if($order_data && $this->isJson($order_data)){
            $order_data=json_decode($order_data);
            if(isset($order_data->data) && !empty($order_data->data) && $order_data->errors == null){
                $catinfo = $order_data->data;
                if(!empty($catinfo)){
                    foreach($catinfo as $catinfo){
                       if(isset($catinfo->id) && is_numeric($catinfo->id) )
                        {
                            if($parent_cat==0){
                               $parent_cat=null;
                            }
                            if($catinfo->parent_category_id==$parent_cat && $category_text==trim(strtolower($catinfo->name)) ){
                                return $catinfo->id;
                                break;
                            }
                            
                        } 
                    }
                }
                
                
            }
            return false;
        }
        
    }
    public function addEkmCategorytest($token, $category_text,$parent_cat)
    {
        
        $cat_id = 0;
        $catname = ucwords(strtolower(trim($category_text)));
        $catname = str_replace(",", " ",$catname);
        $catname = str_replace("  "," ",$catname);
        
        $cat_data = [];
        $cat_data['name'] = $catname;
        $cat_data['description'] = $catname;
        $cat_data['in_category_description'] = $catname;
        $cat_data['meta_description'] = $catname;
        $cat_data['meta_keywords'] = $catname;
        $cat_data['meta_title'] = $catname;
        $cat_data['live'] = true;
        $cat_data['parent_category_id'] = $parent_cat;
        //echo $token;die;
        $url = $this->url."categories";
        $order_data = $this->call($url, $token, 'POST', $cat_data);
        echo '<pre>';
        print_r($cat_data);
        print_r($order_data);
        if($order_data && $this->isJson($order_data)){
            $order_data=json_decode($order_data);
            if(isset($order_data->data) && !empty($order_data->data) && $order_data->errors == null)
            {
                if(isset($order_data->data->id) && is_numeric($order_data->data->id))
                {
                    $cat_id  = $order_data->data->id;
                    $account_id = 1495;
                    $config_id = 11844;
                    if($parent_cat){
                        $store_meta_cat = Storemeta::where([
                        ['account_id', $account_id],
                        ['config_id', $config_id],
                        ['type', 'ekm_category'],
                        ['name', $catname],

                    ])->first();
                    }else{
                    $store_meta_cat = Storemeta::where([
                        ['account_id', $account_id],
                        ['config_id', $config_id],
                        ['type', 'ekm_category'],
                        ['parent_category_id',$parent_cat],
                        ['name', $catname],
                    ])->first();
                    }
                    if($store_meta_cat){

                    }else{
                        $storemeta = new Storemeta();
                        $storemeta->account_id = $account_id;
                        $storemeta->config_id = $config_id;
                        $storemeta->type = 'ekm_category';
                        $storemeta->name = $catname;
                        $storemeta->value = $cat_id;
                        $storemeta->parent_category_id = $parent_cat;
                        $storemeta->save(); 
                    }

                    /*if($parent_cat!=0){
                        $managecaturl = $this->url."categories/$parent_cat/categorymanaged/$cat_id";
                        $manage_cate_data = $this->call($managecaturl, $token, 'POST');
                        echo '<pre>';
                        print_r($manage_cate_data);
                    }*/
                }
            }
        }
        
        return $cat_id;die;
        
        
    }
    public function deleteEkmItem($inventory_id,$config_id='') {
        $response=array();
        if($config_id){
            $inventorymetamultiple = Inventorymeta::where('meta_key','ekm_product_item_id')->where('inventory_id',$inventory_id)->where('config_id',$config_id)->get(); 
        }else{
            $inventorymetamultiple = Inventorymeta::where('meta_key','ekm_product_item_id')->where('inventory_id',$inventory_id)->get();
        }
        if($inventorymetamultiple){
            foreach($inventorymetamultiple as $key=>$value){
                $config_id=$value->config_id;
                $config_details = Accountservice::where(['status' => 1, 'id' =>$config_id])->where('data', '!=', '')->first();
                if ($config_details) {
                    // return $config_details;
                    $account_id = $config_details->account_id;
                    $config = json_decode($config_details->data);
                    if($config){
                        $token = $config->access_token;
                        $product=Inventory::find($inventory_id);
                        if($product){
                            $ekm_item_id=isset($product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value'])?$product->metadataWithExtra('ekm_product_item_id',$config_id)['meta_value']:'';
                            if($ekm_item_id){
                                try {
                                    $url = $this->url.'products/'.$ekm_item_id;
                                    $productuploaddata = $this->call($url,$token, 'DELETE');
                                    $response=array('status'=>1,'message'=>'Product delete Successfully.','data'=>json_encode($productuploaddata));
                                }
                                catch (\Exception $exc) {
                                    $response=array('status'=>0,'message'=>$exc->getMessage());  
                                }
                            }else{
                               $response=array('status'=>0,'message'=>'Ekm item id not found.'); 
                            }
                        }else{
                            $response=array('status'=>0,'message'=>'Inventory not found.');
                        }
                    }else{
                        $response=array('status'=>0,'message'=>'Config data not found.');
                    }
                }else{
                    $response = array("status"=>0, "message"=>"Config data not found.");
                }
            }
        }else{
            $response = array("status"=>0, "message"=>"Can not find Inventory!");
        }
        return json_encode($response);
    }
    public function buildTree(array $elements, $parentId = 0) {
    
        $branch = array();

        foreach ($elements as $element) 
        {
            if ($element['parent_category_id'] == $parentId) 
            {
                $children = $this->buildTree($elements, $element['id']);
                
                if ($children) 
                {
                    $element['children'] = $children;
                }
                
                $branch[] = $element;
            }
        }

        return $branch;
    }



    public function getcategory($config_id){
        $ekmcategories=array();
        $config_details = Accountservice::find($config_id);
        if ($config_details) {
            $account_id = $config_details->account_id;
            $config = json_decode($config_details->data);
            if($config){
                $access_token = $config->access_token;
                $params =[
                  'page'=>1,
                  'limit'=>20  
                ];
                $getcategory = $this->call('https://api.ekm.net/api/v1/categories', $access_token, 'GET',$params); 
                if($getcategory && $this->isJson($getcategory)) {
                    $category=json_decode($getcategory);
                    if(isset($category->data)){
                        $total_page=$category->meta->pages_total;
                        foreach($category->data as $ekmcategory){
                            $ekmcategories[]=array('id'=>$ekmcategory->id,'name'=>$ekmcategory->name,'parent_category_id'=>$ekmcategory->parent_category_id);
                        }
                        //dd($ekmcategories); 
                        $page=2;
                        for($page=2;$page<=$total_page;$page++){
                           $params =[
                              'page'=>$page,
                              'limit'=>20  
                            ];
                            $getcategory = $this->call('https://api.ekm.net/api/v1/categories', $access_token, 'GET',$params);
                            if($getcategory && $this->isJson($getcategory)) {
                                $category=json_decode($getcategory);
                                if(isset($category->data)){
                                   $total_page=$category->meta->pages_total;
                                   foreach($category->data as $ekmcategory){
                                        $ekmcategories[]=array('id'=>$ekmcategory->id,'name'=>$ekmcategory->name,'parent_category_id'=>$ekmcategory->parent_category_id);
                                   }
                                }
                            }
                        }
                    }
                }
            }
        }
        //return $this->buildTree($ekmcategories);
        return $ekmcategories;
        //dd($ekmcategories);
        //$this->displayCategoryTree($this->buildTree($ekmcategories),$ekm_cat_id='',$indent = '');
       
    }
    
    public function deleteekmcategory($ekm_category_id,$config_id) {
        $response=array();
        
        $config_details = Accountservice::where(['status' => 1, 'id' =>$config_id])->where('data', '!=', '')->first();
        if ($config_details) {
                    // return $config_details;
                    $account_id = $config_details->account_id;
                    $config = json_decode($config_details->data);
                    if($config){
                        $token = $config->access_token;
                        
                        
                            try {
                                $url = $this->url.'categories/'.$ekm_category_id;
                                $productuploaddata = $this->call($url,$token, 'DELETE');
                                $response=array('status'=>1,'message'=>'Category delete Successfully.','data'=>json_encode($productuploaddata));
                            }
                            catch (\Exception $exc) {
                                $response=array('status'=>0,'message'=>$exc->getMessage());  
                            }
                        
                        
                    }else{
                        $response=array('status'=>0,'message'=>'Config data not found.');
                    }
                }else{
                    $response = array("status"=>0, "message"=>"Config data not found.");
                }
            
        
        return json_encode($response);
    }
    
    public function CreateCategory($config_id,$category_text,$parent_cat=0){
        $config_details = Accountservice::find($config_id);
        $response=array('status'=>0,'message'=>'Error in category create');
        if ($config_details) {
            $account_id = $config_details->account_id;
            $config = json_decode($config_details->data);
            // return $config;
            if($config){
                $token = $config->access_token;
                $cat_id = 0;
                $catname = ucwords(strtolower(trim($category_text)));
                $catname = str_replace(",", " ",$catname);
                $catname = str_replace("  "," ",$catname);
                
                $existing_cat_chk = $this->searchEkmCategory($token, $catname,$parent_cat);
                if(is_numeric($existing_cat_chk)){
                    $response=array('status'=>0,'message'=>'Category already exists.');
                }else{
                    $cat_data = [];
                    $cat_data['name'] = $catname;
                    $cat_data['description'] = $catname;
                    $cat_data['in_category_description'] = $catname;
                    $cat_data['meta_description'] = $catname;
                    $cat_data['meta_keywords'] = $catname;
                    $cat_data['meta_title'] = $catname;
                    $cat_data['live'] = true;
                    $cat_data['parent_category_id'] = $parent_cat;
                    //echo $token;die;
                    $url = $this->url."categories";
                    $order_data = $this->call($url, $token, 'POST', $cat_data);
                    //dd($order_data);
                    if($order_data && $this->isJson($order_data)){
                        $order_data=json_decode($order_data);
                        if(isset($order_data->code) && $order_data->code == 401){
                            $message=$order_data->message;
                            $response=array('status'=>0,'message'=>$message);
                        }
                        elseif(isset($order_data->data) && !empty($order_data->data) && $order_data->errors == null)
                        {
                            if(isset($order_data->data->id) && is_numeric($order_data->data->id))
                            {
                                $cat_id  = $order_data->data->id;
                                /*if($parent_cat!=0){
                                    $managecaturl = $this->url."categories/$parent_cat/categorymanaged/$cat_id";
                                    $manage_cate_data = $this->call($managecaturl, $token, 'POST');
                                    echo '<pre>';
                                    print_r($manage_cate_data);
                                }*/
                                $response=array('status'=>1,'message'=>'Category created successfully','category_id'=>$cat_id,'category_name'=>$catname,'parent_category_id'=>$parent_cat);
                            }else{
                                $response=array('status'=>0,'message'=>'Error in category create');
                            }
                        }elseif(isset($order_data->status) && $order_data->status=='error'){
                            $response=array('status'=>0,'message'=>$order_data->message);
                        }
                    }else{
                        $response=array('status'=>0,'message'=>'Error in category create');
                    }
                }   
            }else{
                $response=array('status'=>0,'message'=>'Config data not found.');
            }
        }else{
            $response=array('status'=>0,'message'=>'Config data not found.');
        }
        return json_encode($response);
    }
    
    public function getCategorydetails($config_id, $category_id){
        $config_details = Accountservice::find($config_id);
        $response=array('status'=>0,'message'=>'Error in category create');
        if ($config_details) {
            $account_id = $config_details->account_id;
            $config = json_decode($config_details->data);
            // return $config;
            if($config){
                $token = $config->access_token;
                $url = $this->url."categories/$category_id";
                $order_data = $this->call($url, $token, 'GET');
                //dd($order_data);
                if($order_data && $this->isJson($order_data)){
                    $order_data=json_decode($order_data);
                    if(isset($order_data->data) && !empty($order_data->data) && $order_data->errors == null)
                    {
                        if(isset($order_data->data->id) && is_numeric($order_data->data->id))
                        {
                            $cat_id  = $order_data->data->id;
                            /*if($parent_cat!=0){
                                $managecaturl = $this->url."categories/$parent_cat/categorymanaged/$cat_id";
                                $manage_cate_data = $this->call($managecaturl, $token, 'POST');
                                echo '<pre>';
                                print_r($manage_cate_data);
                            }*/
                            $response=array('status'=>1,'message'=>'Category created successfully','category_id'=>$cat_id,'parent_category_id'=>$order_data->data->parent_category_id);
                        }else{
                            $response=array('status'=>0,'message'=>'Error in category create');
                        }
                    }
                }else{
                    $response=array('status'=>0,'message'=>'Error in category create');
                }
                    
            }else{
                $response=array('status'=>0,'message'=>'Config data not found.');
            }
        }else{
            $response=array('status'=>0,'message'=>'Config data not found.');
        }
        return json_encode($response);
    }


    
    function call($url, $access_token, $method, $post_data = array()) {
        //echo "access_token:".$access_token;

        $client = new Client();
        if ($method == 'POST') {
            try{
                $response = $client->request('POST', $url,[
                    'headers' => [
                            'Accept'=> 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization'         => 'Bearer '. $access_token,
                     ],
                     'body' => json_encode($post_data),
                     'verify' => false
                    //'form_params' => $post_data,
                    //'debug' => true
                    //'multipart' => $post_data
                     //'json' => json_encode($post_data)
                ]);                
            }catch (\GuzzleHttp\Exception\RequestException $e) { 
                //dd($e);
                if ( $e->getResponse()->getStatusCode() == 401 ) {
                    return json_encode(array('status'=>'error','message'=>"Unauthorized token.Please reconnect EKM integration",'code'=>401));
                } else {
                    return json_encode(array('status'=>'error','message'=>$e->getMessage()));
                }
            }catch(\Exception $e) {
                return json_encode(array('status'=>'error','message'=>$e->getMessage()));
            }
        }elseif ($method == 'PUT') {
            try{
                $response = $client->request('PUT', $url,[
                    'headers' => [
                            'Accept'                => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization'         => 'Bearer '. $access_token,
                     ],
                    'body' => json_encode($post_data),
                    'verify' => false
                    //'debug'=>false
                    //'multipart' => $postdata
                ]);
                //dd($response);
            }catch (\GuzzleHttp\Exception\RequestException $e) { 
                if ( $e->getResponse()->getStatusCode() == 401 ) {
                    return json_encode(array('status'=>'error','message'=>"Unauthorized token.Please reconnect EKM integration",'code'=>401));
                } else {
                    return json_encode(array('status'=>'error','message'=>$e->getMessage()));
                }
            }catch(\Exception $e) {
                return json_encode(array('status'=>'error','message'=>$e->getMessage()));
            }
        } 
        elseif ($method == 'DELETE') {
            try{
               $response =$client->request('DELETE', $url,[
                    'headers'=>[
                        'Accept' => 'application/json',
                        'Authorization'  => 'Bearer '.$access_token       
                     ],
                     'verify' => false
                ]); 
               //dd($response);
            }catch (\GuzzleHttp\Exception\RequestException $e) { 
                if ( $e->getResponse()->getStatusCode() == 401 ) {
                    return json_encode(array('status'=>'error','message'=>"Unauthorized token.Please reconnect EKM integration",'code'=>401));
                } else {
                    return json_encode(array('status'=>'error','message'=>$e->getMessage()));
                }
            }catch(\Exception $e) {
                return json_encode(array('status'=>'error','message'=>$e->getMessage()));
            }
        }else {
            try{
                $response =$client->request('Get', $url,[
                    'headers'=>[
                        'Accept' => 'application/json',
                        'Authorization'  => 'Bearer '.$access_token       
                    ],
                    'query'=>$post_data,
                    'debug'=>false,
                    'verify' => false
                ]);                      
            } catch (\GuzzleHttp\Exception\RequestException $e) { 
                //dd($e);
                if ($e->getResponse()->getStatusCode() == 401 ) {
                    return json_encode(array('status'=>'error','message'=>"Unauthorized token.Please reconnect EKM integration",'code'=>401));
                } else {
                    return json_encode(array('status'=>'error','message'=>$e->getMessage()));
                }
            } catch(\Exception $e) {                
                return json_encode(array('status'=>'error','message'=>$e->getMessage()));
            }    
        }

        //echo $response->getStatusCode();die();
        //dd($response);
        if($response->getStatusCode() == 200 || $response->getStatusCode() == 201) { // 200 OK
            $response_data = $response->getBody()->getContents();
            /*if($this->isJson($response_data)){
                $response_data=json_decode($response_data);
            }*/
            return $response_data;
        }else{
            return '';
        }
    }

    public function GetToken($url, $client_id, $client_secret, $code) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $post_data = array('client_id' => $client_id, 'client_secret' => $client_secret, 'code' => $code, 'grant_type' => 'authorization_code', 'redirect_uri' => $this->redirect_uri);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }

    public function GetTokenByRefreshToken($url, $client_id, $client_secret, $refresh_token) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $scope = 'tempest.orders.read tempest.orders.write tempest.products.read tempest.products.write tempest.categories.read tempest.categories.write tempest.settings.orderstatuses.read tempest.settings.domains.read offline_access';

        $post_data = array('client_id' => $client_id, 'client_secret' => $client_secret, 'refresh_token' => $refresh_token, 'grant_type' => 'refresh_token', 'scope' => $scope);
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data
        ));

        $response = curl_exec($curl);
        //dd($response);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }

    public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    public function striphtmlcss($string) {
        //preg_match('/<div class="panel-body">\s*(<div.*?</div>\s*)?(.*?)</div>/is', $string, $matches );
        //preg_match('/<div class="panel-body">(.*?)<\/div>/s', $string, $match);
       // preg_match( '/<div class="\panel-body\">(.*?)<\/div>/', $string, $match );
        /*if(isset($match[1])){
            $string=$match[1];
        }*/
        /*$pattern_short = '{<div\s+class="panel-body"\s*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}si';
 
        $matchcount = preg_match_all($pattern_short, $string, $match);
        //dd($match[1][0]);
        if(isset($match[1][0])){
            $string=$match[1][0];
        }*/
        $string = str_replace("Our eBay shop is constantly growing but if you don't see what you are looking for please get in touch as I can probably do a listing for what you need.","If you don't see what you are looking for please get in touch as I can 
probably help.",$string);
        $string = str_replace("If you have any questions please feel free to contact me through eBay messages","",$string);
        $string = str_replace("or if you would prefer to phone you will find my","",$string);
        $string = str_replace('<b>contact telephone number</b>&nbsp;in the&nbsp;',"",$string);
        $string = str_replace('Business seller information',"",$string);
        $string = str_replace("section of every listing","",$string);
        //$string = str_replace('<p style="font-family: Arial; font-size: 14pt; text-align: center;"><br></p><p style="font-family: Arial; font-size: 14pt; text-align: center;">If you have any questions please feel free to contact me through eBay messages&nbsp;</p><p style="font-family: Arial; font-size: 14pt; text-align: center;"><span style="font-size: 14pt;">or if you would prefer to phone you will find my&nbsp;</span></p><p style="font-family: Arial; font-size: 14pt; text-align: center;"><span style="font-size: 14pt;"><b>contact telephone number</b>&nbsp;in the&nbsp;</span><font face="Arial"><span style="font-size: 18.6667px;"><b>Business seller information</b></span></font></p><p style="font-family: Arial; font-size: 14pt; text-align: center;"><font face="Arial"><span style="font-size: 18.6667px;">section of every listing</span></font></p><p style="font-family: Arial; font-size: 14pt; text-align: center;"><br></p>',"",$string);
        $string = str_replace("http://stores.ebay.co.uk/SMOKN-UK","",$string);
       // $string = str_replace("contact me through eBay messages","send me an email or phone me",$string);
        $string = str_replace("send me a message through eBay","send me an email or phone me",$string);
        //$string = str_replace("eBay","",$string);
        $text = preg_replace('#<p[^>]*>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#','',$string);
        //$text = preg_replace('/ style=("|\')(.*?)("|\')/','',$text);
        //$text = strip_tags($string,"<style>");
        $substring = substr($text,strpos($text,"<style"),strlen($text));
        if($text!=$substring){
            $text = str_replace($substring,"",$text);
        }
        //echo strpos($text,"If you have any questions please feel free to contact me through eBay messages");die;
        $text = str_replace(array("\t","\r","\n"),"",$text);
        //dd($text);
        return $text = trim($text);
    }
    
    function fatchEkmmultipleOrderData($account_id) {

        $ebayDataConf = Accountservice::where(['status' => 1, 'service_id' => 9,'validate'=>1,'account_id'=>$account_id])->where('data', '!=', '')->get();
        if (!empty($ebayDataConf)) {
            foreach ($ebayDataConf as $convalue) {
                $config_id=$convalue->id;
                $account_id = $convalue->account_id;
                $confData = json_decode($convalue->data);
                if (isset($confData->access_token)) {
                    if (isset($confData->refresh_token)) {
                            $expire_time  = strtotime($confData->expires_in);
                            $current_time = strtotime(date('Y-m-d H:i:s'));
                            if($current_time>$expire_time){
                                $url = 'https://api.ekm.net/connect/token';
                                $api_key = env('EKM_CLIENT_ID');
                                $SECRET_key = env('EKM_CLIENT_SECRET');
                                $responce = $this->GetTokenByRefreshToken($url, $api_key, $SECRET_key, $confData->refresh_token);
                                //dd($responce);
                                if (isset($responce->access_token)) {
                                    $dbdata = array();
                                    $formData = Accountservice::where(['id' => $convalue->id])->orderBy('id', 'desc')->first();
                                    $dbdata['service_name'] = $confData->service_name;
                                    $dbdata['ekm_store_url'] = $confData->ekm_store_url;
                                    $dbdata['access_token'] = $responce->access_token;
                                    $dbdata['expires_in'] = date('Y-m-d H:i:s',strtotime("+3000 seconds"));//$responce->expires_in;
                                    $dbdata['token_type'] = $responce->token_type;
                                    if (isset($responce->refresh_token)) {
                                        $dbdata['refresh_token'] = $responce->refresh_token;
                                        $dbdata['refresh_token_expires_in'] = date('Y-m-d H:i:s',strtotime(" + 13 days"));//$responce->expires_in;
                                    }
                                    $formData->data = json_encode($dbdata);
                                    $formData->save();
                                    $confData->access_token = $responce->access_token;
                                }
                               // echo $confData->access_token;die;
                            }
                    }
                    $order_url=$this->url.'orders';
                    $order_data = array();
                    if (isset($confData->access_token)) {
                        $params =[
                          'page'=>290,
                          'limit'=>20  
                        ];
                        $order_data_response = $this->call($order_url, $confData->access_token, 'GET',$params);
                        if($order_data_response && $this->isJson($order_data_response)) {
                            $order_data=json_decode($order_data_response);
                            
                        }
                        if(isset($order_data->code) && $order_data->code == 401){
                            $account_id = $account_id;
                            $config_id = $config_id;
                            $message=$order_data->message;
                            $order_notification = new Notifications();
                            $order_notification->account_id = $account_id;
                            $order_notification->type = 'error';
                            $order_notification->link = 'account/viewService/'. $account_id.'/ekm/'.$config_id;
                            $order_notification->icon = 'glyphicon glyphicon-exclamation-sign';
                            $order_notification->text = $message;
                            $order_notification->save();
                            
                            $formData = Accountservice::where(['id' => $config_id])->orderBy('id', 'desc')->first();
                            $formData->validate = 0;
                            $formData->save();
                            
                            continue;
                        }
                    }
                }
                $this->addneworder($order_data,$account_id,$config_id);
               /* echo '<pre>';
                print_r($order_data);die;*/
                //$page=140;
                $total_page=325;
                for($page=320;$page<=$total_page;$page++){
                   $params =[
                      'page'=>$page,
                      'limit'=>20  
                    ];
                   $order_data_response = $this->call($order_url, $confData->access_token, 'GET',$params);
                    if($order_data_response && $this->isJson($order_data_response)) {
                        $order_data=json_decode($order_data_response);
                        $this->addneworder($order_data,$account_id,$config_id);
                    }
                    echo $page.'<br/>';
                }
                
            }
        }
    }
    
    function addneworder($order_data,$account_id,$config_id){
        if (!empty($order_data->data)) {
            //dd($order_data->data);
            $i = 0;
            foreach ($order_data->data as $value) {

                $exchk = Order::where('account_id', $account_id)->where('SupplierOrderNumber', '=', $value->order_number)->where('service_config_id', $config_id)->get()->count();
                //if ($exchk == 0 && !empty($value->items) && ($value->status!='PENDING' && $value->status!='SYSTEMHIDDEN')) {
                //for account id 610 only complete order import 
                if($account_id==610 && $value->status=='PENDING'){
                    continue;
                }
                //for account id 1210  only complete with paid order import 
                if(($account_id==1210 || $account_id==1492) && $value->payment_status!='SUCCESS'){
                    continue;
                }
                if ($exchk == 0 && !empty($value->items) &&  $value->status!='SYSTEMHIDDEN' && $value->order_type) {
                    $order = new Order;
                    $order->SupplierOrderNumber = $value->order_number;
                    $order->CustomerOrderNumber = $value->id;
                    $order->OrderDate = date("Y-m-d H:i:s", strtotime($value->order_date));
                    $order->order_type = 'ekm';
                    $order->ordertax = $value->total_tax;
                    //$order->OrderCurrency = $value->currency_code;
                    $order->OrderTotal = $value->total_cost;
                    if (isset($value->customer_details->customer_id)) {
                        $order->CustomerID = $value->customer_details->customer_id;
                    }
                    $order->service_config_id = $config_id;
                    $order->order_status = ucwords($value->status);
                    $order->delivery_method = $value->delivery_method;
                    $order->order_type_ekm = $value->order_type;
                    $order->account_id = $account_id;
                    $user = User::where('account_id', $account_id)->first();
                    //$ordercount = Order::where('account_id', $account_id)->where('OrderDate','>=',date('Y-m-').'01 00:00;00')->where('display_status', 1)->count();
                    $subc = new \LMS\Http\Controllers\SubscriptionController();
                    $display_status=1;
                    $ordercount =$subc->getBillingCycleOrderCountByAccountId($account_id,$display_status);
                    $limitation = $subc->getLimitationDetailsByAccountId($account_id);
                    //dd($limitation);
                    if ($limitation['order']!='' && $limitation['order'] <= $ordercount) {
                        $order->display_status = 0;
                    }
                    $order->save();
                    $order_id = $order->id;
                    if ($order_id != '') {
                        if (isset($value->customer_facing_notes) && $value->customer_facing_notes != "") {
                            $OrderNote = new OrderNote();
                            $OrderNote->order_id = $order_id;
                            $OrderNote->user_id = $user->id;
                            $OrderNote->note = $value->customer_facing_notes;
                            $OrderNote->save();
                        }
                        if (isset($value->internal_notes) && $value->internal_notes != "") {
                            $OrderNote = new OrderNote();
                            $OrderNote->order_id = $order_id;
                            $OrderNote->user_id = $user->id;
                            $OrderNote->note = $value->internal_notes;
                            $OrderNote->save();
                        }
                        if (!empty($value->items)) {
                            foreach ($value->items as $itemvalue) {
                                $orderItem = new Orderitems();
                                $orderItem->order_id = $order_id;
                                $orderItem->SupplierOrderNumber = $value->order_number;
                                if (isset($itemvalue->product->id)) {
                                    $orderItem->ProductItemID = $itemvalue->product->id;
                                }
                                if (isset($itemvalue->product->product_code)) {
                                    $orderItem->SupplierOrderlineNumber = $itemvalue->product->product_code;
                                }
                                $orderItem->Name = $itemvalue->item_name;
                                if (isset($itemvalue->product->description)) {
                                    $orderItem->ProductDescription = $itemvalue->product->description;
                                }
                                $orderItem->Quantity = $itemvalue->quantity;
                                if (isset($itemvalue->product->mpn)) {
                                    $orderItem->ASIN = $itemvalue->product->mpn;
                                }
                                $orderItem->ProductUnitPrice = $itemvalue->item_price;

                                $orderItem->save();
                                if (isset($orderItem->ProductItemID)) {
                                    $invhelper = new InventoryHelper();
                                    $invhelper->updateQtyInOnepatch($config_id, $account_id,$orderItem->Quantity,'ekm_product_item_id',$orderItem->ProductItemID);
                                }
                                else if (isset($orderItem->SupplierOrderlineNumber)) {
                                    $invhelper = new InventoryHelper();
                                    $invhelper->updateQtyInOnepatch($config_id, $account_id,$orderItem->Quantity,'sku',$orderItem->SupplierOrderlineNumber);
                                } else {
                                    $invhelper = new InventoryHelper();
                                    $invhelper->updateQtyInOnepatch($config_id, $account_id,$orderItem->Quantity,'name',$orderItem->Name);
                                }
                                //End //
                            }
                        }
                        if (isset($value->customer_details) && $value->customer_details) {
                            $billing_address = new Billingaddress();

                            $billing_address->order_id = $order_id;
                            $billing_address->SupplierOrderNumber = $value->order_number;

                            $billing_address->FirstName = $value->customer_details->first_name;
                            $billing_address->LastName = $value->customer_details->last_name;


                            $billing_address->Address1 = $value->customer_details->address;

                            $billing_address->Address2 = $value->customer_details->address2;

                            $billing_address->City = $value->customer_details->town;

                            $billing_address->Country = $value->customer_details->country;

                            $billing_address->Postcode = $value->customer_details->post_code;
                            $billing_address->Phone = $value->customer_details->telephone;
                            $billing_address->EmailAddress = $value->customer_details->email_address;

                            $billing_address->save();
                        }else if (isset($value->shipping_address) && $value->shipping_address) {
                            $billing_address = new Billingaddress();

                            $billing_address->order_id = $order_id;
                            $billing_address->SupplierOrderNumber = $value->order_number;

                            $billing_address->FirstName = $value->shipping_address->first_name;
                            $billing_address->LastName = $value->shipping_address->last_name;

                            $billing_address->Address1 = $value->shipping_address->address;

                            $billing_address->Address2 = $value->shipping_address->address2;

                            $billing_address->City = $value->shipping_address->town;

                            $billing_address->Country = $value->shipping_address->country;

                            $billing_address->Postcode = $value->shipping_address->post_code;
                            $billing_address->Phone = $value->shipping_address->telephone;
                            //$delievery_address->EmailAddress = $value->shipping_address->email;

                            $billing_address->save();
                        }
                        if (isset($value->shipping_address) && $value->shipping_address) {
                            $delievery_address = new Deliveryaddress();

                            $delievery_address->order_id = $order_id;
                            $delievery_address->SupplierOrderNumber = $value->order_number;

                            $delievery_address->FirstName = $value->shipping_address->first_name;
                            $delievery_address->LastName = $value->shipping_address->last_name;

                            $delievery_address->Address1 = $value->shipping_address->address;

                            $delievery_address->Address2 = $value->shipping_address->address2;

                            $delievery_address->City = $value->shipping_address->town;

                            $delievery_address->Country = $value->shipping_address->country;

                            $delievery_address->Postcode = $value->shipping_address->post_code;
                            $delievery_address->Phone = $value->shipping_address->telephone;
                            //$delievery_address->EmailAddress = $value->shipping_address->email;

                            $delievery_address->save();
                        }else if (isset($value->customer_details) && $value->customer_details) {
                            $delievery_address = new Deliveryaddress();

                            $delievery_address->order_id = $order_id;
                            $delievery_address->SupplierOrderNumber = $value->order_number;

                            $delievery_address->FirstName = $value->customer_details->first_name;
                            $delievery_address->LastName = $value->customer_details->last_name;


                            $delievery_address->Address1 = $value->customer_details->address;

                            $delievery_address->Address2 = $value->customer_details->address2;

                            $delievery_address->City = $value->customer_details->town;

                            $delievery_address->Country = $value->customer_details->country;

                            $delievery_address->Postcode = $value->customer_details->post_code;
                            $delievery_address->Phone = $value->customer_details->telephone;
                            $delievery_address->EmailAddress = $value->customer_details->email_address;

                            $delievery_address->save();
                        }
                        $order_status = new Orderstatus();
                        $order_status->order_id = $order_id;
                        $order_status->SupplierOrderNumber = $value->order_number;
                        $order_status->ReferenceNumber = '';
                        $order_status->OrderType = '';
                        if ($value->status == 'DISPATCHED') {
                            $order_status->Status = 'DES';
                        } else {
                            $order_status->Status = 'Imported';
                        }

                        $order_status->Reason = '';
                        $order_status->ConsignmentID = '';
                        $order_status->CarrierName = $value->shipping_company;
                        $order_status->CreateDate = '';
                        $order_status->save();

                        $order_meta_sp = new Ordermetadata();
                        $order_meta_sp->order_id = $order_id;
                        $order_meta_sp->meta_key = '_shipping_price';
                        $order_meta_sp->meta_value = $value->total_delivery;
                        $order_meta_sp->save();

                        if (isset($value->custom_fields) && $value->custom_fields != '') {
                            $order_meta_cf = new Ordermetadata();
                            $order_meta_cf->order_id = $order_id;
                            $order_meta_cf->meta_key = '_custom_fields';
                            $order_meta_cf->meta_value = $value->custom_fields;
                            $order_meta_cf->save();
                        }

                        if (isset($value->shipping_company) && $value->shipping_company != '') {
                            $order_meta_si = new Ordermetadata();
                            $order_meta_si->order_id = $order_id;
                            $order_meta_si->meta_key = '_shipping_info';
                            $order_meta_si->meta_value = $value->shipping_company;
                            $order_meta_si->save();
                        }

                        if ($i == 0) {
                            $order_notification = new Notifications();
                            $order_notification->account_id = $account_id;
                            $order_notification->type = 'order';
                            $order_notification->link = 'orders';
                            $order_notification->icon = 'glyphicon glyphicon-sort-by-order-alt';
                            $order_notification->text = 'New EKM Order';
                            $order_notification->save();
                            $noti = new \LMS\Http\Controllers\AccountController();
                            $limitation = $noti->sentnotificationtodevice($account_id, 'order', 'New EKM Order');
                        }
                        $i++;
                        // update stock to all intigration //
                        $setting = DB::table('setting')->where('account_id', $account_id)->first();
                        if(isset($setting->stock_sync_orders) && $setting->stock_sync_orders==1){
                            $invhelper = new InventoryHelper();
                            $invhelper->updateQtyInAllService($order_id, $account_id);
                        }
                        //auto generate invoice xero
                        $invhelper = new InventoryHelper();
                        $invhelper->autogenerateinvoice($order_id, $account_id);
                    }
                }
            }
        }
    }
    function CheckNewProduct($account_id) {
        ini_set('max_execution_time', 10000);
        $result = array();
        $storeurl="https://youraccount.15.ekm.net" ;
        $ekmDataConf = Accountservice::where(['status' => 1, 'service_id' => 9, 'account_id' => $account_id])->where('data', '!=', '')->get();
        //dd($ebayDataConf);
        if (!empty($ekmDataConf)) {
            foreach ($ekmDataConf as $ekmconf) {
                if ($ekmconf->data != '') {
                    $config = json_decode($ekmconf->data);
                    $account_id = $ekmconf->account_id;
                    $service_id = $ekmconf->service_id;
                    $config_id = $ekmconf->id;
                    if(isset($config->ekm_store_url)){
                        $ekm_store_url=explode("/",$config->ekm_store_url);
                        if(isset($ekm_store_url[0]) && isset($ekm_store_url[2])){
                           $storeurl=$ekm_store_url[0].'//'.$ekm_store_url[2];
                        }
                    }
                    $params =[
                      'query'=>"live eq 'true'",
                      'orderby'=>'-id' 
                    ];
                    $url = $this->url.'products/search';
                    $token = $config->access_token;
                    $response_one = microtime(1);
                    $response=$this->call($url,$token,'GET',$params);
                    /*echo '<pre>';
                    print_r($response);*/
                    
                    //dd($response);
                    if($response != '' && $this->isJson($response)) {
                        $store_meta_domain = Storemeta::where([
                            ['account_id', $account_id],
                            ['config_id', $config_id],
                            ['type', 'ekm_domain']
                        ])->first();
                        if(isset($store_meta_domain->value)){
                            $domain_name=$store_meta_domain->value;
                        }else{
                            $domain_name='';
                            $domain_api_url = $this->url.'settings/domains';
                            $domain_api_response=$this->call($domain_api_url,$token,'GET');
                            //dd($domain_api_response);
                            if($domain_api_response != '' && $this->isJson($domain_api_response)) {
                                $dresp = json_decode($domain_api_response);
                                if (isset($dresp->data->primary_domain_name)) {
                                    $domain_name=$dresp->data->primary_domain_name;
                                    $storemeta = new Storemeta();
                                    $storemeta->account_id = $account_id;
                                    $storemeta->config_id = $config_id;
                                    $storemeta->type = 'ekm_domain';
                                    $storemeta->name = 'domain';
                                    $storemeta->value = $domain_name;
                                    $storemeta->save(); 
                                }
                            }
                        }
                        $resp = json_decode($response);
                        //dd($resp);
                        if (isset($resp->data) && !empty($resp->data)) {
                            if(isset($resp->meta->items_total)){
                                $total_records=$resp->meta->items_total;
                            }else{
                                $total_records=0;
                            }
                            if(isset($resp->meta->pages_total)){
                                $total_pages=$resp->meta->pages_total;
                            }else{
                                $total_pages=0;
                            }
                            if (!empty($resp->data)) {
                                $direct_import=1;
                                foreach ($resp->data as $product) {
                                    $ekm_product_id=$product->id;
                                    $url = $this->url.'products/'.$ekm_product_id;
                                    $token = $config->access_token;
                                    $response=$this->call($url,$token,'GET',$params=array());
                                    if($response && $this->isJson($response)) {
                                        $resp = json_decode($response);
                                        if(isset($resp->data) && !empty($resp->data)) {
                                            $product_data=$resp->data;
                                            $this->addNewProduct($account_id, $product_data,$config_id,$token,$storeurl,$service_id,$domain_name,$direct_import);
                                        }
                                    }
                                }
                                $response=array('status'=>1,'message'=>'successfully imported.','total_records'=>$total_records,'total_pages'=>$total_pages);
                            }else{
                                $response=array('status'=>1,'message'=>'successfully imported.','total_records'=>$total_records); 
                            }
                        }else{
                            if(isset($resp->code) && $resp->code==401){
                                $formData = Accountservice::where(['id' => $config_id])->orderBy('id', 'desc')->first();
                                $formData->validate = 0;
                                $formData->save();
                                $message=$resp->message;
                            }else{
                                $message="Something went wrong in your ekm intregation";
                            }
                            //InventoryQueue::where('id',$queuetaskid)->update(array('api_status' => 0,'api_response'=>$response));
                            $response=array('status'=>0,'message'=> $message);
                        }
                    }else{  
                        $response=array('status'=>0,'message'=>'Something went wrong in api response.');
                    }
                    
                    
                }
            }
        }
        //dd($response);
    }

    public function getOrdersandupdatestatus($config_id,$daysoforder=-2) {
        $ebayDataConf = Accountservice::where(['status' => 1, 'service_id' => 9,'id'=>$config_id,'validate'=>1])->where('data', '!=', '')->get();
        if (!empty($ebayDataConf)) {
            foreach ($ebayDataConf as $convalue) {
                $account_id = $convalue->account_id;
                $confData = json_decode($convalue->data);
                if (isset($confData->access_token)) {
                    if (isset($confData->refresh_token)) {
                            $expire_time  = strtotime($confData->expires_in);
                            $current_time = strtotime(date('Y-m-d H:i:s'));
                            if($current_time>$expire_time){
                                $url = 'https://api.ekm.net/connect/token';
                                $api_key = env('EKM_CLIENT_ID');
                                $SECRET_key = env('EKM_CLIENT_SECRET');
                                $responce = $this->GetTokenByRefreshToken($url, $api_key, $SECRET_key, $confData->refresh_token);
                                //dd($responce);
                                if (isset($responce->access_token)) {
                                    $dbdata = array();
                                    $formData = Accountservice::where(['id' => $convalue->id])->orderBy('id', 'desc')->first();
                                    $dbdata['service_name'] = $confData->service_name;
                                    //$dbdata['ekm_store_url'] = $confData->ekm_store_url;
                                    $dbdata['ekm_currency'] = isset($confData->ekm_currency) ? $confData->ekm_currency : 'GBP';
                                    $dbdata['access_token'] = $responce->access_token;
                                    $dbdata['expires_in'] = date('Y-m-d H:i:s',strtotime("+3000 seconds"));//$responce->expires_in;
                                    $dbdata['token_type'] = $responce->token_type;
                                    if (isset($responce->refresh_token)) {
                                        $dbdata['refresh_token'] = $responce->refresh_token;
                                        $dbdata['refresh_token_expires_in'] = date('Y-m-d H:i:s',strtotime(" + 13 days"));//$responce->expires_in;
                                    }
                                    $formData->data = json_encode($dbdata);
                                    $formData->save();
                                    $confData->access_token = $responce->access_token;
                                }
                               // echo $confData->access_token;die;
                            }
                    }
                    $order_url=$this->url.'orders';
                    $order_data = array();
                    if (isset($confData->access_token)) {
                        $params =[
                          'page'=>1,
                          'limit'=>20  
                        ];
                        $order_data_response = $this->call($order_url, $confData->access_token, 'GET',$params);
                        if($order_data_response && $this->isJson($order_data_response)) {
                            $order_data=json_decode($order_data_response);
                        }
                        if(isset($order_data->code) && $order_data->code == 401){
                            $account_id = $convalue->account_id;
                            $config_id = $convalue->id;
                            $message=$order_data->message;
                            $order_notification = new Notifications();
                            $order_notification->account_id = $account_id;
                            $order_notification->type = 'error';
                            $order_notification->link = 'account/viewService/'. $account_id.'/ekm/'.$config_id;
                            $order_notification->icon = 'glyphicon glyphicon-exclamation-sign';
                            $order_notification->text = $message;
                            $order_notification->save();
                            
                            $formData = Accountservice::where(['id' => $convalue->id])->orderBy('id', 'desc')->first();
                            $formData->validate = 0;
                            $formData->save();
                            
                            continue;
                        }
                    }
                }
                /*echo '<pre>';
                print_r($order_data);die;*/
                if (!empty($order_data->data)) {
                    //dd($order_data->data);
                    $i = 0;
                    foreach ($order_data->data as $value) {

                        $exchk = Order::where('account_id', $account_id)->where('SupplierOrderNumber', '=', $value->order_number)->where('service_config_id', $convalue->id)->get()->count();
                        //if ($exchk == 0 && !empty($value->items) && ($value->status!='PENDING' && $value->status!='SYSTEMHIDDEN')) {
                        //for account id 610 only complete order import 
                        if($account_id==610 && $value->status=='PENDING'){
                            continue;
                        }
                        //for account id 1210  only complete with paid order import 
                        if(($account_id==1210 || $account_id==1492) && $value->payment_status!='SUCCESS'){
                            continue;
                        }
                        if ($exchk == 0 && !empty($value->items) &&  $value->status!='SYSTEMHIDDEN' && $value->order_type) {
                            $order = new Order;
                            $order->SupplierOrderNumber = $value->order_number;
                            $order->CustomerOrderNumber = $value->id;
                            $order->OrderDate = date("Y-m-d H:i:s", strtotime($value->order_date));
                            $order->order_type = 'ekm';
                            $order->ordertax = $value->total_tax;
                            $order->OrderCurrency = isset($confData->ekm_currency) ? $confData->ekm_currency : 'GBP';
                            //$order->OrderCurrency = $value->currency_code;
                            $order->OrderTotal = $value->total_cost;
                            if (isset($value->customer_details->customer_id)) {
                                $order->CustomerID = $value->customer_details->customer_id;
                            }
                            $order->service_config_id = $convalue->id;
                            $order->order_status = ucwords($value->status);
                            $order->delivery_method = $value->delivery_method;
                            $order->order_type_ekm = $value->order_type;
                            $order->account_id = $account_id;
                            $user = User::where('account_id', $account_id)->first();
                            //$ordercount = Order::where('account_id', $account_id)->where('OrderDate','>=',date('Y-m-').'01 00:00;00')->where('display_status', 1)->count();
                            $subc = new \LMS\Http\Controllers\SubscriptionController();
                            $display_status=1;
                            $ordercount =$subc->getBillingCycleOrderCountByAccountId($account_id,$display_status);
                            $limitation = $subc->getLimitationDetailsByAccountId($account_id);
                            //dd($limitation);
                            if ($limitation['order']!='' && $limitation['order'] <= $ordercount) {
                                $order->display_status = 0;
                            }
                            $order->save();
                            $order_id = $order->id;
                            if ($order_id != '') {
                                if (isset($value->customer_facing_notes) && $value->customer_facing_notes != "") {
                                    $OrderNote = new OrderNote();
                                    $OrderNote->order_id = $order_id;
                                    $OrderNote->user_id = $user->id;
                                    $OrderNote->note = $value->customer_facing_notes;
                                    $OrderNote->save();
                                }
                                if (isset($value->internal_notes) && $value->internal_notes != "") {
                                    $OrderNote = new OrderNote();
                                    $OrderNote->order_id = $order_id;
                                    $OrderNote->user_id = $user->id;
                                    $OrderNote->note = $value->internal_notes;
                                    $OrderNote->save();
                                }
                                if (!empty($value->items)) {
                                    foreach ($value->items as $itemvalue) {
                                        $orderItem = new Orderitems();
                                        $orderItem->order_id = $order_id;
                                        $orderItem->SupplierOrderNumber = $value->order_number;
                                        if (isset($itemvalue->product->id)) {
                                            $orderItem->ProductItemID = $itemvalue->product->id;
                                        }
                                        if (isset($itemvalue->product->product_code)) {
                                            $orderItem->SupplierOrderlineNumber = $itemvalue->product->product_code;
                                        }
                                        $orderItem->Name = $itemvalue->item_name;
                                        if (isset($itemvalue->product->description)) {
                                            $orderItem->ProductDescription = $itemvalue->product->description;
                                        }
                                        $orderItem->Quantity = $itemvalue->quantity;
                                        if (isset($itemvalue->product->mpn)) {
                                            $orderItem->ASIN = $itemvalue->product->mpn;
                                        }
                                        $orderItem->ProductUnitPrice = $itemvalue->item_price;
                                        $orderItem->lineItemtax = (double)$itemvalue->item_tax;
                                        $orderItem->lineItemtaxRate = $itemvalue->item_tax_rate;
                                        $orderItem->lineItemDiscount = $itemvalue->item_discount;

                                        $orderItem->save();
                                        if (isset($orderItem->ProductItemID)) {
                                            $invhelper = new InventoryHelper();
                                            $invhelper->updateQtyInOnepatch($convalue->id, $account_id,$orderItem->Quantity,'ekm_product_item_id',$orderItem->ProductItemID);
                                        }
                                        else if (isset($orderItem->SupplierOrderlineNumber)) {
                                            $invhelper = new InventoryHelper();
                                            $invhelper->updateQtyInOnepatch($convalue->id, $account_id,$orderItem->Quantity,'sku',$orderItem->SupplierOrderlineNumber);
                                        } else {
                                            $invhelper = new InventoryHelper();
                                            $invhelper->updateQtyInOnepatch($convalue->id, $account_id,$orderItem->Quantity,'name',$orderItem->Name);
                                        }
                                        //End //
                                    }
                                }
                                $customer_data_Arr = array();
                                if (isset($value->customer_details) && $value->customer_details) {
                                    $billing_address = new Billingaddress();

                                    $billing_address->order_id = $order_id;
                                    $billing_address->SupplierOrderNumber = $value->order_number;

                                    $billing_address->FirstName = $value->customer_details->first_name;
                                    $billing_address->LastName = $value->customer_details->last_name;


                                    $billing_address->Address1 = $value->customer_details->address;

                                    $billing_address->Address2 = $value->customer_details->address2;

                                    $billing_address->City = $value->customer_details->town;

                                    $billing_address->Country = $value->customer_details->country;

                                    $billing_address->Postcode = $value->customer_details->post_code;
                                    $billing_address->Phone = $value->customer_details->telephone;
                                    $billing_address->EmailAddress = $value->customer_details->email_address;

                                    $billing_address->save();
                                    
                                    //customer data store
                                    $customer_data_Arr = [
                                        'first_name' => $value->customer_details->first_name,
                                        'last_name' => $value->customer_details->last_name,
                                        'email' => $value->customer_details->email_address,
                                        'phone_number' => $value->customer_details->telephone,
                                        'post_code' => $value->customer_details->post_code
                                    ];
                                    // End customer data store
                                }else if (isset($value->shipping_address) && $value->shipping_address) {
                                    $billing_address = new Billingaddress();

                                    $billing_address->order_id = $order_id;
                                    $billing_address->SupplierOrderNumber = $value->order_number;

                                    $billing_address->FirstName = $value->shipping_address->first_name;
                                    $billing_address->LastName = $value->shipping_address->last_name;

                                    $billing_address->Address1 = $value->shipping_address->address;

                                    $billing_address->Address2 = $value->shipping_address->address2;

                                    $billing_address->City = $value->shipping_address->town;

                                    $billing_address->Country = $value->shipping_address->country;

                                    $billing_address->Postcode = $value->shipping_address->post_code;
                                    $billing_address->Phone = $value->shipping_address->telephone;
                                    //$delievery_address->EmailAddress = $value->shipping_address->email;

                                    $billing_address->save();
                                    
                                    //customer data store
                                    $customer_data_Arr = [
                                        'first_name' => $value->shipping_address->first_name,
                                        'last_name' => $value->shipping_address->last_name,
                                        'email' => '',
                                        'phone_number' => $value->shipping_address->telephone,
                                        'post_code' => $value->shipping_address->post_code
                                    ];
                                    // End customer data store
                                }
                                if (isset($value->shipping_address) && $value->shipping_address) {
                                    $delievery_address = new Deliveryaddress();

                                    $delievery_address->order_id = $order_id;
                                    $delievery_address->SupplierOrderNumber = $value->order_number;

                                    $delievery_address->FirstName = $value->shipping_address->first_name;
                                    $delievery_address->LastName = $value->shipping_address->last_name;

                                    $delievery_address->Address1 = $value->shipping_address->address;

                                    $delievery_address->Address2 = $value->shipping_address->address2;

                                    $delievery_address->City = $value->shipping_address->town;

                                    $delievery_address->Country = $value->shipping_address->country;

                                    $delievery_address->Postcode = $value->shipping_address->post_code;
                                    $delievery_address->Phone = $value->shipping_address->telephone;
                                    //$delievery_address->EmailAddress = $value->shipping_address->email;

                                    $delievery_address->save();
                                }else if (isset($value->customer_details) && $value->customer_details) {
                                    $delievery_address = new Deliveryaddress();

                                    $delievery_address->order_id = $order_id;
                                    $delievery_address->SupplierOrderNumber = $value->order_number;

                                    $delievery_address->FirstName = $value->customer_details->first_name;
                                    $delievery_address->LastName = $value->customer_details->last_name;


                                    $delievery_address->Address1 = $value->customer_details->address;

                                    $delievery_address->Address2 = $value->customer_details->address2;

                                    $delievery_address->City = $value->customer_details->town;

                                    $delievery_address->Country = $value->customer_details->country;

                                    $delievery_address->Postcode = $value->customer_details->post_code;
                                    $delievery_address->Phone = $value->customer_details->telephone;
                                    $delievery_address->EmailAddress = $value->customer_details->email_address;

                                    $delievery_address->save();
                                }
                                $order_status = new Orderstatus();
                                $order_status->order_id = $order_id;
                                $order_status->SupplierOrderNumber = $value->order_number;
                                $order_status->ReferenceNumber = '';
                                $order_status->OrderType = '';
                                if ($value->status == 'DISPATCHED') {
                                    $order_status->Status = 'DES';
                                } else {
                                    $order_status->Status = 'Imported';
                                }

                                $order_status->Reason = '';
                                $order_status->ConsignmentID = '';
                                $order_status->CarrierName = $value->shipping_company;
                                $order_status->CreateDate = '';
                                $order_status->save();

                                $order_meta_sp = new Ordermetadata();
                                $order_meta_sp->order_id = $order_id;
                                $order_meta_sp->meta_key = '_shipping_price';
                                $order_meta_sp->meta_value = $value->total_delivery;
                                $order_meta_sp->save();
                                
                                //shipping tax rate
                                if(isset($value->delivery_tax_rate)){
                                    $order_meta_str = new Ordermetadata();
                                    $order_meta_str->order_id = $order_id;
                                    $order_meta_str->meta_key = '_shipping_tax_rate';
                                    $order_meta_str->meta_value = $value->delivery_tax_rate;
                                    $order_meta_str->save();
                                }
                                
                                //sub total price
                                if(isset($value->sub_total)){

                                    $order_meta_st = new Ordermetadata();
                                    $order_meta_st->order_id = $order_id;
                                    $order_meta_st->meta_key = '_order_subtotal_price';
                                    $order_meta_st->meta_value = $value->sub_total;
                                    $order_meta_st->save();
                                }
                                //End sub total price
                                
                                //total discount total_discounts
                                if(isset($value->discounts_total)){

                                    $order_meta = new Ordermetadata();
                                    $order_meta->order_id = $order_id;
                                    $order_meta->meta_key = '_order_total_discounts';
                                    $order_meta->meta_value = $value->discounts_total;
                                    $order_meta->save();
                                }
                                //end total discount
                                // insert order response
                                $order_meta_resp = new Ordermetadata();
                                $order_meta_resp->order_id = $order_id;
                                $order_meta_resp->meta_key = '_order_response';
                                $order_meta_resp->meta_value = json_encode($value);
                                $order_meta_resp->save();
                                //End order response

                                if (isset($value->custom_fields) && $value->custom_fields != '') {
                                    $order_meta_cf = new Ordermetadata();
                                    $order_meta_cf->order_id = $order_id;
                                    $order_meta_cf->meta_key = '_custom_fields';
                                    $order_meta_cf->meta_value = $value->custom_fields;
                                    $order_meta_cf->save();
                                }

                                if (isset($value->shipping_company) && $value->shipping_company != '') {
                                    $order_meta_si = new Ordermetadata();
                                    $order_meta_si->order_id = $order_id;
                                    $order_meta_si->meta_key = '_shipping_info';
                                    $order_meta_si->meta_value = $value->shipping_company;
                                    $order_meta_si->save();
                                }

                                if ($i == 0) {
                                    $order_notification = new Notifications();
                                    $order_notification->account_id = $account_id;
                                    $order_notification->type = 'order';
                                    $order_notification->link = 'orders';
                                    $order_notification->icon = 'glyphicon glyphicon-sort-by-order-alt';
                                    $order_notification->text = 'New EKM Order';
                                    $order_notification->save();
                                    //$noti = new \LMS\Http\Controllers\AccountController();
                                    //$limitation = $noti->sentnotificationtodevice($account_id, 'order', 'New EKM Order');
                                }
                                $i++;
                                
                                //order data 
                                $order_data = [
                                    'order_id' => $order_id,
                                    'account_id' => $account_id
                                ];
                                // End order data
                                //insert or update customer record
                                //$add_or_update_customer = Helper::StoreCustomer($customer_data_Arr, $order_data);
                                //End insert or update customer record
                                // update stock to all intigration //
                                $setting = DB::table('setting')->where('account_id', $account_id)->first();
                                if(isset($setting->stock_sync_orders) && $setting->stock_sync_orders==1){
                                    $invhelper = new InventoryHelper();
                                    $invhelper->updateQtyInAllService($order_id, $account_id);
                                }
                                //auto generate invoice xero
                                $invhelper = new InventoryHelper();
                                $invhelper->autogenerateinvoice($order_id, $account_id);
                                //Send Notification to app users
                                $user_notification = new ManageNotifications();
                                $send_notification = $user_notification->sendAppNotification($account_id, $order_id);
                                if(file_exists(base_path().'/app/Libraries/CustomLibraryCode/'.$account_id.'/ordercustomcode.php')){
                                    //custom code indiviual customer if require
                                    include base_path().'/app/Libraries/CustomLibraryCode/'.$account_id.'/ordercustomcode.php';
                                }
                            }
                        }else{
                            $exorder = Order::where('account_id', $account_id)->where('SupplierOrderNumber', '=', $value->order_number)->first();
                            if($exorder){
                                $exorder->order_status=ucwords($value->status);
                                $exorder->save();
                                if(ucwords(strtolower($value->status))=='Dispatched'){
                                    $order_status = Orderstatus::where(['order_id' => $exorder->id])->first();
                                    $order_status->Status = 'DES';
                                    $order_status->OldStatus = 'Imported';
                                    $order_status->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
 