<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\LoginController;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\CorrectionApprovalController;
// use App\Http\Controllers\CorrectionRequestListController;
use App\Http\Controllers\Admin\CorrectionRequestListController;

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

    // // 🔹 認証メール再送信
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

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 勤怠詳細（閲覧＋修正申請）
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    // 修正申請送信 POST
    // Route::post('/attendance/detail/{id}', [CorrectionRequestController::class, 'update'])
    // ->name('attendance.update');

    //  勤怠詳細から修正申請する（PUT）
    Route::put('/attendance/update/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

        // 申請一覧画面（一般ユーザー）
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'list'])
        ->name('stamp_correction_request.list');

    // 勤務登録フォーム
    Route::get('/attendance/create', [AttendanceController::class, 'create'])
        ->name('attendance.create');
    // 勤務登録処理
    Route::post('/attendance/store', [AttendanceController::class, 'store'])
        ->name('attendance.store');

});
/*
|--------------------------------------------------------------------------
| ログアウト（Fortify標準ルートを上書き）
|--------------------------------------------------------------------------
*/
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/admin/login'); // ✅ ログアウト後はログイン画面へ
})->middleware('web')->name('admin.logout');
/*
|--------------------------------------------------------------------------
| 管理者（adminガード）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // 管理ログイン
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});
// 🔒 管理者認証後のページ
Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {

    // 勤怠一覧・詳細
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');
    // 管理側勤怠詳細（読み取り専用 or 修正可）
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.detail');
    // 編集画面
    Route::get('/attendance/{id}/edit', [AdminAttendanceController::class, 'edit'])->name('attendance.edit');

    // Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    // 修正申請（管理者が一般側と同じ申請を出す）
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');


    // 🔹 申請一覧（管理者版）
    Route::get('/stamp_correction_request/list', [CorrectionRequestListController::class, 'adminList'])
        ->name('stamp_correction_request.list');
    // 🔹 承認機能
    Route::get('/stamp_correction_request/approve/{id}', [CorrectionApprovalController::class, 'show'])
        ->name('correction_request.show');

    Route::post('/stamp_correction_request/approve/{id}', [CorrectionApprovalController::class, 'approve'])
        ->name('correction_request.approve');
    // スタッフ管理
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');
    // --- スタッフ別勤怠一覧 ---
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffList'])
        ->name('attendance.staff_list');

    Route::get('/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportStaff'])
        ->name('attendance.staff.export');

    // 管理者ログアウト
    Route::post('/logout', function (Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    })->name('logout');
});
