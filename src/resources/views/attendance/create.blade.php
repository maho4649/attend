@extends('layout.app')

@section('content')
<div class="attendance-form"> 
    @if(!$attendance)
        <p class="status-label">勤務外</p>
        <form method="POST" action="{{ route('attendance.store') }}" >
            @csrf
            <input type="hidden" name="status" value="working">

              {{-- 現在日時の表示 --}}
               <p class="date-label">
                {{ now()->isoFormat('YYYY年M月D日（ddd）') }}<br>
                <span class="time-label">{{ now()->format('H:i') }}</span>
               </p>

            <button type="submit" class="btn-work">出勤</button>
        </form>

    @elseif($attendance->status === \App\Models\Attendance::STATUS_WORKING)
        <p class="status-label">勤務中</p>
          <p class="date-label">
            {{ now()->isoFormat('YYYY年M月D日（ddd）') }}<br>
            <span class="time-label">{{ now()->format('H:i') }}</span>
          </p>

        <div class="d-flex justify-content-center mt-4 gap-2">
          <form method="POST" action="{{ route('attendance.end', $attendance) }}" class="inline-form" >
          @csrf
            <button type="submit" class="btn-work">退勤</button>
          </form>
          <form method="POST" action="{{ route('attendance.break.start', $attendance) }}" class="inline-form">
          @csrf
            <button type="submit" class="btn-break">休憩入</button>
          </form> 
        </div>

    @elseif($attendance->status === \App\Models\Attendance::STATUS_ON_BREAK)
      <p class="status-label">休憩中</p>
          <p class="date-label">
            {{ now()->isoFormat('YYYY年 M月D日(ddd) ') }}<br>
             <span class="time-label">{{ now()->format('H:i') }}</span>
          </p>
        <form method="POST" action="{{ route('attendance.break.end', $attendance) }}">
          @csrf
           <button type="submit" class="btn-break">休憩戻</button>
         </form>
         
    {{-- 退勤済み --}}
      @elseif($attendance->status === \App\Models\Attendance::STATUS_FINISHED)
       <p class="status-label">退勤済</p>
          <p class="date-label">
            {{ now()->isoFormat('YYYY年 M月D日(ddd)') }}<br>
            <span class="time-label">{{ now()->format('H:i') }}</span>
          </p>
       <h2 >お疲れ様でした。</h2>
</div>

        
    @endif

@endsection


