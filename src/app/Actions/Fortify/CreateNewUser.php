<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;

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
        // 🔹 FormRequestを利用したバリデーション
        $request = new RegisterRequest();
        $validated = app(RegisterRequest::class)->validateResolved();

        // ✅ ユーザー登録
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // ✅ イベント発火（メール認証メール送信）
        event(new Registered($user));

        // ✅ Fortify自動ログイン防止
        Auth::logout();

        return $user;
    }
}
