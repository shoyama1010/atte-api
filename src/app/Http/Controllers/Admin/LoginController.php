<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(AdminLoginRequest $request)
    {
        // ✅ validated() によりバリデーション済み
        $credentials = $request->validated();

        // 管理者認証
        if (Auth::guard('admin')->attempt($credentials)) {
            // ✅ セッション再生成
            $request->session()->regenerate();
            // ✅ ログイン成功時：勤怠一覧にリダイレクト
            return redirect()->intended('/admin/attendance/list');
        }

        // ログイン失敗時
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }

    public function logout(AdminLoginRequest $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
