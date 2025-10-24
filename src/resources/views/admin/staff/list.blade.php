@extends('layouts.app')

@section('content')
<div class="staff-list-container">
    <h2>スタッフ一覧</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>名前</th><th>メール</th><th>詳細</th>
                {{-- <th>勤怠一覧</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($staffs as $staff)
            <tr>
                <td>{{ $staff->id }}</td>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                {{-- <td><a href="{{ route('admin.attendance.detail', $staff->id) }}">詳細</a></td> --}}
                <td><a href="{{ route('admin.attendance.staff.list', $staff->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
