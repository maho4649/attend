@extends('layout.admin')

@section('content')
<div class="attendance-form"> 
    <h1>| {{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h1>
    
    <div class="date-navigation">
      {{-- 前日ボタン --}}
       <a href="{{ route('admin.attendance.index', ['date'  => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}" class="link-btn">
        ← 前日
        </a>

       {{-- date picker --}}
        <form method="GET" action="{{ route('admin.attendance.index') }}">
           <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
        </form>

        {{-- 翌日ボタン --}}
         <a href="{{ route('admin.attendance.index', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}" class="link-btn">
        翌日 →
         </a>
    </div>

       

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
                
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->clock_in->format('H:i') }}</td>
                    <td>{{ $attendance->clock_out?->format('H:i') ?? '-' }}</td>
                    <td>{{ sprintf('%d:%02d', $attendance->total_break_hours, $attendance->total_break_minutes) }}</td>
                    <td>{{ sprintf('%d:%02d', $attendance->work_hours, $attendance->work_minutes) }}</td>
                    <td><a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    {{ $attendances->links() }}
@endsection
