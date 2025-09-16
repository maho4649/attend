@extends('layout.admin')

@section('content')
<div class="attendance-form"> 
    <h1>| 申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="request-tab">
        <a href="{{ route('admin.stamp_correction_request.index', ['tab' => 'pending']) }}"
           class="{{ request('tab') !== 'approved' ? 'tab-active' : 'tab-inactive' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.stamp_correction_request.index', ['tab' => 'approved']) }}"
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
                    <td><a href="{{ route('admin.stamp_correction_request.approve', $attendance->id) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>


    {{ $attendances->links() }}

   <div class="pagination">
    @if ($tab === 'approved')
        {{-- 前のページリンク --}}
        @if ($attendances->currentPage() > 1)
            <a href="{{ $attendances->previousPageUrl() }}&tab=approved">前へ</a>
        @endif

        {{-- ページ番号リンク --}}
        @for ($i = 1; $i <= $attendances->lastPage(); $i++)
            <a href="{{ $attendances->url($i) }}&tab=approved" 
               class="{{ $i == $attendances->currentPage() ? 'font-bold text-yellow-500' : '' }}">
                {{ $i }}
            </a>
        @endfor

        {{-- 次のページリンク --}}
        @if ($attendances->hasMorePages())
            <a href="{{ $attendances->nextPageUrl() }}&tab=approved">次へ</a>
        @endif
    @endif
</div>


    

@endsection
