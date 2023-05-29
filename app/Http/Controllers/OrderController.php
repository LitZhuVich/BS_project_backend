<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    /**
     * 已登录授权用户
     *
     * @var $authUser
     */
    protected $authUser;
    protected GroupController $groupController;

    /**
     * 构造函数
     * 为 authUser 全局变量赋值
     */
    public function __construct(){
        $this->authUser = JWTAuth::parseToken()->authenticate();
        $this->groupController = new GroupController();
    }

    /**
     * 获取全部订单数据
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Order::all();
        return response()->json($data, 200);
    }

    /**
     * 分页显示所有订单信息
     * 使用类似： '/Order?pageSize=10' 方式调用
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(Request $request)
    {
        // 页面数据大小
        $page_size = $request->input('pageSize');
        // 接收要查询的数据类型
        // paginate表示显示多少条的数据
        $order = Order::query()->paginate($page_size);
        if (!$order) {
            return response()->json('获取失败', 400);
        }
        return response()->json($order, 200);
    }

    /**
     * 创建工单
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $date_string = $request->appointment;
            $time_stamp = strtotime($date_string);
            $date_time = date("Y-m-d", $time_stamp);

            $data = [
                'priority_id' => $request->priority,
                'status_id' => $request->status,
                'type_id' => $request->orderType,
                'user_id' => $user->id,
                'phone' => $request->phone,
                'title' => $request->title,
                'time_limit' => $request->timeLimit,
                'description' => $request->description,
                'isOnLine' => $request->isOnLine,
                'address' => $request->address,
                'appointment' => $date_time,
            ];

            $order = Order::create($data);
            // 返回结果
            return response()->json($order, 200);

        } catch (\Throwable $e) {
            return response()->json('失败'.$e->getMessage(),400);
        }
    }

    /**
     * 显示指定用户工程师或管理员处理的工单
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showUser(int $id): JsonResponse
    {
        $data = Order::query()->where('user_id',$id)->get();
        return response()->json($data,200);
    }

    /**
     * 显示单个工单
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = Order::query()->where('id',$id)->get();
        return response()->json($data,200);
    }

    /**
     * 显示不同状态下的工单信息
     * ID为：
     *  1. 待处理
     *  2. 已受理
     *  3. 处理中
     *  4. 已完成
     *  5. 已完结
     *
     * @param int $status_id
     * @return JsonResponse
     */
    public function showStatus(int $status_id): JsonResponse
    {
        $data = Order::query()->where('status_id',$status_id)->get();
        return response()->json($data,200);
    }

    /**
     * 显示我的在不同状态下的工单
     *
     * @param int $status_id
     * @return JsonResponse
     */
    public function showMyStatus(int $status_id): JsonResponse
    {
        $data = Order::query()->where('status_id',$status_id)
            ->where('user_id',$this->authUser->id)->get();
        return response()->json($data,200);
    }

    /**
     * 显示我的在不同优先级下的工单
     *
     * @param int $priority_id
     * @return JsonResponse
     */
    public function showMyPriority(int $priority_id): JsonResponse
    {
        $data = Order::query()->where('priority_id',$priority_id)
            ->where('user_id',$this->authUser->id)->get();
        return response()->json($data,200);
    }

    /**
     * 显示不同优先级下的工单信息
     * ID为：
     *  1. 紧急
     *  2. 一般
     *
     * @param int $priority_id
     * @return JsonResponse
     */
    public function showPriority(int $priority_id): JsonResponse
    {
        $data = Order::query()->where('priority_id',$priority_id)->get();
        return response()->json($data,200);
    }

    /**
     * 显示十天内每天的工单与数量
     *
     * @param int $status_id
     *          状态id
     * @return JsonResponse
     */
    public function showWithinTenOrder(int $status_id): JsonResponse
    {
        // 获取当前日期
        $today = Carbon::today();
        // 获取十天前的日期
        $oneWeekAgo = Carbon::today()->subDays(9);

        // 计算过去十天每一天的日期
        for ($i = 0; $i < 10; $i++) {
            $dateArray[] = $oneWeekAgo->copy()->addDays($i)->format('Y-m-d');
        }

        // 构建查询语句
        $query = Order::query()
            ->select(DB::raw("DATE(created_at) AS created_day, COUNT(*) as count"))
            ->where('created_at', '>=', $oneWeekAgo)
            ->where('created_at', '<=', $today->endOfDay())
            ->where('status_id', $status_id)
            ->where('user_id', $this->authUser->id)
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get();

        // 将数据格式化成一个二维数组
        $data = [];
        foreach ($query as $item) {
            $data[$item->created_day] = $item->count;
        }

        // 填充日期缺失的数据并保证数据顺序正确
        $formattedData = [];
        foreach ($dateArray as $date) {
            $formattedData[] = $data[$date] ?? 0;
        }

        return response()->json([
            'created_day' => $data,
            'count' => $formattedData,
        ], 200);
    }

    /**
     * 获取一星期内工单与总数
     *
     * @param int $status_id
     *          状态id
     * @return JsonResponse
     */
    public function showWeekOrder(int $status_id): JsonResponse
    {
        // 获取当前日期
        $today = Carbon::today();
        // 获取一周前的日期
        $oneWeekAgo = Carbon::today()->subDays(6);
        // 获取今天是星期几
        $todayNumber = Carbon::now()->dayOfWeek;
        $weekdayName = $todayNumber == 0 ? '星期7' : "星期{$todayNumber}";
        // 查询一周内每天的订单数据
        $data = [];
        for ($date = $oneWeekAgo; $date->lte($today); $date->addDay()) {
            // 获取 数组
            $count = Order::whereDate('created_at', $date)
                ->where('status_id', $status_id)
                ->where('user_id', $this->authUser->id)
                ->count();
            // 将总数输入到数组中
           $data[] = $count;
        }

        return response()->json([
            'dateDay' => $data,
            'today' => "今天是".$weekdayName
        ],200);
    }

    /**
     * 获取所有工单与总数
     *
     * @return JsonResponse
     */
    public function showAllOrder(): JsonResponse
    {
        // 通过 collect 函数生成一个包含数字 1 到 6 的集合，并对其进行 map 操作，获取每个状态下的订单数据，并将其存储到 $data 集合中
        $data = collect(range(1, 6))->map(function ($statusId) {
            return Order::query()
                ->where('status_id', $statusId)
                ->where('user_id', $this->authUser->id)
                ->get();
        });

        // 获取每个工单状态的总数
        $count = $data->map(function ($orders) {
            return $orders->count();
        });

        return response()->json([
            'data' => $data,
            'count' => $count
        ], 200);
    }

    /**
     * 显示自己所在组的总工单
     *
     * @return Builder[]|Collection|JsonResponse
     */
    public function showGroupOrder()
    {
        $groupIds = GroupUser::query()->where('user_id', $this->authUser->id)
            ->pluck('group_id')->toArray();
        if (empty($groupIds)) {
            return response()->json('用户不存在', 404);
        }

        return Order::query()
            ->where('status_id',4)
            ->whereIn('user_id', function ($query) use ($groupIds) {
                return $query->select('user_id')
                    ->from('group_users')
                    ->whereIn('group_id', $groupIds);
            })
            ->get();
    }
}
