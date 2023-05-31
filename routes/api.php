<?php

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
    //    Route::post('/registerCheckEmail', [\App\Http\Controllers\AuthController::class, 'registerCheckEmail']);
    // 登录接口
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    // 测试接口
    Route::get('/', function () {
        return '1123';
    });
    // 刷新用户的token
    Route::get('/refresh', [\App\Http\Controllers\AuthController::class, 'refreshToken']);

    /**
     * 受保护的  Api 接口
     * 没有登录无法访问
     */
    Route::middleware(['auth:api', 'jwt.auth'])->group(function () {
        //     Route::middleware(['checkLogin'])->group(function () {
        // 登出
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        // 返回用户信息
        Route::get('/user', [\App\Http\Controllers\AuthController::class, 'show']);
        // 发送邮箱令牌
        Route::post('/sendEmailToken', [\App\Http\Controllers\EmailController::class, 'sendEmailToken']);
        // 验证邮箱验证码接口
        Route::post('/verifyEmail', [\App\Http\Controllers\EmailController::class, 'verifyEmail']);
        // TODO:
        //            前缀 : /CustomerRepresentative
        //            例如：
        //            Route::delete('/CustomerRepresentative/{id}',[\App\Http\Controllers\UserController::class,'destroy']);
        //            Route::post('/CustomerRepresentative/filter',[\App\Http\Controllers\UserController::class,'showMany']);
        // 客户分类接口
        Route::group(['prefix' => '/CustomerRepresentative'], function () {
            // 删除客户
            Route::delete('/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
            // 新增客户信息
            Route::post('/', [\App\Http\Controllers\UserController::class, 'store']);
            // 修改客户信息
            Route::put('/{id}', [\App\Http\Controllers\UserController::class, 'update']);
            // 绑定客户姓名
            Route::patch('/{id}/username', [\App\Http\Controllers\UserController::class, 'updateUsername']);
            // 修改密码
            Route::patch('/{id}/password', [\App\Http\Controllers\UserController::class, 'updatePassword']);
            // 绑定客户邮箱
            Route::patch('/{id}/email', [\App\Http\Controllers\UserController::class, 'updateEmail']);
            // 绑定客户手机号
            Route::patch('/{id}/phone', [\App\Http\Controllers\UserController::class, 'updatePhone']);
            // 绑定客户头像
            Route::post('/{id}/avatar', [\App\Http\Controllers\UserController::class, 'updateAvatar']);
            // 禁用用户/启用用户
            //            Route::patch('/lock/{id}',[\App\Http\Controllers\UserController::class,'update']);
            // 显示分页客户数据
            Route::get('/', [\App\Http\Controllers\UserController::class, 'paginate']);
            // 显示所有客户
            Route::get('/all', [\App\Http\Controllers\UserController::class, 'index']);
            // 显示所有用户
            Route::get('/getAllUsers', [\App\Http\Controllers\UserController::class, 'getAllUsers']);
            // 显示所有工程师
            Route::get('/getAllEngineers', [\App\Http\Controllers\UserController::class, 'getAllEngineers']);
            // 根据ID获取客户信息
            Route::get('/{id}', [\App\Http\Controllers\UserController::class, 'show']);
            // 显示筛选客户表单数据
            Route::post('/filter', [\App\Http\Controllers\UserController::class, 'showFilter']);
        });
        // 工程师分页
        Route::get('/engineerPaginate',[\App\Http\Controllers\UserController::class,'engineerPaginate']);
        Route::get('/engineerFilter',[\App\Http\Controllers\UserController::class,'showEngineerFilter']);
        // 组分类接口
        Route::group(['prefix' => '/group'], function () {
            // 获取所有组信息
            Route::get('/', [\App\Http\Controllers\GroupController::class, 'showUser']);
            // 删除组
            Route::delete('/{id}', [\App\Http\Controllers\GroupController::class, 'destroy']);
            // 工程师组分页
            Route::get('/engineerPaginate',[\App\Http\Controllers\GroupController::class,'engineerPaginate']);
            // 工程师组搜索
            Route::get('/engineerFilter',[\App\Http\Controllers\GroupController::class,'showEngineerFilter']);
            // 获取工程师组信息
            Route::get('/engineer', [\App\Http\Controllers\GroupController::class, 'engineerIndex']);
            // 显示所有组的名字
            Route::get('/name', [\App\Http\Controllers\GroupController::class, 'showGroupName']);
            // 获取单个组信息
            Route::get('/{id}', [\App\Http\Controllers\GroupController::class, 'show']);
            // 获取用户组名
            Route::get('/name/{id}', [\App\Http\Controllers\GroupController::class, 'showName']);
        });
        // 技能分类接口
        Route::group(['prefix'=>'/skill'],function (){
            // 显示所有技能
            Route::get('/',[\App\Http\Controllers\SkillController::class,'index']);
            // 显示技能分页
            Route::get('/paginate',[\App\Http\Controllers\SkillController::class,'paginate']);
            // 显示查询后的数据
            Route::get('/showFilter',[\App\Http\Controllers\SkillController::class,'showFilter']);
            Route::get('/name',[\App\Http\Controllers\SkillController::class,'showName']);
            // 根据技能显示人
            Route::get('/{id}',[\App\Http\Controllers\SkillController::class,'show']);
            // 删除技能
            Route::delete('/{id}',[\App\Http\Controllers\SkillController::class,'destroy']);
        });
        // 文件分类接口
        Route::group(['prefix' => '/file'], function () {
            // 获取所有文件信息
            Route::get('/order', [\App\Http\Controllers\UploadController::class, 'orderIndex']);
            // 获取所有头像信息
            Route::get('/avatar', [\App\Http\Controllers\UploadController::class, 'avatarIndex']);
            // 上传文件
            Route::post('/upload', [\App\Http\Controllers\UploadController::class, 'orderUpload']);
        });
        // 工单分类接口
        Route::group(['prefix' => '/order'], function () {
            // 获取全部工单
            Route::get('/', [\App\Http\Controllers\OrderController::class, 'index']);
            // 分页显示工单
            Route::get('/orderPage', [\App\Http\Controllers\OrderController::class, 'paginate']);
            // 发布工单
            Route::post('/', [\App\Http\Controllers\OrderController::class, 'create']);
            // 根据工程师ID查询工单数量
            Route::get('/countOrders/{id}', [\App\Http\Controllers\OrderController::class, 'queryOrderByEngId']);
            // 根据用户名查询工单
            Route::post('/getOrderByUsername', [\App\Http\Controllers\OrderController::class, 'getOrderByUsername']);
            // 获取未分配的工单信息
            Route::get('/getToBeDoneOrder', [\App\Http\Controllers\OrderController::class, 'getToBeDoneOrder']);
            // 显示自己所有的工单信息
            Route::get('/showAllOrder', [\App\Http\Controllers\OrderController::class, 'showAllOrder']);
            // 显示自己所在组的工单信息
            Route::get('/showGroupOrder', [\App\Http\Controllers\OrderController::class, 'showGroupOrder']);
            // 显示自己星期的工单信息
            Route::get('/showWeekOrder/{status_id}', [\App\Http\Controllers\OrderController::class, 'showWeekOrder']);
            // 显示自己今天的工单信息
            Route::get('/showWithinTenOrder/{status_id}', [\App\Http\Controllers\OrderController::class, 'showWithinTenOrder']);
            // 显示不同状态下的工单
            Route::get('/status/{status_id}', [\App\Http\Controllers\OrderController::class, 'showStatus']);
            // 显示不同优先级下的工单
            Route::get('/priority/{priority_id}', [\App\Http\Controllers\OrderController::class, 'showPriority']);
            // 显示我的在不同状态下的工单
            Route::get('/status/my/{status_id}', [\App\Http\Controllers\OrderController::class, 'showMyStatus']);
            // 显示我的在不同优先级下的工单
            Route::get('/priority/my/{priority_id}', [\App\Http\Controllers\OrderController::class, 'showMyPriority']);
            // 显示用户工单信息
            Route::get('/user/{id}', [\App\Http\Controllers\OrderController::class, 'showUser']);
            // 显示单个工单信息
            Route::get('/{id}', [\App\Http\Controllers\OrderController::class, 'show']);
            // 更新工单
            Route::post('/update', [\App\Http\Controllers\OrderController::class, 'update']);
            // 删除工单
            Route::get('/delete/{id}', [\App\Http\Controllers\OrderController::class, 'delete']);
        });
        // 工单回复接口
        Route::group(['prefix' => '/orderReplied'], function () {
            // 显示总工单回复总数
            Route::get('/count', [\App\Http\Controllers\OrderReplieController::class, 'showCount']);
            // 显示自己的工单回复总数
            Route::get('/myCount', [\App\Http\Controllers\OrderReplieController::class, 'showMyCount']);
        });
        /*
            工单
        */
        // 获取全部工单
        Route::get('/order', [\App\Http\Controllers\OrderController::class, 'index']);
        // 分页显示工单
        Route::get('/orderPage', [\App\Http\Controllers\OrderController::class, 'paginate']);
        // 发布工单
        Route::post('/order', [\App\Http\Controllers\OrderController::class, 'create']);
        // 根据用户名查询工单
        Route::post('/getOrderByUsername', [\App\Http\Controllers\OrderController::class, 'getOrderByUsername']);
        // 获取未分配的工单信息
        Route::get('/getToBeDoneOrder', [\App\Http\Controllers\OrderController::class, 'getToBeDoneOrder']);
        /*
            工单类型
        */
        // 查询工单类型
        Route::get('/orderType', [\App\Http\Controllers\OrderTypeController::class, 'index']);
        /*
            资产
        */
        Route::group(['prefix' => '/asset'], function () {
            // 添加资产
            Route::post('/add', [\App\Http\Controllers\AssetController::class, 'create']);
            // 分页显示资产
            Route::get('/assetPage', [\App\Http\Controllers\AssetController::class, 'paginate']);
            // 高级查询（多条件查询）
            Route::post('/query', [\App\Http\Controllers\AssetController::class, 'queryAssetsWithMulti']);
            // 根据工程师id获取资产
            Route::post('/queryByEngineerId', [\App\Http\Controllers\AssetController::class, 'getAssetsByEngineerId']);
        });
    });
})->middleware('cors');
