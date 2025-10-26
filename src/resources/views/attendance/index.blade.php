@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
    <p>{{ now()->format('Y年m月d日 (D)') }}</p>
    <h1>{{ now()->format('H:i') }}</h1> 
        {{-- @if (session('message'))
        <p class="flash-message">{{ session('message') }}</p>
    @endif --}}

        {{-- 状態ごとの表示切り替え --}}
        @if (!$attendance || $attendance->status === 'none')
            {{-- ✅ 勤務前(外) --}}
            <h2>{{ $user->name }}さん、お疲れ様です</h2>
            {{-- <p>{{ now()->format('Y年m月d日 (D)') }}</p>
            <h1>{{ now()->format('H:i') }}</h1> --}}

            <div class="status-label">勤務外</div>
            {{-- <p class="status-text">「出勤」ボタンを押して勤務を開始してください。</p> --}}
            <form action="{{ route('attendance.clockIn') }}" method="POST">
                @csrf
                <button type="submit" class="btn-active">出勤</button>
            </form>

            {{-- ✅ 出勤中 --}}
        @elseif($attendance->status === 'working')
            {{-- <p>{{ \Carbon\Carbon::now()->format('Y年m月d日 (D)') }}</p>
            <h1>{{ \Carbon\Carbon::now()->format('H:i') }}</h1> --}}
            {{-- <p class="status-text">勤務中です。必要に応じて「休憩開始」または「退勤」を押してください。</p> --}}
            <div class="status-label">出勤中</div>

            <div class="attendance-buttons">
                <form action="{{ route('attendance.breakStart') }}" method="POST" style="margin-bottom:10px;">
                    @csrf
                    <button type="submit" class="btn-active">休憩入</button>
                </form>
                <form action="{{ route('attendance.clockOut') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-active">退勤</button>
                </form>
            </div>
        @elseif($attendance->status === 'on_break')
            {{-- ✅ 休憩中 --}}
            {{-- <p>{{ \Carbon\Carbon::now()->format('Y年m月d日 (D)') }}</p>
            <h1>{{ \Carbon\Carbon::now()->format('H:i') }}</h1> --}}
            {{-- <p class="status-text">現在休憩中です。「休憩終了」ボタンを押して勤務を再開してください。</p> --}}
            <div class="status-label">休憩中</div>

            <form action="{{ route('attendance.breakEnd') }}" method="POST">
                @csrf
                <button type="submit" class="btn-active">休憩終了（戻）</button>
            </form>

            {{-- ④ 出勤再開（休憩後）--}}
        @elseif ($attendance->status === 'working_after_break')
            {{-- <p>{{ \Carbon\Carbon::now()->format('Y年m月d日 (D)') }}</p>
            <h1>{{ \Carbon\Carbon::now()->format('H:i') }}</h1> --}}

            <div class="status-label">出勤中</div>

            <div class="attendance-buttons">
                <form action="{{ route('attendance.breakStart') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-break">休憩開始</button>
                </form>
                <form action="{{ route('attendance.clockOut') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-leave">退勤</button>
                </form>
            </div>

        @elseif($attendance->status === 'left')
            {{-- ✅ 退勤後 --}}
            {{-- <p>{{ \Carbon\Carbon::now()->format('Y年m月d日 (D)') }}</p>
            <h1>{{ \Carbon\Carbon::now()->format('H:i') }}</h1> --}}
            <p class="status-text">本日の勤務は終了しました。お疲れさまでした。</p>
        @else
            <p>不明なステータスです。</p>
        @endif
    </div>
@endsection
