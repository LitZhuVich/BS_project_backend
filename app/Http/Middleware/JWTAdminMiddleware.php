<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
class JWTAdminMiddleware
{
    /**
     * 检测是否为管理员权限的中间件
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // 不是管理员权限
            if (!$user || !$user->isAdmin()) {
                return response()->json(['error' => '权限不足']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => '令牌无效']);
        }
        // 通过
        return $next($request);
    }
}
