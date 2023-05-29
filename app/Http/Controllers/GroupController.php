<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

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
     * 根据ID显示对应组信息
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUser(){
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function engineerIndex()
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function showName(int $id){
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function showGroupName()
    {
        // 只显示 group_name
        $groups = Group::all()->map(function($group) {
            return $group->group_name;
        });

        return response()->json($groups,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
