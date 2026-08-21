<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panitia — Yudisium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #F3F4F6;
        }

        /* ── Left panel ── */
        .auth-left {
            width: 420px;
            flex-shrink: 0;
            background: linear-gradient(145deg, #1E1B4B 0%, #4F46E5 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -80px; right: -80px;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -40px; left: -40px;
        }
        .auth-left-content { position: relative; z-index: 1; text-align: center; }
        .auth-brand-icon {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: white;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
        }
        .auth-brand-title {
            font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 8px;
        }
        .auth-brand-subtitle {
            font-size: 14px; color: rgba(255,255,255,0.65); line-height: 1.6;
        }
        .auth-feature {
            margin-top: 40px;
            display: flex; flex-direction: column; gap: 14px;
            text-align: left; width: 100%;
        }
        .auth-feature-item {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.08);
            border-radius: 10px; padding: 12px 14px;
        }
        .auth-feature-item i {
            font-size: 18px; color: #A5B4FC; flex-shrink: 0;
        }
        .auth-feature-item span {
            font-size: 13px; color: rgba(255,255,255,0.8);
        }

        /* ── Right panel (form) ── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }
        .auth-form-wrap {
            width: 100%; max-width: 420px;
        }
        .auth-form-title {
            font-size: 22px; font-weight: 700;
            color: #111827; margin-bottom: 6px;
        }
        .auth-form-subtitle {
            font-size: 14px; color: #6B7280; margin-bottom: 32px;
        }
        .form-label {
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 6px;
        }
        .form-control {
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: #fff;
        }
        .form-control:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
            outline: none;
        }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap i.field-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF; font-size: 15px;
            pointer-events: none;
        }
        .input-icon-wrap .form-control { padding-left: 38px; }

        /* Sembunyikan ikon mata bawaan browser (Microsoft Edge & Chrome) */
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }

        .input-icon-wrap .toggle-pw {
            position: absolute; right: 0; top: 0;
            height: 100%; width: 42px;
            display: flex; align-items: center; justify-content: center;
            color: #9CA3AF; font-size: 15px;
            cursor: pointer; background: none; border: none; padding: 0;
            border-radius: 0 10px 10px 0;
            z-index: 2;
        }
        .input-icon-wrap .toggle-pw:hover { color: #4F46E5; background: rgba(79,70,229,0.05); }
        .input-icon-wrap .form-control.has-toggle { padding-right: 42px; }

        .btn-auth {
            width: 100%;
            background: #4F46E5;
            color: #fff; border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px; font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-auth:hover {
            background: #4338CA;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(79,70,229,0.35);
        }
        .btn-auth:active { transform: translateY(0); }

        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: #E5E7EB;
        }
        .divider span { font-size: 12px; color: #9CA3AF; white-space: nowrap; }

        .auth-link {
            display: block; text-align: center;
            font-size: 13px; color: #6B7280;
        }
        .auth-link a { color: #4F46E5; font-weight: 600; text-decoration: none; }
        .auth-link a:hover { text-decoration: underline; }

        .remember-row {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 20px;
        }
        .remember-row label { font-size: 13px; color: #374151; cursor: pointer; }
        .remember-row input[type=checkbox] { accent-color: #4F46E5; }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-left { display: none; }
        }
    </style>
</head>
<body>

    {{-- Left panel --}}
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="auth-brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="auth-brand-title">Yudisium FASILKOM</div>
            <div class="auth-brand-subtitle">
                Sistem Manajemen Absensi Panitia<br>Ceremonial Yudisium
            </div>
            <div class="auth-feature">
                <div class="auth-feature-item">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Scan QR Code peserta otomatis</span>
                </div>
                <div class="auth-feature-item">
                    <i class="bi bi-people-fill"></i>
                    <span>Kelola data peserta real-time</span>
                </div>
                <div class="auth-feature-item">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Sinkron langsung ke Google Sheet</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="auth-right">
        <div class="auth-form-wrap">

            <div class="auth-form-title">Selamat datang 👋</div>
            <div class="auth-form-subtitle">Masuk ke panel panitia Yudisium</div>

            {{-- Error --}}
            @if($errors->any())
                <div class="alert alert-danger" style="border-radius:10px;font-size:13px;margin-bottom:20px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               placeholder="panitia@email.com"
                               value="{{ old('email') }}" autocomplete="off" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" name="password" id="passwordInput"
                               class="form-control has-toggle @error('password') is-invalid @enderror"
                               placeholder="••••••••" autocomplete="new-password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('passwordInput', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember" class="me-1">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn-auth">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>

            <div class="divider"><span>atau</span></div>

            <p class="auth-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai panitia</a>
            </p>

        </div>
    </div>

<script>
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type   = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type   = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
