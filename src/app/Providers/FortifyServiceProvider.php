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
        // 既存のFortify設定の上に追記（createUsersUsing の前でもOK）
        RateLimiter::for('login', function (Request $request) {
            // 1分間に100回までに緩和（または Limit::none() で完全解除）
            return Limit::perMinute(100)->by(Str::lower($request->email) . $request->ip());
        });

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
            // ✅ 先頭で日本語を強制
            app()->setLocale('ja');

            // ✅ FormRequest のルールを取得
            $loginRequest = new \App\Http\Requests\LoginRequest();
            $rules = $loginRequest->rules();
            $messages = $loginRequest->messages();

            // ✅ Validator生成時に日本語メッセージを明示的に渡す
            $validator = \Illuminate\Support\Facades\Validator::make(
                $request->all(),
                $rules,
                $messages
            );

            // ✅ バリデーション失敗時にエラーをスロー
            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            // ✅ 認証処理
            $credentials = $validator->validated();
            $user = \App\Models\User::where('email', $credentials['email'])->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
                return $user;
            }
            return null;
        });

    }
}
