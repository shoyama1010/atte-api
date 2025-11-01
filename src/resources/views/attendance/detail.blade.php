@extends('layouts.app')

@section('css')
    {{-- 勤務登録・詳細 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/attendance_form.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <h2>勤務詳細</h2>

    {{-- 勤務編集フォーム --}}
    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')
        {{-- 共通フォームを読み込み --}}
        @include('attendance._form')

        <div class="form-actions">
            <button type="submit" class="btn-update">変更を保存</button>
            <a href="{{ route('attendance.list') }}" class="btn-back">一覧に戻る</a>
        </div>
    </form>
</div>
@endsection
