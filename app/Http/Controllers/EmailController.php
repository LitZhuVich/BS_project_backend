<?php

namespace App\Http\Controllers;

use App\Mail\AuthPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    /**
     * 发送邮箱验证码
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmailToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
}
