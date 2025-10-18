<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ | @yield('title', 'Atte')</title>

    {{-- 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="app-header">
        <div class="header-inner">
            <h1 class="logo">
                <a href="{{ url('/') }}">Coachtech</a>
            </h1>
            @auth
            <nav class="nav-links">
                {{-- 一般ユーザー --}}
                @if(Auth::guard('web')->check())
                    <a href="{{ route('attendance.index') }}">login</a>
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-btn">ログアウト</button>
                    </form>

                {{-- 管理者 --}}
                @elseif(Auth::guard('admin')->check())
                    <a href="{{ route('admin.dashboard') }}">管理者ダッシュボード</a>
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-btn">ログアウト</button>
                    </form>
                @endif
            </nav>
            @endauth
        </div>
    </header>

    <main class="app-main">
        @yield('content')
    </main>

    <footer class="app-footer">
        <p>&copy; 2025 Atte 勤怠管理システム</p>
    </footer>
</body>
</html>
