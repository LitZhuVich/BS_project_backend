<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'file_name',
        'file_url',
        'order_id',
        'user_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
