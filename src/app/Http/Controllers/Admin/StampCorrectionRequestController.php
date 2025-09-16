<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\BreakTime;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
   {
    $tab = $request->input('tab', 'pending'); // デフォルトは承認待ち

    if ($tab === 'approved') {
        $attendances = Attendance::with('user')
                        ->where('status', Attendance::STATUS_APPROVED )
                        ->latest()
                        ->paginate(30)
                        ->appends(['tab' => $tab]);
    }else {
        $attendances = Attendance::with('user')
                        ->where('status', Attendance::STATUS_PENDING)
                        ->latest()
                        ->paginate(30)
                        ->appends(['tab' => $tab]);
    }
   

    return view('admin.stamp_correction_request.index', compact('attendances', 'tab'));
    }

    public function approve($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.stamp_correction_request.approve', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
    $attendance = Attendance::findOrFail($id);

    if ($attendance->status === Attendance::STATUS_PENDING) {
        $attendance->status = Attendance::STATUS_APPROVED;
        $attendance->save();

        return redirect()->route('admin.stamp_correction_request.index', ['tab' => 'approved'])
                         ->with('success', '承認しました');
        }

    return redirect()->back()->with('error', 'すでに承認済みです');
    }

}
