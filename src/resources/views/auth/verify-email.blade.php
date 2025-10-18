@extends('layouts.app')

@section('content')
<div class="verify-container">
  <h2>メールアドレスの確認</h2>
  <p>
    登録されたメールアドレス宛に確認メールを送信しました。<br>
    メールに記載されたリンクをクリックして認証を完了してください。
  </p>

  @if (session('status') == 'verification-link-sent')
      <p class="success">
          新しい確認リンクを送信しました！
      </p>
  @endif

  <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="btn btn-primary">確認メールを再送信</button>
  </form>

  <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-secondary">ログアウト</button>
  </form>
</div>
@endsection
