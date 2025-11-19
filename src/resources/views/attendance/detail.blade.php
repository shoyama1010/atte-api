@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_form.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
        <h2>勤務詳細</h2>

        {{-- 修正禁止時メッセージ --}}
        {{-- @if ($attendance->status === 'pending') --}}
        @if ($status === 'pending')
            {{-- フォーム項目は表示するが入力は無効 --}}
            <form>
                @csrf
                <table class="attendance-detail-table">
                    <tr>
                        <th>日付</th>
                        <td>{{ $attendance->created_at->format('Y年m月d日') }}</td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>{{ $attendance->clock_in_time }} ～ {{ $attendance->clock_out_time }}</td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            @foreach ($attendance->rests as $rest)
                                {{ $rest->break_start }} ～ {{ $rest->break_end }}<br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>備考（修正理由など）</th>
                        <td>{{ $attendance->note ?? '—' }}</td>
                    </tr>
                </table>

                {{-- 🚨 修正禁止メッセージ（ここに赤帯で表示） --}}
                <div class="alert alert-danger" style="margin-top: 20px; text-align:center; font-weight: bold;">
                    承認待ちのため修正はできません。
                </div>

                <div class="form-actions">
                    <a href="{{ route('attendance.list') }}" class="btn-back">一覧に戻る</a>
                </div>
            </form>
        @else
            {{-- 通常の編集フォーム --}}
            <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('attendance._form')

                <div class="form-actions">
                    <button type="submit" class="btn-update">修正</button>
                    {{-- <a href="{{ route('attendance.list') }}" class="btn-back">一覧に戻る</a> --}}
                </div>
            </form>
        @endif
    </div>
@endsection
