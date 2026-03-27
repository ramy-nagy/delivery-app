<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.dashboard.index');
    }
}

