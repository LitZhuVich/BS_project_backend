<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetSource extends Model
{
    use HasFactory;
    // 指定连接数据库
    protected $table = 'asset_sources';
    protected $hidden = ['id'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
