<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に名前が表示される()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2025, 9, 16, 9, 0, 0),
            'clock_out' => Carbon::create(2025, 9, 16, 18, 0, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    /** @test */
    public function 勤怠詳細画面に日付が表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2025, 9, 16, 9, 0, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('2025年'); // 年
        $response->assertSee('9月16日'); // 月日
    }

    /** @test */
    public function 出勤退勤時間が表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2025, 9, 16, 9, 0, 0),
            'clock_out' => Carbon::create(2025, 9, 16, 18, 0, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩時間が表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2025, 9, 16, 9, 0, 0),
            'clock_out' => Carbon::create(2025, 9, 16, 18, 0, 0),
        ]);

        $break1 = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::create(2025, 9, 16, 12, 0, 0),
            'clock_out' => Carbon::create(2025, 9, 16, 12, 30, 0),
        ]);

        $break2 = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::create(2025, 9, 16, 15, 0, 0),
            'clock_out' => Carbon::create(2025, 9, 16, 15, 15, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertSee('12:00');
        $response->assertSee('12:30');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }
}
