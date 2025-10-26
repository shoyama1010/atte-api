@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>会員登録</h2>

        {{-- バリデーションエラー --}}
        @if ($errors->any())
            <div class="error-message">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input type="text" name="name" placeholder="氏名" value="{{ old('name') }}" required>
            <input type="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}" required>
            <input type="password" name="password" placeholder="パスワード" required>
            <input type="password" name="password_confirmation" placeholder="パスワード（確認）" required>
            <button type="submit">登録する</button>
        </form>

        <div class="link-area">
            <p>すでにアカウントをお持ちの方は <a href="{{ route('login') }}">こちらからログイン</a></p>
        </div>
    </div>
</div>
@endsection


