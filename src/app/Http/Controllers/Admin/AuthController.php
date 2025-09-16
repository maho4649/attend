<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

class AuthController extends Controller
{
    // 管理者ログイン POST
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Fortify の authenticateUsing を利用してログイン
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            // ログイン後のリダイレクトは Fortify の LoginResponse に任せる
            return app(\Laravel\Fortify\Contracts\LoginResponse::class)->toResponse($request);
        }
        return back()->withErrors([
            'password' => 'ログイン情報が登録されていません',
        ]);

    }
}
