<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrder;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        dd(Order::all()->toArray());
    }

    public function create(Request $request)
    {
        if ($request->input('user_name'))
            $order = Order::create($request->only('user_name'));
        else
            $order = Order::first();
        SendOrder::dispatch($order);
        dd(get_called_class());
    }

    public function store(Request $request)
    {
        if ($request->input('user_name'))
            Order::create($request->only('user_name'));
        $order = Order::first();
        SendOrder::dispatch($order);
    }
}
