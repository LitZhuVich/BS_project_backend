<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'avatar_name',
        'avatar_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
