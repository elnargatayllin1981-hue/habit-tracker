<?php $__env->startSection('title', 'Журнал · Habit Tracker'); ?>

<?php $__env->startSection('content'); ?>
<section class="mb-3">
    <h1>Журнал отметок</h1>
    <p class="muted">Все ваши выполненные, проваленные и пропущенные дни по всем привычкам за выбранный период.</p>
</section>


<form method="GET" action="<?php echo e(route('journal.index')); ?>" class="card flat mb-3">
    <div class="row" style="flex-wrap:wrap;gap:14px;align-items:end">
        <div class="field" style="margin:0;flex:1 1 200px">
            <label>Период</label>
            <div class="range-tabs">
                <a href="?range=7"   class="range-tab <?php if($range==='7'): ?>   active <?php endif; ?>">7 дн.</a>
                <a href="?range=30"  class="range-tab <?php if($range==='30'): ?>  active <?php endif; ?>">30 дн.</a>
                <a href="?range=90"  class="range-tab <?php if($range==='90'): ?>  active <?php endif; ?>">90 дн.</a>
                <a href="?range=all" class="range-tab <?php if($range==='all'): ?> active <?php endif; ?>">Всё</a>
            </div>
        </div>
        <div class="field" style="margin:0">
            <label for="status">Статус</label>
            <select id="status" name="status" class="status-select" onchange="this.form.submit()">
                <option value="">Все</option>
                <option value="success" <?php if($statusF==='success'): echo 'selected'; endif; ?>>+ Выполнено</option>
                <option value="fail"    <?php if($statusF==='fail'): echo 'selected'; endif; ?>>− Провал</option>
                <option value="skipped" <?php if($statusF==='skipped'): echo 'selected'; endif; ?>>∗ Форс-мажор</option>
            </select>
        </div>
        <div class="field" style="margin:0">
            <label for="habit_id">Привычка</label>
            <select id="habit_id" name="habit_id" class="status-select" onchange="this.form.submit()">
                <option value="">Все</option>
                <?php $__currentLoopData = $habits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($h->id); ?>" <?php if((string)$habitIdF === (string)$h->id): echo 'selected'; endif; ?>><?php echo e($h->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="field" style="margin:0">
            <label>Свой период</label>
            <div class="row" style="gap:6px">
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" style="padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
                <span class="muted">–</span>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" style="padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--input-bg);color:var(--text)">
                <button class="btn ghost sm" type="submit">OK</button>
            </div>
        </div>
        <div class="field" style="margin:0">
            <a class="btn ghost sm" href="<?php echo e(route('journal.export', request()->query())); ?>">📥 CSV</a>
        </div>
    </div>
    <div class="muted mt-2" style="font-size:.85rem"><?php echo e($rangeLabel); ?> · с <?php echo e($from->format('d.m.Y')); ?> по <?php echo e($to->format('d.m.Y')); ?></div>
</form>


<div class="grid grid-4 mb-3">
    <div class="stat positive">
        <div class="label">Выполнено</div>
        <div class="value"><?php echo e($summary['success']); ?></div>
    </div>
    <div class="stat negative">
        <div class="label">Провалов</div>
        <div class="value"><?php echo e($summary['fail']); ?></div>
    </div>
    <div class="stat neutral">
        <div class="label">Форс-мажор</div>
        <div class="value"><?php echo e($summary['skipped']); ?></div>
    </div>
    <div class="stat">
        <div class="label">Всего отметок</div>
        <div class="value"><?php echo e($summary['total']); ?></div>
    </div>
</div>


<?php if($grouped->isEmpty()): ?>
    <div class="card center">
        <h2 style="margin-bottom:.5rem">За этот период отметок нет</h2>
        <p class="muted">Попробуйте расширить период или снять фильтры.</p>
        <a class="btn ghost" href="<?php echo e(route('journal.index')); ?>">Сбросить фильтры</a>
    </div>
<?php else: ?>
    <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $d = \Carbon\Carbon::parse($date); ?>
        <div class="card mb-2">
            <h3 style="margin:0 0 12px;display:flex;justify-content:space-between;align-items:center">
                <span><?php echo e($d->isoFormat('dddd, D MMMM')); ?></span>
                <span class="muted" style="font-weight:400;font-size:.85rem"><?php echo e($d->format('d.m.Y')); ?></span>
            </h3>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="journal-row">
                    <div class="row" style="gap:10px;flex:1;min-width:0">
                        <span class="swatch" style="background: <?php echo e($log->habit->color); ?>;width:8px;height:24px"></span>
                        <div style="min-width:0;flex:1">
                            <div style="font-weight:600"><?php echo e($log->habit->title); ?></div>
                            <?php if($log->value !== null): ?>
                                <div class="meta" style="font-size:.8rem"><?php echo e($log->value); ?> <?php echo e($log->habit->unit); ?></div>
                            <?php endif; ?>
                            <?php if($log->failure_note): ?>
                                <div class="journal-note">⚠️ <?php echo e($log->failure_note); ?></div>
                            <?php endif; ?>
                            <?php if($log->improvement_note): ?>
                                <div class="journal-note positive">💡 <?php echo e($log->improvement_note); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="pill <?php echo e($log->status); ?>">
                        <?php if($log->status === 'success'): ?> + выполнено
                        <?php elseif($log->status === 'fail'): ?> − провал
                        <?php else: ?> ∗ форс-мажор
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/journal/index.blade.php ENDPATH**/ ?>