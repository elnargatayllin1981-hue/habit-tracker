<?php $__env->startSection('title', 'Предложения · Habit Tracker'); ?>

<?php $__env->startSection('content'); ?>
<section class="mb-3">
    <h1>Предложения по формированию привычек</h1>
    <p class="muted">Готовые шаблоны от лёгких к сложным. Нажмите «Принять» — привычка сразу попадёт в ваш дашборд с подходящим расписанием и длительностью.</p>
</section>


<div class="cat-pills mb-3">
    <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="#cat-<?php echo e($key); ?>" class="cat-pill" style="--cat-color: <?php echo e($g['meta']['color']); ?>">
            <?php echo e($g['meta']['icon']); ?> <?php echo e($g['meta']['title']); ?>

            <span class="cat-count"><?php echo e(count($g['items'])); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <section id="cat-<?php echo e($key); ?>" class="mb-3">
        <h2 style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.4rem"><?php echo e($g['meta']['icon']); ?></span>
            <?php echo e($g['meta']['title']); ?>

        </h2>
        <div class="grid grid-3 mt-2">
            <?php $__currentLoopData = $g['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card sug-card" style="--accent-line: <?php echo e($t['color']); ?>">
                    <div class="row between" style="margin-bottom:8px">
                        <h3 style="margin:0"><?php echo e($t['title']); ?></h3>
                        <?php if($t['adopted']): ?>
                            <span class="pill success">✓ принято</span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size:.9rem;color:var(--text-muted);margin:0 0 12px"><?php echo e($t['description']); ?></p>

                    <div class="sug-meta">
                        <?php if($t['target_value'] > 0): ?>
                            <span>🎯 <?php echo e($t['target_value']); ?> <?php echo e($t['unit']); ?></span>
                        <?php else: ?>
                            <span>🎯 без таргета</span>
                        <?php endif; ?>
                        <span>📅 <?php echo e(\App\Models\Habit::SCHEDULES[$t['schedule']] ?? $t['schedule']); ?></span>
                        <span>⏳ <?php echo e($t['duration_days']); ?> <?php echo e(\Illuminate\Support\Str::plural('день|дня|дней', $t['duration_days'])); ?></span>
                    </div>

                    <?php if(!empty($t['why'])): ?>
                        <div class="sug-why">💡 <?php echo e($t['why']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('suggestions.adopt', $t['slug'])); ?>" style="margin-top:14px">
                        <?php echo csrf_field(); ?>
                        <button class="btn sm" type="submit" <?php if($t['adopted']): ?> disabled style="opacity:.5;cursor:not-allowed" <?php endif; ?>>
                            <?php if($t['adopted']): ?> Уже у вас <?php else: ?> + Принять <?php endif; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/suggestions/index.blade.php ENDPATH**/ ?>