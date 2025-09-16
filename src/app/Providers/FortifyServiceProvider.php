<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // CreatesNewUsers インターフェースを CreateNewUser にバインド
        $this->app->singleton(
            CreatesNewUsers::class,
            CreateNewUser::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify に各アクションを設定
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // ビューの指定
        Fortify::loginView(function () {
           if (request()->is('admin/*')) {
             return view('admin.login');  // 管理者ログイン画面
           }
           return view('auth.login');       // 一般ユーザーログイン画面
        });

        Fortify::registerView(fn() => view('auth.register'));//会員登録

        // レートリミッター設定
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // 新規登録後のリダイレクト
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('attendance.create');
            }
        });

        // ログイン後のリダイレクト
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                // URL に admin が含まれていたら管理者用にリダイレクト
                if ($request->is('admin/*')) {
                 return redirect()->route('admin.attendance.index');
                }

                // 通常ユーザー
                 return redirect()->route('attendance.create');
              }
        });

        Fortify::authenticateUsing(function (Request $request) {
            $validator = Validator::make($request->all(), [
                'email'    => ['required', 'email',],
                'password' => ['required'],
            ], [
                'email.required'    => 'メールアドレスを入力してください',
                'email.email'       => '正しいメールアドレス形式で入力してください',
                'password.required' => 'パスワードを入力してください',
            ]);
        
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
    
    
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && \Hash::check($request->password, $user->password)) {
              // 同じユーザーで管理者ログインもOK
            return $user;
            }
            

            // email または password が正しくない場合
            throw ValidationException::withMessages([
               'password' => ['ログイン情報が登録されていません'],
            ]);
        });

    }
}
