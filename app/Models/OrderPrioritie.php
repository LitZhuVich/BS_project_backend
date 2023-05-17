<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPrioritie extends Model
{
    use HasFactory;

    protected $hidden = ['id'];

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
