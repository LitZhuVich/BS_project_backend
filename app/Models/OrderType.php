<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'type_name'
    ];

    protected $hidden = [
        'id',
        'type_description'
    ];

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
