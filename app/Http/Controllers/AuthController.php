<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

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
        // 添加用户
        // 使用 Argon2 哈希算法加密密码 memory 参数定义了哈希需要使用的内存大小，time 参数定义了哈希需要执行的时间，threads 参数定义了哈希算法需要使用的线程数。
        $user = User::create([
            'username'      => $request->username,
            'password'      => Hash::make($request->password, ['memory' => 1024, 'time' => 2, 'threads' => 2, 'argon2i']),
            //            TODO:后期需要修改，现在不允许为空
            'companyname'   => ''
        ]);

        // 注册成功返回 token
        $token = JWTAuth::fromUser($user);

        // 返回创建成功信息
        //        return response()->json(['user'=>$user,'token'=>$token],200);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 200);
    }

    /**
     * TODO:待完成
     * 注册，有邮箱验证功能
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCheckEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users|max:255',
            'email' => 'required|string|unique:users|max:255|email:filter',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password, ['memory' => 1024, 'time' => 2, 'threads' => 2, 'argon2i']),
            'is_verified' => 0,
            'email_verification_token' => Str::random(32),
        ]);

        Mail::to($user->email)->send(new VerifyEmail($user));

        return response()->json('用户成功注册。 请检查您的电子邮件以验证您的帐户.', 201);
    }

    /**
     * TODO:待完成
     * 验证邮箱
     *
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            abort(404);
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
            return response()->json('用户名或者密码错误', 500);
        }
    }

    /**
     * 刷新 token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        //        // 获取当前用户的 Token
        //        $token = JWTAuth::getToken();
        //
        //        // 刷新 Token 并返回新的 Token
        //        $newToken = JWTAuth::refresh($token);
        //
        //        return response()->json(['token' => $newToken]);
        try {
            $token = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            return response()->json('无法刷新令牌', 500);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
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
     * 解析JWT令牌 显示已登录的用户
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function show()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user->load('role');
        $role = $user->role;
        $user->setAttribute('role_name', $role->role_name);
        // 返回用户信息`
        return response()->json($user, 200);
    }
}
