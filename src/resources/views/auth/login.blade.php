@extends('layout.app')

@section('content')

<div class="auth-form">
    <h2>ログイン</h2>

    <form method="POST" action="{{ route('login') }}">
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
         
        <button  class="submit">ログイン</button>
    </form>
    <a href="{{ route('register') }}" >会員登録はこちら</a>
</div>
@endsection
