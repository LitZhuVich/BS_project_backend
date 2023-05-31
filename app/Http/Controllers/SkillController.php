<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * 显示所有技能
     *
     * @return JsonResponse
     */
    public function index():JsonResponse
    {
        $data = Skill::with('users')->withCount('users')->get();
        return response()->json($data,200);
    }

    /**
     * 显示技能名称
     *
     * @return JsonResponse
     */
    public function showName(): JsonResponse
    {
        $data = Skill::query()->pluck('skill_name');
        return response()->json($data,200);
    }

    /**
     * 分页显示
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(Request $request):JsonResponse
    {
        // 页面数据大小
        $page_size = $request->input('pageSize');

        $data = Skill::with('users')->withCount('users')
            ->paginate($page_size);

        return response()->json($data,200);
    }

    /**
     * 查询搜索框筛选之后的数据
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showFilter(Request $request):JsonResponse
    {
        try {
            // 页面数据大小
            $page_size = $request->input('pageSize');
            // 接收要查询的数据内容
            $searchValue = $request->input('searchValue');
            // 使用模糊查询获取数据
            $filteredData = Skill::query()
                ->where('skill_name', 'like', "%$searchValue%")
                ->with('users')->withCount('users')->paginate($page_size);
            return response()->json($filteredData, 200);
        } catch (\Throwable $e) {
            return response()->json('获取失败' . $e->getMessage(), 400);
        }
    }

    /**
     * 删除技能
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id):JsonResponse
    {
        $data = Skill::find($id);
        if (!$data) {
            return response()->json('技能不存在', 400);
        }
        $data->delete();

        if ($data != 1) {
            return response()->json('删除失败', 400);
        }

        return response()->json('删除成功', 200);
    }

    /**
     * 显示技能
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = Skill::query()->with('users')->withCount('users')
            ->where('id',$id)->get();

        return response()->json($data,200);
    }
}
