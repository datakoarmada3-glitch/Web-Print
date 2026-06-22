<?php $__env->startSection('title', 'Detail Print Job'); ?>

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
                    <div class="col-md-6"><strong>Nama File:</strong><br><?php echo e($printJob->original_filename); ?></div>
                    <div class="col-md-6"><strong>Tipe File:</strong><br><?php echo e(strtoupper($printJob->file_type)); ?></div>
                    <div class="col-md-6"><strong>Ukuran:</strong><br><?php echo e($printJob->fileSizeFormatted()); ?></div>
                    <div class="col-md-6"><strong>Printer:</strong><br><?php echo e($printJob->printer->name); ?></div>
                    <div class="col-md-4"><strong>Copy:</strong><br><?php echo e($printJob->copies); ?></div>
                    <div class="col-md-4"><strong>Kertas:</strong><br><?php echo e($printJob->paper_size->value); ?></div>
                    <div class="col-md-4"><strong>Orientasi:</strong><br><?php echo e($printJob->orientation->label()); ?></div>
                    <div class="col-md-4"><strong>Duplex:</strong><br><?php echo e($printJob->duplex->label()); ?></div>
                    <div class="col-md-4"><strong>Warna:</strong><br><?php echo e($printJob->color_mode->label()); ?></div>
                    <div class="col-md-4"><strong>Range:</strong><br><?php echo e($printJob->page_range ?: '-'); ?></div>
                    <div class="col-md-6"><strong>CUPS Job ID:</strong><br><?php echo e($printJob->cups_job_id ?: '-'); ?></div>
                    <div class="col-md-6"><strong>Halaman:</strong><br><?php echo e($printJob->page_count ?: '-'); ?></div>
                    <div class="col-12"><strong>Error:</strong><br><?php echo e($printJob->error_message ?: '-'); ?></div>
                </div>
            </div>
            <?php if($printJob->isCancellable()): ?>
                <div class="card-footer text-end">
                    <form method="POST" action="<?php echo e(route('print-jobs.cancel', $printJob)); ?>" onsubmit="return confirm('Batalkan print job ini?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-danger">Batalkan Job</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Log Status</h3>
            </div>
            <div class="list-group list-group-flush list-group-hoverable">
                <?php $__empty_1 = true; $__currentLoopData = $printJob->logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo e(ucfirst($log->status)); ?></strong>
                            <small class="text-muted"><?php echo e($log->created_at->format('d/m H:i')); ?></small>
                        </div>
                        <div class="text-secondary small"><?php echo e($log->message ?: '-'); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-muted">Belum ada log status.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/print-jobs/show.blade.php ENDPATH**/ ?>