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
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                // 管理者の場合
                if ($user->role === 'admin') {
                    // Fortifyのreturnは「$user」でOK（リダイレクトは別設定で制御）
                    session(['redirect_after_login' => '/admin/attendance/list']);
                    return $user;
                }

                // 一般ユーザーの場合
                session(['redirect_after_login' => '/attendance']);
                return $user;
            }

            return null;
        });
    }
}
