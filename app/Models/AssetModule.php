<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetModule extends Model
{
    use HasFactory;
    // 指定连接的数据库
    protected $table = 'asset_modules';
    protected $hidden = ['id'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
