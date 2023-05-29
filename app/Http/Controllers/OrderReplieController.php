<?php

namespace App\Http\Controllers;

use App\Models\OrderReplie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderReplieController extends Controller
{
    /**
     * 已登录授权用户
     *
     * @var $authUser
     */
    protected $authUser;

    /**
     * 构造函数
     * 为 authUser 全局变量赋值
     */
    public function __construct()
    {
        $this->authUser = JWTAuth::parseToken()->authenticate();
    }

    /**
     * 显示工单总数
     *
     * @return JsonResponse
     */
    public function showCount():JsonResponse
    {
        $data = OrderReplie::query()->count();
        return response()->json($data,200);
    }

    /**
     * 显示自己的工单总数
     *
     * @return JsonResponse
     */
    public function showMyCount():JsonResponse
    {
        $data = OrderReplie::query()
            ->where('user_id',$this->authUser->id)->count();
        return response()->json($data,200);
    }
}
