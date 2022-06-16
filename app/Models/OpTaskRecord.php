<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpTaskRecord extends Model
{
    use HasFactory;
    protected $table = 'op_task_record';
    public $timestamps = false;
}
