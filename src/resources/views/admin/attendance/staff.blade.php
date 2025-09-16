@extends('layout.admin')

@section('content')
<div class="attendance-form"> 
    <h1>| {{ $user->name }}さんの勤怠</h1>
      <div class="date-navigation">
         {{-- 前月ボタン --}}
          <a href="{{ route('admin.attendance.staff',['id' => $user->id, 'date'  => \Carbon\Carbon::parse($date)->subMonth()->format('Y-m')]) }}" class="link-btn" >
          ← 前月
          </a>

          {{-- date picker --}}
           <span class="month-label">{{ $date->format('Y/m') }}</span>

          {{-- 翌月ボタン --}}
            <a href="{{ route('admin.attendance.staff',['id' => $user->id, 'date' => \Carbon\Carbon::parse($date)->addMonth()->format('Y-m')]) }}" class="link-btn">
            翌月 →
            </a>
       </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
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
                    <td>{{ $attendance->clock_in->isoFormat('MM/D（ddd）') }}</td>
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
