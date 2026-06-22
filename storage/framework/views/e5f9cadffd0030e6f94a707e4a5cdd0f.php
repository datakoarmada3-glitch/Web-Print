<?php $__env->startSection('title', 'Kelola User'); ?>
<?php $__env->startSection('content'); ?>

<div class="card">
    <div class="card-header">
        <h3>Daftar User</h3>
        <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary btn-sm">+ Tambah User</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Username</th><th>Nama</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><code style="font-size:12px"><?php echo e($user->username); ?></code></td>
                        <td><?php echo e($user->name); ?></td>
                        <td><span class="badge badge-<?php echo e($user->role === 'admin' ? 'purple' : 'primary'); ?>"><?php echo e(ucfirst($user->role)); ?></span></td>
                        <td><span class="badge badge-<?php echo e($user->is_active ? 'success' : 'secondary'); ?>"><?php echo e($user->is_active ? 'Aktif' : 'Nonaktif'); ?></span></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="<?php echo e(route('admin.users.toggle-active', $user)); ?>" class="d-inline"><?php echo csrf_field(); ?>
                                <button class="btn btn-<?php echo e($user->is_active ? 'warning' : 'success'); ?> btn-sm"><?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php if($users->hasPages()): ?><div class="card-footer"><?php echo e($users->links()); ?></div><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>