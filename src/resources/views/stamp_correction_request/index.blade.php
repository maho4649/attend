@extends('layout.app')

@section('content')
<div class="attendance-form"> 
    <h1>| 申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="request-tab">
        <a href="{{ route('stamp_correction_request.index', ['tab' => 'pending']) }}"
           class="{{ request('tab') !== 'approved' ? 'tab-active' : 'tab-inactive' }}">
            承認待ち
        </a>
        <a href="{{ route('stamp_correction_request.index', ['tab' => 'approved']) }}"
           class="{{ request('tab') === 'approved' ? 'tab-active' : 'tab-inactive' }}">
            承認済み
        </a>      
    </div>
    <div class="tab-underline"></div>
    

    <table class="attendance-table">
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
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->status }}</td>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->clock_in?->format('Y/m/d') }}</td>
                    <td>{{ $attendance->description}}</td>
                    <td>{{ $attendance->updated_at?->format('Y/m/d') }}</td>
                    <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    {{ $attendances->appends(['tab' => $tab])->links() }}

    {{ $attendances->links() }}

@endsection
