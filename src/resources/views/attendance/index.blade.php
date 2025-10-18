@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <h2>{{ $user->name }} さん、お疲れ様です！</h2>
    <p>{{ now()->format('Y年m月d日 (D)') }}</p>
    <h1>{{ now()->format('H:i') }}</h1>

    @if (session('message'))
        <p class="flash-message">{{ session('message') }}</p>
    @endif

    {{-- 状態ごとの表示切り替え --}}
    @if(!$attendance || $attendance->status === 'none')
        {{-- ✅ 勤務前 --}}
        <p class="status-text">「出勤」ボタンを押して勤務を開始してください。</p>
        <form action="{{ route('attendance.clockIn') }}" method="POST">
            @csrf
            <button type="submit" class="btn-active">出勤</button>
        </form>

    @elseif($attendance->status === 'working')
        {{-- ✅ 勤務中 --}}
        <p class="status-text">勤務中です。必要に応じて「休憩開始」または「退勤」を押してください。</p>
        <form action="{{ route('attendance.breakStart') }}" method="POST" style="margin-bottom:10px;">
            @csrf
            <button type="submit" class="btn-active">休憩入</button>
        </form>
        <form action="{{ route('attendance.clockOut') }}" method="POST">
            @csrf
            <button type="submit" class="btn-active">退勤</button>
        </form>

    @elseif($attendance->status === 'on_break')
        {{-- ✅ 休憩中 --}}
        <p class="status-text">現在休憩中です。「休憩終了」ボタンを押して勤務を再開してください。</p>
        <form action="{{ route('attendance.breakEnd') }}" method="POST">
            @csrf
            <button type="submit" class="btn-active">休憩終了（戻）</button>
        </form>

    @elseif($attendance->status === 'left')
        {{-- ✅ 退勤後 --}}
        <p class="status-text">本日の勤務は終了しました。お疲れさまでした。</p>

    @else
        <p>不明なステータスです。</p>
    @endif
</div>
@endsection

