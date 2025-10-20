<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;

/*
|--------------------------------------------------------------------------
| メール認証関連ルート
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // 🔹 確認待ち画面（verify-email.blade.php）
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // 🔹 認証メール再送信
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '認証メールを再送しました！');
    })->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| メール内リンククリック後の処理
|--------------------------------------------------------------------------
*/
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // ✅ メール認証済みにする
    return redirect()->route('attendance.index')
        ->with('success', 'メール認証が完了しました！'); // 完了後メッセージ表示
})->middleware(['auth', 'signed'])->name('verification.verify');

/*
|--------------------------------------------------------------------------
| 勤怠・打刻関連ルート（メール認証済ユーザーのみ）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    // 勤怠一覧・詳細
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    // 🔹 勤務修正申請（再設計版）
    Route::get('/attendance/request/{attendance}', [CorrectionRequestController::class, 'edit'])
        ->name('attendance.request.edit');  // ← 旧 create() → edit() に変更

        Route::post('/attendance/request/{attendance}', [CorrectionRequestController::class, 'update'])
        ->name('attendance.request.update'); // ← 旧 store() → update() に変更

    // 申請一覧画面（一般ユーザー）
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'list'])
        ->name('stamp_correction_request.list');

    // 勤務修正申請一覧
    // Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'list'])
    //     ->name('stamp_correction_request.list');
});

/*
|--------------------------------------------------------------------------
| ログアウト（Fortify標準ルートを上書き）
|--------------------------------------------------------------------------
*/
use Illuminate\Support\Facades\Auth;

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login'); // ✅ ログアウト後はログイン画面へ
})->middleware('web')->name('logout');


