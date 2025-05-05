<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Inbounds;

class DashboardController extends Controller
{
    public function index()
    {
        $inbounds = auth()->user()->inbounds()->with('metadata')->get();

        return Inertia::render('Dashboard', [
            'inbounds' => $inbounds
        ]);
    }
}
