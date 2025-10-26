@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approval_detail.css') }}">
@endsection

@section('content')
<div class="approval-container">
    <h2>勤務詳細：{{ $requestData->attendance->user->name }}</h2>

    <table class="approval-table">
        <tr>
            <th>日付</th>
            <td>{{ $requestData->attendance->created_at->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <th>申請種別</th>
            <td>{{ $requestData->request_type }}</td>
        </tr>
        <tr>
            <th>修正理由</th>
            <td>{{ $requestData->reason }}</td>
        </tr>
        <tr>
            <th>修正前の時刻</th>
            <td>
                @if($requestData->before_time)
                    @php $before = json_decode($requestData->before_time, true); @endphp
                    出勤：{{ $before['clock_in_time'] ?? '-' }}　
                    退勤：{{ $before['clock_out_time'] ?? '-' }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>修正後の時刻</th>
            <td>
                @if($requestData->after_time)
                    @php $after = json_decode($requestData->after_time, true); @endphp
                    出勤：{{ $after['clock_in_time'] ?? '-' }}　
                    退勤：{{ $after['clock_out_time'] ?? '-' }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="approval-buttons">
        @if($requestData->status === 'pending')
            <form method="POST" action="{{ route('admin.correction_request.approve', $requestData->id) }}">
                @csrf
                <button type="submit" class="btn-approve">承認する</button>
            </form>
        @else
            <button class="btn-approved" disabled>承認済</button>
        @endif

        <a href="{{ route('admin.stamp_correction_request.list') }}" class="btn-back">一覧に戻る</a>
    </div>
</div>
@endsection
