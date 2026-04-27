@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.staff.css') }}">

@section('content')
    <div class="attendance-container">
        <h2>>{{ $staff->name }} さんの勤怠一覧</h2>
        
         {{-- 月切替バー --}}
<div class="month-bar">

    {{-- 前月 --}}
    <a href="{{ route('admin.attendance.staff_list', [
        'id' => $staff->id,
        'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')
    ]) }}" class="month-arrow">
        ＜ 前月
    </a>

    {{-- 中央（月表示） --}}
    <div class="month-center">
        <span class="calendar-icon">📅</span>
        <span class="month-text">
            {{ \Carbon\Carbon::parse($month)->format('Y年m月') }}
        </span>
    </div>

    {{-- 翌月 --}}
    <a href="{{ route('admin.attendance.staff_list', [
        'id' => $staff->id,
        'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')
    ]) }}" class="month-arrow">
        翌月 ＞
    </a>

</div>

        {{-- 勤怠テーブル --}}
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                    @php
                        $clockIn = $attendance->clock_in_time
                            ? \Carbon\Carbon::parse($attendance->clock_in_time)
                            : null;
                        $clockOut = $attendance->clock_out_time
                            ? \Carbon\Carbon::parse($attendance->clock_out_time)
                            : null;

                        // 🔹休憩時間は Controller 側で計算済みの break_total を使う
                        $breakTime = $attendance->break_total ?? '-';

                        $workMinutes = 0;
                        if ($clockIn && $clockOut) {
                            $workMinutes = $clockOut->diffInMinutes($clockIn);

                            // 🔹休憩分を減算（break_total があればそれを減算）
                            if ($attendance->break_total && $attendance->break_total !== '-') {
                                [$h, $m] = explode(':', $attendance->break_total);
                                $workMinutes -= $h * 60 + $m;
                            }
                            // if ($breakStart && $breakEnd) {
                            //     $workMinutes -= $breakEnd->diffInMinutes($breakStart);
                            // }
                        }
                        $workHours = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                    @endphp

                    <tr>
                        <td>{{ optional($clockIn)->format('Y/m/d') ?? '-' }}</td>
                        <td>{{ optional($clockIn)->format('H:i') ?? '-' }}</td>
                        <td>{{ optional($clockOut)->format('H:i') ?? '-' }}</td>

                        <td>{{ $breakTime }}</td>

                        <td>{{ $workHours }}</td>
                        <td>
                            {{-- <a href="{{ route('admin.attendance.edit', ['id' => $attendance->id]) }}">詳細</a> --}}
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">該当データがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- ページネーション --}}
        {{-- <div class="pagination">
            {{ $attendances->links() }}
        </div> --}}
        {{-- ✅ CSV出力ボタン（下部右寄せ） --}}
        <div class="csv-btn-container">
            <a href="{{ route('admin.attendance.staff.export', $staff->id) }}" class="btn-csv">CSV出力</a>
        </div>

        {{-- 戻るボタン --}}
        <div class="back-btn-area" style="margin-top: 20px;">
            <a href="{{ route('admin.staff.list') }}" class="back-btn">← スタッフ一覧に戻る</a>
        </div>
    </div>
@endsection

