@extends('layouts.app')

@section('content')
    <div class="auth-container">
        <div class="auth-card">
            <h2>会員登録</h2>

            {{-- バリデーションエラー --}}
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <label for="name">氏名</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="氏名">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <label for="email">メールアドレス<span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="メールアドレス">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <label for="password">パスワード</label>
                <input type="password" name="password" placeholder="パスワード">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <label for="password_confirmation">パスワード（確認）</label>
                <input type="password" name="password_confirmation" placeholder="パスワード（確認）">
                @error('password_confirmation')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <button type="submit">登録する</button>
            </form>

            <div class="link-area">
                <p>すでにアカウントをお持ちの方は <a href="{{ route('login') }}">こちらからログイン</a></p>
            </div>
        </div>
    </div>
@endsection
