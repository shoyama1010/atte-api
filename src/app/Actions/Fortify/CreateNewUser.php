<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // FormRequest のバリデーションを実行
        app(RegisterRequest::class)->validateResolved();

        // ユーザー登録
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),

            // Railway本番環境ではメール認証済みとして登録
            // ローカル環境では null のままにして MailHog で認証
            'email_verified_at' => app()->environment('production')
                ? now()
                : null,
        ]);

        // 登録イベントを発火
        // ローカルではメール認証通知に利用
        event(new Registered($user));

        // 登録直後の自動ログインを解除
        // 登録後はログイン画面からログインさせる
        Auth::logout();

        return $user;
    }
}
