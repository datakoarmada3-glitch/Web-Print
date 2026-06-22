<?php $__env->startSection('title', 'Semua Riwayat Print'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Print</h3>
        <div class="ms-auto d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode/file..." value="<?php echo e(request('search')); ?>">
                <select name="status" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Semua Status</option>
                    <?php $__currentLoopData = ['waiting','processing','printing','completed','failed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>User</th>
                    <th>File</th>
                    <th>Tipe</th>
                    <th>Copy</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $printJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($job->job_code); ?></td>
                        <td><?php echo e($job->user->name); ?></td>
                        <td><?php echo e(Str::limit($job->original_filename, 20)); ?></td>
                        <td><?php echo e(strtoupper($job->file_type)); ?></td>
                        <td><?php echo e($job->copies); ?></td>
                        <td><span class="badge <?php echo e($job->status->badgeClass()); ?>"><?php echo e($job->status->label()); ?></span></td>
                        <td><?php echo e($job->submitted_at?->format('d/m/Y H:i')); ?></td>
                        <td><a href="<?php echo e(route('admin.history.show', $job)); ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data riwayat.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?php echo e($printJobs->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/history/index.blade.php ENDPATH**/ ?>