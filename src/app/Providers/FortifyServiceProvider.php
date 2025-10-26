<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🔹 新規登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // 🔹 会員登録画面を表示
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // 🔹 ログイン画面を表示
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 🔹 メール認証処理
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        // 🔹 ログイン後のリダイレクト処理
        Fortify::authenticateUsing(function (Request $request) {
            // ✅ バリデーションをここで手動実行
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ], [
                'email.required' => 'メールアドレスを入力してください',
                'email.email' => '有効なメールアドレスを入力してください',
                'password.required' => 'パスワードを入力してください',
                'password.min' => 'パスワードは8文字以上で入力してください',
            ]);

            // エラーがあれば throw
            $validator->validate();

            // ✅ 入力データ取得
            $credentials = $validator->validated();

            // ✅ ユーザー検索
            $user = \App\Models\User::where('email', $credentials['email'])->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                // 管理者
                if ($user->role === 'admin') {
                    session(['redirect_after_login' => '/admin/attendance/list']);
                    return $user;
                }

                // 一般ユーザー
                session(['redirect_after_login' => '/attendance']);
                return $user;
            }

            return null;
        });
    }
}
