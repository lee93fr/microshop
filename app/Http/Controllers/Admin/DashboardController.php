<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'orders_today'  => Order::whereDate('created_at', today())->count(),
            'pending'       => Order::where('status', 'pending')->count(),
            'unpaid'        => Order::where('payment_status', 'unpaid')->count(),
            'revenue_month' => Order::where('payment_status', 'paid')
                                    ->whereMonth('created_at', now()->month)
                                    ->sum('total'),
        ];

        $latestOrders = Order::with('user')->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'latestOrders'));
    }
}
