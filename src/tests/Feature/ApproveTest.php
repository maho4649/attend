<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ApproveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーを作るヘルパー
     */
    private function createAdminUser()
    {
        return User::factory()->create(); // テスト内で管理者として扱う
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示される()
    {
        $admin = $this->createAdminUser();
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'status' => Attendance::STATUS_PENDING,
            'description' => '修正申請1',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'status' => Attendance::STATUS_PENDING,
            'description' => '修正申請2',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.index', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('修正申請1');
        $response->assertSee('修正申請2');
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示される()
    {
        $admin = $this->createAdminUser();
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'status' => Attendance::STATUS_APPROVED,
            'description' => '承認済み1',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'status' => Attendance::STATUS_APPROVED,
            'description' => '承認済み2',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.index', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済み1');
        $response->assertSee('承認済み2');
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');
    }

    /** @test */
    public function 詳細ページに遷移できる()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create(['name' => '詳細ユーザー']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_PENDING,
            'description' => '詳細テスト',
        ]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.stamp_correction_request.approve', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('詳細ユーザー');
        $response->assertSee('詳細テスト');
    }

    /** @test */
    public function 承認ボタンを押すとステータスが承認済みになる()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)
                         ->put(route('admin.stamp_correction_request.update', $attendance->id));

        $response->assertRedirect(route('admin.stamp_correction_request.index', ['tab' => 'approved']));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_APPROVED,
        ]);
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create(['name' => '詳細ユーザー']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_PENDING,
            'description' => '詳細テスト',
        ]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.stamp_correction_request.approve', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('詳細ユーザー');
        $response->assertSee('詳細テスト');

        // 出勤・退勤時間も確認
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)
                         ->put(route('admin.stamp_correction_request.update', $attendance->id));

        // 承認後に承認済みタブにリダイレクトされる
        $response->assertRedirect(route('admin.stamp_correction_request.index', ['tab' => 'approved']));

        // DBのステータスが更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_APPROVED,
        ]);
    }
}
