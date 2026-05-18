<!DOCTYPE html>
<html lang="ru" data-theme="{{ auth()->check() ? auth()->user()->theme : 'auto' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Habit Tracker')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
<div class="shell">
    <header class="topbar no-print">
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="brand">
            <span class="dot"></span> Habit Tracker
        </a>
        <nav class="nav">
            @auth
                <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) class="active" @endif>Дашборд</a>
                <a href="{{ route('suggestions.index') }}" @if(request()->routeIs('suggestions.*')) class="active" @endif>💡 Идеи</a>
                <a href="{{ route('journal.index') }}" @if(request()->routeIs('journal.*')) class="active" @endif>📔 Журнал</a>
                <a href="{{ route('habits.create') }}" class="btn sm">+ Привычка</a>
                <span class="muted user-chip" style="font-size:.85rem">{{ auth()->user()->login }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button class="btn ghost sm" type="submit">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}">Войти</a>
                <a href="{{ route('register') }}" class="btn sm">Регистрация</a>
            @endauth
            <div class="theme-toggle" role="group" aria-label="Тема">
                <button data-theme="light" type="button" title="Светлая">☀</button>
                <button data-theme="dark"  type="button" title="Тёмная">☾</button>
                <button data-theme="auto"  type="button" title="Авто">A</button>
            </div>
        </nav>
    </header>

    @if (session('flash'))
        <div class="flash">{{ session('flash') }}</div>
    @endif

    @yield('content')
</div>

{{-- Тост-контейнер --}}
<div class="toasts" id="toasts" aria-live="polite"></div>
</body>
</html>
