@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    {{-- =============================
         ステータスラベル（日付より上に配置）
       ============================= --}}
    @if (!$attendance || $attendance->status === 'none')
        <div class="status-label status-none">勤務外</div>
    @elseif($attendance->status === 'working')
        <div class="status-label status-working">出勤中</div>
    @elseif($attendance->status === 'on_break')
        <div class="status-label status-break">休憩中</div>
    @elseif($attendance->status === 'working_after_break')
        <div class="status-label status-working">出勤中</div>
    @elseif($attendance->status === 'left')
        <div class="status-label status-left">退勤済</div>
    @else
        <div class="status-label status-unknown">不明</div>
    @endif
    {{-- =============================
         日付・時刻表示
       ============================= --}}
    <p>{{ now()->format('Y年m月d日 (D)') }}</p>
    <h1>{{ now()->format('H:i') }}</h1>
    {{-- =============================
         ステータス別の操作表示
       ============================= --}}
    @if (!$attendance || $attendance->status === 'none')
        {{-- ✅ 勤務前(外) --}}
        <h3>{{ $user->name }}さん、お疲れ様です!</h3>
        <form action="{{ route('attendance.clockIn') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-work">出勤</button>
        </form>

    @elseif($attendance->status === 'working')
        {{-- ✅ 出勤中 --}}
        <div class="attendance-buttons">
            <form action="{{ route('attendance.breakStart') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-break">休憩入</button>
            </form>
            <form action="{{ route('attendance.clockOut') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-leave">退勤</button>
            </form>
        </div>

    @elseif($attendance->status === 'on_break')
        {{-- ✅ 休憩中 --}}
        <form action="{{ route('attendance.breakEnd') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-work">休憩終了（戻）</button>
        </form>

    @elseif ($attendance->status === 'working_after_break')
        {{-- ✅ 出勤再開（休憩後） --}}
        <div class="attendance-buttons">
            <form action="{{ route('attendance.breakStart') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-break">休憩入</button>
            </form>
            <form action="{{ route('attendance.clockOut') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-leave">退勤</button>
            </form>
        </div>

    @elseif($attendance->status === 'left')
        {{-- ✅ 退勤後 --}}
        <p class="status-text">本日の勤務は終了しました。お疲れさまでした。</p>

    @else
        <p>不明なステータスです。</p>
    @endif
</div>
@endsection
