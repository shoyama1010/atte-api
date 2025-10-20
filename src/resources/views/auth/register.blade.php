@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>新規会員登録</h2>

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


{{-- @extends('layouts.app')

@section('content')
    <div class="auth-container">
        <h2>会員登録</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">名前</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">パスワード（確認）</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">登録する</button>
            </div>

            <p class="link">
                すでにアカウントをお持ちの方は
                <a href="{{ route('login') }}">こちらからログイン</a>
            </p>
        </form>
    </div>
@endsection --}}
