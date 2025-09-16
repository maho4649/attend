<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    /** 管理者ユーザーを作成 */
    private function createAdminUser()
    {
        return User::factory()->create(); // is_admin カラムなし
    }

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        // 管理者を作成
        $admin = $this->createAdminUser();

        // 一般ユーザーを複数作成（勤怠も追加）
        $user1 = User::factory()->create([
            'name' => 'テストユーザー1',
            'email' => 'user1@example.com',
        ]);
        Attendance::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create([
            'name' => 'テストユーザー2',
            'email' => 'user2@example.com',
        ]);
        Attendance::factory()->create(['user_id' => $user2->id]);

        // 管理者としてアクセス
        $response = $this->actingAs($admin)->get(route('admin.staff.index'));

        $response->assertStatus(200);

        // 一般ユーザーの「氏名」「メールアドレス」が表示されているか確認
        $response->assertSee('テストユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('テストユーザー2');
        $response->assertSee('user2@example.com');
    }
}
