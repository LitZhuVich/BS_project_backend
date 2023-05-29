<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'asset_address',
        'module_id',
        'asset_name',
        'user_id',
        'model_id',
        'source_id',
        'asset_warranty',
        'asset_serial',
        'asset_categorie_id',
        'description',
        'created_at',
    ];

    protected $appends = ['username', 'categorie', 'module', 'source'];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s'
    ];

    public function categorie()
    {
        return $this->belongsTo(AssetCategorie::class);
    }
    public function module()
    {
        return $this->belongsTo(AssetModule::class);
    }
    public function source()
    {
        return $this->belongsTo(AssetSource::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getUsernameAttribute()
    {
        $user = User::query()->find($this->user_id);
        return $user->username;
    }
    public function getCategorieAttribute()
    {
        $categorie = AssetCategorie::query()->find($this->asset_categorie_id);
        return $categorie->asset_name;
    }
    public function getModuleAttribute()
    {
        $module = AssetModule::query()->find($this->module_id);
        return $module->module_name;
    }
    public function getSourceAttribute()
    {
        $source = AssetSource::query()->find($this->source_id);
        return $source->source_name;
    }
}
