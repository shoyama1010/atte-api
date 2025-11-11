@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/approval_detail.css') }}">
@endsection

@section('content')
    <div class="approval-detail-container">


        <h2>勤務詳細：{{ $requestData->attendance->user->name }}</h2>

        <table class="approval-detail-table">
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($requestData->attendance->clock_in_time)->format('Y年m月d日') }}</td>
            </tr>
            {{-- <tr>
            <th>申請種別</th>
            <td>{{ $requestData->request_type }}</td>
        </tr> --}}
            <tr>
                <th>修正前の時刻</th>
                <td>
                    出勤：{{ $requestData->before_clock_in ?? '－' }}
                    ～ 退勤：{{ $requestData->before_clock_out ?? '－' }}
                    ／ 休憩：{{ $requestData->before_break_start ?? '－' }}～{{ $correctionRequest->before_break_end ?? '－' }}
                </td>
            </tr>
            <tr>
                <th>修正後の時刻</th>
                <td>
                    出勤：{{ $requestData->after_clock_in ?? '－' }}
                    ～ 退勤：{{ $requestData->after_clock_out ?? '－' }}
                    ／ 休憩：{{ $requestData->after_break_start ?? '－' }}～{{ $correctionRequest->after_break_end ?? '－' }}
                </td>
            </tr>
            <tr>
                <th>修正理由</th>
                <td>{{ $requestData->reason }}</td>
            </tr>
        </table>

        <div class="approval-actions">
            @if ($requestData->status === 'pending')
                {{-- <form action="{{ route('admin.stamp_correction_request.approve', $requestData->id) }}" method="POST"> --}}
                <form action="{{ route('admin.correction_request.approve', $requestData->id) }}" method="POST">

                    @csrf
                    <button type="submit" class="btn-approve">承認する</button>
                </form>
            @else
                <button class="btn-approved" disabled>承認済</button>
            @endif

            {{-- <a href="{{ route('admin.stamp_correction_request.list') }}" class="btn-back">一覧に戻る</a> --}}
            <a href="{{ route('admin.correction_request.show', $requestData->id) }}" class="btn-back">一覧に戻る</a>

        </div>
    </div>
@endsection
