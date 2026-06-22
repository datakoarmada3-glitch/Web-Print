<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - <?php echo e(config('app.name', 'Web Printer')); ?></title>
    <style>
        :root {
            --bg: #f0f2f5; --card: #fff; --border: #e5e7eb; --text: #1a1a2e;
            --muted: #6b7280; --primary: #3b82f6; --primary-hover: #2563eb;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --info: #3b82f6; --sidebar-bg: #1e293b; --sidebar-text: #94a3b8;
            --sidebar-active: #fff; --radius: 8px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--text); font-size: 14px; line-height: 1.5; }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* Layout */
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: var(--sidebar-bg); color: var(--sidebar-text); position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; z-index: 200; transition: transform .25s ease; }
        .sidebar-brand { padding: 20px; font-size: 18px; font-weight: 700; color: #fff; border-bottom: 1px solid rgba(255,255,255,.08); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-close { display: none; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; padding: 4px; }
        .sidebar-nav { padding: 12px 0; }
        .sidebar-section { padding: 8px 20px 4px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #475569; margin-top: 8px; }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: var(--sidebar-text); font-size: 13px; transition: all .15s; border-left: 3px solid transparent; }
        .sidebar-link:hover { background: rgba(255,255,255,.05); color: #e2e8f0; text-decoration: none; }
        .sidebar-link.active { color: var(--sidebar-active); background: rgba(255,255,255,.08); border-left-color: var(--primary); }

        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 150; }

        .main { margin-left: 240px; flex: 1; min-width: 0; }
        .topbar { background: var(--card); border-bottom: 1px solid var(--border); padding: 0 24px; height: 56px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 16px; font-weight: 600; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { font-size: 13px; color: var(--muted); }
        .topbar-menu { display: none; background: none; border: none; font-size: 22px; cursor: pointer; padding: 6px; color: var(--text); }

        .content { padding: 24px; max-width: 1200px; }

        /* Cards */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 16px; }
        .card-header { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .card-header h3 { font-size: 14px; font-weight: 600; }
        .card-body { padding: 18px; }
        .card-footer { padding: 12px 18px; border-top: 1px solid var(--border); }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 18px; }
        .stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; }
        .stat-value { font-size: 28px; font-weight: 700; margin-top: 4px; }
        .stat-value.green { color: var(--success); }
        .stat-value.red { color: var(--danger); }
        .stat-value.blue { color: var(--info); }
        .stat-value.muted { color: var(--muted); }

        /* Tables */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 14px; font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; border-bottom: 2px solid var(--border); white-space: nowrap; }
        td { padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 13px; }
        tr:hover td { background: #f9fafb; }
        .text-muted { color: var(--muted); }
        .text-center { text-align: center; }
        .text-end { text-align: right; }

        /* Mobile card list (used as alternative to tables on mobile) */
        .mobile-cards { display: none; }
        .mobile-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 8px; }
        .mobile-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .mobile-card-row { display: flex; justify-content: space-between; font-size: 13px; padding: 2px 0; }
        .mobile-card-row .label { color: var(--muted); }

        /* Badges */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: .2px; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-primary { background: #dbeafe; color: #1d4ed8; }
        .badge-secondary { background: #f3f4f6; color: #4b5563; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius); font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all .15s; text-decoration: none; line-height: 1.4; white-space: nowrap; }
        .btn:hover { text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-warning { background: var(--warning); color: #fff; }
        .btn-warning:hover { background: #d97706; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #059669; }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: #f9fafb; color: var(--text); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
        .form-group { margin-bottom: 0; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .form-input, .form-select { width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: var(--radius); font-size: 14px; outline: none; transition: border-color .15s, box-shadow .15s; font-family: inherit; -webkit-appearance: none; }
        .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        .form-input.is-invalid { border-color: var(--danger); }
        .form-hint { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: var(--radius); font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .alert-close { margin-left: auto; cursor: pointer; opacity: .6; background: none; border: none; font-size: 16px; }
        .alert-close:hover { opacity: 1; }

        /* Detail grid */
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
        .detail-item label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; }
        .detail-item .val { font-size: 14px; font-weight: 500; margin-top: 2px; word-break: break-word; }

        /* Log list */
        .log-item { padding: 10px 14px; border-bottom: 1px solid var(--border); }
        .log-item:last-child { border-bottom: none; }
        .log-header { display: flex; justify-content: space-between; align-items: center; }
        .log-status { font-weight: 600; font-size: 13px; }
        .log-time { font-size: 11px; color: var(--muted); }
        .log-msg { font-size: 12px; color: var(--muted); margin-top: 2px; }

        /* Pagination */
        .pagination { display: flex; gap: 4px; justify-content: center; padding: 8px 0; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 6px 12px; border-radius: 6px; font-size: 13px; border: 1px solid var(--border); color: var(--text); }
        .pagination span.current { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination a:hover { background: #f3f4f6; text-decoration: none; }

        /* Utility */
        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mt-3 { margin-top: 12px; }
        .mb-3 { margin-bottom: 12px; }
        .d-inline { display: inline; }
        .row-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-close { display: block; }
            .sidebar-overlay.open { display: block; }
            .main { margin-left: 0; }
            .topbar { padding: 0 16px; }
            .topbar-menu { display: block; }
            .topbar-user { display: none; }
            .content { padding: 16px; }

            .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stat-card { padding: 12px 14px; }
            .stat-value { font-size: 22px; }

            .row-cards { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .detail-grid { grid-template-columns: 1fr 1fr; }

            .card-header { padding: 12px 14px; }
            .card-body { padding: 14px; }

            th, td { padding: 8px 10px; font-size: 12px; }

            .btn { padding: 10px 16px; font-size: 14px; }
            .btn-sm { padding: 8px 14px; font-size: 13px; }

            .form-input, .form-select { padding: 11px 14px; font-size: 16px; }

            /* Show mobile card list, hide table on small screens */
            .desktop-table { display: none; }
            .mobile-cards { display: block; }
        }

        @media (min-width: 769px) {
            .mobile-cards { display: none; }
            .desktop-table { display: block; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .detail-grid { grid-template-columns: 1fr; }
        }

        <?php echo $__env->yieldPushContent('styles'); ?>
    </style>
</head>
<body>
    <div class="layout">
        
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <span>🖨️ Web Printer</span>
                <button class="sidebar-close" onclick="closeSidebar()">×</button>
            </div>
            <nav class="sidebar-nav">
                <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    📊 Dashboard
                </a>
                <a href="<?php echo e(route('print-jobs.create')); ?>" class="sidebar-link <?php echo e(request()->routeIs('print-jobs.create') ? 'active' : ''); ?>">
                    🖨️ Print Dokumen
                </a>
                <a href="<?php echo e(route('print-jobs.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('print-jobs.index') ? 'active' : ''); ?>">
                    📋 Riwayat Print
                </a>

                <?php if(auth()->user()->role === 'admin'): ?>
                    <div class="sidebar-section">Admin</div>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                        📈 Dashboard Admin
                    </a>
                    <a href="<?php echo e(route('admin.queue.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.queue.*') ? 'active' : ''); ?>">
                        ⏳ Antrean Print
                    </a>
                    <a href="<?php echo e(route('admin.history.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.history.*') ? 'active' : ''); ?>">
                        📜 Semua Riwayat
                    </a>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                        👥 Kelola User
                    </a>
                    <a href="<?php echo e(route('admin.printers.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.printers.*') ? 'active' : ''); ?>">
                        🖨️ Printer
                    </a>
                    <a href="<?php echo e(route('admin.settings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
                        ⚙️ Pengaturan
                    </a>
                <?php endif; ?>
            </nav>
        </aside>

        
        <div class="main">
            <header class="topbar">
                <div class="flex items-center gap-2">
                    <button class="topbar-menu" onclick="openSidebar()">☰</button>
                    <div class="topbar-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></div>
                </div>
                <div class="topbar-right">
                    <span class="topbar-user"><?php echo e(auth()->user()->name); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                    </form>
                </div>
            </header>

            <div class="content">
                <?php if(session('success')): ?>
                    <div class="alert alert-success">
                        ✅ <?php echo e(session('success')); ?>

                        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger">
                        ❌ <?php echo e(session('error')); ?>

                        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>

    <script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }
    // Close sidebar on nav click (mobile)
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>