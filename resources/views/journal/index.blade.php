@extends('layouts.app')
@section('title', 'Журнал · Habit Tracker')

@section('content')
<section class="mb-3">
    <h1>Журнал отметок</h1>
    <p class="muted">Все ваши выполненные, проваленные и пропущенные дни по всем привычкам за выбранный период.</p>
</section>

{{-- Фильтры --}}
<form method="GET" action="{{ route('journal.index') }}" class="card flat mb-3">
    <div class="row" style="flex-wrap:wrap;gap:14px;align-items:end">
        <div class="field" style="margin:0;flex:1 1 200px">
            <label>Период</label>
            <div class="range-tabs">
                <a href="?range=7"   class="range-tab @if($range==='7')   active @endif">7 дн.</a>
                <a href="?range=30"  class="range-tab @if($range==='30')  active @endif">30 дн.</a>
                <a href="?range=90"  class="range-tab @if($range==='90')  active @endif">90 дн.</a>
                <a href="?range=all" class="range-tab @if($range==='all') active @endif">Всё</a>
            </div>
        </div>
        <div class="field" style="margin:0">
            <label for="status">Статус</label>
            <select id="status" name="status" class="status-select" onchange="this.form.submit()">
                <option value="">Все</option>
                <option value="success" @selected($statusF==='success')>+ Выполнено</option>
                <option value="fail"    @selected($statusF==='fail')>− Провал</option>
                <option value="skipped" @selected($statusF==='skipped')>∗ Форс-мажор</option>
            </select>
        </div>
        <div class="field" style="margin:0">
            <label for="habit_id">Привычка</label>
            <select id="habit_id" name="habit_id" class="status-select" onchange="this.form.submit()">
                <option value="">Все</option>
                @foreach ($habits as $h)
                    <option value="{{ $h->id }}" @selected((string)$habitIdF === (string)$h->id)>{{ $h->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin:0">
            <label>Свой период</label>
            <div class="row" style="gap:6px">
                <input type="date" name="from" value="{{ request('from') }}" style="padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
                <span class="muted">–</span>
                <input type="date" name="to" value="{{ request('to') }}" style="padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
                <button class="btn ghost sm" type="submit">OK</button>
            </div>
        </div>
        <div class="field" style="margin:0">
            <a class="btn ghost sm" href="{{ route('journal.export', request()->query()) }}">📥 CSV</a>
        </div>
    </div>
    <div class="muted mt-2" style="font-size:.85rem">{{ $rangeLabel }} · с {{ $from->format('d.m.Y') }} по {{ $to->format('d.m.Y') }}</div>
</form>

{{-- Сводка --}}
<div class="grid grid-4 mb-3">
    <div class="stat positive">
        <div class="label">Выполнено</div>
        <div class="value">{{ $summary['success'] }}</div>
    </div>
    <div class="stat negative">
        <div class="label">Провалов</div>
        <div class="value">{{ $summary['fail'] }}</div>
    </div>
    <div class="stat neutral">
        <div class="label">Форс-мажор</div>
        <div class="value">{{ $summary['skipped'] }}</div>
    </div>
    <div class="stat">
        <div class="label">Всего отметок</div>
        <div class="value">{{ $summary['total'] }}</div>
    </div>
</div>

{{-- Список по датам --}}
@if ($grouped->isEmpty())
    <div class="card center">
        <h2 style="margin-bottom:.5rem">За этот период отметок нет</h2>
        <p class="muted">Попробуйте расширить период или снять фильтры.</p>
        <a class="btn ghost" href="{{ route('journal.index') }}">Сбросить фильтры</a>
    </div>
@else
    @foreach ($grouped as $date => $items)
        @php $d = \Carbon\Carbon::parse($date); @endphp
        <div class="card mb-2">
            <h3 style="margin:0 0 12px;display:flex;justify-content:space-between;align-items:center">
                <span>{{ $d->isoFormat('dddd, D MMMM') }}</span>
                <span class="muted" style="font-weight:400;font-size:.85rem">{{ $d->format('d.m.Y') }}</span>
            </h3>
            @foreach ($items as $log)
                <div class="journal-row">
                    <div class="row" style="gap:10px;flex:1;min-width:0">
                        <span class="swatch" style="background: {{ $log->habit->color }};width:8px;height:24px"></span>
                        <div style="min-width:0;flex:1">
                            <div style="font-weight:600">{{ $log->habit->title }}</div>
                            @if ($log->value !== null)
                                <div class="meta" style="font-size:.8rem">{{ $log->value }} {{ $log->habit->unit }}</div>
                            @endif
                            @if ($log->failure_note)
                                <div class="journal-note">⚠️ {{ $log->failure_note }}</div>
                            @endif
                            @if ($log->improvement_note)
                                <div class="journal-note positive">💡 {{ $log->improvement_note }}</div>
                            @endif
                        </div>
                    </div>
                    <span class="pill {{ $log->status }}">
                        @if ($log->status === 'success') + выполнено
                        @elseif ($log->status === 'fail') − провал
                        @else ∗ форс-мажор
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endforeach
@endif
@endsection
