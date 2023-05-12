<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
use App\Models\OrderType;

class OrderTypeController extends Controller
{
    // 获取全部工单类型
    public function index()
    {
        return OrderType::get();
=======
use App\Models\OrderType;
use Illuminate\Http\Request;

class OrderTypeController extends Controller
{
    public function index(){
        return response()->json(OrderType::all(),200);
>>>>>>> 9549e201c85eadb4446ff8d40e2c90111eeef21f
    }
}
