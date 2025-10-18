@extends('layouts.app')

@section('content')
<div class="auth-container">
  <h2>ログイン</h2>

  <form method="POST" action="{{ route('login') }}">
    @csrf
    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email') <p class="error">{{ $message }}</p> @enderror

    <label>パスワード</label>
    <input type="password" name="password">
    @error('password') <p class="error">{{ $message }}</p> @enderror

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">ログイン</button>
    </div>

    <p class="link">
      アカウントをお持ちでない方は
      <a href="{{ route('register') }}">こちらから登録</a>
    </p>
    {{-- <button type="submit">ログイン</button> --}}
  </form>
</div>
@endsection
