<?php

namespace App\Http\Controllers;

use App\Mail\AuthPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * 注册方法
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
            return response()->json($validator->errors(), 400);
        }
        // 添加用户 输入邮箱令牌
        // 使用 Argon2 哈希算法加密密码 memory 参数定义了哈希    需要使用的内存大小，time 参数定义了哈希需要执行的时间，threads 参数定义了哈希算法需要使用的线程数。
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password, ['memory' => 1024, 'time' => 2, 'threads' => 2, 'argon2i']),
            'is_verified' => 0,
            'email_verification_token' => Str::random(32),
            //            TODO:后期需要修改，现在不允许为空
            'companyname'   => '',
        ]);

        // 注册成功返回 token
        $token = JWTAuth::fromUser($user);

        // 返回创建成功信息
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 200);
    }

    /**
     * 发送邮箱验证码
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmailToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
//            'email' => 'required|string|unique:users|max:255|email:filter',
//            'email' => 'required|string|max:255|email:filter',
            'user_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        // 获取用户
        $user = User::query()->find($request->post('user_id'));
        if (!$user) {
            return response()->json('获取用户失败', 400);
        }
        // 发送邮箱
        Mail::to($user)->queue(new authPost($user));
        return response()->json('发送成功，请检查您的电子邮件以验证帐户', 201);
    }

    /**
     * 验证邮箱
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        $user = User::where('email_verification_token', $request->input('token'))->first();

        if (!$user) {
            return response()->json('令牌输入有误', 400);
        }

        $user->is_verified = 1;
        $user->email_verification_token = null;
        $user->save();

        return response()->json('电子邮件已成功验证', 200);
    }

    /**
     * 登录 获取token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 获取用户名和密码
        $credentials = $request->only('username', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json('账号或密码有误');
            } else {
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ], 200);
            }
        } catch (JWTException $e) {
            return response()->json('账号或密码有误'.$e, 500);
        }
    }

    /**
     * 刷新 token
     * 会让之前的 token 失效
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken()
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            return response()->json('无法刷新令牌', 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 200);
    }

    /**
     * 登出
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();
        return response()->json('成功登出', 200);
    }

    /**
     * 显示已登录的用户
     * 解析JWT令牌
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function show()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $user->load('role');
            $role = $user->role;
            $user->setAttribute('role_name', $role->role_name);
            // 返回用户信息
            return response()->json($user, 200);
        } catch (JWTException $e) {
            return response()->json($e, 401);
        }
    }
}
