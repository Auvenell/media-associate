<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $inbounds = auth()->user()->inbounds;

        return Inertia::render('Dashboard', [
            'inbounds' => $inbounds
        ]);
    }
}
