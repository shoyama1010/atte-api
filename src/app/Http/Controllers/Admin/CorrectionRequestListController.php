<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;

class CorrectionRequestListController extends Controller
{
    // public function list(Request $request)
    // {
    //     $status = request('status', 'pending');

    //     $requests = CorrectionRequest::with(['attendance.user'])
    //         ->where('user_id', auth()->id())
    //         ->where('status', $status)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('stamp_correction_request.list', compact('requests'));
    // }
    /**
     * ✅ 管理者の申請一覧（全ユーザー分を取得）
     */
    public function adminList(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = CorrectionRequest::with(['attendance.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        // 管理者用ビュー（resources/views/admin/stamp_correction_request/list.blade.php）
        return view('admin.stamp_correction_request.list', compact('requests'));
    }

}

