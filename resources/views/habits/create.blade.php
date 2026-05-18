@extends('layouts.app')
@section('title', 'Новая привычка · Habit Tracker')

@section('content')
<div class="card" style="max-width:760px;margin:0 auto">
    <h1>Новая привычка</h1>
    <p class="muted mb-2">Например: «Тренировка 30 минут», «Чтение 20 страниц», «Без сахара 60 дней».</p>

    <form method="POST" action="{{ route('habits.store') }}">
        @csrf

        <div class="field">
            <label for="title">Название</label>
            <input id="title" name="title" type="text" value="{{ old('title') }}" required autofocus>
            @error('title') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="description">Описание <span class="muted">(необязательно)</span></label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
        </div>

        <div class="day-form">
            <div class="field">
                <label for="target_value">Числовая цель</label>
                <input id="target_value" name="target_value" type="number" min="0" max="100000" value="{{ old('target_value', 30) }}">
                <div class="hint">0 — без числового таргета</div>
            </div>
            <div class="field">
                <label for="unit">Единица</label>
                <input id="unit" name="unit" type="text" value="{{ old('unit', 'минут') }}" placeholder="минут / страниц / раз">
            </div>
            <div class="field">
                <label for="color">Цвет</label>
                <input id="color" name="color" type="color" value="{{ old('color', '#7c5cff') }}">
            </div>
            <div class="field">
                <label for="start_date">Дата старта</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', now()->toDateString()) }}">
                <div class="hint">С этого дня будет вестись календарь</div>
            </div>
        </div>

        <div class="day-form">
            <div class="field">
                <label for="duration_days">На сколько дней</label>
                <input id="duration_days" name="duration_days" type="number" min="0" max="3650" value="{{ old('duration_days', 30) }}">
                <div class="hint">Например, 21 / 30 / 66 / 100. 0 — бессрочно</div>
            </div>
            <div class="field">
                <label for="schedule">Расписание</label>
                <select id="schedule" name="schedule" class="status-select">
                    @foreach (\App\Models\Habit::SCHEDULES as $val => $label)
                        <option value="{{ $val }}" @selected(old('schedule', 'daily') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="hint">В какие дни выполнять привычку</div>
            </div>
        </div>

        <div class="row" style="margin-top:8px">
            <button type="submit" class="btn">Создать</button>
            <a href="{{ route('dashboard') }}" class="btn ghost">Отмена</a>
        </div>
    </form>
</div>
@endsection
