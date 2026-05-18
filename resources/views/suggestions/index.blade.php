@extends('layouts.app')
@section('title', 'Предложения · Habit Tracker')

@section('content')
<section class="mb-3">
    <h1>Предложения по формированию привычек</h1>
    <p class="muted">Готовые шаблоны от лёгких к сложным. Нажмите «Принять» — привычка сразу попадёт в ваш дашборд с подходящим расписанием и длительностью.</p>
</section>

{{-- Пилюли категорий для быстрой навигации --}}
<div class="cat-pills mb-3">
    @foreach ($grouped as $key => $g)
        <a href="#cat-{{ $key }}" class="cat-pill" style="--cat-color: {{ $g['meta']['color'] }}">
            {{ $g['meta']['icon'] }} {{ $g['meta']['title'] }}
            <span class="cat-count">{{ count($g['items']) }}</span>
        </a>
    @endforeach
</div>

@foreach ($grouped as $key => $g)
    <section id="cat-{{ $key }}" class="mb-3">
        <h2 style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.4rem">{{ $g['meta']['icon'] }}</span>
            {{ $g['meta']['title'] }}
        </h2>
        <div class="grid grid-3 mt-2">
            @foreach ($g['items'] as $t)
                <div class="card sug-card" style="--accent-line: {{ $t['color'] }}">
                    <div class="row between" style="margin-bottom:8px">
                        <h3 style="margin:0">{{ $t['title'] }}</h3>
                        @if ($t['adopted'])
                            <span class="pill success">✓ принято</span>
                        @endif
                    </div>
                    <p style="font-size:.9rem;color:var(--text-muted);margin:0 0 12px">{{ $t['description'] }}</p>

                    <div class="sug-meta">
                        @if ($t['target_value'] > 0)
                            <span>🎯 {{ $t['target_value'] }} {{ $t['unit'] }}</span>
                        @else
                            <span>🎯 без таргета</span>
                        @endif
                        <span>📅 {{ \App\Models\Habit::SCHEDULES[$t['schedule']] ?? $t['schedule'] }}</span>
                        <span>⏳ {{ $t['duration_days'] }} {{ \Illuminate\Support\Str::plural('день|дня|дней', $t['duration_days']) }}</span>
                    </div>

                    @if (!empty($t['why']))
                        <div class="sug-why">💡 {{ $t['why'] }}</div>
                    @endif

                    <form method="POST" action="{{ route('suggestions.adopt', $t['slug']) }}" style="margin-top:14px">
                        @csrf
                        <button class="btn sm" type="submit" @if ($t['adopted']) disabled style="opacity:.5;cursor:not-allowed" @endif>
                            @if ($t['adopted']) Уже у вас @else + Принять @endif
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>
@endforeach

@endsection
