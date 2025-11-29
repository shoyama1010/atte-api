@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="detail-wrapper">
    <div class="detail-card">

        <h2 class="detail-title">勤務詳細</h2>

        {{-- ▼ 承認待ち（読み取り専用） --}}
        @if ($correctionStatus === 'pending')

            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>{{ $user->name }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        {{ \Carbon\Carbon::parse($attendance->created_at)->format('Y年') }}
                        &nbsp;
                        {{ \Carbon\Carbon::parse($attendance->created_at)->format('n月j日') }}
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        {{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') }}
                    </td>
                </tr>

                <tr>
                    <th>休憩</th>
                    <td>
                        @foreach ($attendance->rests as $rest)
                            {{ \Carbon\Carbon::parse($rest->break_start)->format('H:i') }}
                            〜
                            {{ \Carbon\Carbon::parse($rest->break_end)->format('H:i') }}
                            <br>
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>{{ $attendance->note }}</td>
                </tr>
            </table>

            {{-- ▼ 赤文字メッセージ（帯なし） --}}
            <p class="pending-text">
                ※ 承認待ちのため修正はできません。
            </p>

            {{-- <div class="footer-btn-area">
                <a href="{{ route('attendance.list') }}" class="btn-back">一覧に戻る</a>
            </div> --}}

        @else
            {{-- ▼ 編集フォーム --}}
            <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PUT')

                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $user->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            {{ \Carbon\Carbon::parse($attendance->created_at)->format('Y年') }}
                            &nbsp;
                            {{ \Carbon\Carbon::parse($attendance->created_at)->format('n月j日') }}
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="edit-field">
                            <input type="time" name="clock_in_time"
                                value="{{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') }}">

                            〜

                            <input type="time" name="clock_out_time"
                                value="{{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') }}">
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td class="edit-field">
                            @foreach ($attendance->rests as $i => $rest)
                                <div class="rest-row">
                                    <input type="time" name="rests[{{ $i }}][break_start]"
                                        value="{{ \Carbon\Carbon::parse($rest->break_start)->format('H:i') }}">
                                    〜
                                    <input type="time" name="rests[{{ $i }}][break_end]"
                                        value="{{ \Carbon\Carbon::parse($rest->break_end)->format('H:i') }}">
                                </div>
                            @endforeach
                        </td>
                    </tr>

                    <tr>
                        <th>備考（修正理由）</th>
                        <td class="edit-field">
                            <textarea name="note">{{ $attendance->note }}</textarea>
                        </td>
                    </tr>
                </table>

                <div class="footer-btn-area">
                    <button type="submit" class="btn-submit">修正</button>
                </div>

            </form>
        @endif
    </div>
</div>
@endsection
