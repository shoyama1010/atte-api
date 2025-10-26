@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">

    <div class="staff-list-container">
        <h2>スタッフ一覧</h2>

        @if ($staffs->isEmpty())
            <p class="no-data">スタッフデータがありません。</p>
        @else
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名前</th>
                        <th>メール</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffs as $staff)
                        <tr>
                            <td>{{ $staff->id }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.staff.list', $staff->id) }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-container">
                {{ $staffs->onEachSide(1)->links('vendor.pagination.simple-default') }}
            </div>
            {{-- <div class="pagination">
                {{ $staffs->links() }}
            </div> --}}
        @endif
    </div>
@endsection
