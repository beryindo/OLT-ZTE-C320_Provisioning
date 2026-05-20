<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOlts = Olt::count();
        $totalOnus = Onu::count();
        $recentOnus = Onu::with('olt')->latest()->take(5)->get();

        return view('dashboard', compact('totalOlts', 'totalOnus', 'recentOnus'));
    }
}
