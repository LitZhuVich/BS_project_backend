<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// api 路径首部自带 /api/v1
Route::group(['prefix'=>'/v1'],function (){
    // 注册接口
    Route::post('/register',[\App\Http\Controllers\AuthController::class,'register']);
    // 登录接口
    Route::post('/login',[\App\Http\Controllers\AuthController::class,'login'])->middleware('cors');
    // 测试接口
    Route::get('/asd',function (){
        return '1123';
    });

    //
//    Route::post('/user',[\App\Http\Controllers\AuthController::class,'show']);

    // 受保护的 api
    Route::middleware('auth:api')->group(function(){
        Route::post('/logout',[\App\Http\Controllers\AuthController::class,'logout']);
        // 返回用户信息
        Route::get('/user',[\App\Http\Controllers\AuthController::class,'show']);
    });
});
