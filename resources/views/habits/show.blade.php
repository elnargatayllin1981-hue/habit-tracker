@extends('layouts.app')
@section('title', $habit->title . ' · Habit Tracker')

@section('content')
<section class="row between mb-3" style="flex-wrap:wrap;gap:14px">
    <div class="row" style="gap:14px">
        <span style="display:inline-block;width:14px;height:32px;border-radius:6px;background: {{ $habit->color }}"></span>
        <div>
            <h1 style="margin:0">{{ $habit->title }}</h1>
            <div class="muted" style="display:flex;gap:12px;flex-wrap:wrap">
                @if ($habit->target_value > 0)
                    <span>🎯 {{ $habit->target_value }} {{ $habit->unit }}/день</span>
                @endif
                <span>📅 {{ $habit->scheduleLabel() }}</span>
                @if ($habit->endDate())
                    <span>📆 до {{ $habit->endDate()->format('d.m.Y') }}</span>
                @endif
                @if ($habit->daysRemaining() !== null)
                    <span>⏳ {{ $habit->daysRemaining() }} {{ \Illuminate\Support\Str::plural('день|дня|дней', $habit->daysRemaining()) }} до конца</span>
                @endif
                <span>🔥 стрик {{ $stats['streak'] }}</span>
            </div>
        </div>
    </div>
    <div class="row" style="gap:8px"><a href="{{ route('habits.report', $habit) }}" class="btn ghost sm">📊 Отчёт</a><a href="{{ route('habits.edit', $habit) }}" class="btn ghost sm">⚙ Настройки</a></div>
</section>

@if ($habit->description)
    <p class="muted mb-3">{{ $habit->description }}</p>
@endif

{{-- Селектор периода для статистики --}}
<div class="card flat mb-3">
    <div class="row between" style="flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="margin:0 0 4px">Период статистики</h3>
            <div class="muted" style="font-size:.85rem">{{ $rangeLabel }} · с {{ \Carbon\Carbon::parse($from)->format('d.m.Y') }} по {{ \Carbon\Carbon::parse($to)->format('d.m.Y') }}</div>
        </div>
        <div class="range-tabs">
            <a href="{{ route('habits.show', ['habit' => $habit, 'range' => 7]) }}"   class="range-tab @if($range==='7') active @endif">7 дн.</a>
            <a href="{{ route('habits.show', ['habit' => $habit, 'range' => 30]) }}"  class="range-tab @if($range==='30') active @endif">30 дн.</a>
            <a href="{{ route('habits.show', ['habit' => $habit, 'range' => 90]) }}"  class="range-tab @if($range==='90') active @endif">90 дн.</a>
            <a href="{{ route('habits.show', ['habit' => $habit, 'range' => 'all']) }}" class="range-tab @if($range==='all') active @endif">Всё время</a>
        </div>
    </div>
    <form method="GET" action="{{ route('habits.show', $habit) }}" class="row" style="margin-top:12px;gap:10px;flex-wrap:wrap">
        <div class="row" style="gap:6px;align-items:center">
            <span class="muted" style="font-size:.85rem">Свой период:</span>
            <input type="date" name="from" value="{{ $from->toDateString() }}" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
            <span class="muted">—</span>
            <input type="date" name="to" value="{{ $to->toDateString() }}" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
        </div>
        <button class="btn ghost sm" type="submit">Применить</button>
    </form>
</div>

{{-- Статистика за выбранный период --}}
<div class="grid grid-4 mb-3">
    <div class="stat positive">
        <div class="label">Выполнено</div>
        <div class="value">{{ $stats['success'] }}</div>
        <div class="muted" style="font-size:.75rem">из {{ $stats['expected'] }} запланированных</div>
    </div>
    <div class="stat negative">
        <div class="label">Провалов</div>
        <div class="value">{{ $stats['fail'] }}</div>
    </div>
    <div class="stat neutral">
        <div class="label">Форс-мажор</div>
        <div class="value">{{ $stats['skipped'] }}</div>
    </div>
    <div class="stat">
        <div class="label">Успех</div>
        <div class="value">{{ $stats['success_rate'] }}%</div>
        <div class="muted" style="font-size:.75rem">от запланированных дней</div>
    </div>
</div>

{{-- Календарь-heatmap от старта --}}
<div class="card mb-3">
    <div class="row between" style="flex-wrap:wrap">
        <h2 style="margin:0">Календарь от старта</h2>
        <span class="muted" style="font-size:.85rem">
            {{ $calStart->format('d.m.Y') }}
            @if ($habit->endDate()) → {{ $habit->endDate()->format('d.m.Y') }} @endif
        </span>
    </div>
    <p class="muted" style="font-size:.85rem;margin:6px 0 12px">Кликните по дню, чтобы заполнить форму ниже. Серые ячейки — выходные по расписанию или дни вне периода.</p>

    <div class="heatmap-wrap">
        <div class="heatmap-weekdays">
            <span>Пн</span><span></span><span>Ср</span><span></span><span>Пт</span><span></span><span>Вс</span>
        </div>
        <div class="heatmap-grid" id="heatmap-grid">
            @foreach ($weeks as $week)
                <div class="hm-col">
                    @foreach ($week as $cell)
                        <div class="hm-cell {{ $cell['class'] }} @if($cell['is_today']) today @endif"
                             @if (in_array($cell['class'], ['success','fail','skipped','pending']))
                                data-day="{{ $cell['date'] }}"
                             @endif
                             title="{{ $cell['tooltip'] }}">
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="row mt-2" style="font-size:.75rem;color:var(--text-muted);gap:14px;flex-wrap:wrap">
        <span><span class="hm-legend success"></span> выполнено</span>
        <span><span class="hm-legend fail"></span> провал</span>
        <span><span class="hm-legend skipped"></span> форс-мажор</span>
        <span><span class="hm-legend pending"></span> запланирован, не отмечен</span>
        <span><span class="hm-legend future"></span> впереди</span>
        <span><span class="hm-legend rest"></span> не по расписанию</span>
        <span><span class="hm-legend off"></span> вне периода</span>
    </div>
</div>

{{-- Форма дня --}}
<div class="card mb-3">
    <h2>Отметить день</h2>
    <form method="POST" action="{{ route('habit-logs.upsert', $habit) }}">
        @csrf
        <div class="day-form">
            <div class="field">
                <label for="log_date">Дата</label>
                <input id="log_date" name="log_date" type="date" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="field">
                <label for="status">Статус</label>
                <select id="status" name="status" class="status-select" required>
                    <option value="success">+ &nbsp; Выполнено</option>
                    <option value="fail">− &nbsp; Провал</option>
                    <option value="skipped">∗ &nbsp; Форс-мажор / не смог</option>
                </select>
            </div>
            @if ($habit->target_value > 0)
            <div class="field">
                <label for="value">Сколько сделали ({{ $habit->unit }})</label>
                <input id="value" name="value" type="number" min="0" placeholder="напр. 25">
            </div>
            @endif
        </div>

        <div class="field">
            <label for="failure_note">Что не получилось / почему провал</label>
            <textarea id="failure_note" name="failure_note" placeholder="Опишите, что помешало. Это поможет AI подсказать улучшения."></textarea>
        </div>

        <div class="field">
            <label for="improvement_note">Как можно улучшить</label>
            <textarea id="improvement_note" name="improvement_note" placeholder="Ваши идеи, как сделать лучше завтра."></textarea>
        </div>

        <button class="btn" type="submit">Сохранить день</button>
    </form>
</div>

{{-- Графики --}}
<div class="grid grid-2 mb-3">
    <div class="card">
        <h2>{{ $chart['aggregated'] ? 'Динамика по неделям' : 'Динамика по дням' }}</h2>
        <canvas id="chart-trend" height="180"></canvas>
    </div>
    <div class="card">
        <h2>Распределение статусов</h2>
        <canvas id="chart-pie" height="180"></canvas>
    </div>
</div>

{{-- AI подсказки --}}
<div class="card ai-card mb-3">
    <div class="row between">
        <h2>🤖 Совет от Claude</h2>
        <button class="btn sm" type="button" data-ai-generate="{{ route('ai.generate', $habit) }}">
            <span class="ai-spinner" data-ai-spinner style="display:none"></span>
            Сгенерировать совет
        </button>
    </div>
    <p class="muted" style="font-size:.85rem">
        Claude проанализирует вашу статистику и заметки о провалах
        и предложит конкретные шаги.
    </p>
    <div class="ai-output" data-ai-output>Ещё не запрашивали — нажмите кнопку выше.</div>
    <div class="muted mt-2" style="font-size:.75rem" data-ai-stamp></div>
</div>

{{-- Последние провалы --}}
@if ($failures->isNotEmpty())
<div class="card mb-3">
    <h2>Записи о провалах в этом периоде</h2>
    @foreach ($failures as $f)
        <div style="padding:10px 0;border-bottom:1px solid var(--border)">
            <div class="muted" style="font-size:.8rem">{{ $f->log_date->format('d.m.Y') }}</div>
            <div>{{ $f->failure_note }}</div>
        </div>
    @endforeach
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = @json($chart);

    const css = getComputedStyle(document.documentElement);
    const cs  = css.getPropertyValue('--success').trim() || '#2ecc71';
    const cf  = css.getPropertyValue('--fail').trim()    || '#ff5d6c';
    const ck  = css.getPropertyValue('--skipped').trim() || '#f6b73c';
    const tm  = css.getPropertyValue('--text-muted').trim() || '#888';
    const br  = css.getPropertyValue('--border').trim()       || '#222';

    const ctxTrend = document.getElementById('chart-trend');
    if (ctxTrend && window.Chart) {
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Выполнено', data: data.success, backgroundColor: cs, stack: 's' },
                    { label: 'Провал',    data: data.fail,    backgroundColor: cf, stack: 's' },
                    { label: 'Форс-мажор',data: data.skipped, backgroundColor: ck, stack: 's' },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: tm } } },
                scales: {
                    x: { stacked: true, ticks: { color: tm }, grid: { color: br } },
                    y: { stacked: true, beginAtZero: true, ticks: { color: tm, stepSize: 1 }, grid: { color: br } },
                },
            },
        });
    }

    const ctxPie = document.getElementById('chart-pie');
    if (ctxPie && window.Chart) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Выполнено', 'Провал', 'Форс-мажор'],
                datasets: [{
                    data: [data.pie.success, data.pie.fail, data.pie.skipped],
                    backgroundColor: [cs, cf, ck],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { labels: { color: tm } } },
            },
        });
    }
});
</script>
@endsection
