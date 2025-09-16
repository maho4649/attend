<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;


class StaffController extends Controller
{
    public function index(){
        $users = User::whereHas('attendances') // 勤怠があるユーザーのみ
                     ->paginate(10);
        return view('admin.staff.index',compact('users'));
    }
}