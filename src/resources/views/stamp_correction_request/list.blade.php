@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request.css') }}">
@endsection

@section('content')
    <h1>申請一覧</h1>
    <div class="correction-list-container">
        {{-- ▼ 上部タブメニュー --}}
        <div class="status-tabs">
            <ul>
                <li class="{{ request('status') === 'pending' || !request('status') ? 'active' : '' }}">
                    <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}">未承認</a>
                </li>
                <li class="{{ request('status') === 'approved' ? 'active' : '' }}">
                    <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}">承認済</a>
                </li>
            </ul>
        </div>
        {{-- <div class="correction-list-container"> --}}
        {{-- ▼ 一覧テーブル --}}
        @if ($requests->isEmpty())
            <p>現在、申請履歴はありません。</p>
        @else
            <table class="correction-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>社員名</th>
                        <th>申請日</th>
                        <th>修正対象日</th>
                        <th>申請内容</th>
                        {{-- <th>状態</th> --}}
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td>
                                @if ($request->status === 'pending')
                                    <span class="status pending">未承認</span>
                                @elseif($request->status === 'approved')
                                    <span class="status approved">承認済</span>
                                @elseif ($request->status === 'rejected')
                                    <span class="status rejected">却下</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ $request->created_at->format('Y/m/d') }}</td>
                            <td>{{ optional($request->attendance)->clock_in_time?->format('Y/m/d') ?? '-' }}</td>
                            <td>{{ $request->reason ?? '（理由なし）' }}</td>

                            <td><a href="#">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="button-area">
            <a href="{{ route('attendance.list') }}" class="btn-back">← 勤怠一覧へ戻る</a>
        </div>
    </div>
@endsection
