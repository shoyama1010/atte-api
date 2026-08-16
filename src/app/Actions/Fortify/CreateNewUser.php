<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
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
        // FormRequestによるバリデーション
        app(RegisterRequest::class)->validateResolved();

        // ユーザー登録
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Railway本番環境ではメール認証済みにする
        if (app()->environment('production')) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        return $user;
    }
}
