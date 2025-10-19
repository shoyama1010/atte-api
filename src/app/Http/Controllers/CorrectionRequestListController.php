<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionRequest;

class CorrectionRequestListController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $requests = CorrectionRequest::where('user_id', $user->id)
            ->with(['attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.list', compact('requests'));
    }
}
