<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
use Laravel\Fortify\Fortify;


// 一般ユーザー用

Route::get('/register', function() {
    return view('auth.register');
})->name('register');

Route::get('/login', function() {
    return view('auth.login');
})->name('login');

Route::post('/logout', function() {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

Route::prefix('attendance')->group(function () {
    //勤怠登録画面（一般ユーザー）
    Route::get('/', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/{attendance}/break/start', [AttendanceController::class, 'startBreak'])->name('attendance.break.start');
    Route::post('/{attendance}/break/end', [AttendanceController::class, 'endBreak'])->name('attendance.break.end');
    Route::post('/{attendance}/end', [AttendanceController::class, 'workEnd'])->name('attendance.end');

    //勤怠一覧画面（一般ユーザー）
    Route::get('/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠修正
    Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    // 勤怠詳細画面
    Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
           
   });


Route::prefix('stamp_correction_request')->group(function () {
    //申請一覧画面（一般ユーザー）
    Route::get('/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.index');
});


// 管理者用
Route::prefix('admin')->group(function () {
    
    Route::get('/login', function() {
      return view('admin.login');
    })->name('admin.login');
      Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])
        ->name('admin.login.post');
    
    Route::prefix('attendances')->group(function () {
        //勤怠一覧画面（管理者）
        Route::get('/', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');

        // 勤怠修正
        Route::put('/{attendance}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
        //勤怠詳細画面（管理者）
        Route::get('/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');

        
    });

    //スタッフ一覧画面（管理者）
    Route::prefix('users')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('admin.staff.index');

        //スタッフ別勤怠一覧画面（管理者）
        Route::get('/{id}/attendances', [AdminAttendanceController::class, 'staffAttendances'])
            ->name('admin.attendance.staff');
    });

    Route::prefix('requests')->group(function () {
        //申請一覧画面（管理者）
        Route::get('/', [AdminStampCorrectionRequestController::class, 'index'])
            ->name('admin.stamp_correction_request.index');
        //修正申請承認画面（管理者）
        Route::get('/{id}', [AdminStampCorrectionRequestController::class, 'approve'])
            ->name('admin.stamp_correction_request.approve');

        Route::put('/{id}', [AdminStampCorrectionRequestController::class, 'update'])
             ->name('admin.stamp_correction_request.update');


    });
});
