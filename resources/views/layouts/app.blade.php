<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Web Printer') }}</title>
    <style>
        :root {
            --bg: #f4f7fb; --card: #fff; --border: #dfe7f1; --text: #172033;
            --muted: #64748b; --primary: #2563eb; --primary-hover: #1d4ed8;
            --success: #059669; --danger: #dc2626; --warning: #d97706;
            --info: #2563eb; --accent: #06b6d4; --sidebar-bg: #0b1220; --sidebar-text: #9fb0c8;
            --sidebar-active: #fff; --radius: 12px; --radius-lg: 16px;
            --shadow-sm: 0 1px 2px rgba(15,23,42,.04); --shadow-md: 0 14px 34px rgba(15,23,42,.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--text); font-size: 14px; line-height: 1.5; }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* Layout */
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: var(--sidebar-bg); color: var(--sidebar-text); position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; z-index: 200; transition: transform .25s ease; }
        .sidebar-brand { padding: 20px; font-size: 18px; font-weight: 700; color: #fff; border-bottom: 1px solid rgba(255,255,255,.08); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .brand-mark { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .brand-logo { width: 38px; height: 38px; object-fit: contain; flex-shrink: 0; border-radius: 8px; background: rgba(255,255,255,.08); padding: 4px; }
        .brand-fallback { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: rgba(255,255,255,.08); color: #fff; font-size: 14px; font-weight: 700; flex-shrink: 0; }
        .brand-copy { min-width: 0; }
        .brand-title { display: block; color: #fff; line-height: 1.2; }
        .brand-subtitle { display: block; font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 2px; }
        .sidebar-close { display: none; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; padding: 4px; }
        .hidden { display: none !important; }
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
        .print-job-layout { display: grid; grid-template-columns: minmax(360px, 0.9fr) minmax(520px, 1.1fr); gap: 16px; align-items: start; }
        .preview-card { position: sticky; top: 72px; }
        .preview-frame { width: 100%; height: calc(100vh - 220px); min-height: 560px; border: 1px solid var(--border); border-radius: var(--radius); background: #fff; }
        .print-actions { display: grid; grid-template-columns: 1fr auto auto; gap: 8px; align-items: end; }
        .compact-log { max-height: 220px; overflow-y: auto; }

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
            .print-job-layout { grid-template-columns: 1fr; }
            .preview-card { position: static; }
            .preview-frame { height: 520px; min-height: 420px; }
            .print-actions { grid-template-columns: 1fr; }
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


        /* 2026 UI refresh: presentation-only overrides */
        html { color-scheme: light; }
        body { font-family: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif; background: var(--bg); color: var(--text); line-height: 1.55; -webkit-font-smoothing: antialiased; }
        body::before { content: ""; position: fixed; inset: 0; pointer-events: none; z-index: -1; background: radial-gradient(circle at 88% 0%, rgba(37,99,235,.07), transparent 26rem), radial-gradient(circle at 38% 100%, rgba(6,182,212,.045), transparent 30rem); }
        button, input, select { font: inherit; }
        :focus-visible { outline: 3px solid rgba(37,99,235,.25); outline-offset: 2px; }

        .sidebar { width: 264px; background: linear-gradient(180deg, #0b1220 0%, #0e192b 56%, #0b1424 100%); border-right: 1px solid rgba(148,163,184,.1); box-shadow: 16px 0 44px rgba(15,23,42,.08); transition-duration: .22s; scrollbar-width: thin; scrollbar-color: #26364f transparent; }
        .sidebar-brand { padding: 22px 18px 19px; border-bottom-color: rgba(255,255,255,.075); }
        .brand-mark { gap: 12px; }
        .brand-logo, .brand-fallback { width: 42px; height: 42px; border-radius: 12px; }
        .brand-logo { padding: 5px; background: rgba(255,255,255,.1); box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); }
        .brand-fallback { background: linear-gradient(145deg, var(--primary), var(--accent)); font-size: 13px; font-weight: 800; letter-spacing: .04em; box-shadow: 0 10px 22px rgba(37,99,235,.28); }
        .brand-title { letter-spacing: -.02em; }
        .brand-subtitle { margin-top: 4px; color: #7f93ae; font-size: 10px; font-weight: 650; text-transform: uppercase; letter-spacing: .12em; }
        .sidebar-close { width: 34px; height: 34px; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,.08); border-radius: 10px; background: rgba(255,255,255,.06); color: #cbd5e1; line-height: 1; }
        .sidebar-nav { padding: 14px 12px 24px; }
        .sidebar-section { padding: 17px 10px 7px; margin-top: 6px; color: #5f7390; font-size: 10px; font-weight: 750; letter-spacing: .14em; }
        .sidebar-link { gap: 11px; margin: 3px 0; padding: 9px 10px; border: 0; border-radius: 11px; font-size: 13px; font-weight: 570; transition: color .15s, background .15s, transform .15s; }
        .sidebar-link:hover { background: rgba(148,163,184,.09); color: #eef5ff; transform: translateX(2px); }
        .sidebar-link.active { color: #fff; background: linear-gradient(90deg, rgba(37,99,235,.28), rgba(6,182,212,.09)); box-shadow: inset 0 0 0 1px rgba(96,165,250,.16); }
        .nav-icon { width: 32px; height: 32px; flex: 0 0 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 9px; color: #7f93ae; background: rgba(148,163,184,.07); font-size: 15px; }
        .sidebar-link.active .nav-icon { color: #bfdbfe; background: rgba(37,99,235,.28); }
        .sidebar-overlay { background: rgba(3,7,18,.62); backdrop-filter: blur(3px); }

        .main { margin-left: 264px; }
        .topbar { height: 70px; padding: 0 28px; background: rgba(255,255,255,.86); border-bottom-color: rgba(223,231,241,.9); backdrop-filter: blur(14px); box-shadow: 0 1px 0 rgba(255,255,255,.75); }
        .topbar-heading { display: flex; flex-direction: column; }
        .topbar-kicker { margin-bottom: 3px; color: var(--primary); font-size: 10px; line-height: 1.2; font-weight: 750; text-transform: uppercase; letter-spacing: .13em; }
        .topbar-title { font-size: 17px; font-weight: 720; letter-spacing: -.02em; }
        .user-chip { display: flex; align-items: center; gap: 9px; padding: 5px 10px 5px 6px; border: 1px solid var(--border); border-radius: 999px; background: #fff; box-shadow: var(--shadow-sm); }
        .user-avatar { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #1d4ed8; background: #dbeafe; font-size: 13px; font-weight: 800; }
        .topbar-user { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #334155; font-size: 12px; font-weight: 650; }
        .topbar-menu { width: 38px; height: 38px; align-items: center; justify-content: center; padding: 0; border: 1px solid var(--border); border-radius: 11px; background: #fff; box-shadow: var(--shadow-sm); }
        .content { width: 100%; max-width: 1440px; margin: 0 auto; padding: 28px; }

        .card { border-radius: var(--radius-lg); margin-bottom: 18px; box-shadow: var(--shadow-sm); overflow: hidden; }
        .card-header { min-height: 58px; padding: 15px 20px; background: linear-gradient(180deg, #fff, #fcfdff); }
        .card-header h3 { font-size: 14px; font-weight: 720; letter-spacing: -.01em; }
        .card-body { padding: 20px; }
        .card-footer { padding: 14px 20px; background: #fbfdff; }
        .stats-grid { grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 14px; }
        .stat-card { position: relative; padding: 18px 20px; border-radius: var(--radius-lg); background: linear-gradient(145deg, #fff 45%, #f8fbff); box-shadow: var(--shadow-sm); overflow: hidden; transition: transform .18s, box-shadow .18s, border-color .18s; }
        .stat-card::after { content: ""; position: absolute; width: 74px; height: 74px; right: -24px; top: -28px; border-radius: 50%; background: rgba(37,99,235,.075); }
        .stat-card:hover { transform: translateY(-2px); border-color: #c8d7eb; box-shadow: 0 10px 25px rgba(15,23,42,.075); }
        .stat-label { font-size: 10px; font-weight: 720; letter-spacing: .1em; }
        .stat-value { margin-top: 7px; font-size: 30px; line-height: 1.15; font-weight: 760; letter-spacing: -.04em; font-variant-numeric: tabular-nums; }

        th { padding: 11px 16px; border-bottom-width: 1px; background: #f8fafc; font-size: 10px; font-weight: 750; letter-spacing: .08em; }
        td { padding: 12px 16px; border-bottom-color: #edf2f7; }
        tbody tr:last-child td { border-bottom: 0; }
        tr:hover td { background: #f8fbff; }
        .mobile-card { border-radius: 14px; padding: 15px 16px; margin-bottom: 10px; box-shadow: var(--shadow-sm); }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 10px; line-height: 1.4; font-weight: 750; letter-spacing: .035em; border: 1px solid transparent; }
        .btn { min-height: 40px; justify-content: center; border: 1px solid transparent; border-radius: 11px; font-weight: 650; transition: transform .15s, background .15s, border-color .15s, box-shadow .15s; }
        .btn:active { transform: translateY(1px); }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); box-shadow: 0 7px 16px rgba(37,99,235,.2); }
        .btn-primary:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); box-shadow: 0 9px 20px rgba(37,99,235,.25); }
        .btn-ghost { background: #fff; border-color: var(--border); box-shadow: var(--shadow-sm); }
        .btn-ghost:hover { border-color: #cbd7e6; }
        .btn-sm { min-height: 34px; padding: 6px 12px; border-radius: 9px; }
        .form-label { margin-bottom: 7px; color: #334155; font-size: 12px; font-weight: 680; }
        .form-input, .form-select { min-height: 42px; border-color: #ced9e7; border-radius: 11px; background: #fff; color: var(--text); }
        .form-input:hover, .form-select:hover { border-color: #aebfd3; }
        .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .alert { padding: 13px 16px; border-radius: 12px; font-weight: 540; box-shadow: var(--shadow-sm); }
        .preview-card { top: 88px; }

        @media (max-width: 768px) {
            .sidebar-close, .topbar-menu { display: inline-flex; }
            .topbar { height: 64px; padding: 0 16px; }
            .topbar-kicker, .user-chip { display: none; }
            .content { padding: 18px 14px; }
            .card-header { padding: 13px 15px; }
            .card-body { padding: 16px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }

        @stack('styles')
    </style>
</head>
<body>
    <div class="layout">
        {{-- Sidebar overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="brand-logo" onerror="this.classList.add('hidden');this.nextElementSibling.classList.remove('hidden')">
                    <span class="brand-fallback hidden">WP</span>
                    <span class="brand-copy">
                        <span class="brand-title">Web Printer</span>
                        <span class="brand-subtitle">Print Terpusat</span>
                    </span>
                </div>
                <button class="sidebar-close" onclick="closeSidebar()">×</button>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">▦</span><span>Dashboard</span>
                </a>
                <a href="{{ route('print-jobs.create') }}" class="sidebar-link {{ request()->routeIs('print-jobs.create') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">⌁</span><span>Print Dokumen</span>
                </a>
                <a href="{{ route('print-jobs.index') }}" class="sidebar-link {{ request()->routeIs('print-jobs.index') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">≡</span><span>Riwayat Print</span>
                </a>

                @if(auth()->user()->role === 'admin')
                    <div class="sidebar-section">Admin</div>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">⌁</span><span>Dashboard Admin</span>
                    </a>
                    <a href="{{ route('admin.queue.index') }}" class="sidebar-link {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">◷</span><span>Antrean Print</span>
                    </a>
                    <a href="{{ route('admin.history.index') }}" class="sidebar-link {{ request()->routeIs('admin.history.*') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">≣</span><span>Semua Riwayat</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">♙</span><span>Kelola User</span>
                    </a>
                    <a href="{{ route('admin.printers.index') }}" class="sidebar-link {{ request()->routeIs('admin.printers.*') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">▣</span><span>Printer</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true">⚙</span><span>Pengaturan</span>
                    </a>
                @endif
            </nav>
        </aside>

        {{-- Main --}}
        <div class="main">
            <header class="topbar">
                <div class="flex items-center gap-2">
                    <button class="topbar-menu" type="button" aria-label="Buka navigasi" onclick="openSidebar()">☰</button>
                    <div class="topbar-heading">
                        <span class="topbar-kicker">Ruang kerja</span>
                        <div class="topbar-title">@yield('title', 'Dashboard')</div>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="user-chip">
                        <span class="user-avatar" aria-hidden="true">●</span>
                        <span class="topbar-user">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                    </form>
                </div>
            </header>

            <div class="content">
                @if(session('success'))
                    <div class="alert alert-success">
                        ✅ {{ session('success') }}
                        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        ❌ {{ session('error') }}
                        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif

                @yield('content')
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
    @stack('scripts')
</body>
</html>
