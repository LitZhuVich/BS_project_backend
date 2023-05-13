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
Route::group(['prefix' => '/v1'], function () {
    // 注册接口
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
    // 登录接口
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    // 测试接口
    Route::get('/asd', function () {
        return '1123';
    });

    // 刷新用户的token
    Route::get('/refresh', [\App\Http\Controllers\AuthController::class, 'refreshToken']);
    // 受保护的 api
    Route::middleware(['auth:api', 'jwt.auth'])->group(function () {
        // 登出
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        // 返回用户信息
        Route::get('/user', [\App\Http\Controllers\AuthController::class, 'show']);

        // 批量删除客户
        Route::delete('/CustomerRepresentative',[\App\Http\Controllers\UserController::class,'destroyMany']);
        // 删除客户
        Route::delete('/CustomerRepresentative/{id}',[\App\Http\Controllers\UserController::class,'destroy']);
        // 新增客户信息
        Route::post('/CustomerRepresentative',[\App\Http\Controllers\UserController::class,'store']);
        // 修改客户信息
        Route::patch('/CustomerRepresentative/{id}',[\App\Http\Controllers\UserController::class,'update']);
        // 根据ID获取客户信息
        Route::get('/CustomerRepresentative/{id}',[\App\Http\Controllers\UserController::class,'show']);
        // 显示所有客户
        Route::get('/CustomerRepresentative',[\App\Http\Controllers\UserController::class,'index']);
        // 显示筛选客户表单数据
        Route::post('/filterCustomerRepresentative',[\App\Http\Controllers\UserController::class,'showMany']);
        // 显示所有工单状态
//        Route::get('/orderType',[\App\Http\Controllers\OrderTypeController::class,'index']);
        // 显示所有组的名字
        Route::get('/groupName',[\App\Http\Controllers\GroupController::class,'showGroupName']);
        Route::get('/group/{id}',[\App\Http\Controllers\GroupController::class,'show']);
        Route::post('/group',[\App\Http\Controllers\GroupController::class,'showMany']);
        /*
            工单
        */
        // 获取全部工单
        Route::get('/order', [\App\Http\Controllers\OrderController::class, 'index']);
        // 发布工单
        Route::post('/order', [\App\Http\Controllers\OrderController::class, 'create']);
        // 根据工单预约时间月份搜索
        Route::post('/getOrderByMonth', [\App\Http\Controllers\OrderController::class, 'getOrdersByMonth']);

        Route::get('/asdsad/{id}', [\App\Http\Controllers\OrderController::class, 'getOrder']);
        /*
            工单类型
        */
        // 查询工单类型
        Route::get('/orderType', [\App\Http\Controllers\OrderTypeController::class, 'index']);
    });
})->middleware('cors');
