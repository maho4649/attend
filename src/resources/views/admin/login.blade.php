@extends('layout.admin')

@section('content')

<div class="auth-form">
    <h2>管理者ログイン</h2>

    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf
         <label for="email">メールアドレス</label>
         <input type="text" name="email" id="email" value="{{ old('email') }}">
         @error('email')
             <p class="error-message">{{ $message }}</p>
        @enderror

         <label for="password">パスワード</label>
         <input type="password" name="password" id="password">
         @error('password')
             <p  class="error-message">{{ $message }}</p>
        @enderror
         
        <button  class="submit">管理者ログインする</button>
    </form>
</div>
@endsection
