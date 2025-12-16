@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request.css') }}">
@endsection

@section('content')
    {{-- <h1>申請一覧（管理者）</h1> --}}
    <div class="correction-list-container">
        <h1 class="page-title">申請一覧（管理者）</h1>
        {{-- ▼ 上部タブメニュー --}}
        <div class="status-tabs">
            <ul>
                <li class="{{ request('status') === 'pending' || !request('status') ? 'active' : '' }}">
                    <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'pending']) }}">未承認</a>
                </li>
                <li class="{{ request('status') === 'approved' ? 'active' : '' }}">
                    <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'approved']) }}">承認済</a>
                </li>
            </ul>
        </div>
        {{-- ▼ 一覧テーブル --}}
        @if ($requests->isEmpty())
            <p>現在、申請履歴はありません。</p>
        @else
            <table class="correction-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td>
                                @if ($request->status === 'pending')
                                    <span class="status pending">承認待ち</span>
                                @elseif($request->status === 'approved')
                                    <span class="status approved">承認済</span>
                                @elseif($request->status === 'rejected')
                                    <span class="status rejected">却下</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            {{-- 名前 --}}
                            <td>{{ $request->attendance->user->name ?? '―' }}</td>
                            {{-- 対象日時 --}}
                            <td>{{ optional($request->attendance)->clock_in_time?->format('Y/m/d') ?? '-' }}</td>
                            {{-- 申請理由 --}}
                            <td>{{ $request->reason ?? '（理由なし）' }}</td>
                            {{-- 申請日時 --}}
                            <td>{{ $request->created_at->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('admin.correction_request.show', $request->id) }}" class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="button-area">
            <a href="{{ route('admin.attendance.list') }}" class="btn-back">← 勤怠一覧へ戻る</a>
        </div>
    </div>
@endsection
