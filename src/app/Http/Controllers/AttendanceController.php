<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\BreakTime;
use App\Http\Requests\ShowRequest;


class AttendanceController extends Controller
{
    // 勤怠登録画面
    public function create()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('clock_in', now()->toDateString())
            ->first();

        return view('attendance.create', compact('attendance'));
    }

    // 出勤ボタン押下時
    public function store(Request $request)
    {
        Attendance::create([
            'user_id'   => Auth::id(),
            'clock_in'  => now(),
            'status'    => $request->input('status', 'working'),
        ]);

        return redirect()->route('attendance.create')
            ->with('success', '出勤しました');
    }

    public function startBreak(Attendance $attendance)
{
    // 休憩開始
    $attendance->status = Attendance::STATUS_ON_BREAK;
    $attendance->save();

    // Breakレコード作成（break_outは後で更新）
    $attendance->breaks()->create([
        'clock_in' => now()
    ]);

    return redirect()->route('attendance.create');
}

    public function endBreak(Attendance $attendance)
{
    // 最新のBreakレコードに終了時刻をセット
    $lastBreak = $attendance->breaks()->latest()->first();
    $lastBreak->update(['clock_out' => now()]);

    // ステータスを出勤中に戻す
    $attendance->status = Attendance::STATUS_WORKING;
    $attendance->save();

    return redirect()->route('attendance.create');
}

   //退勤処理
    public function workEnd(Attendance $attendance)
{
    $attendance->clock_out = now();
    $attendance->status = Attendance::STATUS_FINISHED;
    $attendance->save();

    return redirect()->route('attendance.create');
}


    // 勤怠一覧
    public function index(Request $request)
    {
        $date = \Carbon\Carbon::parse($request->input('date', now()->startOfMonth()));

        $attendances = Attendance::where('user_id',Auth::id())
            ->whereYear('clock_in', $date->year)
            ->whereMonth('clock_in', $date->month)
            ->with('breaks')
            ->orderByDesc('clock_in')
            ->paginate(10);

        // 各 attendance に休憩合計・労働時間を追加
        $attendances->getCollection()->transform(function ($attendance) {
          // 休憩合計
           $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
            if ($break->clock_in && $break->clock_out) {
                return $break->clock_out->diffInMinutes($break->clock_in);
            }
            return 0;
        });
        
        // 労働時間（分）
        $workMinutes = 0;
        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in) - $totalBreakMinutes;
        }

        // Blade で使えるように属性を追加
        $attendance->total_break_hours   = floor($totalBreakMinutes / 60);
        $attendance->total_break_minutes = $totalBreakMinutes % 60;
        $attendance->work_hours          = floor($workMinutes / 60);
        $attendance->work_minutes        = $workMinutes % 60;

        return $attendance;
       });

        return view('attendance.index', compact('attendances','date'));
    }

    // 勤怠詳細
    public function show($id)
    {
        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($id);
        return view('attendance.show', compact('attendance'));
    }

    public function update(ShowRequest $request, Attendance $attendance)
   {

    if ($attendance->status === '承認待ち') {
        return redirect()->route('attendance.show', $attendance->id)
                         ->with('error', '承認待ちのため修正できません');
    }

    // 出勤・退勤時刻を更新
    $attendanceDate = $request->input('attendance_date'); // YYYY-MM-DD
    $clockInTime    = $request->input('clock_in');       // HH:MM
    $clockOutTime   = $request->input('clock_out');      // HH:MM

    $attendance->update([
        'clock_in'  => $clockInTime ? $attendanceDate . ' ' . $clockInTime : null,
        'clock_out' => $clockOutTime ? $attendanceDate . ' ' . $clockOutTime : null,
        'description' => $request->input('description'),
        'status'      => Attendance::STATUS_PENDING,
    ]);

    // 休憩時間の更新
    if ($request->has('breaks')) {
        foreach ($request->input('breaks') as $breakId => $breakData) {
            // new は別処理にするのでスキップ
            if ($breakId === 'new') {
                continue;
            }

            $attendance->breaks()->where('id', $breakId)->update([
                'clock_in'  => $attendanceDate . ' ' . ($breakData['clock_in'] ?? '00:00'),
                'clock_out' => $attendanceDate . ' ' . ($breakData['clock_out'] ?? '00:00'),
            ]);
        }
    }

    if (!empty($request->breaks['new']['clock_in']) && !empty($request->breaks['new']['clock_out'])) {
      $attendance->breaks()->create([
        'clock_in' => $attendanceDate . ' ' .$request->breaks['new']['clock_in'],
        'clock_out'=> $attendanceDate . ' ' .$request->breaks['new']['clock_out'],
        ]);
     }


    return redirect()->route('attendance.show', $attendance->id)
                     ->with('success');
    }

}
