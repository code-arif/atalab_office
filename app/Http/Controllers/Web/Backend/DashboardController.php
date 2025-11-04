<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Team;
use App\Models\User;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     */
    public function index()
    {
        $user = auth()->user();

        // User Statistics
        $totalUsers = User::where('role', 'employee')->count();

        return view('backend.layouts.dashboard', compact(
            'totalUsers',
        ));
    }
}
