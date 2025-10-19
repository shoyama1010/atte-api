@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="correction-list-container">
    <h2>申請一覧</h2>

    <table class="correction-table">
        <thead>
            <tr>
                <th>社員名</th>
                <th>申請日</th>
                <th>修正対象日</th>
                <th>申請内容</th>
                <th>状態</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $request)
                <tr>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>{{ $request->attendance->created_at->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>
                        @if($request->status === 'pending')
                            <span class="status pending">未承認</span>
                        @elseif($request->status === 'approved')
                            <span class="status approved">承認済</span>
                        @else
                            <span class="status rejected">却下</span>
                        @endif
                    </td>
                    <td><a href="{{ route('attendance.detail', $request->attendance_id) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
