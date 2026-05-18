@extends('layouts.app')
@section('title', 'Редактирование · ' . $habit->title)

@section('content')
<div class="card" style="max-width:760px;margin:0 auto">
    <h1>Редактирование привычки</h1>

    <form method="POST" action="{{ route('habits.update', $habit) }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="title">Название</label>
            <input id="title" name="title" type="text" value="{{ old('title', $habit->title) }}" required>
            @error('title') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="description">Описание</label>
            <textarea id="description" name="description">{{ old('description', $habit->description) }}</textarea>
        </div>

        <div class="day-form">
            <div class="field">
                <label for="target_value">Числовая цель</label>
                <input id="target_value" name="target_value" type="number" min="0" max="100000" value="{{ old('target_value', $habit->target_value) }}">
            </div>
            <div class="field">
                <label for="unit">Единица</label>
                <input id="unit" name="unit" type="text" value="{{ old('unit', $habit->unit) }}">
            </div>
            <div class="field">
                <label for="color">Цвет</label>
                <input id="color" name="color" type="color" value="{{ old('color', $habit->color) }}">
            </div>
            <div class="field">
                <label for="start_date">Дата старта</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', optional($habit->start_date)->toDateString()) }}">
            </div>
        </div>

        <div class="day-form">
            <div class="field">
                <label for="duration_days">На сколько дней</label>
                <input id="duration_days" name="duration_days" type="number" min="0" max="3650" value="{{ old('duration_days', $habit->duration_days) }}">
                <div class="hint">0 — бессрочно</div>
            </div>
            <div class="field">
                <label for="schedule">Расписание</label>
                <select id="schedule" name="schedule" class="status-select">
                    @foreach (\App\Models\Habit::SCHEDULES as $val => $label)
                        <option value="{{ $val }}" @selected(old('schedule', $habit->schedule) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <label class="row" style="margin-bottom:18px;font-size:.95rem">
            <input type="checkbox" name="is_archived" value="1" style="width:auto" @checked($habit->is_archived)>
            Архивировать
        </label>

        <div class="row between">
            <div class="row">
                <button type="submit" class="btn">Сохранить</button>
                <a href="{{ route('habits.show', $habit) }}" class="btn ghost">Отмена</a>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('habits.destroy', $habit) }}" class="mt-3"
          onsubmit="return confirm('Удалить эту привычку? Все отметки также будут удалены.')">
        @csrf
        @method('DELETE')
        <button class="btn danger sm" type="submit">Удалить привычку</button>
    </form>
</div>
@endsection
