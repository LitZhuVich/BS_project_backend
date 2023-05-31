<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Asset;

class AssetController extends Controller
{
    // 分页
    public function paginate(Request $request)
    {
        // 页面数据大小
        $page_size = $request->input('pageSize');
        // 接收要查询的数据类型
        // paginate表示显示多少条的数据
        $asset = Asset::query()->paginate($page_size);
        if (!$asset) {
            return response()->json('获取失败', 400);
        }
        return response()->json($asset, 200);
    }
    // 高级查询（多条件查询）
    public function queryAssetsWithMulti(Request $request)
    {
        // 页面数据大小
        $page_size = $request->input('pageSize');

        $asset_name = $request->post('asset_name');
        $module_id = $request->module_id;
        $asset_serial = $request->asset_serial;
        $user_id = $request->user_id;
        $model_id = $request->model_id;
        $source_id = $request->source_id;
        $asset_warranty = $request->asset_warranty;
        $asset_address = $request->asset_address;
        $asset_categorie_id = $request->asset_categorie_id;

        $result = Asset::query()
                ->when($asset_name, function ($query, $asset_name) {
            $query->where('asset_name','like',$asset_name);
        })->when($module_id, function ($query, $module_id) {
            $query->where('module_id','like',$module_id);
        })->when($asset_serial, function ($query, $asset_serial) {
            $query->where('asset_serial','like', $asset_serial);
        })->when($user_id, function ($query, $user_id) {
            $query->where('user_id','like', $user_id);
        })->when($model_id, function ($query, $model_id) {
            $query->where('model_id','like', $model_id);
        })->when($source_id, function ($query, $source_id) {
            $query->where('source_id','like', $source_id);
        })->when($asset_warranty, function ($query, $asset_warranty) {
            $query->where('asset_warranty', 'like',$asset_warranty);
        })->when($asset_address, function ($query, $asset_address) {
            $query->where('asset_address','like', $asset_address);
        })->when($asset_categorie_id, function ($query, $asset_categorie_id) {
            $query->where('asset_categorie_id','like', $asset_categorie_id);
        })->paginate($page_size);

        if (!$result) {
            return response()->json('获取失败', 400);
        }
        return response()->json($result, 200);
    }
    // 添加资产
    public function create(Request $request)
    {
        // 表单验证
        $validator = Validator::make($request->all(), [
            'module_id' => 'int',
            'asset_name' => 'required|string|max:255',
            'model_id' => 'int',
            'source_id' => 'int',
            'asset_warranty' => 'required|int',
            'asset_address' => 'max:255',
            'asset_categorie_id' => 'required|int',
            'description' => 'max:255'
        ]);

        // 验证失败
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 获取随机序列号
        $serial = $this->summonSerial();
        // 获取当前登录用户
        $user = JWTAuth::parseToken()->authenticate();

        // 执行添加
        $result = Asset::create([
            'module_id' => $request->module_id,
            'asset_name' => $request->asset_name,
            'asset_serial' => $serial,
            'user_id' => $user->id,
            'model_id' => $request->model_id,
            'source_id' => $request->source_id,
            'asset_warranty' => $request->asset_warranty,
            'asset_address' => $request->asset_address,
            'asset_categorie_id' => $request->asset_categorie_id,
            'description' => $request->description
        ]);
        if (!$result) {
            return response()->json('添加失败', 400);
        }
        return response()->json('添加成功', 200);
    }
    // 根据工程师id查询资产
    public function getAssetsByEngineerId(Request $request)
    {
        // return $request->post('engineer_id');
        $asset = Asset::query()->where('engineer_id', $request->post('engineer_id'))->count();
        if (!$asset) {
            return response()->json('空闲', 400);
        }
        switch ($asset) {
            case $asset >= 1 || $asset < 3:
                return response()->json('闲忙', 200);
                break;
            case $asset >= 3:
                return response()->json('忙碌', 200);
                break;
            default:
                return response()->json('空闲', 200);
                break;
        }
    }

    // 随机生成序列号
    //! 没有做是否重复判断！！！
    public function summonSerial()
    {
        $serial = 'BY' . rand(100000, 999999);
        return $serial;
    }
}
