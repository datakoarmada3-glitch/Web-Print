<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Print Hari Ini</div>
        <div class="stat-value blue"><?php echo e($stats['total_today']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Print Bulan Ini</div>
        <div class="stat-value blue"><?php echo e($stats['total_month']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Dalam Antrean</div>
        <div class="stat-value <?php echo e($stats['pending'] > 0 ? 'muted' : 'green'); ?>"><?php echo e($stats['pending']); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Print Job Terakhir</h3>
        <a href="<?php echo e(route('print-jobs.create')); ?>" class="btn btn-primary btn-sm">+ Print Baru</a>
    </div>

    
    <div class="desktop-table">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>File</th><th>Status</th><th>Copy</th><th>Waktu</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('print-jobs.show', $job)); ?>"><?php echo e($job->job_code); ?></a></td>
                            <td><?php echo e(Str::limit($job->original_filename, 35)); ?></td>
                            <td><span class="badge badge-<?php echo e(match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' }); ?>"><?php echo e($job->status->label()); ?></span></td>
                            <td><?php echo e($job->copies); ?></td>
                            <td class="text-muted"><?php echo e($job->submitted_at?->format('d/m/Y H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted" style="padding:32px">Belum ada print job. <a href="<?php echo e(route('print-jobs.create')); ?>">Print sekarang</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="mobile-cards" style="padding:12px">
        <?php $__empty_1 = true; $__currentLoopData = $recentJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('print-jobs.show', $job)); ?>" class="mobile-card" style="display:block;text-decoration:none;color:inherit">
                <div class="mobile-card-header">
                    <code style="font-size:11px;color:var(--muted)"><?php echo e($job->job_code); ?></code>
                    <span class="badge badge-<?php echo e(match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' }); ?>"><?php echo e($job->status->label()); ?></span>
                </div>
                <div style="font-weight:500"><?php echo e(Str::limit($job->original_filename, 40)); ?></div>
                <div class="mobile-card-row" style="margin-top:4px"><span class="label">Copy</span><span><?php echo e($job->copies); ?>× · <?php echo e($job->submitted_at?->format('d/m H:i')); ?></span></div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center;padding:32px;color:var(--muted)">Belum ada print job. <a href="<?php echo e(route('print-jobs.create')); ?>">Print sekarang</a></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard.blade.php ENDPATH**/ ?>