<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\BreakTime;
use App\Models\User;
use App\Http\Requests\ShowRequest;


class AttendanceController extends Controller
{
   // 管理者用: 全ユーザーの勤怠一覧
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        // 全ユーザーの勤怠を取得
        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('clock_in', $date)
            ->orderByDesc('clock_in')
            ->paginate(10);

        // 各勤怠に休憩合計・労働時間を追加
        $attendances->getCollection()->transform(function ($attendance) {
        $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
            if ($break->clock_in && $break->clock_out) {
                return $break->clock_out->diffInMinutes($break->clock_in);
            }
            return 0;
        });

        $workMinutes = 0;
        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in) - $totalBreakMinutes;
        }

        $attendance->total_break_hours   = floor($totalBreakMinutes / 60);
        $attendance->total_break_minutes = $totalBreakMinutes % 60;
        $attendance->work_hours          = floor($workMinutes / 60);
        $attendance->work_minutes        = $workMinutes % 60;

        return $attendance;
    });

        return view('admin.attendance.index', compact('attendances','date'));
    }

    // 管理者がスタッフの勤怠一覧を確認
    public function staffAttendances(Request $request,$id)
    {
        $date = \Carbon\Carbon::parse($request->input('date', now()->startOfMonth()));

        $user = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $id)
            ->whereYear('clock_in', $date->year)
            ->whereMonth('clock_in', $date->month)
            ->with('breaks')
            ->orderByDesc('clock_in')
            ->paginate(10);

        $attendances->getCollection()->transform(function ($attendance) {
            $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
                if ($break->clock_in && $break->clock_out) {
                    return $break->clock_out->diffInMinutes($break->clock_in);
                }
                return 0;
            });

            $workMinutes = 0;
            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in) - $totalBreakMinutes;
            }

            $attendance->total_break_hours   = floor($totalBreakMinutes / 60);
            $attendance->total_break_minutes = $totalBreakMinutes % 60;
            $attendance->work_hours          = floor($workMinutes / 60);
            $attendance->work_minutes        = $workMinutes % 60;

            return $attendance;
        });

        return view('admin.attendance.staff', compact('attendances', 'user', 'date'));
    }
        


    // 勤怠詳細
    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
    }

    public function update(ShowRequest $request, Attendance $attendance)
   {

    if ($attendance->status === '承認待ち') {
        return redirect()->route('attendance.show', $attendance->id)->toDateString();
    }

    $attendanceDate = $request->input('attendance_date') ?? now()->toDateString();
    $clockInTime = $request->input('clock_in');
    $clockOutTime = $request->input('clock_out');

    // 出勤・退勤時刻を更新
     $attendance->clock_in  = $clockInTime ? \Carbon\Carbon::parse($attendanceDate . ' ' . $clockInTime) : null;
     $attendance->clock_out = $clockOutTime ? \Carbon\Carbon::parse($attendanceDate . ' ' . $clockOutTime) : null;
     $attendance->description = $request->input('description');
     $attendance->status = Attendance::STATUS_PENDING; // 修正したら承認待ちに

     $attendance->save();
    

    // 休憩時間の更新
    if ($request->has('breaks')) {
        foreach ($request->input('breaks') as $breakId => $breakData) {
            // new は別処理にするのでスキップ
            if ($breakId === 'new') {
                continue;
            }

            $break = $attendance->breaks()->find($breakId);
            if ($break) {
                $break->clock_in = $breakData['clock_in'] ? \Carbon\Carbon::parse($attendanceDate . ' ' . $breakData['clock_in']) : null;
                $break->clock_out = $breakData['clock_out'] ? \Carbon\Carbon::parse($attendanceDate . ' ' . $breakData['clock_out']) : null;
                $break->save();
            }
        }
    }

    // 新しい休憩が入力された場合
    if (!empty($request->breaks['new']['clock_in']) && !empty($request->breaks['new']['clock_out'])) {
        $attendance->breaks()->create([
            'clock_in' => \Carbon\Carbon::parse($attendanceDate . ' ' . $request->breaks['new']['clock_in']),
            'clock_out' => \Carbon\Carbon::parse($attendanceDate . ' ' . $request->breaks['new']['clock_out']),
        ]);
    }

    return redirect()->route('admin.attendance.show', $attendance->id)
                     ->with('success', '勤怠を修正しました');
    }

    


}

