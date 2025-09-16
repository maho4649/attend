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
                    
                    <a href="{{ route('admin.attendance.index')}}">勤怠一覧</a>
                    <a href="{{ route('admin.staff.index')}}">スタッフ一覧</a> 
                    <a href="{{ route('admin.stamp_correction_request.index')}}">申請一覧</a>

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
    'main-content' => !in_array(Route::currentRouteName(), ['admin.login'])
    ])>
    @yield('content')
</main>
</body>
</html>
