<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 一般メールアドレスが未入力だとバリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function 一般パスワードが未入力だとバリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

       $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 一般登録内容と一致しないとバリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'ログイン情報が登録されていません'
        ]);
    }




    /** @test */
    public function 管理者メールアドレスが未入力だとバリデーションエラーになる()
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function 管理者パスワードが未入力だとバリデーションエラーになる()
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => 'test@example.com',
            'password' => '',
        ]);

       $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 管理者登録内容と一致しないとバリデーションエラーになる()
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
        'password' => 'ログイン情報が登録されていません'
        ]);
    }
}
