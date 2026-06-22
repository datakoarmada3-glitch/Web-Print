<?php $__env->startSection('title', 'Edit Printer'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <form method="POST" action="<?php echo e(route('admin.printers.update', $printer)); ?>" class="card">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="card-header"><h3 class="card-title">Edit: <?php echo e($printer->name); ?></h3></div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Nama Printer</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $printer->name)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">CUPS Name</label>
                    <input type="text" name="cups_name" class="form-control" value="<?php echo e(old('cups_name', $printer->cups_name)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" value="<?php echo e(old('ip_address', $printer->ip_address)); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Connection URI</label>
                    <input type="text" name="connection_uri" class="form-control" value="<?php echo e(old('connection_uri', $printer->connection_uri)); ?>" required>
                    <small class="form-hint">Contoh: ipp://10.3.105.224/ipp/print</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" class="form-control" value="<?php echo e(old('location', $printer->location)); ?>">
                </div>
                <div class="col-12">
                    <label class="form-check">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" <?php echo e($printer->is_default ? 'checked' : ''); ?>>
                        <span class="form-check-label">Jadikan printer default</span>
                    </label>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="<?php echo e(route('admin.printers.index')); ?>" class="btn btn-ghost-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/printers/edit.blade.php ENDPATH**/ ?>