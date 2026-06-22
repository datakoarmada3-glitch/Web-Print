<?php $__env->startSection('title', 'Printer'); ?>
<?php $__env->startSection('content'); ?>

<div class="card">
    <div class="card-header"><h3>Daftar Printer</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>CUPS Name</th><th>IP</th><th>Status</th><th>Default</th><th></th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $printers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $printer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($printer->name); ?></td>
                        <td><code style="font-size:12px"><?php echo e($printer->cups_name); ?></code></td>
                        <td><?php echo e($printer->ip_address); ?></td>
                        <td><span class="badge badge-<?php echo e(match($printer->status) { 'online','idle'=>'success','printing'=>'info','error'=>'danger','paused'=>'warning',default=>'secondary' }); ?>"><?php echo e($printer->statusLabel()); ?></span></td>
                        <td><?php echo e($printer->is_default ? '✓' : '—'); ?></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('admin.printers.edit', $printer)); ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="<?php echo e(route('admin.printers.check-status', $printer)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-primary btn-sm">Cek</button></form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/printers/index.blade.php ENDPATH**/ ?>