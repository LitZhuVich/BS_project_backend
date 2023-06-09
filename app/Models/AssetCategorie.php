<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategorie extends Model
{
    use HasFactory;
    protected $fillable = ['asset_name'];
    // 指定连接的数据表
    protected $table = 'asset_categories';
    protected $hidden = ['id'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
