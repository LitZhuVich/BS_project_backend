<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $user = User::query()->where('id',$id)->with('groups')->withCount('groups')->first();
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
     * 新增客户信息
     * 通过客户管理页面新增的客户密码默认是asd123456
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'companyname'   => ['required','unique:users','max:255'],
            'username'      => ['required', 'max:255'],
            'address'       => ['nullable'],
            'remark'        => ['nullable'],
            'phone'         => ['nullable', 'integer', 'digits:11'],
            'group_name'    => ['nullable', 'array']
        ]);

        try {
//            $operator = JWTAuth::parseToken()->authenticate();

            // 开始进行事务
            DB::beginTransaction();
            // 创建用户并加密密码,在客户管理页面新建的客户密码默认：asd123456
            $user = User::create([
                'companyname'   =>  $validatedData['companyname'],
                'username'      =>  $validatedData['username'] ?? "1232131231321sda",
                'password'      =>  Hash::make('asd123456', ['memory' => 1024, 'time' => 2, 'threads' => 2, 'argon2i']),
                'address'       =>  $validatedData['address'] ?? "",
                'remark'        =>  $validatedData['remark'] ?? "",
                'phone'         =>  $validatedData['phone'] ?? "",
            ]);

            if (!$user) {
                throw new \Exception('创建失败');
            }
            // 提交事务，如果事务已成功执行，则将更改提交到数据库。
            DB::commit();
            return response()->json('创建成功', 200);
        } catch (\Throwable $e) {
            // 回滚刚才的数据库操作
            DB::rollBack();
            logger()->error('创建用户数据保存时发生错误：'.$e->getMessage());
            return response()->json('创建失败', 403);
        }
    }

    /**
     * 修改客户信息
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'companyname'   => ['required', 'max:255'],
            'username'      => ['required', 'max:255'],
            'address'       => ['nullable'],
            'remark'        => ['nullable'],
            'phone'         => ['nullable', 'integer', 'digits:11'],
            'group_name'    => ['nullable', 'array']
        ]);

        try {
            // 开始进行事务
            DB::beginTransaction();
            // 获取用户信息并更新
            $user = User::findOrFail($id)->fill([
                'companyname'   =>  $validatedData['companyname'],
                'username'      =>  $validatedData['username'],
                'address'       =>  $validatedData['address'] ?? "",
                'remark'        =>  $validatedData['remark'] ?? "",
                'phone'         =>  $validatedData['phone'] ?? "",
            ]);
            // 保存刷新
            $user->saveOrFail();

            // 更新用户组
            $groupNames = $validatedData['group_name'] ?? [];
            $groups = Group::whereIn('group_name', $groupNames)->get(['id', 'group_name']);
            $groupIds = $groups->pluck('id')->toArray();

            $user->groups()->sync($groupIds);
            // 提交事务，如果事务已成功执行，则将更改提交到数据库。
            DB::commit();
            return response()->json('更新成功', 200);
        } catch (\Throwable $e) {
            // 回滚刚才的数据库操作
            DB::rollBack();
            return response()->json('更新失败：' . $e->getMessage(), 500);
        }
    }
}
