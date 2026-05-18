<?php $__env->startSection('title', 'Вход · Habit Tracker'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-wrap">
    <div class="auth-card">
        <h1>С возвращением</h1>
        <p class="subtitle">Введите логин и пароль, чтобы продолжить.</p>

        <form method="POST" action="<?php echo e(route('login.store')); ?>" novalidate>
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="login" type="text" value="<?php echo e(old('login')); ?>" autocomplete="username" autofocus required>
                <?php $__errorArgs = ['login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <label class="row" style="font-size:.9rem; color:var(--text-muted); margin-bottom:18px">
                <input type="checkbox" name="remember" value="1" style="width:auto"> Запомнить меня
            </label>

            <button class="btn" type="submit" style="width:100%">Войти</button>
        </form>

        <div class="switch">
            Нет аккаунта? <a href="<?php echo e(route('register')); ?>">Зарегистрироваться</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH P:\OSPanel\domains\habit-tracker3v\resources\views/auth/login.blade.php ENDPATH**/ ?>