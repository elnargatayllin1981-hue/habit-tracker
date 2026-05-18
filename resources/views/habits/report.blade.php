@extends('layouts.app')
@section('title', 'Отчёт · ' . $habit->title)

@section('content')
<div class="report-actions no-print mb-3">
    <a href="{{ route('habits.show', $habit) }}" class="btn ghost sm">← К привычке</a>
    <button class="btn ghost sm" onclick="window.print()">🖨 Распечатать / PDF</button>
</div>

<div class="report-sheet">
    <header class="report-head">
        <div class="row" style="gap:14px">
            <span style="display:inline-block;width:14px;height:36px;border-radius:6px;background: {{ $habit->color }}"></span>
            <div>
                <div class="muted" style="font-size:.85rem">Отчёт по привычке</div>
                <h1 style="margin:0">{{ $habit->title }}</h1>
            </div>
        </div>
        <div class="report-period">
            <div>📅 {{ $start->format('d.m.Y') }} — {{ $end->format('d.m.Y') }}</div>
            <div>⏱ {{ $start->diffInDays($end) + 1 }} {{ \Illuminate\Support\Str::plural('день|дня|дней', $start->diffInDays($end) + 1) }}</div>
            <div>📋 {{ $habit->scheduleLabel() }}</div>
            @if ($isCompleted)
                <div class="report-badge done">✅ Завершена</div>
            @else
                <div class="report-badge active">▶ Активна</div>
            @endif
        </div>
    </header>

    @if ($habit->description)
        <div class="muted mb-3" style="font-size:.92rem">{{ $habit->description }}</div>
    @endif

    {{-- Ключевые метрики --}}
    <section class="mb-3">
        <h2>Ключевые метрики</h2>
        <div class="grid grid-4">
            <div class="metric metric-good">
                <div class="metric-icon">✅</div>
                <div class="metric-value">{{ $stats['success'] }}</div>
                <div class="metric-label">Выполнено<br><span class="muted">из {{ $stats['expected'] }}</span></div>
            </div>
            <div class="metric metric-bad">
                <div class="metric-icon">✗</div>
                <div class="metric-value">{{ $stats['fail'] }}</div>
                <div class="metric-label">Провалов</div>
            </div>
            <div class="metric metric-warn">
                <div class="metric-icon">∗</div>
                <div class="metric-value">{{ $stats['skipped'] }}</div>
                <div class="metric-label">Форс-мажоров</div>
            </div>
            <div class="metric metric-accent">
                <div class="metric-icon">🎯</div>
                <div class="metric-value">{{ $stats['success_rate'] }}%</div>
                <div class="metric-label">Успех<br><span class="muted">от плана</span></div>
            </div>
        </div>
        <div class="grid grid-2 mt-2">
            <div class="metric metric-fire">
                <div class="metric-icon">🔥</div>
                <div class="metric-value">{{ $longestStreak }}</div>
                <div class="metric-label">Лучший стрик подряд</div>
            </div>
            <div class="metric">
                <div class="metric-icon">📅</div>
                <div class="metric-value">{{ $stats['streak'] }}</div>
                <div class="metric-label">Текущий стрик</div>
            </div>
        </div>
    </section>

    {{-- Ачивки --}}
    @if (count($achievements))
        <section class="mb-3">
            <h2>Достижения</h2>
            <div class="grid grid-3">
                @foreach ($achievements as $a)
                    <div class="achievement">
                        <div class="ach-icon">{{ $a['icon'] }}</div>
                        <div>
                            <div class="ach-title">{{ $a['title'] }}</div>
                            <div class="ach-desc">{{ $a['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Дни недели --}}
    <section class="mb-3">
        <h2>Эффективность по дням недели</h2>
        <p class="muted" style="font-size:.85rem">Процент успеха в каждый день недели — где у вас сильнее и слабее.</p>
        <table class="report-table">
            <thead>
                <tr>
                    <th>День</th>
                    <th>Выполнено</th>
                    <th>Запланировано</th>
                    <th>%</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($weekdayStats as $dow => $w)
                    @if ($w['expected'] > 0)
                        <tr @if ($dow === $bestDow) class="row-best" @elseif ($dow === $worstDow) class="row-worst" @endif>
                            <td>{{ $weekdayNames[$dow] }}</td>
                            <td>{{ $w['success'] }}</td>
                            <td>{{ $w['expected'] }}</td>
                            <td><strong>{{ $w['rate'] }}%</strong></td>
                            <td>
                                @if ($dow === $bestDow) 🌟 лучший день
                                @elseif ($dow === $worstDow) ⚠️ слабый день
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- Топ провалов --}}
    @if ($topFailures->isNotEmpty())
        <section class="mb-3">
            <h2>Заметки о провалах</h2>
            @foreach ($topFailures as $f)
                <div class="report-failure">
                    <div class="muted" style="font-size:.75rem">{{ $f->log_date->format('d.m.Y, dddd') }}</div>
                    <div>{{ $f->failure_note }}</div>
                    @if ($f->improvement_note)
                        <div class="report-improvement">💡 {{ $f->improvement_note }}</div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    {{-- AI-резюме --}}
    <section class="card ai-card mb-3 no-print">
        <div class="row between">
            <h2 style="margin:0">🤖 Резюме от Claude</h2>
            <button class="btn sm" type="button" data-ai-generate="{{ route('ai.generate', $habit) }}">
                <span class="ai-spinner" data-ai-spinner style="display:none"></span>
                Сгенерировать
            </button>
        </div>
        <div class="ai-output" data-ai-output>Нажмите кнопку — Claude обобщит ваш результат и подскажет, как закрепить или продолжить.</div>
        <div class="muted mt-2" style="font-size:.75rem" data-ai-stamp></div>
    </section>

    <footer class="report-footer no-print">
        <span class="muted" style="font-size:.8rem">Сформировано {{ now()->format('d.m.Y H:i') }} · Habit Tracker</span>
    </footer>
</div>
@endsection
