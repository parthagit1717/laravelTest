<?php

namespace App\Http\Controllers\Modules\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProductCatagory;
use App\Models\Accountservice;
use App\Models\Order;
use Auth;
use Config;


class SettingController extends Controller
{
    /**
     * Display settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        
        $client_id  = env('client_id'); 
        $currencies = Order::getCurrencies();  

        return view('modules.settings.settings',compact('currencies'));
    }

    /**
     *This is for authekmservice.
     *
     * @return \Illuminate\Http\Response
     */
    public function authekmservice(Request $req)
    {

        // $api_key = env('EKM_CLIENT_ID');
        $api_key = env('client_id');
        $currnetUser = Auth::user();
        // $dbdata = array();
        // $service_name = $req->get('service_name');
        // $ekm_currency = $req->get('ekm_currency');
        //$ekm_store_url = $req->get('ekm_store_url');
        // $dbdata['service_name'] = $service_name;
        // $dbdata['ekm_currency'] = $ekm_currency;
        //$dbdata['ekm_store_url'] = $ekm_store_url;
        $formData = new Accountservice();
        $formData->service_id = 1;
        $formData->account_id = $currnetUser->account_id;
        // $formData->data = json_encode($dbdata);
        $formData->status = 0;
        $formData->save();
        $scope = 'tempest.orders.read tempest.orders.write tempest.products.read tempest.products.write tempest.categories.read tempest.categories.write tempest.settings.orderstatuses.read tempest.settings.domains.read offline_access';
        return 'https://api.ekm.net/connect/authorize?client_id='.$api_key.'&scope='.$scope.'&redirect_uri=https://opdev.onepatch.com/wooplugin/ekmauthreturn&prompt=login&state=onepatch&response_type=code';

    }

    function reconnectEkmService(Request $req) {
        $api_key = env('EKM_CLIENT_ID');
        $id = $req->get('id');
        $scope = 'tempest.orders.read tempest.orders.write tempest.products.read tempest.products.write tempest.categories.read tempest.categories.write tempest.settings.orderstatuses.read tempest.settings.domains.read offline_access';
        return 'https://api.ekm.net/connect/authorize?client_id='.$api_key.'&scope='.$scope.'&redirect_uri=https://opdev.onepatch.com/wooplugin/ekmauthreturn&prompt=login&state='.$id.'&response_type=code';
    }

    /**
     *This is for authekmservice.
     *
     * @return \Illuminate\Http\Response
     */
    public function saveekmdata(Request $req)
    {
         

        $api_key = env('client_id');
        $currnetUser = Auth::user();
        $dbdata = array();
        $service_name = $req->get('integuration_name');
        $ekm_currency = $req->get('ekm_currency');
        // $ekm_store_url = $req->get('ekm_store_url');
        $dbdata['service_name'] = $service_name;
        $dbdata['ekm_currency'] = $ekm_currency;
        // $dbdata['ekm_store_url'] = $ekm_store_url;
        $formData = new Accountservice();
        $formData->service_id = 1;
        $formData->account_id = $currnetUser->account_id;
        // $formData->data = json_encode($dbdata);
        $formData->status = 0;
        // dd($formData);
        $formData->save();

        $scope = 'tempest.orders.read tempest.orders.write tempest.products.read tempest.products.write tempest.categories.read tempest.categories.write tempest.settings.orderstatuses.read tempest.settings.domains.read offline_access';

        return 'https://api.ekm.net/connect/authorize?client_id='.$api_key.'&scope='.$scope.'&redirect_uri=https://opdev.onepatch.com/wooplugin/ekmauthreturn&prompt=login&state=onepatch&response_type=code';

    }

     
}
