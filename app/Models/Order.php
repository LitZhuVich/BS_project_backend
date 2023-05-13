<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'priority_id',
        'status_id',
        'type_id',
        'user_id',
        'phone',
        'title',
        'time_limit',
        'description',
        'attachment',
        'isOnLine',
        'address',
        'appointment'
    ];
}
