<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Web Printer') }} - Login</title>
    <style>
        :root {
            --ink: #172033; --muted: #64748b; --line: #dfe7f1; --canvas: #f4f7fb;
            --primary: #2563eb; --primary-dark: #1d4ed8; --accent: #06b6d4;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { color-scheme: light; }
        body {
            min-height: 100vh; display: grid; place-items: center; padding: 28px;
            font-family: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif;
            color: var(--ink); background: var(--canvas); -webkit-font-smoothing: antialiased;
        }
        body::before, body::after { content: ""; position: fixed; z-index: -1; border-radius: 50%; filter: blur(2px); pointer-events: none; }
        body::before { width: 520px; height: 520px; top: -240px; right: -130px; background: radial-gradient(circle, rgba(37,99,235,.13), transparent 68%); }
        body::after { width: 430px; height: 430px; bottom: -230px; left: -90px; background: radial-gradient(circle, rgba(6,182,212,.1), transparent 68%); }

        .login-shell {
            width: min(920px, 100%); min-height: 570px; display: grid; grid-template-columns: .92fr 1.08fr;
            overflow: hidden; border: 1px solid rgba(203,213,225,.78); border-radius: 24px; background: #fff;
            box-shadow: 0 30px 80px rgba(15,23,42,.14), 0 2px 8px rgba(15,23,42,.04);
        }
        .login-story {
            position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;
            padding: 42px; color: #fff; background: linear-gradient(150deg, #08111f 0%, #10213a 62%, #0c3152 100%);
        }
        .login-story::before { content: ""; position: absolute; width: 360px; height: 360px; right: -190px; top: -150px; border-radius: 50%; border: 70px solid rgba(96,165,250,.08); }
        .login-story::after { content: ""; position: absolute; width: 210px; height: 210px; left: -120px; bottom: -100px; border-radius: 50%; background: rgba(6,182,212,.08); }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 13px; }
        .login-logo, .login-fallback { width: 46px; height: 46px; border-radius: 13px; flex: 0 0 46px; }
        .login-logo { object-fit: contain; padding: 5px; background: rgba(255,255,255,.1); }
        .login-fallback { display: inline-flex; align-items: center; justify-content: center; color: #fff; background: linear-gradient(145deg, var(--primary), var(--accent)); font-size: 13px; font-weight: 800; letter-spacing: .04em; box-shadow: 0 12px 28px rgba(37,99,235,.3); }
        .hidden { display: none !important; }
        .brand-title { display: block; font-size: 17px; font-weight: 760; letter-spacing: -.02em; }
        .brand-caption { display: block; margin-top: 2px; color: #91a8c5; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; }
        .story-copy { position: relative; z-index: 1; margin: 64px 0 44px; }
        .story-eyebrow { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px; color: #9edcf0; font-size: 11px; font-weight: 750; text-transform: uppercase; letter-spacing: .14em; }
        .story-eyebrow::before { content: ""; width: 24px; height: 1px; background: #22d3ee; }
        .story-copy h1 { max-width: 330px; font-size: clamp(30px, 4vw, 42px); line-height: 1.08; letter-spacing: -.045em; }
        .story-copy p { max-width: 325px; margin-top: 18px; color: #aebed2; font-size: 14px; line-height: 1.7; }
        .story-footer { position: relative; z-index: 1; display: flex; align-items: center; gap: 9px; color: #8da1ba; font-size: 11px; }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: #34d399; box-shadow: 0 0 0 5px rgba(52,211,153,.1); }

        .login-panel { display: flex; align-items: center; padding: 54px clamp(34px, 6vw, 68px); }
        .login-content { width: 100%; max-width: 390px; margin: 0 auto; }
        .mobile-brand { display: none; }
        .form-heading { margin-bottom: 30px; }
        .form-heading .eyebrow { color: var(--primary); font-size: 11px; font-weight: 760; text-transform: uppercase; letter-spacing: .13em; }
        .form-heading h2 { margin-top: 8px; font-size: 28px; line-height: 1.2; font-weight: 760; letter-spacing: -.035em; }
        .form-heading p { margin-top: 9px; color: var(--muted); font-size: 13px; line-height: 1.6; }
        .form-group { margin-bottom: 17px; }
        .form-group label { display: block; margin-bottom: 7px; color: #334155; font-size: 12px; font-weight: 680; }
        .form-input {
            width: 100%; min-height: 46px; padding: 10px 13px; border: 1px solid #ced9e7; border-radius: 12px;
            color: var(--ink); background: #fff; font: inherit; font-size: 14px; outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .form-input:hover { border-color: #aebfd3; }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37,99,235,.11); }
        .form-input.is-invalid { border-color: #dc2626; }
        .form-check { display: flex; align-items: center; gap: 9px; margin: 5px 0 23px; color: var(--muted); font-size: 12px; cursor: pointer; }
        .form-check input { width: 17px; height: 17px; accent-color: var(--primary); }
        .btn-primary {
            width: 100%; min-height: 46px; padding: 11px 18px; border: 0; border-radius: 12px; cursor: pointer;
            color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); font: inherit; font-size: 13px; font-weight: 720;
            box-shadow: 0 10px 22px rgba(37,99,235,.23); transition: transform .15s, box-shadow .15s, background .15s;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); box-shadow: 0 12px 27px rgba(37,99,235,.28); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }
        .alert-error { margin-bottom: 18px; padding: 11px 13px; border: 1px solid #fecaca; border-radius: 11px; color: #991b1b; background: #fef2f2; font-size: 12px; line-height: 1.5; }
        :focus-visible { outline: 3px solid rgba(37,99,235,.24); outline-offset: 2px; }

        @media (max-width: 760px) {
            body { padding: 18px; }
            .login-shell { min-height: 0; grid-template-columns: 1fr; border-radius: 20px; }
            .login-story { display: none; }
            .login-panel { padding: 34px 28px 38px; }
            .mobile-brand { display: flex; align-items: center; gap: 11px; margin-bottom: 34px; }
            .mobile-brand .brand-title { color: var(--ink); }
            .mobile-brand .brand-caption { color: var(--muted); }
        }
        @media (max-width: 420px) {
            body { padding: 0; background: #fff; }
            .login-shell { min-height: 100vh; border: 0; border-radius: 0; box-shadow: none; }
            .login-panel { padding: 30px 22px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-story" aria-label="Tentang Web Printer">
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="login-logo" onerror="this.classList.add('hidden');this.nextElementSibling.classList.remove('hidden')">
                <span class="login-fallback hidden">WP</span>
                <span>
                    <span class="brand-title">Web Printer</span>
                    <span class="brand-caption">Print terpusat</span>
                </span>
            </div>
            <div class="story-copy">
                <span class="story-eyebrow">Ruang kerja digital</span>
                <h1>Mencetak lebih cepat, rapi, dan terpantau.</h1>
                <p>Kirim dokumen, pilih printer, dan pantau setiap pekerjaan cetak dari satu tempat.</p>
            </div>
            <div class="story-footer"><span class="status-dot"></span><span>Layanan siap digunakan</span></div>
        </section>

        <section class="login-panel">
            <div class="login-content">
                <div class="mobile-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="login-logo" onerror="this.classList.add('hidden');this.nextElementSibling.classList.remove('hidden')">
                    <span class="login-fallback hidden">WP</span>
                    <span>
                        <span class="brand-title">Web Printer</span>
                        <span class="brand-caption">Print terpusat</span>
                    </span>
                </div>
                <div class="form-heading">
                    <span class="eyebrow">Selamat datang</span>
                    <h2>Masuk ke akun Anda</h2>
                    <p>Gunakan akun kantor untuk mengakses layanan pencetakan.</p>
                </div>

                @if($errors->any())
                    <div class="alert-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-input @error('username') is-invalid @enderror" value="{{ old('username') }}" autocomplete="username" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-input" autocomplete="current-password" required>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                    <button type="submit" class="btn-primary">Masuk ke Web Printer</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
