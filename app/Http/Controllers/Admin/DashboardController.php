<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard.index', [
            'counts' => [
                'draft' => 0,
                'scheduled' => 0,
                'published' => 0,
            ],
            'recentlyPublished' => collect(),
            'upcomingScheduled' => collect(),
            'unreadMessages' => 0,
        ]);
    }
}
