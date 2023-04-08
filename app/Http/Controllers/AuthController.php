<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    /**
     * 登录方法
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // 表单验证
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users|max:255',
            'password' => 'required|string|confirmed',
        ]);
//        当您使用 confirmed 规则时，Laravel 会自动检查与被验证字段名称相同，但后缀为 _confirmation 的字段。例如，如果您想验证 password 字段
//          ，Laravel 会自动检查 password_confirmation 字段
        // 验证失败
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        // 添加用户
        $user = User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        // 返回创建成功信息
        return response()->json(['user'=>$user,'token'=>$token]);
    }

    /**
     * 登录 获取token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        // 获取用户名和密码
        $credentials = $request->only('username', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => '账号或密码有误']);
            }else{
                return response()->json(['success'=>'登录成功','token' => $token],200);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => '用户名或者密码错误'], 500);
        }

    }

    /**
     * 刷新 token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // 获取当前用户的 Token
        $token = JWTAuth::getToken();

        // 刷新 Token 并返回新的 Token
        $newToken = JWTAuth::refresh($token);

        return response()->json(['token' => $newToken]);
    }

    /**
     * 登出
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => '成功登出']);
    }

    /**
     * 解析JWT令牌 显示已登录的用户
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function show(){
        $user = JWTAuth::parseToken()->authenticate();

        // 返回用户信息
        return response()->json(compact('user'));
    }
}
