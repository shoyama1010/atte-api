<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atte 勤怠管理システム</title>

    {{-- ✅ 共通CSS（全ページのベーススタイル） --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    {{-- ✅ ページごとの追加CSSを読み込み（@section('css')で個別定義） --}}
    @yield('css')
</head>

<body>
    {{-- ===============================
         ヘッダー部（全ページ共通）
    ================================ --}}
    <header class="app-header">
        <div class="header-inner">
            {{-- 左側：ロゴ --}}
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo.jpg') }}" alt="ロゴ" height="40">
            </a>
            {{-- <div class="header-logo">
                <a href="{{ url('/attendance') }}" style="color: #fff; text-decoration:none;">Coachtech</a>
            </div> --}}

            {{-- 右側：ナビメニュー --}}
            <nav class="header-nav">
                <ul>
                    {{-- 🔹 一般ユーザー用メニュー(Fortify /auth:web） --}}
                    @if (Auth::guard('web')->check())
                        <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                        <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                        {{-- @endif --}}

                        {{-- ▼ 管理者用メニュー（/admin/login でログインしたauth:admin） --}}
                    @elseif (Auth::guard('admin')->check())
                        <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                        <li><a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                    @endif

                    {{-- 未ログイン時（一般ユーザーも管理者もログインしていない時だけ） --}}
                    @if (!Auth::guard('web')->check() && !Auth::guard('admin')->check())
                        <li><a href="{{ route('login') }}">一般ログイン</a></li>
                        <li><a href="{{ route('register') }}">新規登録</a></li>
                        <li><a href="{{ url('/admin/login') }}">管理者ログイン</a></li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>

    {{-- ===============================
         メインコンテンツ部
    ================================ --}}
    <main class="app-main">
        @yield('content')
    </main>

    {{-- ===============================
         フッター部
    ================================ --}}
    <footer class="app-footer">
        © 2025 Atte 勤怠管理システム
    </footer>
</body>

</html>
