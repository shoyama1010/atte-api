@extends('layouts.app')

@section('content')
    <div class="auth-container">
        <div class="auth-card">
            <h2 class="h2-page-title">ログイン</h2>

            {{-- 全体エラーメッセージ（例：認証失敗など） --}}
            @if (session('status'))
                <div class="error-message">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- メールアドレス --}}
                <label for="email">メールアドレス</label>
                {{-- <input type="email" name="email" value="{{ old('email') }}" placeholder="メールアドレス"> --}}
                <input type="text" name="email" value="{{ old('email') }}" placeholder="メールアドレス">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                {{-- パスワード --}}
                <label for="password">パスワード</label>
                <input type="password" name="password" placeholder="パスワード">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-submit">ログインする</button>
            </form>

            <div class="link-area">
                <p>アカウントをお持ちでない方は
                    <a href="{{ route('register') }}">こちらから登録</a>
                </p>
            </div>
        </div>
    </div>
@endsection
