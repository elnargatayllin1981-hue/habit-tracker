@extends('layouts.app')
@section('title', 'Дашборд · Habit Tracker')

@section('content')
<section class="mb-3" style="display:flex;justify-content:space-between;align-items:end;gap:16px;flex-wrap:wrap">
    <div>
        <h1>Привет, {{ auth()->user()->login }} 👋</h1>
        <p class="muted">Сегодня {{ now()->isoFormat('dddd, D MMMM') }}.</p>
    </div>
    <div class="row" style="gap:8px;flex-wrap:wrap">
        <a href="{{ route('suggestions.index') }}" class="btn ghost sm">💡 Идеи привычек</a>
        <a href="{{ route('habits.create') }}" class="btn">+ Своя привычка</a>
    </div>
</section>

{{-- Сводная статистика --}}
<div class="grid grid-4 mb-3">
    <div class="stat positive">
        <div class="label">Выполнено · 30 дн.</div>
        <div class="value">{{ $summary['success'] }}</div>
    </div>
    <div class="stat negative">
        <div class="label">Провалов · 30 дн.</div>
        <div class="value">{{ $summary['fail'] }}</div>
    </div>
    <div class="stat neutral">
        <div class="label">Пропусков · 30 дн.</div>
        <div class="value">{{ $summary['skipped'] }}</div>
    </div>
    <div class="stat">
        <div class="label">Успех, %</div>
        <div class="value">{{ $summary['success_rate'] }}%</div>
    </div>
</div>

{{-- Сегодняшние задачи --}}
@if ($todaysHabits->isNotEmpty())
<div class="card mb-3">
    <h2>На сегодня · {{ $todaysHabits->count() }} {{ \Illuminate\Support\Str::plural('задача|задачи|задач', $todaysHabits->count()) }}</h2>
    @foreach ($todaysHabits as $h)
        <div class="today-row">
            <div class="row" style="gap:12px;flex:1;min-width:0">
                <span class="swatch" style="background: {{ $h->color }}"></span>
                <div style="min-width:0">
                    <div style="font-weight:600">{{ $h->title }}</div>
                    <div class="meta">
                        @if ($h->target_value > 0) {{ $h->target_value }} {{ $h->unit }} · @endif
                        {{ $h->schedule_label }}
                    </div>
                </div>
            </div>
            <div class="row" style="gap:8px;flex-wrap:wrap">
                @if ($h->today_log)
                    <span class="pill {{ $h->today_log->status }}">
                        @if ($h->today_log->status === 'success') ✓ выполнено
                        @elseif ($h->today_log->status === 'fail') ✗ провал
                        @else ∗ форс-мажор
                        @endif
                    </span>
                @else
                    <form method="POST" action="{{ route('habit-logs.upsert', $h) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="log_date" value="{{ now()->toDateString() }}">
                        <button class="btn sm" name="status" value="success">+ Готово</button>
                    </form>
                    <form method="POST" action="{{ route('habit-logs.upsert', $h) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="log_date" value="{{ now()->toDateString() }}">
                        <button class="btn ghost sm" name="status" value="skipped">∗ Пропуск</button>
                    </form>
                @endif
                <a href="{{ route('habits.show', $h) }}" class="btn ghost sm">→</a>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- Фильтры и сортировка --}}
<div class="row between mb-2" style="flex-wrap:wrap;gap:12px">
    <div class="row" style="gap:6px;flex-wrap:wrap">
        <span class="muted" style="font-size:.8rem">Показать:</span>
        @foreach (['active' => 'Активные', 'today' => 'На сегодня', 'completed' => 'Завершённые', 'archived' => 'Архив', 'all' => 'Все'] as $key => $label)
            <a href="?filter={{ $key }}&sort={{ $sort }}" class="range-tab @if($filter === $key) active @endif">{{ $label }}</a>
        @endforeach
    </div>
    <div class="row" style="gap:6px;flex-wrap:wrap">
        <span class="muted" style="font-size:.8rem">Сортировка:</span>
        @foreach (['recent' => 'Новые', 'streak' => 'Стрик', 'success' => 'Успех', 'progress' => 'Прогресс'] as $key => $label)
            <a href="?filter={{ $filter }}&sort={{ $key }}" class="range-tab @if($sort === $key) active @endif">{{ $label }}</a>
        @endforeach
    </div>
</div>

@if ($habits->isEmpty())
    <div class="card center">
        <h2 style="margin-bottom:.5rem">Здесь пока пусто</h2>
        <p class="muted mb-2">Создайте свою привычку или выберите готовый шаблон.</p>
        <div class="row" style="justify-content:center;gap:10px;flex-wrap:wrap">
            <a class="btn" href="{{ route('habits.create') }}">+ Своя привычка</a>
            <a class="btn ghost" href="{{ route('suggestions.index') }}">💡 Каталог идей</a>
        </div>
    </div>
@else
    @foreach ($habits as $habit)
        @php $s = $habit->stats_30; @endphp
        <a href="{{ route('habits.show', $habit) }}" class="habit-row">
            <div class="left">
                <span class="swatch" style="background: {{ $habit->color }}"></span>
                <div style="min-width:0;flex:1">
                    <div style="font-weight:600;display:flex;align-items:center;gap:8px">
                        {{ $habit->title }}
                        @if ($habit->is_completed)
                            <span class="pill success" style="font-size:.7rem">✓ завершена</span>
                        @endif
                    </div>
                    <div class="meta" style="display:flex;gap:10px;flex-wrap:wrap">
                        @if ($habit->target_value > 0)
                            <span>🎯 {{ $habit->target_value }} {{ $habit->unit }}</span>
                        @endif
                        <span>📅 {{ $habit->schedule_label }}</span>
                        @if ($habit->days_remaining !== null)
                            <span>⏳ {{ $habit->days_remaining }} {{ \Illuminate\Support\Str::plural('день|дня|дней', $habit->days_remaining) }}</span>
                        @endif
                        @if ($habit->today_scheduled && !$habit->today_log)
                            <span class="badge-today">сегодня</span>
                        @endif
                        <span>🔥 {{ $s['streak'] }}</span>
                    </div>
                    @if ($habit->progress_pct !== null)
                        <div class="progress-bar" style="margin-top:8px">
                            <div class="progress-fill" style="width:{{ $habit->progress_pct }}%; background: {{ $habit->color }}"></div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="stats-mini">
                <span class="pill success">+ {{ $s['success'] }}</span>
                <span class="pill fail">− {{ $s['fail'] }}</span>
                <span class="pill skipped">∗ {{ $s['skipped'] }}</span>
                @if ($habit->is_completed)
                    <span class="btn ghost sm" style="pointer-events:none">📊 Отчёт</span>
                @endif
            </div>
        </a>
    @endforeach
@endif
@endsection
