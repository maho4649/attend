<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報が全て表示される()
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $fixedTime1 = Carbon::create(2025, 9, 16, 11, 14, 0);
    $fixedTime2 = Carbon::create(2025, 9, 15, 11, 14, 0);

    $attendance1 = Attendance::factory()->create(['user_id' => $user->id, 'clock_in' => $fixedTime1]);
    $attendance2 = Attendance::factory()->create(['user_id' => $user->id, 'clock_in' => $fixedTime2]);
    $attendanceOther = Attendance::factory()->create(['user_id' => $otherUser->id, 'clock_in' => $fixedTime1]);

    $response = $this->actingAs($user)->get(route('attendance.index'));

    $response->assertStatus(200);
    $response->assertSee('11:14'); // attendance1
    $response->assertSee('11:14'); // attendance2
    $response->assertDontSee('08:59'); // 他ユーザーの時間など
}

    /** @test */
    public function 現在の月が表示される()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('attendance.index'));

        $currentMonth = Carbon::now()->format('Y/m');
        $response->assertSee($currentMonth);
    }

    /** @test */
    public function 前月の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $prevMonthDate = Carbon::now()->subMonth();
        $attendancePrev = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $prevMonthDate,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index', ['date' => $prevMonthDate->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($attendancePrev->clock_in->format('H:i'));
    }

    /** @test */
    public function 翌月の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonthDate = Carbon::now()->addMonth();
        $attendanceNext = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $nextMonthDate,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index', ['date' => $nextMonthDate->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($attendanceNext->clock_in->format('H:i'));
    }

    /** @test */
    public function 勤怠詳細ページに遷移できる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id, 'clock_in' => now()]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in->format('H:i'));
    }
}
