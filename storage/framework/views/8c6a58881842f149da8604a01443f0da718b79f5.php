<?php $__env->startSection('title', 'Дашборд · Habit Tracker'); ?>

<?php $__env->startSection('content'); ?>
<section class="mb-3" style="display:flex;justify-content:space-between;align-items:end;gap:16px;flex-wrap:wrap">
    <div>
        <h1>Привет, <?php echo e(auth()->user()->login); ?> 👋</h1>
        <p class="muted">Сегодня <?php echo e(now()->isoFormat('dddd, D MMMM')); ?>.</p>
    </div>
    <div class="row" style="gap:8px;flex-wrap:wrap">
        <a href="<?php echo e(route('suggestions.index')); ?>" class="btn ghost sm">💡 Идеи привычек</a>
        <a href="<?php echo e(route('habits.create')); ?>" class="btn">+ Своя привычка</a>
    </div>
</section>


<div class="grid grid-4 mb-3">
    <div class="stat positive">
        <div class="label">Выполнено · 30 дн.</div>
        <div class="value"><?php echo e($summary['success']); ?></div>
    </div>
    <div class="stat negative">
        <div class="label">Провалов · 30 дн.</div>
        <div class="value"><?php echo e($summary['fail']); ?></div>
    </div>
    <div class="stat neutral">
        <div class="label">Пропусков · 30 дн.</div>
        <div class="value"><?php echo e($summary['skipped']); ?></div>
    </div>
    <div class="stat">
        <div class="label">Успех, %</div>
        <div class="value"><?php echo e($summary['success_rate']); ?>%</div>
    </div>
</div>


<?php if($todaysHabits->isNotEmpty()): ?>
<div class="card mb-3">
    <h2>На сегодня · <?php echo e($todaysHabits->count()); ?> <?php echo e(\Illuminate\Support\Str::plural('задача|задачи|задач', $todaysHabits->count())); ?></h2>
    <?php $__currentLoopData = $todaysHabits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="today-row">
            <div class="row" style="gap:12px;flex:1;min-width:0">
                <span class="swatch" style="background: <?php echo e($h->color); ?>"></span>
                <div style="min-width:0">
                    <div style="font-weight:600"><?php echo e($h->title); ?></div>
                    <div class="meta">
                        <?php if($h->target_value > 0): ?> <?php echo e($h->target_value); ?> <?php echo e($h->unit); ?> · <?php endif; ?>
                        <?php echo e($h->schedule_label); ?>

                    </div>
                </div>
            </div>
            <div class="row" style="gap:8px;flex-wrap:wrap">
                <?php if($h->today_log): ?>
                    <span class="pill <?php echo e($h->today_log->status); ?>">
                        <?php if($h->today_log->status === 'success'): ?> ✓ выполнено
                        <?php elseif($h->today_log->status === 'fail'): ?> ✗ провал
                        <?php else: ?> ∗ форс-мажор
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('habit-logs.upsert', $h)); ?>" style="display:inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="log_date" value="<?php echo e(now()->toDateString()); ?>">
                        <button class="btn sm" name="status" value="success">+ Готово</button>
                    </form>
                    <form method="POST" action="<?php echo e(route('habit-logs.upsert', $h)); ?>" style="display:inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="log_date" value="<?php echo e(now()->toDateString()); ?>">
                        <button class="btn ghost sm" name="status" value="skipped">∗ Пропуск</button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo e(route('habits.show', $h)); ?>" class="btn ghost sm">→</a>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="row between mb-2" style="flex-wrap:wrap;gap:12px">
    <div class="row" style="gap:6px;flex-wrap:wrap">
        <span class="muted" style="font-size:.8rem">Показать:</span>
        <?php $__currentLoopData = ['active' => 'Активные', 'today' => 'На сегодня', 'completed' => 'Завершённые', 'archived' => 'Архив', 'all' => 'Все']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="?filter=<?php echo e($key); ?>&sort=<?php echo e($sort); ?>" class="range-tab <?php if($filter === $key): ?> active <?php endif; ?>"><?php echo e($label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="row" style="gap:6px;flex-wrap:wrap">
        <span class="muted" style="font-size:.8rem">Сортировка:</span>
        <?php $__currentLoopData = ['recent' => 'Новые', 'streak' => 'Стрик', 'success' => 'Успех', 'progress' => 'Прогресс']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="?filter=<?php echo e($filter); ?>&sort=<?php echo e($key); ?>" class="range-tab <?php if($sort === $key): ?> active <?php endif; ?>"><?php echo e($label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<?php if($habits->isEmpty()): ?>
    <div class="card center">
        <h2 style="margin-bottom:.5rem">Здесь пока пусто</h2>
        <p class="muted mb-2">Создайте свою привычку или выберите готовый шаблон.</p>
        <div class="row" style="justify-content:center;gap:10px;flex-wrap:wrap">
            <a class="btn" href="<?php echo e(route('habits.create')); ?>">+ Своя привычка</a>
            <a class="btn ghost" href="<?php echo e(route('suggestions.index')); ?>">💡 Каталог идей</a>
        </div>
    </div>
<?php else: ?>
    <?php $__currentLoopData = $habits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $habit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $s = $habit->stats_30; ?>
        <a href="<?php echo e(route('habits.show', $habit)); ?>" class="habit-row">
            <div class="left">
                <span class="swatch" style="background: <?php echo e($habit->color); ?>"></span>
                <div style="min-width:0;flex:1">
                    <div style="font-weight:600;display:flex;align-items:center;gap:8px">
                        <?php echo e($habit->title); ?>

                        <?php if($habit->is_completed): ?>
                            <span class="pill success" style="font-size:.7rem">✓ завершена</span>
                        <?php endif; ?>
                    </div>
                    <div class="meta" style="display:flex;gap:10px;flex-wrap:wrap">
                        <?php if($habit->target_value > 0): ?>
                            <span>🎯 <?php echo e($habit->target_value); ?> <?php echo e($habit->unit); ?></span>
                        <?php endif; ?>
                        <span>📅 <?php echo e($habit->schedule_label); ?></span>
                        <?php if($habit->days_remaining !== null): ?>
                            <span>⏳ <?php echo e($habit->days_remaining); ?> <?php echo e(\Illuminate\Support\Str::plural('день|дня|дней', $habit->days_remaining)); ?></span>
                        <?php endif; ?>
                        <?php if($habit->today_scheduled && !$habit->today_log): ?>
                            <span class="badge-today">сегодня</span>
                        <?php endif; ?>
                        <span>🔥 <?php echo e($s['streak']); ?></span>
                    </div>
                    <?php if($habit->progress_pct !== null): ?>
                        <div class="progress-bar" style="margin-top:8px">
                            <div class="progress-fill" style="width:<?php echo e($habit->progress_pct); ?>%; background: <?php echo e($habit->color); ?>"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stats-mini">
                <span class="pill success">+ <?php echo e($s['success']); ?></span>
                <span class="pill fail">− <?php echo e($s['fail']); ?></span>
                <span class="pill skipped">∗ <?php echo e($s['skipped']); ?></span>
                <?php if($habit->is_completed): ?>
                    <span class="btn ghost sm" style="pointer-events:none">📊 Отчёт</span>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/dashboard/index.blade.php ENDPATH**/ ?>