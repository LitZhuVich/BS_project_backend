<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\orderFile;
use App\Models\User;
use App\Models\userAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UploadController extends Controller
{
    /**
     * 获取工单文件所有数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderIndex()
    {
        $data = orderFile::with('order')->get();
        return response()->json($data,200);
    }

    /**
     * 获取用户头像所有数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function avatarIndex()
    {
        $data = userAvatar::with('user')->get();
        return response()->json($data,200);
    }

    /**
     * 上传工单文件
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function orderUpload(Request $request)
    {
        // 最大 5MB
        $request->validate([
            'file' => ['required', 'max:5120'], // 5 * 1024 = 5120KB = 5MB
            'order_id' => ['required', 'integer'], // 工单类型ID
        ], [
            'file.max' => '上传文件不能超过5MB。',
        ]);

        try {
            $user = JWTAuth::parseToken()->authenticate(); // 获取用户数据
            $file = $request->file('file'); // 接收文件
            $filename = "$user->username" . '_' . time() .Str::random(3). '.' . $file->getClientOriginalExtension(); // 文件名称
            // 这将会把文件存储在storage/app/public/uploads目录下，可通过public/storage/order/<Uploaded Files>访问。
            $path = Storage::disk('public')->putFileAs('order', $file,$filename);

            $files = orderFile::create([
                'user_id'   =>  $user->id,
                'order_id'  =>  $request->input('order_id'),
                'file_name' =>  $filename,
                'file_url'  =>  Storage::disk('public')->url($path)
            ]);

            if (!$files) {
                throw new \Exception('创建失败');
            }

            return response()->json(['file_url' => Storage::disk('public')->url($path),'file_name'=>$filename], 200);
        }catch (\Throwable $e){
            return response(['message' => '上传失败！',$e->getMessage()], 400);
        }
    }

    /**
     * 上传用户头像
     *
     * @param Request $request
     * @param int $id 保存用户ID
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function userUploadAvatar(Request $request,int $id)
    {
        // 最大 5MB
        $request->validate([
            'avatar' => ['required', 'max:5120'], // 5 * 1024 = 5120KB = 5MB
        ], [
            'avatar.max' => '上传文件不能超过5MB。',
        ]);

        $username = User::find($id);

        try {
            $user = JWTAuth::parseToken()->authenticate(); // 获取用户数据
            $file = $request->file('avatar'); // 接收文件
            $filename = "$username->username" . '_' . time() .Str::random(3). '.' . $file->getClientOriginalExtension(); // 文件名称
            // 这将会把文件存储在storage/app/public/uploads目录下，可通过public/storage/avatar/<Uploaded Files>访问。
            $path = Storage::disk('public')->putFileAs('avatar', $file,$filename);

            $files = userAvatar::create([
                'user_id'   =>  $id,
                'avatar_name' =>  $filename,
                'avatar_url'  =>  Storage::disk('public')->url($path)
            ]);

            if (!$files) {
                throw new \Exception('创建失败');
            }

            $user->avator = Storage::disk('public')->url($path);
            $user->save();

            return ['avatar_url' => Storage::disk('public')->url($path),'avatar_name'=>$filename];

//            return response()->json(['avatar_url' => Storage::disk('public')->url($path),'avatar_name'=>$filename], 200);
        }catch (\Throwable $e){
            return response(['message' => '上传失败！',$e->getMessage()], 400);
        }
    }
}
