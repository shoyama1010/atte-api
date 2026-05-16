<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CorrectionRequestController;
use App\Http\Controllers\Api\AdminCorrectionRequestController;
use App\Http\Controllers\Api\AdminAttendanceController;

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
Route::post('/correction-requests', [CorrectionRequestController::class, 'store']);

Route::get('/correction-requests/{id}', [CorrectionRequestController::class, 'show']);

Route::put('/attendances/{id}', [AttendanceController::class, 'updateApi']);


// ->put('/correction_requests/{id}/approve', [CorrectionRequestController::class, 'approve']);
Route::get('/admin/corrections/{id}', [AdminCorrectionRequestController::class, 'show']);
Route::post('/admin/corrections/{id}/approve', [AdminCorrectionRequestController::class, 'approve']);


Route::get('/attendances/user/{id}', [AttendanceController::class, 'listByUser']);
// 月別（休憩付き）
Route::get('/attendances/user/{id}/monthly', [AttendanceController::class, 'userMonthly']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'list']);
});