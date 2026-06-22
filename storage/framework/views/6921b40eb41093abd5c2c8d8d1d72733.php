<?php $__env->startSection('title', 'Pengaturan'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" class="card">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="card-header"><h3 class="card-title">Pengaturan Aplikasi</h3></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Maks Ukuran Upload (MB)</label>
                    <input type="number" name="upload_max_size_mb" class="form-control"
                           value="<?php echo e(old('upload_max_size_mb', $settings['upload_max_size_mb']->value ?? 50)); ?>"
                           min="1" max="500" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Retensi File (Hari)</label>
                    <input type="number" name="file_retention_days" class="form-control"
                           value="<?php echo e(old('file_retention_days', $settings['file_retention_days']->value ?? 30)); ?>"
                           min="1" max="365" required>
                    <small class="form-hint">File akan dihapus otomatis setelah X hari. Data histori tetap tersimpan.</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Ekstensi File yang Diizinkan</label>
                    <input type="text" name="allowed_file_extensions" class="form-control"
                           value="<?php echo e(old('allowed_file_extensions', $settings['allowed_file_extensions']->value ?? 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png')); ?>" required>
                    <small class="form-hint">Pisahkan dengan koma, tanpa spasi.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ukuran Kertas Default</label>
                    <select name="default_paper_size" class="form-select">
                        <?php $__currentLoopData = ['A4', 'Legal', 'F4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($size); ?>" <?php echo e(($settings['default_paper_size']->value ?? 'A4') === $size ? 'selected' : ''); ?>><?php echo e($size); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mode Warna Default</label>
                    <select name="default_color_mode" class="form-select">
                        <option value="grayscale" <?php echo e(($settings['default_color_mode']->value ?? 'grayscale') === 'grayscale' ? 'selected' : ''); ?>>Hitam Putih</option>
                        <option value="color" <?php echo e(($settings['default_color_mode']->value ?? 'grayscale') === 'color' ? 'selected' : ''); ?>>Berwarna</option>
                    </select>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>