@extends('layouts.app')
@section('title', 'Вход · Habit Tracker')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>С возвращением</h1>
        <p class="subtitle">Введите логин и пароль, чтобы продолжить.</p>

        <form method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" autofocus required>
                @error('login') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <label class="row" style="font-size:.9rem; color:var(--text-muted); margin-bottom:18px">
                <input type="checkbox" name="remember" value="1" style="width:auto"> Запомнить меня
            </label>

            <button class="btn" type="submit" style="width:100%">Войти</button>
        </form>

        <div class="switch">
            Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a>
        </div>
    </div>
</div>
@endsection
