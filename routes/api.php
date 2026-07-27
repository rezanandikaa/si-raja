<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AjaxController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/backend-ajax/chart-by-type/', [AjaxController::class, 'chartByType'])->name('web.ajax.chart_by_type');
Route::post('/backend-ajax/chart/', [AjaxController::class, 'chart'])->name('web.ajax.chart');
