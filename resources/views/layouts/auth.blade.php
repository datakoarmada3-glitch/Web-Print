<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Web Printer') }} - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
</head>
<body class="d-flex flex-column bg-white">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <h1 class="fw-bold">🖨️ Web Printer</h1>
                <p class="text-secondary">Sistem Print Terpusat Kantor</p>
            </div>
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>
</html>
