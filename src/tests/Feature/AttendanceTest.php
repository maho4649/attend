<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外の画面が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外'); // 画面に「勤務外」と出る想定
    }

    /** @test */
    public function 出勤処理ができる()
    {
        $user = User::factory()->create();

        // 出勤ボタン押下
        $response = $this->actingAs($user)->post(route('attendance.store'));

        $response->assertRedirect('/attendance');

        // DBに勤務開始が記録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function 休憩開始と終了ができる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        // 休憩開始
        $response = $this->actingAs($user)
            ->post(route('attendance.break.start', $attendance));
        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'clock_in' => now()->format('Y-m-d H:i:s'),
        ]);

        // 休憩終了
        $response = $this->actingAs($user)
            ->post(route('attendance.break.end', $attendance));
        $response->assertRedirect('/attendance');
        $this->assertDatabaseMissing('break_times', [
            'attendance_id' => $attendance->id,
            'clock_out' => null,
        ]);
    }

    /** @test */
    public function 退勤処理ができる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        // 退勤
        $response = $this->actingAs($user)
            ->post(route('attendance.end', $attendance));

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
