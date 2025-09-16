<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'clock_in',//何度でも休憩できる
        'clock_out',//休憩終了
    ];

    protected $dates = [
        'clock_in',
        'clock_out',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
