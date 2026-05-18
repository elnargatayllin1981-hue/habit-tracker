<?php $__env->startSection('title', 'Новая привычка · Habit Tracker'); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="max-width:760px;margin:0 auto">
    <h1>Новая привычка</h1>
    <p class="muted mb-2">Например: «Тренировка 30 минут», «Чтение 20 страниц», «Без сахара 60 дней».</p>

    <form method="POST" action="<?php echo e(route('habits.store')); ?>">
        <?php echo csrf_field(); ?>

        <div class="field">
            <label for="title">Название</label>
            <input id="title" name="title" type="text" value="<?php echo e(old('title')); ?>" required autofocus>
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field">
            <label for="description">Описание <span class="muted">(необязательно)</span></label>
            <textarea id="description" name="description"><?php echo e(old('description')); ?></textarea>
        </div>

        <div class="day-form">
            <div class="field">
                <label for="target_value">Числовая цель</label>
                <input id="target_value" name="target_value" type="number" min="0" max="100000" value="<?php echo e(old('target_value', 30)); ?>">
                <div class="hint">0 — без числового таргета</div>
            </div>
            <div class="field">
                <label for="unit">Единица</label>
                <input id="unit" name="unit" type="text" value="<?php echo e(old('unit', 'минут')); ?>" placeholder="минут / страниц / раз">
            </div>
            <div class="field">
                <label for="color">Цвет</label>
                <input id="color" name="color" type="color" value="<?php echo e(old('color', '#7c5cff')); ?>">
            </div>
            <div class="field">
                <label for="start_date">Дата старта</label>
                <input id="start_date" name="start_date" type="date" value="<?php echo e(old('start_date', now()->toDateString())); ?>">
                <div class="hint">С этого дня будет вестись календарь</div>
            </div>
        </div>

        <div class="day-form">
            <div class="field">
                <label for="duration_days">На сколько дней</label>
                <input id="duration_days" name="duration_days" type="number" min="0" max="3650" value="<?php echo e(old('duration_days', 30)); ?>">
                <div class="hint">Например, 21 / 30 / 66 / 100. 0 — бессрочно</div>
            </div>
            <div class="field">
                <label for="schedule">Расписание</label>
                <select id="schedule" name="schedule" class="status-select">
                    <?php $__currentLoopData = \App\Models\Habit::SCHEDULES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php if(old('schedule', 'daily') === $val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div class="hint">В какие дни выполнять привычку</div>
            </div>
        </div>

        <div class="row" style="margin-top:8px">
            <button type="submit" class="btn">Создать</button>
            <a href="<?php echo e(route('dashboard')); ?>" class="btn ghost">Отмена</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/habits/create.blade.php ENDPATH**/ ?>