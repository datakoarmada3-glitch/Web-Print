<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'Web Printer')); ?> - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-wrap { width: 100%; max-width: 380px; padding: 20px; }
        .login-brand { text-align: center; margin-bottom: 32px; }
        .login-brand h1 { font-size: 28px; font-weight: 700; color: #1a1a2e; }
        .login-brand p { color: #6b7280; font-size: 14px; margin-top: 4px; }
        .login-card { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04); }
        .login-card h2 { font-size: 18px; font-weight: 600; margin-bottom: 24px; text-align: center; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: border-color .15s, box-shadow .15s; outline: none; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        .form-input.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #ef4444; font-size: 12px; margin-top: 4px; }
        .form-check { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 13px; color: #6b7280; }
        .form-check input { width: 16px; height: 16px; accent-color: #3b82f6; }
        .btn-primary { width: 100%; padding: 10px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background .15s; }
        .btn-primary:hover { background: #2563eb; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-brand">
            <h1>🖨️ Web Printer</h1>
            <p>Sistem Print Terpusat Kantor</p>
        </div>
        <div class="login-card">
            <h2>Masuk</h2>

            <?php if($errors->any()): ?>
                <div class="alert-error"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" autocomplete="off">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-input <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('username')); ?>" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-input" autocomplete="current-password" required>
                </div>
                <label class="form-check">
                    <input type="checkbox" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                    <span>Ingat saya</span>
                </label>
                <button type="submit" class="btn-primary">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>