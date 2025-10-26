<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;

class CorrectionRequestListController extends Controller
{
    // public function index()
    // {
    //     $user = Auth::user();
    //     $requests = CorrectionRequest::where('user_id', $user->id)
    //         ->with(['attendance'])
    //         ->orderBy('created_at', 'desc')
    //         ->get();
    //     return view('stamp_correction_request.list', compact('requests'));
    // }

    /**
     * 勤務修正申請一覧の表示
     */
    public function list()
    {
        $user = Auth::user();

        // ログインユーザー自身の申請履歴を新しい順に取得
        $requests = CorrectionRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.list', compact('user', 'requests'));
    }

    /**
     * 勤務修正申請フォームの編集画面
     */
    public function edit($attendanceId)
    {
        $attendance = \App\Models\Attendance::findOrFail($attendanceId);
        $user = Auth::user();

        return view('attendance.detail', compact('attendance', 'user'));
    }

    /**
     * 勤務修正申請の更新処理
     */
    public function update(Request $request, $attendanceId)
    {
        $request->validate([
            'request_type' => 'required|string',
            'reason' => 'required|string|max:255',
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendanceId,
            'user_id' => Auth::id(),
            'request_type' => $request->request_type,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '修正申請を送信しました。');
    }
}
