<?php

namespace App\Http\Controllers\Modules\TaskProcesser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\InventoryQueue; 
use App\Models\InventoryMetadata; 
use App\Models\Inventory; 
use App\Models\OpTaskRecord; 
use App\Models\Order; 
use Auth; 
use ManageEkm;

class TaskProcesserController extends Controller
{
    public function ekmImportprocessqueuetaskgenerate(){
        $taskdata = OpTaskRecord::where(['isstarted' => 0,'isfinished' => 0,'iscancelled' => 0, 'name' => 'import_product_ekm'])->orderBy('priority', 'desc')->first();
        if($taskdata){
            $taskdata->isstarted = 1;
            $taskdata->save();
            $task_record_data = json_decode($taskdata->data);
            //dd($task_record_data->config);
            $offset = 0;
            $page=1;
            $limit = 20;
            $queues = new InventoryQueue();
            $queues->account_id = $taskdata->account_id;
            $queues->task_id = $taskdata->id;
            $queues->config_id = $task_record_data->config;
            $queues->extra_details = '';
            $queues->priority = 1;
            $queues->offset = $offset;
            $queues->limit = $limit;
            $queues->api_response = '';
            $queues->api_status = '';
            $queues->type = 'import_product_ekm';
            $queues->status = 0;
            $queues->save();
            $queuetaskid = $queues->id;
            $config_id = $task_record_data->config;
            $ManageEkm = new ManageEkm();
            $response = $ManageEkm->importproducts($config_id,$queuetaskid,$page,$limit);
            /*echo 123;
            dd($response);*/
            $response_data = json_decode($response);
            //dd($response_data);
            if(isset($response_data->status) && $response_data->status == 1){
                $total_product = $response_data->total_records;
                $response_time = $response_data->response_time;
                $totalpages = $response_data->total_pages;
                if($totalpages>0){
                    for($i = 1; $i<= $totalpages; $i++){
                       $offset  = ($i-1)*$limit+20;
                        if($total_product>=$offset){
                            $queues = new InventoryQueue();
                            $queues->account_id = $taskdata->account_id;
                            $queues->task_id = $taskdata->id;;
                            $queues->config_id = $task_record_data->config;
                            $queues->extra_details = '';
                            $queues->priority = 1;
                            $queues->offset = $offset;
                            $queues->limit = $limit;
                            $queues->api_response = '';
                            $queues->api_status = '';
                            $queues->type = 'import_product_ekm';
                            $queues->status = 0;
                            $queues->save();
                        }
                    }
                }
                //echo $offset.'==limit'.$limit.'==';die;
                $count_check_queue_task=InventoryQueue::where(['status' => 0,'task_id'=>$taskdata->id])->count();
                if($count_check_queue_task==0){
                    $taskdata = OpTaskRecord::find($taskdata->id);
                    $taskdata->isfinished = 1;
                    $taskdata->save();
                    $order_notification = new Notifications();
                    $order_notification->account_id = $taskdata->account_id;
                    $order_notification->type = 'import';
                    $order_notification->link = 'products';
                    $order_notification->icon = 'glyphicon glyphicon-cloud-download';
                    $order_notification->text = 'Import Ekm product task is done.';
                    $order_notification->save();
                }
            }else{
                if(isset($response_data->message)){
                    $message=$response_data->message;
                }else{
                    $message="Something went wrong in your ekm intregation";
                }
                $order_notification = new Notifications();
                $order_notification->account_id = $taskdata->account_id;
                $order_notification->type = 'error';
                $order_notification->link = '';
                $order_notification->icon = 'glyphicon glyphicon-exclamation-sign';
                $order_notification->text = $message;
                $order_notification->save();
                $taskdata->isfinished = 1;
                $taskdata->save();
            }
        }
        //end
    }


    public function ekmImportprocessqueuetask(){
        $queuetaskdata = InventoryQueue::where(['status' => 0,'type' => 'import_product_ekm'])->orderBy('priority','desc')->orderBy('id','asc')->first();
        //dd($queuetaskdata);
        if($queuetaskdata){
            $offset=$queuetaskdata->offset;
            $limit=$queuetaskdata->limit;
            $page = ($offset+$limit)/$limit;
            $queuetaskid=$queuetaskdata->id;
            $config_id=$queuetaskdata->config_id;
            $task_id=$queuetaskdata->task_id;
            $ManageEkm = new ManageEkm();
            $response=$ManageEkm->importproducts($config_id,$queuetaskid,$page,$limit);
            //dd($response);
            $count_check_queue_task=InventoryQueue::where(['status' => 0,'task_id'=>$task_id])->count();
            if($count_check_queue_task==0){
                $taskdata = OpTaskRecord::find($task_id);
                $taskdata->isfinished = 1;
                $taskdata->save();
                $order_notification = new Notifications();
                $order_notification->account_id = $taskdata->account_id;
                $order_notification->type = 'import';
                $order_notification->link = 'products';
                $order_notification->icon = 'glyphicon glyphicon-cloud-download';
                $order_notification->text = 'Import Ekm product task is done.';
                $order_notification->save();
            }
        }
    }

    public function EKMProductImportRequest($config) {
        $user = Auth::user();
        $taskdata = OpTaskRecord::where(['isfinished' => 0, 'name' => 'import_product_ekm', 'account_id' => $user->account_id])->orderBy('priority', 'desc')->first();
        if ($user) {
            if (empty($taskdata)) {
                if (is_numeric($config)) {
                    $taskdata = new OpTaskRecord();
                    $taskdata->account_id = $user->account_id;
                    $taskdata->name = 'import_product_ekm';
                    $taskdata->data = json_encode(['service' => 'ekm', 'config' => $config, 'req_data' => '']);
                    $taskdata->isstarted = 0;
                    $taskdata->isfinished = 0;
                    $taskdata->save();
                    return Redirect::to('inventory/preimport')->with('alert-success', "Request processing..");
                }
            } else
                return Redirect::back()->with('alert-success', "Task is alredy started");
        }
        return Redirect::back()->with('alert-error', "Invalid configuration or you dont have access to do that..");
    }
}
