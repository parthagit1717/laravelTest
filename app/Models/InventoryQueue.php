<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryQueue extends Model
{
    use HasFactory;
    
    protected $table = 'inventory_queues';
    protected $guarded = ['id'];
    protected $fillable = ['account_id', 'task_id', 'config_id', 'extra_details', 'priority', 'offset', 'limit', 'api_response','api_status', 'type', 'status'];
}
