<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CorrectionRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::get('/test', function () {
    return response()->json(['message' => 'API connected successfully!']);
});

Route::get('/attendances', [AttendanceController::class, 'index']);

Route::get('/attendances/{id}', [AttendanceController::class, 'show']);
Route::get('/correction-requests', [CorrectionRequestController::class, 'index']);
Route::get('/correction-requests/{id}', [CorrectionRequestController::class, 'show']);

Route::put('/attendances/{id}', [AttendanceController::class, 'updateApi']);

// Route::put('/attendances/{id}', [AttendanceController::class, '????']);
