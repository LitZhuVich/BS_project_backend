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
            $group = Group::find($id);
            if (!$group){
                return response()->json('没有组',400);
            }
            return response()->json($group,200);
        }catch (JWTException $e){
            return response()->json($e,200);
        }
        //
    }
    // TODO:需修改
    public function showMany(Request $request){
        try {
            $user_id = $request->ids;
            $user = User::query()->where('id',$user_id)->get();
//            $group = Group::query()->whereIn('id',$group_id)->get();
            return $user;
            return response()->json($group,200);
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
