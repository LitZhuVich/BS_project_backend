<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group_name',
    ];

    protected $hidden = [
        'pivot'
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_users');
    }
}
