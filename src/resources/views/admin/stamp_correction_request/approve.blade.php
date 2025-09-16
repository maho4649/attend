@extends('layout.admin')

@section('content')
<div class="attendance-form"> 
    <h1>| 勤怠詳細</h1>

    <form method="POST" action="{{ route('admin.stamp_correction_request.update', $attendance->id) }}">
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
                 <input type="hidden" name="attendance_date" value="{{ optional($attendance->clock_in)->toDateString() }}">
             </td>
        </tr>
        <tr>
            <th >出勤・退勤</th>
            <td >
                <input type="time" name="clock_in" value="{{ optional($attendance->clock_in)->format('H:i') }}" class="time-approve" disabled>
                <span class="time-separator">〜</span>
                <input type="time" name="clock_out" value="{{ optional($attendance->clock_out)->format('H:i')  }}" class="time-approve" disabled>
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
                <input type="time" name="breaks[{{ $i }}][clock_in]" value="{{ optional($break->clock_in)->format('H:i') }}" class="time-approve" disabled>
                 <span class="time-separator">〜</span>
                <input type="time" name="breaks[{{ $i }}][clock_out]" value="{{  optional($break->clock_out)->format('H:i') }}" class="time-approve" disabled>
            </td>
        </tr>
        @endforeach

        <tr>
            <th >休憩{{ $attendance->breaks->count() + 1 }}</th>
            <td >
                <input type="time" name="breaks[new][clock_in]" value="" class="time-approve" disabled>
                <span class="time-separator">〜</span>
                <input type="time" name="breaks[new][clock_out]" value="" class="time-approve" disabled>
            </td>
        </tr>


        <tr>
            <th >備考</th>
            <td >
                <textarea name="description" class="textarea-approve" rows="4" disabled
                @if($attendance->status === '承認済み')  @endif>{{ old('description', $attendance->description) }}</textarea>
            </td>
        </tr>
    </table>

        @if ($attendance->status === \App\Models\Attendance::STATUS_PENDING)
            <button type="submit" class="btn-edit">承認</button>
        @else
            <p class="edit-approved">承認済み</p>
        @endif
  </form>


@endsection
