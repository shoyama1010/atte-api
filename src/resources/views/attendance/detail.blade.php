@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_form.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
        <h2>勤務詳細</h2>
        {{-- ▼ 承認待ちのとき（編集不可ブロック） --}}
        @if ($correctionStatus === 'pending')
            {{-- 勤務情報表示（読み取り専用） --}}
            @include('attendance.show_block')
            {{-- メッセージ表示 --}}
            <div class="alert alert-danger" style="margin-top:30px; text-align:center; padding:15px; font-weight:bold;">
                承認待ちのため修正はできません。
            </div>
            {{-- 戻るボタン --}}
            <div class="form-actions" style="text-align:center; margin-top:20px;">
                <a href="{{ route('attendance.list') }}" class="btn-back">一覧に戻る</a>
            </div>
            {{-- 修正可能（承認待ちでない） --}}
        @else
            {{-- 🔥ここを修正申請に使う --}}
            <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
                @csrf
                @method('PUT')

                @include('attendance.form_block')

                <div class="form-actions" style="margin-top:20px; text-align:center;">
                    <button type="submit" class="btn-update">修正</button>
                </div>
            </form>

        @endif
    </div>
@endsection
