<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'skills';

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_skill');
    }
}
