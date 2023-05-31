<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'companyname',
        'password',
        'avator',
        'phone',
        'address',
        'email',
        'is_verified',
        'email_verification_token',
        'role_id',
        'remark',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
        'role',
        'pivot',
    ];

    protected $appends = ['skill_proficiency'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_users');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skill');
    }

    public function username()
    {
        return 'username';
    }

    public function getSkillProficiencyAttribute()
    {
        return UserSkill::query()->where('user_id',$this->id)->pluck('skill_proficiency')->first();
    }

    public function isAdmin(): bool
    {
        // 权限等于 3 是管理员
        return $this->role_id === 3;
    }

    public function isEngineer(): bool
    {
        // 权限等于 2 是工程师
        return $this->role_id === 2;
    }

    public function isCustomerRepresentative(): bool
    {
        // 权限等于 1 == 客户代表
        return $this->role_id === 1;
    }
}
