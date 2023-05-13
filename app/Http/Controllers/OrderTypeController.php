<?php

namespace App\Http\Controllers;

use App\Models\OrderType;
use Illuminate\Http\Request;

class OrderTypeController extends Controller
{
    // 获取全部工单类型
    public function index(){
        return response()->json(OrderType::all(),200);
    }
}
