<?php $__env->startSection('title', 'Riwayat Print'); ?>
<?php $__env->startSection('content'); ?>

<div class="card">
    <div class="card-header">
        <h3>Riwayat Print Saya</h3>
        <a href="<?php echo e(route('print-jobs.create')); ?>" class="btn btn-primary btn-sm">+ Print Baru</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Kertas</th>
                    <th>Copy</th>
                    <th>Waktu</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $printJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><code style="font-size:12px"><?php echo e($job->job_code); ?></code></td>
                        <td><?php echo e(Str::limit($job->original_filename, 30)); ?></td>
                        <td><span class="badge badge-<?php echo e(match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' }); ?>"><?php echo e($job->status->label()); ?></span></td>
                        <td><?php echo e($job->paper_size->value); ?></td>
                        <td><?php echo e($job->copies); ?></td>
                        <td class="text-muted"><?php echo e($job->submitted_at?->format('d/m H:i')); ?></td>
                        <td><a href="<?php echo e(route('print-jobs.show', $job)); ?>" class="btn btn-ghost btn-sm">Detail</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Belum ada riwayat print.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($printJobs->hasPages()): ?>
        <div class="card-footer"><?php echo e($printJobs->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/print-jobs/index.blade.php ENDPATH**/ ?>