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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

// api 路径首部自带 /api/v1
Route::group(['prefix'=>'/v1'],function (){
    // 注册接口
    Route::post('/register',[\App\Http\Controllers\AuthController::class,'register']);
    // 登录接口
    Route::post('/login',[\App\Http\Controllers\AuthController::class,'login']);

    // 测试接口
    Route::get('/asd',function (){
        return '1123';
    });

    // 受保护的 api
    Route::middleware(['auth:api','jwt.auth'])->group(function(){
        // 刷新用户的token
        Route::get('/refresh',[\App\Http\Controllers\AuthController::class,'refresh']);
        // 登出
        Route::post('/logout',[\App\Http\Controllers\AuthController::class,'logout']);
        // 返回用户信息
        Route::get('/user',[\App\Http\Controllers\AuthController::class,'show']);
        // 批量删除客户
        Route::delete('/CustomerRepresentative',[\App\Http\Controllers\UserController::class,'destroyMany']);
        // 删除客户
        Route::delete('/CustomerRepresentative/{id}',[\App\Http\Controllers\UserController::class,'destroy']);
        // 显示所有客户
        Route::get('/getAllCustomerRepresentative',[\App\Http\Controllers\UserController::class,'getAllCustomerRepresentative']);
        // 显示筛选客户表单数据
        Route::post('/filterCustomerRepresentative',[\App\Http\Controllers\UserController::class,'filterCustomerRepresentative']);

        // 显示所有工单状态
        Route::get('/orderType',[\App\Http\Controllers\OrderTypeController::class,'index']);

        // 显示所有组的名字
        Route::get('/groupName',[\App\Http\Controllers\GroupController::class,'showGroupName']);
    });
})->middleware('cors');
