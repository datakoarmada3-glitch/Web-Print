<?php $__env->startSection('title', 'Antrean Print'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Antrean Print Aktif</h3>
        <div>
            <?php if($isPaused): ?>
                <form method="POST" action="<?php echo e(route('admin.queue.resume')); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-success btn-sm">Resume Antrean</button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.queue.pause')); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-warning btn-sm">Pause Antrean</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php if($isPaused): ?>
        <div class="alert alert-warning m-3">⚠️ Antrean sedang dijeda. Job baru tidak akan diproses.</div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>User</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $activeJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($job->job_code); ?></td>
                        <td><?php echo e($job->user->name); ?></td>
                        <td><?php echo e(Str::limit($job->original_filename, 25)); ?></td>
                        <td><span class="badge <?php echo e($job->status->badgeClass()); ?>"><?php echo e($job->status->label()); ?></span></td>
                        <td><?php echo e($job->submitted_at?->format('H:i')); ?></td>
                        <td>
                            <?php if($job->isCancellable()): ?>
                                <form method="POST" action="<?php echo e(route('admin.queue.cancel', $job)); ?>" class="d-inline" onsubmit="return confirm('Batalkan?')">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada job dalam antrean.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?php echo e($activeJobs->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/queue/index.blade.php ENDPATH**/ ?>