<?php $__env->startSection('title', 'Отчёт · ' . $habit->title); ?>

<?php $__env->startSection('content'); ?>
<div class="report-actions no-print mb-3">
    <a href="<?php echo e(route('habits.show', $habit)); ?>" class="btn ghost sm">← К привычке</a>
    <button class="btn ghost sm" onclick="window.print()">🖨 Распечатать / PDF</button>
</div>

<div class="report-sheet">
    <header class="report-head">
        <div class="row" style="gap:14px">
            <span style="display:inline-block;width:14px;height:36px;border-radius:6px;background: <?php echo e($habit->color); ?>"></span>
            <div>
                <div class="muted" style="font-size:.85rem">Отчёт по привычке</div>
                <h1 style="margin:0"><?php echo e($habit->title); ?></h1>
            </div>
        </div>
        <div class="report-period">
            <div>📅 <?php echo e($start->format('d.m.Y')); ?> — <?php echo e($end->format('d.m.Y')); ?></div>
            <div>⏱ <?php echo e($start->diffInDays($end) + 1); ?> <?php echo e(\Illuminate\Support\Str::plural('день|дня|дней', $start->diffInDays($end) + 1)); ?></div>
            <div>📋 <?php echo e($habit->scheduleLabel()); ?></div>
            <?php if($isCompleted): ?>
                <div class="report-badge done">✅ Завершена</div>
            <?php else: ?>
                <div class="report-badge active">▶ Активна</div>
            <?php endif; ?>
        </div>
    </header>

    <?php if($habit->description): ?>
        <div class="muted mb-3" style="font-size:.92rem"><?php echo e($habit->description); ?></div>
    <?php endif; ?>

    
    <section class="mb-3">
        <h2>Ключевые метрики</h2>
        <div class="grid grid-4">
            <div class="metric metric-good">
                <div class="metric-icon">✅</div>
                <div class="metric-value"><?php echo e($stats['success']); ?></div>
                <div class="metric-label">Выполнено<br><span class="muted">из <?php echo e($stats['expected']); ?></span></div>
            </div>
            <div class="metric metric-bad">
                <div class="metric-icon">✗</div>
                <div class="metric-value"><?php echo e($stats['fail']); ?></div>
                <div class="metric-label">Провалов</div>
            </div>
            <div class="metric metric-warn">
                <div class="metric-icon">∗</div>
                <div class="metric-value"><?php echo e($stats['skipped']); ?></div>
                <div class="metric-label">Форс-мажоров</div>
            </div>
            <div class="metric metric-accent">
                <div class="metric-icon">🎯</div>
                <div class="metric-value"><?php echo e($stats['success_rate']); ?>%</div>
                <div class="metric-label">Успех<br><span class="muted">от плана</span></div>
            </div>
        </div>
        <div class="grid grid-2 mt-2">
            <div class="metric metric-fire">
                <div class="metric-icon">🔥</div>
                <div class="metric-value"><?php echo e($longestStreak); ?></div>
                <div class="metric-label">Лучший стрик подряд</div>
            </div>
            <div class="metric">
                <div class="metric-icon">📅</div>
                <div class="metric-value"><?php echo e($stats['streak']); ?></div>
                <div class="metric-label">Текущий стрик</div>
            </div>
        </div>
    </section>

    
    <?php if(count($achievements)): ?>
        <section class="mb-3">
            <h2>Достижения</h2>
            <div class="grid grid-3">
                <?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="achievement">
                        <div class="ach-icon"><?php echo e($a['icon']); ?></div>
                        <div>
                            <div class="ach-title"><?php echo e($a['title']); ?></div>
                            <div class="ach-desc"><?php echo e($a['desc']); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>

    
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
                <?php $__currentLoopData = $weekdayStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dow => $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($w['expected'] > 0): ?>
                        <tr <?php if($dow === $bestDow): ?> class="row-best" <?php elseif($dow === $worstDow): ?> class="row-worst" <?php endif; ?>>
                            <td><?php echo e($weekdayNames[$dow]); ?></td>
                            <td><?php echo e($w['success']); ?></td>
                            <td><?php echo e($w['expected']); ?></td>
                            <td><strong><?php echo e($w['rate']); ?>%</strong></td>
                            <td>
                                <?php if($dow === $bestDow): ?> 🌟 лучший день
                                <?php elseif($dow === $worstDow): ?> ⚠️ слабый день
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </section>

    
    <?php if($topFailures->isNotEmpty()): ?>
        <section class="mb-3">
            <h2>Заметки о провалах</h2>
            <?php $__currentLoopData = $topFailures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="report-failure">
                    <div class="muted" style="font-size:.75rem"><?php echo e($f->log_date->format('d.m.Y, dddd')); ?></div>
                    <div><?php echo e($f->failure_note); ?></div>
                    <?php if($f->improvement_note): ?>
                        <div class="report-improvement">💡 <?php echo e($f->improvement_note); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
    <?php endif; ?>

    
    <section class="card ai-card mb-3 no-print">
        <div class="row between">
            <h2 style="margin:0">🤖 Резюме от Claude</h2>
            <button class="btn sm" type="button" data-ai-generate="<?php echo e(route('ai.generate', $habit)); ?>">
                <span class="ai-spinner" data-ai-spinner style="display:none"></span>
                Сгенерировать
            </button>
        </div>
        <div class="ai-output" data-ai-output>Нажмите кнопку — Claude обобщит ваш результат и подскажет, как закрепить или продолжить.</div>
        <div class="muted mt-2" style="font-size:.75rem" data-ai-stamp></div>
    </section>

    <footer class="report-footer no-print">
        <span class="muted" style="font-size:.8rem">Сформировано <?php echo e(now()->format('d.m.Y H:i')); ?> · Habit Tracker</span>
    </footer>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/habits/report.blade.php ENDPATH**/ ?>