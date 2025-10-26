@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card admin-login-card">
        <h2>管理者ログイン</h2>

        {{-- エラーメッセージ --}}
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-admin-login">管理者ログインする</button>
        </form>
    </div>
</div>
@endsection

