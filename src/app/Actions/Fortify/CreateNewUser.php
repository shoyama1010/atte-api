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
        // // ✅ RegisterRequest を使ってバリデーションを実行
        // $request = app(RegisterRequest::class);
        // $validated = $request->validate($input, $request->rules(), $request->messages());

        // // ✅ 登録ユーザーを作成
        // $user = User::create([
        //     'name' => $validated['name'],
        //     'email' => $validated['email'],
        //     'password' => Hash::make($validated['password']),
        // ]);

        // // ✅ イベントを発火してメール認証を有効化
        // event(new Registered($user));

        // // ✅ Fortify が自動ログインするのを防ぐ
        // Auth::logout();

        // return $user;
        // ✅ Validator::make → validate()
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            // ✅ カスタムメッセージ
            'name.required' => 'お名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードと一致しません。',
        ])->validate();

        // ✅ DB登録
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // ✅ イベント発火（メール認証メール送信）
        // event(new Registered($user));

        // ✅ Fortify自動ログイン防止
        Auth::logout();

        return $user;
    }
}
