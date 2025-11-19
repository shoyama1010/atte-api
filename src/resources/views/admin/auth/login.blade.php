@extends('layouts.app')

@section('content')
    <div class="auth-container">
        <div class="auth-card admin-login-card">
            <h2>管理者ログイン</h2>

            <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
                @csrf

                <label for="email">メールアドレス</label>
                <input type="text" id="email" name="email" value="{{ old('email') }}" placeholder="メールアドレス">
                {{-- <input type="text" name="email" value="{{ old('email') }}"> --}}
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" placeholder="パスワード">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-admin-login">管理者ログインする</button>
            </form>
        </div>
    </div>
@endsection
