<?php

namespace App\Http\Controllers;

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
                        ->where('status', Attendance::STATUS_APPROVED)
                        ->latest()
                        ->paginate(30);
    } else {
        $attendances = Attendance::with('user')
                        ->where('status', Attendance::STATUS_PENDING)
                        ->latest()
                        ->paginate(30);
    }

    return view('stamp_correction_request.index', compact('attendances', 'tab'));
    }

}
