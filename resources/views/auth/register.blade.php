@extends('layouts.app')
@section('title', 'Регистрация · Habit Tracker')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Создать аккаунт</h1>
        <p class="subtitle">Начните вести привычки уже сегодня.</p>

        <form method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" required>
                <div class="hint">Латиница, цифры, _ и -</div>
                @error('login') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="phone">Телефон <span class="muted">(необязательно)</span></label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" placeholder="+7 (000) 000-00-00">
                @error('phone') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                <div class="hint">Минимум 8 символов</div>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Повторите пароль</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>

            <button class="btn" type="submit" style="width:100%">Создать аккаунт</button>
        </form>

        <div class="switch">
            Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>
        </div>
    </div>
</div>
@endsection
