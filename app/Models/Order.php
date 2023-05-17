<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // public $timestamps = false;

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


    protected $appends = ['username', 'status', 'type', 'priority'];

    public function priority()
    {
        return $this->belongsTo(OrderPrioritie::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function type()
    {
        return $this->belongsTo(OrderType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // 
    public function getUsernameAttribute()
    {
        $user = User::query()->find($this->user_id);
        return $user->username;
    }

    public function getStatusAttribute()
    {
        $status = OrderStatus::query()->find($this->status_id);
        return $status->status_name;
    }

    public function getTypeAttribute()
    {
        $type = OrderType::query()->find($this->type_id);
        return $type->type_name;
    }

    public function getPriorityAttribute()
    {
        $priority = OrderPrioritie::query()->find($this->priority_id);
        return $priority->priority_name;
    }
}
