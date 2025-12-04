@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}" />
@endsection

@section('content')

    <div class="detail-wrapper">
        <div class="detail-card">

            <h2 class="detail-title">勤怠詳細（管理者）</h2>
            {{-- -------------------------------------------------------
             修正申請状況に応じて画面を切り替える
             -------------------------------------------------------
             ・$correctionRequest が null → 申請なし → 修正フォーム表示（管理者は直接修正可能）
             ・$correctionRequest が pending → 承認待ち → 読み取り専用＆メッセージ表示
             ・$correctionRequest が approved → 承認済 → 読み取り専用＆メッセージ表示
        -------------------------------------------------------- --}}
            @if ($correctionRequest && $correctionRequest->status !== 'none')
                {{-- ======================================================
                 読み取り専用（承認待ち or 承認済）
            ======================================================= --}}
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $staff->name }}</td>
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
                            {{ $clockIn }} 〜 {{ $clockOut }}
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td>
                            @forelse ($attendance->rests as $rest)
                                {{ \Carbon\Carbon::parse($rest->break_start)->format('H:i') }}
                                〜
                                {{ \Carbon\Carbon::parse($rest->break_end)->format('H:i') }}
                                <br>
                            @empty
                                ー
                            @endforelse
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td>{{ $attendance->note }}</td>
                    </tr>
                </table>

                {{-- 承認待ち or 承認済 --}}
                @if ($correctionRequest->status === 'pending')
                    <p class="status-msg">※ 承認待ちのため修正はできません。</p>
                @elseif($correctionRequest->status === 'approved')
                    <p class="status-msg">※ 承認済みのため修正はできません。</p>
                @endif
            @else

                {{-- ======================================================
                  修正可能（申請なし）
            ======================================================= --}}
                <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <table class="detail-table">
                        <tr>
                            <th>名前</th>
                            <td>{{ $staff->name }}</td>
                        </tr>

                        <tr>
                            <th>日付</th>
                            <td>
                                {{ \Carbon\Carbon::parse($attendance->created_at)->format('Y年') }}
                                &nbsp;
                                {{ \Carbon\Carbon::parse($attendance->created_at)->format('n月j日') }}
                            </td>
                        </tr>

                        {{-- ▼ 出勤・退勤 --}}
                        <tr>
                            <th>出勤・退勤</th>
                            <td class="edit-field-row">
                                <input type="time" name="clock_in_time"
                                    value="{{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') }}">
                                〜
                                <input type="time" name="clock_out_time"
                                    value="{{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') }}">
                            </td>
                        </tr>

                        {{-- ▼ 休憩（複数対応・行分割表示） --}}
                        @if ($attendance->rests && $attendance->rests->count() > 0)
                            @foreach ($attendance->rests as $index => $rest)
                                <tr>
                                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                    <td class="edit-field-row">
                                        <input type="time" name="rests[{{ $index }}][break_start]"
                                            value="{{ $rest->break_start ? \Carbon\Carbon::parse($rest->break_start)->format('H:i') : '' }}">
                                        〜
                                        <input type="time" name="rests[{{ $index }}][break_end]"
                                            value="{{ $rest->break_end ? \Carbon\Carbon::parse($rest->break_end)->format('H:i') : '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        @else

                            {{-- 休憩が未登録の場合のデフォルト --}}
                            <tr>
                                <th>休憩</th>
                                <td class="edit-field-row">
                                    <input type="time" name="rests[0][break_start]" value="">
                                    〜
                                    <input type="time" name="rests[0][break_end]" value="">
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <th>備考</th>
                            <td>
                                <textarea name="note" placeholder="修正理由を入力してください" required>{{ $attendance->note }}</textarea>
                            </td>
                        </tr>
                    </table>

                    <div class="btn-area">
                        <button type="submit" class="btn-submit">修正</button>
                        {{-- <a href="{{ route('admin.attendance.list', ['date' => $attendance->created_at->format('Y-m-d')]) }}"
                            class="btn-back">一覧に戻る</a> --}}
                    </div>
                </form>

            @endif

        </div>
    </div>

@endsection
