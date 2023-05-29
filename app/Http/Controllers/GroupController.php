<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\JWTException;

class GroupController extends Controller
{

    /**
     * 根据ID显示对应组信息
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id):JsonResponse
    {
        try {
            $group = Group::where('id',$id)->with('users')->withCount('users')->first();
            if (!$group){
                return response()->json('没有组',400);
            }
            return response()->json($group,200);
        }catch (JWTException $e){
            return response()->json($e,200);
        }
    }

    /**
     * 获取所有组信息
     *
     * @return JsonResponse
     */
    public function showUser():JsonResponse
    {
        $group = Group::with('users')->withCount('users')->get();

        if (!$group){
            return response()->json('获取失败',400);
        }

        return response()->json($group->map(function ($group){
            return [
                'group'=>$group->group_name,
                'user_count'=>$group->users_count,
                'users'=>$group->users->toArray(),
            ];
        }));
    }

    /**
     * 显示工程师组信息
     *
     * @return JsonResponse
     */
    public function engineerIndex():JsonResponse
    {
        try {
            $group = Group::with('users')->withCount('users')
                ->where('group_role',1)->first();
            if (!$group){
                return response()->json('没有组',400);
            }
            return response()->json($group,200);
        }catch (JWTException $e){
            return response()->json($e,200);
        }
    }

    /**
     * 获取所有
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showName(int $id):JsonResponse
    {
        try {
            $user = User::query()->where('id',$id)->with('groups')->first();
            if (!$user){
                return response()->json('没有用户',400);
            }
            // 只取组名
            $groups = $user->groups->map(function ($a){
                return $a->group_name;
            });

            return response()->json($groups,200);
        }catch (JWTException $e){
            return response()->json($e,200);
        }
    }

    /**
     * 只显示 组名
     *
     * @return JsonResponse
     */
    public function showGroupName():JsonResponse
    {
        // 只显示 group_name
        $groups = Group::all()->map(function($group) {
            return $group->group_name;
        });

        return response()->json($groups,200);
    }
}
