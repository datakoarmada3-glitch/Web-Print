<?php $__env->startSection('title', 'Dashboard Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="row row-deck row-cards">
    
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Job Hari Ini</div>
                <div class="h1 mt-2"><?php echo e($todayStats['jobs_today']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Halaman Hari Ini</div>
                <div class="h1 mt-2"><?php echo e($todayStats['pages_today']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Job Bulan Ini</div>
                <div class="h1 mt-2"><?php echo e($monthStats['jobs_month']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Halaman Bulan Ini</div>
                <div class="h1 mt-2"><?php echo e($monthStats['pages_month']); ?></div>
            </div>
        </div>
    </div>

    
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Sukses</div>
                <div class="h2 text-success mt-2"><?php echo e($statusCounts['success']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Gagal</div>
                <div class="h2 text-danger mt-2"><?php echo e($statusCounts['failed']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Dibatalkan</div>
                <div class="h2 text-secondary mt-2"><?php echo e($statusCounts['cancelled']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Dalam Antrean</div>
                <div class="h2 text-info mt-2"><?php echo e($statusCounts['pending']); ?></div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Print per Hari (30 Hari Terakhir)</h3></div>
            <div class="card-body">
                <canvas id="dailyChart" height="200"></canvas>
            </div>
        </div>
    </div>

    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top User Bulan Ini</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr><th>Nama</th><th>Job</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $topUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr><td><?php echo e($u['name']); ?></td><td><?php echo e($u['total_jobs']); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="2" class="text-muted text-center">Belum ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Print per Bulan (12 Bulan Terakhir)</h3></div>
            <div class="card-body">
                <canvas id="monthlyChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const dailyData = <?php echo json_encode($dailyChart, 15, 512) ?>;
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: dailyData.labels,
            datasets: [{
                label: 'Job',
                data: dailyData.jobs,
                backgroundColor: 'rgba(32, 107, 196, 0.6)',
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    const monthlyData = <?php echo json_encode($monthlyChart, 15, 512) ?>;
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyData.labels,
            datasets: [
                { label: 'Job', data: monthlyData.jobs, borderColor: '#206bc4', tension: 0.3 },
                { label: 'Halaman', data: monthlyData.pages, borderColor: '#2fb344', tension: 0.3 }
            ]
        },
        options: { responsive: true }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>