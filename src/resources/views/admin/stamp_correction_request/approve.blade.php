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
                {{-- <td>{{ $requestData->attendance->clock_in_time->format('Y年m月d日') }}</td> --}}
                <td>{{ optional($requestData->attendance->clock_in_time)->format('Y年m月d日') ?? '-' }}</td>
            </tr>
            <tr>
                <th>申請種別</th>
                <td>{{ $requestData->request_type }}</td>
            </tr>
            <tr>
                <th>修正理由</th>
                {{-- <td>{{ $requestData->reason }}</td> --}}
                <td>{{ $requestData->reason ?? '（なし）' }}</td>
            </tr>
            <tr>
                <th>修正前の時刻</th>
                <td>
                    {{-- 出勤：{{ $requestData->before_clock_in ?? '-' }}
                    退勤：{{ $requestData->before_clock_out ?? '-' }}
                    休憩：{{ $requestData->before_break_start ?? '-' }}〜{{ $requestData->before_break_end ?? '-' }} --}}
                    出勤：{{ optional($requestData->after_clock_in)->format('H:i:s') ?? '-' }}
                    退勤：{{ optional($requestData->after_clock_out)->format('H:i:s') ?? '-' }}
                    休憩：{{ optional($requestData->after_break_start)->format('H:i:s') ?? '-' }}
                    〜{{ optional($requestData->after_break_end)->format('H:i:s') ?? '-' }}
                </td>
            </tr>
            <tr>
                <th>修正後の時刻</th>
                <td>
                    {{-- 出勤：{{ $requestData->after_clock_in ?? '-' }}
                    退勤：{{ $requestData->after_clock_out ?? '-' }}
                    休憩：{{ $requestData->after_break_start ?? '-' }}〜{{ $requestData->after_break_end ?? '-' }} --}}
                    出勤：{{ optional($requestData->after_clock_in)->format('H:i:s') ?? '-' }}
                    退勤：{{ optional($requestData->after_clock_out)->format('H:i:s') ?? '-' }}
                    休憩：{{ optional($requestData->after_break_start)->format('H:i:s') ?? '-' }}
                    〜{{ optional($requestData->after_break_end)->format('H:i:s') ?? '-' }}
                </td>
            </tr>
        </table>

        <div class="approval-buttons">
            @if ($requestData->status === 'pending')
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
