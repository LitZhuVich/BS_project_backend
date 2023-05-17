<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;
    //    指定连接的数据表
    protected $table = 'order_status';
    protected $hidden = ['id'];

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
