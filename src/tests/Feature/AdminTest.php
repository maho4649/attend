<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーを作るためのヘルパー
     * is_admin カラムを使わず、単にテスト内で管理者として扱う
     */
    private function createAdminUser()
    {
        return User::factory()->create(); // 管理者として扱う
    }

    /** @test */
    public function 全ユーザーの勤怠情報が表示される()
    {
        $admin = $this->createAdminUser();
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        $today = Carbon::today();
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'clock_in' => $today->copy()->setHour(9),
            'clock_out' => $today->copy()->setHour(18),
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'clock_in' => $today->copy()->setHour(10),
            'clock_out' => $today->copy()->setHour(19),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $today->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('ユーザー2');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 勤怠一覧に今日の日付が表示される()
    {
        $admin = $this->createAdminUser();
        $today = Carbon::today();

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $today->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($today->format('Y年n月j日'));
    }

    /** @test */
    public function 前日の勤怠情報が表示される()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $yesterday = Carbon::yesterday();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $yesterday->copy()->setHour(9),
            'clock_out' => $yesterday->copy()->setHour(18),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $yesterday->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年n月j日'));
        $response->assertSee($attendance->user->name);
    }

    /** @test */
    public function 翌日の勤怠情報が表示される()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $tomorrow = Carbon::tomorrow();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $tomorrow->copy()->setHour(9),
            'clock_out' => $tomorrow->copy()->setHour(18),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $tomorrow->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年n月j日'));
        $response->assertSee($attendance->user->name);
    }

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create(['name' => 'テストユーザー']);
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => Carbon::create(2025, 9, 16, 9, 0, 0),
            'clock_out' => Carbon::create(2025, 9, 16, 18, 0, 0),
            'description' => 'テスト備考',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('テスト備考');
    }

     /** @test */
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = $this->createAdminUser();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in' => '20:00',
            'clock_out' => '10:00',
            'description' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['clock_out']);
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('clock_out')
        );
    }

     /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = $this->createAdminUser();
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                 [
                    'clock_in' => '19:00',
                    'clock_out' => '19:30',
                ]
            ],
            'description' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['breaks.0.clock_in']);
        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            session('errors')->first('breaks.0.clock_in')
        );
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = $this->createAdminUser();
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                 [
                    'clock_in' => '17:00',
                    'clock_out' => '19:00',
                ]
            ],
            'description' => 'テスト',
        ]);
        $response->assertSessionHasErrors(['breaks.0.clock_out']);
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('breaks.0.clock_out')
        );
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdminUser();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'attendance_date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['description']);
        $this->assertStringContainsString(
            '備考を記入してください',
            session('errors')->first('description')
        );
    }

    /** @test */
public function ユーザーの勤怠情報が正しく表示される()
{
    $admin = $this->createAdminUser();
    $user = User::factory()->create(['name' => 'テスト太郎']);

    // 勤怠データ作成
    $attendance = Attendance::factory()->create([
        'user_id'   => $user->id,
        'clock_in'  => now()->setHour(9)->setMinute(0)->setSecond(0),
        'clock_out' => now()->setHour(18)->setMinute(0)->setSecond(0),
        'description' => '詳細テスト',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.attendance.staff', ['id' => $user->id, 'date' => now()->format('Y-m')]));

    $response->assertStatus(200);
    $response->assertSee('テスト太郎');
    $response->assertSee('09:00');
    $response->assertSee('18:00');
}

/** @test */
public function 前月ボタンを押すと前月の勤怠情報が表示される()
{
    $admin = $this->createAdminUser();
    $user = User::factory()->create(['name' => '前月ユーザー']);

    $lastMonth = now()->subMonth()->startOfMonth();
    $attendance = Attendance::factory()->create([
        'user_id'   => $user->id,
        'clock_in'  => $lastMonth->copy()->setHour(10),
        'clock_out' => $lastMonth->copy()->setHour(19),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.attendance.staff', ['id' => $user->id, 'date' => $lastMonth->format('Y-m')]));

    $response->assertStatus(200);
    $response->assertSee($lastMonth->format('Y/m'));
    $response->assertSee('10:00');
    $response->assertSee('19:00');
}

/** @test */
public function 翌月ボタンを押すと翌月の勤怠情報が表示される()
{
    $admin = $this->createAdminUser();
    $user = User::factory()->create(['name' => '翌月ユーザー']);

    $nextMonth = now()->addMonth()->startOfMonth();
    $attendance = Attendance::factory()->create([
        'user_id'   => $user->id,
        'clock_in'  => $nextMonth->copy()->setHour(8),
        'clock_out' => $nextMonth->copy()->setHour(17),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.attendance.staff', ['id' => $user->id, 'date' => $nextMonth->format('Y-m')]));

    $response->assertStatus(200);
    $response->assertSee($nextMonth->format('Y/m'));
    $response->assertSee('08:00');
    $response->assertSee('17:00');
}

/** @test */
public function 詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
{
    $admin = $this->createAdminUser();
    $user = User::factory()->create(['name' => '詳細ユーザー']);

    $attendance = Attendance::factory()->create([
        'user_id'   => $user->id,
        'clock_in'  => now()->setHour(9)->setMinute(0)->setSecond(0),
        'clock_out' => now()->setHour(18)->setMinute(0)->setSecond(0),
        'description' => '詳細テスト',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.attendance.show', $attendance->id));

    $response->assertStatus(200);
    $response->assertSee('詳細ユーザー');
    $response->assertSee('09:00');
    $response->assertSee('18:00');
    $response->assertSee('詳細テスト');
}

}