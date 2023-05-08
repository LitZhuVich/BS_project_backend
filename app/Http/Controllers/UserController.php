<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * 显示所有客户信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCustomerRepresentative()
    {
        $user = User::with('groups')->withCount('groups')->where('role_id',1)->get();
        return response()->json($user,200);

        /*
         * 这是根据 组 显示 客户
         *
         * $group = Group::with('users')->withCount('users')->get();

        return $group->map(function ($group){
            return [
                'group'=>$group->group_name,
                'user_count'=>$group->users_count,
                'users'=>$group->users->toArray(),
            ];
        });*/
    }

    public function filterCustomerRepresentative(Request $request)
    {
        // 接收要查询的数据内容
        $searchValue = $request->input('searchValue');
        // 接收要查询的数据类型
        $searchType = $request->input('searchType');

        $allowedFields = ['companyname', 'username', 'phone'];
        // 如果 $searchType 不在 $allowedFields 中，则默认为 'companyname'
        $field = in_array($searchType, $allowedFields) ? $searchType : 'companyname';
        // 使用模糊查询获取数据
        $filteredData = User::where($field,'like',"%$searchValue%")->where('role_id',1)->with('groups')->withCount('groups')->get();
        return response()->json($filteredData,200);
    }

    /**
     * 删除客户,需要管理员权限
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id)->delete();
        if ($user != 1 && $user->isAdmin()){
            return response()->json('删除失败',400);
        }

        return response()->json('删除成功',200);
    }

    /**
     * 批量删除客户,需要管理员权限
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyMany(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'ids' => 'required|integer',
        ]);

        // 删除用户
        User::whereIn('id', $validatedData['ids'])->delete();

        return response()->json('成功批量删除',200);
    }
}
