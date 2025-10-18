@extends('layouts.app')

@section('content')
<div class="dashboard">
  <h2>管理者ダッシュボード</h2>
  <p>{{ $admin->name }} さん、ようこそ！</p>
  <form method="POST" action="{{ route('admin.logout') }}">
    @csrf
    <button type="submit" class="btn-secondary">ログアウト</button>
  </form>
</div>
@endsection

