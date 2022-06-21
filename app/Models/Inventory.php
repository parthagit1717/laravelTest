<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $table = 'inventory';

    protected $fillable = ['account_id','sku','barcode','asin','name','type','parent_id','short_description','long_description','quantity','stock_status','currency','buy_price','price','sale_price','brand','location','price','sale_price', 'status', 'category_id', 'created_at','updated_at'];
    
    protected $primaryKey = 'id';
    
    public $timestamps = true;
    
    protected $casts = [
        'pd_attributes' => 'array'
    ];

    public function metadata($key = "", $type = '')
    {
        if($this->account_id){
            if($key == ""){
                return $this->hasMany("LMS\Inventorymeta",'inventory_id','id')->where('account_id', $this->account_id);
            }
            else{
                if($type){
                    return $this->hasMany("LMS\Inventorymeta",'inventory_id','id')->where('account_id', $this->account_id)->where('meta_key', $key)->get();
                }else{
                   return $this->hasOne("LMS\Inventorymeta",'inventory_id','id')->where('account_id', $this->account_id)->where('meta_key', $key)->first(); 
                }
                
            }
        }else{
            return $this->hasMany("LMS\Inventorymeta",'inventory_id','id');
        }
        
    }
    public function metadataWithExtra($key = "", $config_id = "")
    {
        return $this->hasOne("LMS\Inventorymeta",'inventory_id','id')->where('account_id', $this->account_id)->where('config_id', $config_id)->where('meta_key', $key)->first();
    }

    public function category()
    {
        return $this->belongsTo("LMS\OnepatchGoogleCategories", 'category_id', 'id')->first();
    }

    public function childInventory()
	{
	    return $this->hasMany('LMS\Inventory', 'parent_id', 'id');
	}
}
