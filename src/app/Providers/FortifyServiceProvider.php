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
use Illuminate\Support\Facades\Auth;

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
        Fortify::createUsersUsing(CreateNewUser::class);

        // 🔹 会員登録画面を表示
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // 🔹 ログイン画面を表示
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 🔹 ログイン認証処理
        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if (
                $user &&
                \Hash::check($request->password, $user->password)
            ) {
                return $user;
            }

            return null;
        });

        // 🔹 ログイン後のリダイレクト処理
        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();
            
            if ($user && \Hash::check($request->password, $user->password)) {
                // 管理者 or 一般ユーザーで遷移先を切り替え
                if ($user->role === 'admin') {
                    session(['redirect_after_login' => 'admin.dashboard']);
                } else {
                    session(['redirect_after_login' => 'attendance.index']);
                }
                return $user;
            }
            return null;
        });
    }
}
