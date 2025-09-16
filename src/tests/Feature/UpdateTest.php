<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザー作成 & ログイン
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function 出勤時間より退勤時間が前だとエラーになる()
    {
        $response = $this->put(route('attendance.update', $this->attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in'  => '10:00',
            'clock_out' => '09:00', // ❌ 出勤より前
            'description' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が空だとエラーになる()
    {
        $response = $this->put(route('attendance.update', $this->attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'description' => '', // ❌ 空
        ]);

        $response->assertSessionHasErrors([
            'description' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 休憩開始が出勤前だとエラーになる()
    {
        $response = $this->put(route('attendance.update', $this->attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'description' => 'テスト備考',
            'breaks' => [
                [
                    'clock_in'  => '08:00', // ❌ 出勤前
                    'clock_out' => '09:30',
                ]
            ]
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.clock_in' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 正常データなら更新できる()
    {
        $response = $this->put(route('attendance.update', $this->attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'description' => 'テスト備考',
            'breaks' => [
                [
                    'clock_in'  => '12:00',
                    'clock_out' => '13:00'
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('attendance.show', $this->attendance->id));
    }
}
