<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <header class="logo-header">
        <!-- 左側：ロゴ -->
        <div class="nav-container">
          <a href="{{ route('login') }}" class="logo">
            <img src="{{ asset('storage/logo.svg') }}" alt="Logo">
          </a>

         
          <!-- 右側：リンク -->
          <div class="right-area"> 
           <nav class="nav-links auth-links">
               @auth
                    <a href="/attendance">勤怠</a>
                    <a href="/attendance/list">勤怠一覧</a> 
                    <a href="/stamp_correction_request/list">申請</a>

                    <form method="POST" action="/logout" class="logout-form">
                        @csrf
                        <button type="submit"class="btn-logout">ログアウト</button>
                    </form>

                @endauth    
               
           </nav>
        </div>  
      </div>
    </header>

    <main @class([
    'main-content' => !in_array(Route::currentRouteName(), ['login', 'register','admin.login'])
    ])>
    @yield('content')
</main>
</body>
</html>
