<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atte 勤怠管理システム</title>

    {{-- ✅ 共通CSS（全ページのベーススタイル） --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

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
            <div class="header-logo">
                <a href="{{ url('/attendance') }}" style="color: #fff; text-decoration:none;">Coachtech</a>
            </div>

            {{-- 右側：ナビメニュー --}}
            <nav class="header-nav">
                <ul>
                    @auth
                        {{-- 🔹 一般ユーザー用メニュー（guard: web） --}}
                        @if(Auth::guard('web')->check())
                            <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                            <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                            <li><a href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
                        @endif

                        {{-- 🔹 管理者用メニュー（guard: admin） --}}
                        @if(Auth::guard('admin')->check())
                            <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                            <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                            <li><a href="{{ route('admin.correction.list') }}">申請一覧</a></li>
                        @endif

                        {{-- 🔹 ログアウトボタン（Fortify対応POSTフォーム） --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                @csrf
                                <button type="submit" class="logout-btn">ログアウト</button>
                            </form>
                        </li>
                    @endauth

                    {{-- 未ログイン時 --}}
                    @guest
                        <li><a href="{{ route('login') }}">login</a></li>
                        <li><a href="{{ route('register') }}">register</a></li>
                    @endguest
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


{{-- <!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理アプリ')</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <header class="app-header">

        <div class="header-inner">

            <h1 class="header-logo">COACHTECH</h1>

            <nav class="header-nav">
                <ul>
                    <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                    <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>


    <main class="app-main">
        @yield('content')
    </main>

    <footer class="app-footer">
        <p>&copy; 2025 Atte 勤怠管理システム</p>
    </footer>
</body>

</html> --}}
