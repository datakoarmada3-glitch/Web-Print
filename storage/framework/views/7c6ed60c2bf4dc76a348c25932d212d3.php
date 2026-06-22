<?php $__env->startSection('title', 'Print Dokumen'); ?>
<?php $__env->startSection('content'); ?>

<div style="max-width:680px">
<form method="POST" action="<?php echo e(route('print-jobs.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="card">
        <div class="card-header"><h3>Upload Dokumen</h3></div>
        <div class="card-body">
            <div class="form-group full">
                <label class="form-label">Pilih File</label>
                <input type="file" name="document" class="form-input <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <div class="form-hint">Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG · Maks <?php echo e(config('print.upload_max_size_mb', 50)); ?> MB</div>
                <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Opsi Print</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Jumlah Copy</label>
                    <input type="number" name="copies" class="form-input <?php $__errorArgs = ['copies'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" min="1" max="99" value="<?php echo e(old('copies', 1)); ?>">
                    <?php $__errorArgs = ['copies'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Ukuran Kertas</label>
                    <select name="paper_size" class="form-select">
                        <option value="A4">A4 (210 × 297 mm)</option>
                        <option value="F4">F4 / Folio (215 × 330 mm)</option>
                        <option value="Legal">Legal (216 × 356 mm)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Orientasi</label>
                    <select name="orientation" class="form-select">
                        <option value="portrait">Portrait (tegak)</option>
                        <option value="landscape">Landscape (miring)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Duplex</label>
                    <select name="duplex" class="form-select">
                        <option value="none">Satu Sisi</option>
                        <option value="long-edge">Dua Sisi – Tepi Panjang</option>
                        <option value="short-edge">Dua Sisi – Tepi Pendek</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mode Warna</label>
                    <select name="color_mode" class="form-select">
                        <option value="grayscale">Hitam Putih</option>
                        <option value="color">Berwarna</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Range Halaman <span class="text-muted">(opsional)</span></label>
                    <input type="text" name="page_range" class="form-input <?php $__errorArgs = ['page_range'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('page_range')); ?>" placeholder="mis. 1-5 atau 1,3,5-10">
                    <?php $__errorArgs = ['page_range'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
        <div class="card-footer" style="text-align:right">
            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-ghost" style="margin-right:8px">Batal</a>
            <button type="submit" class="btn btn-primary">🖨️ Kirim ke Antrean Print</button>
        </div>
    </div>
</form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/print-jobs/create.blade.php ENDPATH**/ ?>