<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;


class UserController extends Controller
{
    /**
     * 创建一个受保护的全局变量
     * UploadController 实例对象
     *
     * @var \App\Http\Controllers\UploadController
     */
    protected $uploadController;

    /**
     * 构造函数
     *
     * @param \App\Http\Controllers\UploadController $authController
     *                      传入的 UploadController 实例对象。
     * @return void
     */
    public function __construct(UploadController $uploadController)
    {
        // 将传入的 UploadController 实例保存到 $uploadController 中
        $this->uploadController = $uploadController;
    }
    // 显示所有用户
    public function getAllUsers()
    {
        $user = User::all();
        if (!$user) {
            return response()->json('获取失败', 400);
        }
        return response()->json($user, 200);
    }
    // 显示所有工程师
    public function getAllEngineers()
    {
        $engineer = User::query()->where('role_id', 2)->get();
        if (!$engineer) {
            return response()->json('获取失败', 400);
        }
        return response()->json($engineer, 200);
    }
    /**
     * 显示所有role_id为1的数据
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // 接收要查询的数据类型
        $user = User::query()->with('groups')->withCount('groups')
            ->where('role_id', 1)->get();
        if (!$user) {
            return response()->json('获取失败', 400);
        }
        return response()->json($user, 200);
    }

    /**
     * 分页显示所有客户信息
     * 使用类似： '/CustomerRepresentative?pageSize=10' 方式调用
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(Request $request): JsonResponse
    {
        // 页面数据大小
        $page_size = $request->input('pageSize');
        // 接收要查询的数据类型
        // paginate表示显示多少条的数据
        $user = User::query()->with('groups')->withCount('groups')->where('role_id', 1)
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
    public function showFilter(Request $request): JsonResponse
    {
        try {
            // 页面数据大小
            $page_size = $request->input('pageSize');
            // 接收要查询的数据内容
            $searchValue = $request->input('searchValue');
            // 接收要查询的数据类型
            $searchType = $request->input('searchType');
            // 如果 $searchType 不在 $allowedFields 中，则默认为 'companyname'
            $allowedFields = ['companyname', 'username', 'phone'];
            $field = in_array($searchType, $allowedFields) ? $searchType : 'companyname';
            // 使用模糊查询获取数据
            $filteredData = User::query()
                ->where($field, 'like', "%$searchValue%")->where('role_id', 1)
                ->with('groups')->withCount('groups')->paginate($page_size);
            return response()->json($filteredData, 200);
        } catch (\Throwable $e) {
            return response()->json('获取失败' . $e->getMessage(), 400);
        }
    }

    /**
     * 根据用户ID获取用户信息
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::query()
                ->where('id', $id)
                ->where('role_id', 1)
                ->with('groups')->withCount('groups')->first();
            if (!$user) {
                return response()->json('获取失败，该用户不存在', 400);
            }
            return response()->json($user, 200);
        } catch (\Throwable $e) {
            return response()->json($e, 400);
        }
    }

    /**
     * 删除客户,需要管理员权限
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json('用户不存在', 400);
        }
        $user->delete();

        if ($user != 1 && $user->isAdmin()) {
            return response()->json('删除失败', 400);
        }

        return response()->json('删除成功', 200);
    }

    /**
     * 新增客户信息
     * 通过客户管理页面新增的客户密码默认是asd123456
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'companyname'   => ['nullable', 'unique:users', 'max:255'],
            'username'      => ['required', 'max:255'],
            'address'       => ['nullable'],
            'remark'        => ['nullable'],
            'phone'         => ['nullable', 'integer', 'digits:11'],
            'group_name'    => ['nullable', 'array']
        ]);

        try {
            // 开始进行事务
            DB::beginTransaction();
            // 创建用户并加密密码,在客户管理页面新建的客户密码默认：asd123456
            $user = User::create([
                'companyname'   =>  $validatedData['companyname'] ?? "XX公司",
                'username'      =>  $validatedData['username'],
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
            logger()->error('创建用户数据保存时发生错误：' . $e->getMessage());
            return response()->json('创建失败', 403);
        }
    }

    /**
     * 修改客户信息
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'companyname'   => ['nullable', 'max:255'],
            'username'      => ['required', 'max:255'],
            'address'       => ['nullable'],
            'remark'        => ['nullable'],
            'phone'         => ['nullable', 'integer'],
            'group_name'    => ['nullable', 'array'],
        ]);
        try {
            // 开始进行事务
            DB::beginTransaction();
            // 获取用户信息并更新
            $user = User::findOrFail($id)->fill([
                'companyname'   =>  $validatedData['companyname'] ?? "",
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

    /**
     * 更新字段的公共方法
     *
     * @param array $validatedData
     * @param string $field
     * @param int $id
     * @return JsonResponse
     */
    public function updateField(array $validatedData, string $field, int $id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json('用户不存在', 404);
            }

            if ($field == "password") {
                $user->password = Hash::make($validatedData["password"], ['memory' => 1024, 'time' => 2, 'threads' => 2, 'argon2i']);
            } else {
                $user->$field = $validatedData["$field"];
            }

            // 保存修改
            $user->save();
            return $user;
        } catch (\Throwable $e) {
            return response()->json('修改失败' . $e->getMessage(), 500);
        }
    }

    /**
     * 绑定邮箱
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function updateEmail(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $data = $this->updateField($validatedData, 'email', $id);

        return response()->json(['message' => '修改成功', 'email' => $data->email], 200);
    }

    /**
     * 绑定手机号
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function updatePhone(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'phone' => ['required', 'regex:/^[1][3-9][0-9]{9}$/', 'unique:users,phone'],
        ]);

        $data = $this->updateField($validatedData, 'phone', $id);

        return response()->json(['message' => '修改成功', 'phone' => $data->phone], 200);
    }

    /**
     * 绑定用户名
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function updateUsername(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'username' => ['required', 'unique:users,username'],
        ]);

        $data = $this->updateField($validatedData, 'username', $id);

        return response()->json(['message' => '修改成功', 'username' => $data->username], 200);
    }

    /**
     * 绑定头像
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function updateAvatar(Request $request, int $id): JsonResponse
    {
        // 执行上传控制器中上传用户头像的方法
        $data = $this->uploadController->userUploadAvatar($request, $id);

        return response()->json($data, 200);
    }

    public function updatePassword(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $data = $this->updateField($validatedData, 'password', $id);

        return response()->json(['message' => '修改成功', 'password' => $data], 200);
    }

    /**
     * 禁用和启用客户
     *
     * @return void
     */
    public function lock()
    {
    }
}
