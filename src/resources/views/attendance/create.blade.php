@extends('layouts.app')

@section('css')
    {{-- 勤務登録・詳細 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/attendance_form.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <h2>勤務登録</h2>

    {{-- 勤務登録フォーム --}}
    <form action="{{ route('attendance.store') }}" method="POST">
        @csrf

        {{-- 共通フォームを読み込み --}}
        @include('attendance._form')

        <div class="form-actions">
            <button type="submit" class="btn-submit">登録</button>
            <a href="{{ route('attendance.index') }}" class="btn-back">戻る</a>
        </div>
    </form>
</div>
@endsection
