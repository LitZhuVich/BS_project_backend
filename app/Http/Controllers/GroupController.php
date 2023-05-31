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
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id):JsonResponse
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
                ->where('group_role',1)->get();
            if (!$group){
                return response()->json('没有组',400);
            }
            return response()->json($group,200);
        }catch (JWTException $e){
            return response()->json($e,200);
        }
    }

    public function engineerPaginate(Request $request): JsonResponse
    {
        // 页面数据大小
        $page_size = $request->input('pageSize');
        // 接收要查询的数据类型
        // paginate表示显示多少条的数据
        $user = Group::with('users')->withCount('users')
            ->where('group_role',1)
            ->paginate($page_size);
        if (!$user) {
            return response()->json('获取失败', 400);
        }
        return response()->json($user, 200);
    }

    /**
     * 查询搜索框筛选之后的数据
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showEngineerFilter(Request $request):JsonResponse
    {
        try {
            // 页面数据大小
            $page_size = $request->input('pageSize');
            // 接收要查询的数据内容
            $searchValue = $request->input('searchValue');
            // 使用模糊查询获取数据
            $filteredData = Group::query()
                ->where('group_name', 'like', "%$searchValue%")->where('group_role', 1)
                ->with('users')->withCount('users')->paginate($page_size);
            return response()->json($filteredData, 200);
        } catch (\Throwable $e) {
            return response()->json('获取失败' . $e->getMessage(), 400);
        }
    }

    /**
     * 删除组
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Group::find($id);
        if (!$user) {
            return response()->json('组不存在', 400);
        }
        $user->delete();

        if ($user != 1) {
            return response()->json('删除失败', 400);
        }

        return response()->json('删除成功', 200);
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
