<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMetadata extends Model
{
    use HasFactory;
    protected $table = 'inventory_metadata';
    public $timestamps = true;
    protected $fillable = ['account_id', 'inventory_id', 'sku', 'extrainfo', 'config_id', 'meta_key', 'meta_value', 'service_flag'];

}
