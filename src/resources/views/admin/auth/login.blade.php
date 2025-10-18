@extends('layouts.app')

@section('content')
    <div class="auth-container">
        <h2>管理者ログイン</h2>

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <label for="email">メールアドレス</label>
            <input type="email" name="email" required autofocus>

            <label for="password">パスワード</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-primary">ログイン</button>

            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </form>
    </div>
@endsection
