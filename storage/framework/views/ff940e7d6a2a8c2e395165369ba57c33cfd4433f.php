<!DOCTYPE html>
<html lang="ru" data-theme="<?php echo e(auth()->check() ? auth()->user()->theme : 'auto'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Habit Tracker'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
    <script src="<?php echo e(asset('js/app.js')); ?>" defer></script>
</head>
<body>
<div class="shell">
    <header class="topbar no-print">
        <a href="<?php echo e(auth()->check() ? route('dashboard') : route('login')); ?>" class="brand">
            <span class="dot"></span> Habit Tracker
        </a>
        <nav class="nav">
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" <?php if(request()->routeIs('dashboard')): ?> class="active" <?php endif; ?>>Дашборд</a>
                <a href="<?php echo e(route('suggestions.index')); ?>" <?php if(request()->routeIs('suggestions.*')): ?> class="active" <?php endif; ?>>💡 Идеи</a>
                <a href="<?php echo e(route('journal.index')); ?>" <?php if(request()->routeIs('journal.*')): ?> class="active" <?php endif; ?>>📔 Журнал</a>
                <a href="<?php echo e(route('habits.create')); ?>" class="btn sm">+ Привычка</a>
                <span class="muted user-chip" style="font-size:.85rem"><?php echo e(auth()->user()->login); ?></span>
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn ghost sm" type="submit">Выйти</button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>">Войти</a>
                <a href="<?php echo e(route('register')); ?>" class="btn sm">Регистрация</a>
            <?php endif; ?>
            <div class="theme-toggle" role="group" aria-label="Тема">
                <button data-theme="light" type="button" title="Светлая">☀</button>
                <button data-theme="dark"  type="button" title="Тёмная">☾</button>
                <button data-theme="auto"  type="button" title="Авто">A</button>
            </div>
        </nav>
    </header>

    <?php if(session('flash')): ?>
        <div class="flash"><?php echo e(session('flash')); ?></div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</div>


<div class="toasts" id="toasts" aria-live="polite"></div>
</body>
</html>
<?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/layouts/app.blade.php ENDPATH**/ ?>