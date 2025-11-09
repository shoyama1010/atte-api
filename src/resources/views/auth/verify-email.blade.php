
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
    <div class="auth-container">
        <div class="auth-card verify-card">
            {{-- <h2>メールアドレスの確認</h2> --}}
            <p class="verify-text">
                登録していただいたメールアドレス宛に認証メールを送信しました。<br>
                メール認証を完了してください。
            </p>

            {{-- ✅ 成功メッセージ --}}
            @if (session('status') == 'verification-link-sent')
                <p class="success-message">
                    新しい認証リンクを送信しました！
                </p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary">認証はこちらから</button>
            </form>

            {{-- ✅ 認証メール再送リンク --}}
            <div class="link-area">
                <a href="{{ route('verification.send') }}"
                    onclick="event.preventDefault(); this.closest('div').querySelector('form').submit();">
                    認証メールを再送する
                </a>

                {{-- 非表示フォームで送信 --}}
                <form method="POST" action="{{ route('verification.send') }}" style="display:none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
@endsection
