@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/approval_detail.css') }}">
@endsection

@section('content')
    <div class="approval-detail-container">

        <h2>勤務詳細</h2>
        <table class="approval-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $requestData->attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($requestData->attendance->clock_in_time)->format('Y年m月d日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ $requestData->after_clock_in ?? 'ーー' }}
                    〜 {{ $requestData->after_clock_out ?? 'ーー' }}
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    {{ $requestData->after_break_start ?? 'ーー' }}
                    〜
                    {{ $requestData->after_break_end ?? 'ーー' }}
                </td>
            </tr>
            <tr>
                <th>修正理由</th>
                <td>{{ $requestData->reason }}</td>
            </tr>
        </table>
        <div class="approval-actions">
            @if ($requestData->status === 'pending')
                <form action="{{ route('admin.correction_request.approve', $requestData->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-approve">承認する</button>
                </form>
            @else
                <button class="btn-approved" disabled>承認済</button>
            @endif
        </div>
    </div>
@endsection
