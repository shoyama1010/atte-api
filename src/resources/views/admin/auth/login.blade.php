@extends('layouts.app')

@section('content')
    <div class="auth-container">
        <div class="auth-card admin-login-card">
            <h2>管理者ログイン</h2>

            {{-- エラーメッセージ --}}
            @if ($errors->any())
                <div class="error-message">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <label for="password">パスワード</label>
                <input type="password" id="password" name="password">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-admin-login">管理者ログインする</button>
            </form>
        </div>
    </div>
@endsection
