<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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
        'appointment',
        'engineer_id'
    ];

    protected $appends = ['username', 'status', 'type', 'priority', 'engineer'];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s'
    ];

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
    public function engineer()
    {
        return $this->belongsTo(User::class);
    }
    public function getUsernameAttribute()
    {
        $user = User::query()->find($this->user_id);
        return $user->username;
    }
    public function getEngineerAttribute()
    {
        if ($this->engineer_id != 0) {
            $engineer = User::query()->where('role_id', 2)->find($this->engineer_id);
            return $engineer->username;
        }
        return '无';
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
