<?php $__env->startSection('title', $habit->title . ' · Habit Tracker'); ?>

<?php $__env->startSection('content'); ?>
<section class="row between mb-3" style="flex-wrap:wrap;gap:14px">
    <div class="row" style="gap:14px">
        <span style="display:inline-block;width:14px;height:32px;border-radius:6px;background: <?php echo e($habit->color); ?>"></span>
        <div>
            <h1 style="margin:0"><?php echo e($habit->title); ?></h1>
            <div class="muted" style="display:flex;gap:12px;flex-wrap:wrap">
                <?php if($habit->target_value > 0): ?>
                    <span>🎯 <?php echo e($habit->target_value); ?> <?php echo e($habit->unit); ?>/день</span>
                <?php endif; ?>
                <span>📅 <?php echo e($habit->scheduleLabel()); ?></span>
                <?php if($habit->endDate()): ?>
                    <span>📆 до <?php echo e($habit->endDate()->format('d.m.Y')); ?></span>
                <?php endif; ?>
                <?php if($habit->daysRemaining() !== null): ?>
                    <span>⏳ <?php echo e($habit->daysRemaining()); ?> <?php echo e(\Illuminate\Support\Str::plural('день|дня|дней', $habit->daysRemaining())); ?> до конца</span>
                <?php endif; ?>
                <span>🔥 стрик <?php echo e($stats['streak']); ?></span>
            </div>
        </div>
    </div>
    <div class="row" style="gap:8px"><a href="<?php echo e(route('habits.report', $habit)); ?>" class="btn ghost sm">📊 Отчёт</a><a href="<?php echo e(route('habits.edit', $habit)); ?>" class="btn ghost sm">⚙ Настройки</a></div>
</section>

<?php if($habit->description): ?>
    <p class="muted mb-3"><?php echo e($habit->description); ?></p>
<?php endif; ?>


<div class="card flat mb-3">
    <div class="row between" style="flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="margin:0 0 4px">Период статистики</h3>
            <div class="muted" style="font-size:.85rem"><?php echo e($rangeLabel); ?> · с <?php echo e(\Carbon\Carbon::parse($from)->format('d.m.Y')); ?> по <?php echo e(\Carbon\Carbon::parse($to)->format('d.m.Y')); ?></div>
        </div>
        <div class="range-tabs">
            <a href="<?php echo e(route('habits.show', ['habit' => $habit, 'range' => 7])); ?>"   class="range-tab <?php if($range==='7'): ?> active <?php endif; ?>">7 дн.</a>
            <a href="<?php echo e(route('habits.show', ['habit' => $habit, 'range' => 30])); ?>"  class="range-tab <?php if($range==='30'): ?> active <?php endif; ?>">30 дн.</a>
            <a href="<?php echo e(route('habits.show', ['habit' => $habit, 'range' => 90])); ?>"  class="range-tab <?php if($range==='90'): ?> active <?php endif; ?>">90 дн.</a>
            <a href="<?php echo e(route('habits.show', ['habit' => $habit, 'range' => 'all'])); ?>" class="range-tab <?php if($range==='all'): ?> active <?php endif; ?>">Всё время</a>
        </div>
    </div>
    <form method="GET" action="<?php echo e(route('habits.show', $habit)); ?>" class="row" style="margin-top:12px;gap:10px;flex-wrap:wrap">
        <div class="row" style="gap:6px;align-items:center">
            <span class="muted" style="font-size:.85rem">Свой период:</span>
            <input type="date" name="from" value="<?php echo e($from->toDateString()); ?>" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
            <span class="muted">—</span>
            <input type="date" name="to" value="<?php echo e($to->toDateString()); ?>" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
        </div>
        <button class="btn ghost sm" type="submit">Применить</button>
    </form>
</div>


<div class="grid grid-4 mb-3">
    <div class="stat positive">
        <div class="label">Выполнено</div>
        <div class="value"><?php echo e($stats['success']); ?></div>
        <div class="muted" style="font-size:.75rem">из <?php echo e($stats['expected']); ?> запланированных</div>
    </div>
    <div class="stat negative">
        <div class="label">Провалов</div>
        <div class="value"><?php echo e($stats['fail']); ?></div>
    </div>
    <div class="stat neutral">
        <div class="label">Форс-мажор</div>
        <div class="value"><?php echo e($stats['skipped']); ?></div>
    </div>
    <div class="stat">
        <div class="label">Успех</div>
        <div class="value"><?php echo e($stats['success_rate']); ?>%</div>
        <div class="muted" style="font-size:.75rem">от запланированных дней</div>
    </div>
</div>


<div class="card mb-3">
    <div class="row between" style="flex-wrap:wrap">
        <h2 style="margin:0">Календарь от старта</h2>
        <span class="muted" style="font-size:.85rem">
            <?php echo e($calStart->format('d.m.Y')); ?>

            <?php if($habit->endDate()): ?> → <?php echo e($habit->endDate()->format('d.m.Y')); ?> <?php endif; ?>
        </span>
    </div>
    <p class="muted" style="font-size:.85rem;margin:6px 0 12px">Кликните по дню, чтобы заполнить форму ниже. Серые ячейки — выходные по расписанию или дни вне периода.</p>

    <div class="heatmap-wrap">
        <div class="heatmap-weekdays">
            <span>Пн</span><span></span><span>Ср</span><span></span><span>Пт</span><span></span><span>Вс</span>
        </div>
        <div class="heatmap-grid" id="heatmap-grid">
            <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="hm-col">
                    <?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="hm-cell <?php echo e($cell['class']); ?> <?php if($cell['is_today']): ?> today <?php endif; ?>"
                             <?php if(in_array($cell['class'], ['success','fail','skipped','pending'])): ?>
                                data-day="<?php echo e($cell['date']); ?>"
                             <?php endif; ?>
                             title="<?php echo e($cell['tooltip']); ?>">
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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


<div class="card mb-3">
    <h2>Отметить день</h2>
    <form method="POST" action="<?php echo e(route('habit-logs.upsert', $habit)); ?>">
        <?php echo csrf_field(); ?>
        <div class="day-form">
            <div class="field">
                <label for="log_date">Дата</label>
                <input id="log_date" name="log_date" type="date" value="<?php echo e(now()->toDateString()); ?>" required>
            </div>
            <div class="field">
                <label for="status">Статус</label>
                <select id="status" name="status" class="status-select" required>
                    <option value="success">+ &nbsp; Выполнено</option>
                    <option value="fail">− &nbsp; Провал</option>
                    <option value="skipped">∗ &nbsp; Форс-мажор / не смог</option>
                </select>
            </div>
            <?php if($habit->target_value > 0): ?>
            <div class="field">
                <label for="value">Сколько сделали (<?php echo e($habit->unit); ?>)</label>
                <input id="value" name="value" type="number" min="0" placeholder="напр. 25">
            </div>
            <?php endif; ?>
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


<div class="grid grid-2 mb-3">
    <div class="card">
        <h2><?php echo e($chart['aggregated'] ? 'Динамика по неделям' : 'Динамика по дням'); ?></h2>
        <canvas id="chart-trend" height="180"></canvas>
    </div>
    <div class="card">
        <h2>Распределение статусов</h2>
        <canvas id="chart-pie" height="180"></canvas>
    </div>
</div>


<div class="card ai-card mb-3">
    <div class="row between">
        <h2>🤖 Совет от Claude</h2>
        <button class="btn sm" type="button" data-ai-generate="<?php echo e(route('ai.generate', $habit)); ?>">
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


<?php if($failures->isNotEmpty()): ?>
<div class="card mb-3">
    <h2>Записи о провалах в этом периоде</h2>
    <?php $__currentLoopData = $failures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="padding:10px 0;border-bottom:1px solid var(--border)">
            <div class="muted" style="font-size:.8rem"><?php echo e($f->log_date->format('d.m.Y')); ?></div>
            <div><?php echo e($f->failure_note); ?></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = <?php echo json_encode($chart, 15, 512) ?>;

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/habits/show.blade.php ENDPATH**/ ?>