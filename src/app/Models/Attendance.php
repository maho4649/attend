<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'status',
        'description',
    ];

    const STATUS_OFF_DUTY = 'off_duty';
    const STATUS_WORKING  = 'working';
    const STATUS_ON_BREAK = 'on_break';//休憩中
    const STATUS_FINISHED = 'finished';//休憩終了
    const STATUS_PENDING    = '承認待ち';
    const STATUS_APPROVED   = '承認済み';  

    protected $dates = [
        'clock_in',
        'clock_out',
    ];

    public function breaks()
   {
    return $this->hasMany(BreakTime::class);
   }

   public function user()
   {
    return $this->belongsTo(User::class);
   }



}
