<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var LaravelResponse */
        $response = $next($request);
        $data = '';

        // 根据响应状态码设置不同的消息内容
        switch ($response->status()) {
            case 200:
                $status = 'success';
                break;
            case 201:
                $status = 'created';
                break;
            case 204:
                $status = 'no content';
                break;
            case 302:
                $status = 'redirect';
                break;
            case 400:
                $status = 'bad request';
                $data = '请求无效，请检查参数是否正确';
                break;
            case 401:
                $status = 'unauthorized';
                $data = '未授权的访问';
                break;
            case 403:
                $status = 'forbidden';
                $data = '您没有权限进行此操作';
                break;
            case 404:
                $status = 'not found';
                $data = '请求资源不存在';
                break;
            case 405:
                $status = 'method not allowed';
                $data = '请求方法不允许';
                break;
            case 422:
                $status = 'unprocessable entity';
                $data = '数据验证失败，请检查表单中是否填写正确';
                break;
            case 429:
                $status = 'too many requests';
                $data = '请求过于频繁，请稍后再试';
                break;
            case 500:
                $status = 'server error';
                $data = '服务器发生了错误';
                break;
            case 503:
                $status = 'service unavailable';
                $data = '服务当前无法访问，请稍后再试';
                break;
            default:
                $status = 'unknown error';
                break;
        }

        return response()->json([
            'code' => $response->status(),
            'status' => $status,
            'data' => $response->original,
            'message'=> $data,
        ]);
    }
}
