<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Web Printer') }} - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <style>
        .login-logo { width: 88px; height: 88px; object-fit: contain; margin: 0 auto 12px; display: block; }
        .login-fallback { width: 88px; height: 88px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; border-radius: 20px; background: #e0f2fe; color: #0369a1; font-size: 24px; font-weight: 800; }
        .hidden { display: none !important; }
    </style>
</head>
<body class="d-flex flex-column bg-white">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="login-logo" onerror="this.classList.add('hidden');this.nextElementSibling.classList.remove('hidden')">
                <div class="login-fallback hidden">WP</div>
                <h1 class="fw-bold">Web Printer</h1>
                <p class="text-secondary">Sistem Print Terpusat Kantor</p>
            </div>
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>
</html>
