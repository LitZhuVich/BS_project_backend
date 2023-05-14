<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * 获取全部订单数据
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Order::get();
    }

    /**
     * 创建工单
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $date_string = $request->appointment;
            $time_stamp = strtotime($date_string);
            $date_time = date("Y-m-d", $time_stamp);

            $data = [
                'priority_id' => $request->priority,
                'status_id' => $request->status,
                'type_id' => $request->orderType,
                'user_id' => $request->user,
                'phone' => $request->phone,
                'title' => $request->title,
                'time_limit' => $request->timeLimit,
                'description' => $request->description,
                // 'attachment' => $request->fileList,
                'isOnLine' => $request->isOnLine,
                'address' => $request->address,
                'appointment' => $date_time
            ];
            Order::create($data);
            // 返回结果
            return response()->json('成功', 200);
            // return $request->status . '成功';
        } catch (JWTException $e) {
            return '失败' . $e;
        }
    }

    /**
     * 获取全部订单数据
     */
    public function getOrder()
    {
        $data = Order::all();
        return response()->json($data, 200);
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
