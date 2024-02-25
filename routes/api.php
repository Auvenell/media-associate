<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboundsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| TODO: Authentication
|
*/
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Inbounds Routes
/* POST to store links & sources */
Route::post('/inbounds', [InboundsController::class, 'receiveInbounds']);

Route::get('/inbounds', [InboundsController::class, 'showAllInbounds']);
Route::get('/inbounds/{id}', [InboundsController::class, 'showInbound']);

Route::put('/inbounds/{id}', [InboundsController::class, 'updateInbound']);
Route::delete('/inbounds/{id}', [InboundsController::class, 'removeInbound']);
