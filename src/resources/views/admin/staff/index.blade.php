@extends('layout.admin')

@section('content')
<div class="attendance-form"> 
    <h1>| スタッフ一覧</h1>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤務</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('admin.attendance.staff', $user->id) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    {{ $users->links() }}
@endsection
