<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestListController extends Controller
{
    public function __construct()
    {
        // ✅ 管理者のみアクセス許可
        $this->middleware('auth:admin');
    }
    /**
     * ✅ 管理者の申請一覧（全ユーザー分を取得）
     */
    public function adminList(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $status = $request->input('status', 'pending');

        $requests = CorrectionRequest::with(['attendance.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        // 管理者用ビュー
        // return view('stamp_correction_request.list', compact('requests'));
        return view('admin.stamp_correction_request.list', compact('requests'));
    }
}
