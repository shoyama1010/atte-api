@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-request-container">
    <h2>勤務詳細：{{ $user->name }} </h2>

    <form method="POST" action="{{ route('attendance.request.update', $attendance->id) }}">
        @csrf

        <table class="request-table">
            <tr>
                <th>日付</th>
                <td>{{ $attendance->created_at->format('Y年m月d日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in_time" value="{{ optional($attendance->clock_in_time)->format('H:i') }}">
                    〜
                    <input type="time" name="clock_out_time" value="{{ optional($attendance->clock_out_time)->format('H:i') }}">
                    @error('clock_in_time')<p class="error">{{ $message }}</p>@enderror
                    @error('clock_out_time')<p class="error">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    <input type="time" name="break_start" value="{{ optional($attendance->break_start)->format('H:i') }}">
                    〜
                    <input type="time" name="break_end" value="{{ optional($attendance->break_end)->format('H:i') }}">
                    @error('break_start')<p class="error">{{ $message }}</p>@enderror
                    @error('break_end')<p class="error">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr>
                <th>備考（修正理由）</th>
                <td>
                    <textarea name="reason" rows="2" placeholder="修正理由を入力してください">{{ old('reason') }}</textarea>
                    @error('reason')<p class="error">{{ $message }}</p>@enderror
                </td>
            </tr>
        </table>

        <div class="button-area">
            <button type="submit" class="btn-submit">修正申請する</button>
            <a href="{{ route('attendance.list') }}" class="btn-back">一覧に戻る</a>
        </div>
    </form>
</div>
@endsection


