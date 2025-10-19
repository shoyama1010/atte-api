@extends('layouts.app')

{{-- @section('content')
<div class="auth-container">
  <h2>会員登録</h2>

  <form method="POST" action="{{ route('register') }}">
    @csrf
    <label>名前</label>
    <input type="text" name="name" value="{{ old('name') }}">
    @error('name') <p class="error">{{ $message }}</p> @enderror

    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email') <p class="error">{{ $message }}</p> @enderror

    <label>パスワード</label>
    <input type="password" name="password">
    @error('password') <p class="error">{{ $message }}</p> @enderror

    <label>パスワード（確認）</label>
    <input type="password" name="password_confirmation">

    <button type="submit">登録する</button>
  </form>
</div>
@endsection --}}

@section('content')
    <div class="auth-container">
        <h2>会員登録</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- 名前 --}}
            <div class="form-group">
                <label for="name">名前</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            {{-- メールアドレス --}}
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード確認 --}}
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
@endsection
