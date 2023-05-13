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
    /**
     * 受保护的  Api 接口
     * 没有登录无法访问
     */
    Route::middleware(['auth:api', 'jwt.auth'])->group(function () {
        // 登出
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        // 返回用户信息
        Route::get('/user', [\App\Http\Controllers\AuthController::class, 'show']);
        // TODO:
//            前缀 : /CustomerRepresentative
//            例如：
//            Route::delete('/CustomerRepresentative/{id}',[\App\Http\Controllers\UserController::class,'destroy']);
//            Route::post('/CustomerRepresentative/filter',[\App\Http\Controllers\UserController::class,'showMany']);
        // 客户分类
        Route::group(['prefix'=>'/CustomerRepresentative'],function (){
            // 删除客户
            Route::delete('/{id}',[\App\Http\Controllers\UserController::class,'destroy']);
            // 新增客户信息
            Route::post('/',[\App\Http\Controllers\UserController::class,'store']);
            // 修改客户信息
            Route::patch('/{id}',[\App\Http\Controllers\UserController::class,'update']);
            // 根据ID获取客户信息
            Route::get('/{id}',[\App\Http\Controllers\UserController::class,'show']);
            // 显示所有客户
            Route::get('/',[\App\Http\Controllers\UserController::class,'index']);
            // 显示筛选客户表单数据
            Route::post('/filter',[\App\Http\Controllers\UserController::class,'showMany']);
        });
        // 组分类
        Route::group(['prefix'=>'/group'],function (){
            // 获取所有组信息
            Route::get('/',[\App\Http\Controllers\GroupController::class,'showUser']);
            // 显示所有组的名字
            Route::get('/name',[\App\Http\Controllers\GroupController::class,'showGroupName']);
            // 获取单个组信息
            Route::get('/{id}',[\App\Http\Controllers\GroupController::class,'show']);
            // 获取用户组名
            Route::get('/name/{id}',[\App\Http\Controllers\GroupController::class,'showName']);
        });
//        TODO: cly 你也改成上面用组分类的格式。收到就可以删除这段 TODO注释了！
        /*
            工单
        */
        // 获取全部工单
        Route::get('/order', [\App\Http\Controllers\OrderController::class, 'index']);
        // 发布工单
        Route::post('/order', [\App\Http\Controllers\OrderController::class, 'create']);
        // 根据工单预约时间月份搜索
        Route::post('/getOrderByMonth', [\App\Http\Controllers\OrderController::class, 'getOrdersByMonth']);
        /*
            工单类型
        */
        // 查询工单类型
        Route::get('/orderType', [\App\Http\Controllers\OrderTypeController::class, 'index']);
    });
})->middleware('cors');
