@extends('layout.admin')

@section('content')
<div class="attendance-form">
    <h1>| 勤怠詳細</h1>
    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id)}}">
        @csrf
        @method('PUT')
      <table class="attendance-show">
        <tr>
            <th >名前</th>
            <td >{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th >日付</th>
            <td >
                 <span class="year">{{ optional($attendance->clock_in)->isoFormat('YYYY年') }}</span>
                 <span class="month-day">{{ optional($attendance->clock_in)->isoFormat('M月D日') ?? '-' }}</span>
                 <input type="hidden" name="attendance_date" value="{{ $attendance->clock_in ? $attendance->clock_in->toDateString() : now()->toDateString() }}">
            </td>
        </tr>
        <tr>
            <th >出勤・退勤</th>
            <td >
                <input type="time" name="clock_in" value="{{ old('clock_in',optional($attendance->clock_in)->format('H:i')) }}" class="time-input">
                <span class="time-separator">〜</span>
                <input type="time" name="clock_out" value="{{ old('clock_out',optional($attendance->clock_out)->format('H:i'))  }}" class="time-input">

                @error('clock_in')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                @error('clock_out')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </td>
        </tr>

        @foreach ($attendance->breaks as $i => $break)
        <tr>
            <th >
                休憩
                @if($attendance->breaks->count() > 1)
                  {{ $i + 1 }}
                @endif
            </th>
            <td >
                <input type="time" name="breaks[{{ $break->id }}][clock_in]" value="{{ optional($break->clock_in)->format('H:i') }}" class="time-input">
                <span class="time-separator">〜</span>
                <input type="time" name="breaks[{{ $break->id }}][clock_out]" value="{{ optional($break->clock_out)->format('H:i') }}" class="time-input">

                @error("breaks.$i.clock_in")
                    <div class="error-message">{{ $message }}</div>
                @enderror
                @error("breaks.$i.clock_out")
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </td>
        </tr>
        @endforeach

        <tr>
            <th >休憩{{ $attendance->breaks->count() + 1 }}</th>
            <td >
                <input type="time" name="breaks[new][clock_in]" value="" class="time-input">
                <span class="time-separator">〜</span>
                <input type="time" name="breaks[new][clock_out]" value="" class="time-input">

                @error('breaks.new.clock_in')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                @error('breaks.new.clock_out')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </td>
        </tr>


        <tr>
            <th >備考</th>
            <td >
                <textarea name="description" class="attendance-textarea" rows="4" 
                @if($attendance->status === '承認済み')  @endif>{{ old('description', $attendance->description) }}</textarea>

                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </td>
        </tr>
    </table>

        @if ($attendance->status !== '承認待ち')
            <button type="submit" class="btn-edit">修正</button>
        @else
            <p class="edit-wait">・承認待ちのため修正はできません</p>
        @endif
 </form>
</div>
@endsection
