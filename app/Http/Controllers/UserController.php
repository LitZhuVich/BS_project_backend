<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\AuthController;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * 创建一个受保护的全局变量
     * AuthController 实例对象
     *
     * @var \App\Http\Controllers\AuthController
     */
    protected $authController;

    /**
     * 构造函数
     *
     * @param \App\Http\Controllers\AuthController $authController
     *                      传入的 AuthController 实例对象。
     * @return void
     */
    public function __construct(AuthController $authController){
        // 将传入的 AuthController 实例保存到 $authController 中
        $this->authController = $authController;
    }
    /**
     * 显示所有客户信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $user = User::with('groups')->withCount('groups')->where('role_id',1)->get();
        if (!$user){
            return response()->json('获取失败',400);
        }
        return response()->json($user,200);

//          TODO:
//         这是根据 组 显示 客户
//        $group = Group::with('users')->withCount('users')->get();
//
//        return $group->map(function ($group){
//            return [
//                'group'=>$group->group_name,
//                'user_count'=>$group->users_count,
//                'users'=>$group->users->toArray(),
//            ];
//        });
    }

    /**
     * 查询搜索框筛选之后的数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMany(Request $request)
    {
        try {
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
        }catch (JWTException $e){
            return response()->json('获取失败'.$e,400);
        }
    }

    /**
     * 根据用户ID获取用户信息
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id){
        try {
            $user = User::query()->where('id',$id)->first();
            if (!$user){
                return response()->json('获取失败，该用户不存在',400);
            }
            return response()->json($user,200);
        }catch (JWTException $e){
            return response()->json($e,400);
        }
    }

    /**
     * 删除客户,需要管理员权限
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
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
            'ids' => ['required','array'],
        ]);
        // 删除用户
        $user = User::whereIn('id', $validatedData['ids'])->delete();
        if ($user != 1 && $user->isAdmin()){
            return response()->json('删除失败',400);
        }
        return response()->json('成功批量删除',200);
    }

    /**
     * 新增客户信息
     * 通过客户管理页面新增的客户密码默认是123456
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'companyname'   => ['required','unique:users','max:255'],
            'username'      => ['required', 'max:255'],
            'address'       => 'nullable',
            'remark'        => 'nullable',
            'phone'         => ['nullable', 'integer', 'digits:11'],
            'group_name'    => ['nullable', 'array']
        ]);
        // 在客户管理页面新建的客户密码默认：asd123456
        $user = User::create([
            'companyname'   =>  $validatedData['companyname'],
            'username'      =>  $validatedData['username'],
            'password'      =>  Hash::make('asd123456', ['memory' => 1024, 'time' => 2, 'threads' => 2, 'argon2i']),
            'address'       =>  $validatedData['address'] ?? "",
            'remark'        =>  $validatedData['remark'] ?? "",
            'phone'         =>  $validatedData['phone'] ?? "",
        ]);
        if (!$user){
            return response()->json('创建失败',500);
        }

        return response()->json('创建成功',200);
    }

    /**
     * 修改客户信息，需要管理员权限
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'companyname'   => ['required', 'max:255'],
            'username'      => ['required', 'max:255'],
            'address'       => 'nullable',
            'remark'        => 'nullable',
            'phone'         => ['nullable', 'integer', 'digits:11'],
            'group_name'    => ['nullable', 'array']
        ]);

        $userInfo = User::where('id',$id)->first();
//         获取组
        $group_name = $validatedData['group_name'] ?? [];
        Group::query()->whereIn('group_name',$group_name)->get()->map(function ($group) use ($userInfo){
            GroupUser::create([
                'group_id'  =>  $group->id,
                'user_id'   =>  $userInfo->id
            ]);
        });

        $user = $userInfo->update([
            'companyname'   =>  $validatedData['companyname'],
            'username'      =>  $validatedData['username'],
            'address'       =>  $validatedData['address'] ?? "",
            'remark'        =>  $validatedData['remark'] ?? "",
            'phone'         =>  $validatedData['phone'] ?? "",
        ]);

        if (!$user){
            return response()->json('创建失败',500);
        }

        return response()->json('创建成功',200);
    }
}
