@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approval_detail.css') }}">
@endsection

@section('content')
<div class="approval-wrapper">
    <div class="approval-card">

        <h2 class="title">勤務詳細</h2>

        <table class="detail-table">

            <tr>
                <th>名前</th>
                <td>{{ $requestData->attendance->user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>
                    {{ \Carbon\Carbon::parse($requestData->attendance->clock_in_time)->format('Y年') }}
                    <span style="margin-left:40px;">
                        {{ \Carbon\Carbon::parse($requestData->attendance->clock_in_time)->format('n月j日') }}
                    </span>
                </td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ \Carbon\Carbon::parse($requestData->after_clock_in)->format('H:i') }}
                    &nbsp;&nbsp;〜&nbsp;&nbsp;
                    {{ \Carbon\Carbon::parse($requestData->after_clock_out)->format('H:i') }}
                </td>
            </tr>

            <tr>
    <th>休憩</th>
    <td>
        {{-- 複数休憩をループで表示 --}}
        @if ($attendance->rests && $attendance->rests->count() > 0)
            @foreach ($attendance->rests as $i => $rest)
                休憩{{ $i + 1 }}：
                {{ \Carbon\Carbon::parse($rest->break_start)->format('H:i') }}
                〜
                {{ \Carbon\Carbon::parse($rest->break_end)->format('H:i') }}
                <br>
            @endforeach
        @else
            ーー 〜 ーー
        @endif
    </td>
</tr>

            <tr>
                <th>備考</th>
                <td>{{ $requestData->reason }}</td>
            </tr>
        </table>

        {{-- ===== ボタンエリア ===== --}}
        <div class="button-area">

            @if ($requestData->status === 'pending')
                <form method="POST" action="{{ route('admin.correction_request.approve', $requestData->id) }}">
                    @csrf
                    <button type="submit" class="btn-approve">承認する</button>
                </form>
            @else
                <button class="btn-approved" disabled>承認済み</button>
            @endif
        </div>
    </div>
</div>
@endsection
