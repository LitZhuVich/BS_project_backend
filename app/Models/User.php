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
        'created_at'=>'date:Y-m-d H:i:s',
        'updated_at'=>'date:Y-m-d H:i:s'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role',
        'pivot',
    ];

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

    public function groups(){
        return $this->belongsToMany(Group::class,'group_users');
    }

    public function username(){
        return 'username';
    }

    public function isAdmin()
    {
        // 权限等于 3 是管理员
        return $this->role_id === 3;
    }

    public function isEngineer()
    {
        // 权限等于 2 是工程师
        return $this->role_id === 2;
    }

    public function isCustomerRepresentative()
    {
        // 权限等于 1 == 客户代表
        return $this->role_id === 1;
    }
}
