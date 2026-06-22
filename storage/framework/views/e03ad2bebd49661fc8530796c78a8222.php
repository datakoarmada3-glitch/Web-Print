<?php $__env->startSection('title', 'Detail Job: ' . $printJob->job_code); ?>

<?php $__env->startSection('content'); ?>
<div class="row row-cards">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><?php echo e($printJob->job_code); ?></h3>
                <span class="badge <?php echo e($printJob->status->badgeClass()); ?>"><?php echo e($printJob->status->label()); ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>User:</strong><br><?php echo e($printJob->user->name); ?> (<?php echo e($printJob->user->email); ?>)</div>
                    <div class="col-md-6"><strong>Printer:</strong><br><?php echo e($printJob->printer->name); ?></div>
                    <div class="col-md-6"><strong>File Asli:</strong><br><?php echo e($printJob->original_filename); ?></div>
                    <div class="col-md-3"><strong>Tipe:</strong><br><?php echo e(strtoupper($printJob->file_type)); ?></div>
                    <div class="col-md-3"><strong>Ukuran:</strong><br><?php echo e($printJob->fileSizeFormatted()); ?></div>
                    <div class="col-md-3"><strong>Halaman:</strong><br><?php echo e($printJob->page_count ?? '-'); ?></div>
                    <div class="col-md-3"><strong>Copy:</strong><br><?php echo e($printJob->copies); ?></div>
                    <div class="col-md-3"><strong>Kertas:</strong><br><?php echo e($printJob->paper_size->value); ?></div>
                    <div class="col-md-3"><strong>Orientasi:</strong><br><?php echo e($printJob->orientation->label()); ?></div>
                    <div class="col-md-3"><strong>Duplex:</strong><br><?php echo e($printJob->duplex->label()); ?></div>
                    <div class="col-md-3"><strong>Warna:</strong><br><?php echo e($printJob->color_mode->label()); ?></div>
                    <div class="col-md-3"><strong>Range:</strong><br><?php echo e($printJob->page_range ?: 'Semua'); ?></div>
                    <div class="col-md-3"><strong>Est. Lembar:</strong><br><?php echo e($printJob->estimatedSheets()); ?></div>
                    <div class="col-md-3"><strong>CUPS Job:</strong><br><?php echo e($printJob->cups_job_id ?: '-'); ?></div>
                    <div class="col-md-3"><strong>Upload:</strong><br><?php echo e($printJob->submitted_at?->format('d/m/Y H:i:s')); ?></div>
                    <div class="col-md-3"><strong>Mulai Proses:</strong><br><?php echo e($printJob->processing_started_at?->format('H:i:s') ?? '-'); ?></div>
                    <div class="col-md-3"><strong>Cetak:</strong><br><?php echo e($printJob->printed_at?->format('H:i:s') ?? '-'); ?></div>
                    <div class="col-md-3"><strong>Selesai:</strong><br><?php echo e($printJob->completed_at?->format('H:i:s') ?? '-'); ?></div>
                    <?php if($printJob->error_message): ?>
                        <div class="col-12"><strong>Error:</strong><br><span class="text-danger"><?php echo e($printJob->error_message); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="<?php echo e(route('admin.history.index')); ?>" class="btn btn-ghost-secondary">Kembali</a>
                <?php if($printJob->isCancellable()): ?>
                    <form method="POST" action="<?php echo e(route('admin.queue.cancel', $printJob)); ?>" onsubmit="return confirm('Batalkan job ini?')">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-danger">Cancel Job</button>
                    </form>
                <?php endif; ?>
                <?php if($printJob->status->value === 'failed'): ?>
                    <form method="POST" action="<?php echo e(route('admin.queue.retry', $printJob)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-warning">Retry Job</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Log Status</h3></div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $printJob->logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo e(ucfirst($log->status)); ?></strong>
                            <small class="text-muted"><?php echo e($log->created_at->format('d/m H:i:s')); ?></small>
                        </div>
                        <div class="text-secondary small"><?php echo e($log->message ?: '-'); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-muted">Belum ada log.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/history/show.blade.php ENDPATH**/ ?>