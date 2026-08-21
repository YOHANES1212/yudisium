<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Panitia — Yudisium</title>
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
        .auth-left {
            width: 420px; flex-shrink: 0;
            background: linear-gradient(145deg, #1E1B4B 0%, #4F46E5 100%);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 48px 40px; position: relative; overflow: hidden;
        }
        .auth-left::before {
            content:''; position:absolute;
            width:320px;height:320px; border-radius:50%;
            background:rgba(255,255,255,0.05); top:-80px; right:-80px;
        }
        .auth-left::after {
            content:''; position:absolute;
            width:200px;height:200px; border-radius:50%;
            background:rgba(255,255,255,0.05); bottom:-40px; left:-40px;
        }
        .auth-left-content { position:relative;z-index:1;text-align:center; }
        .auth-brand-icon {
            width:72px;height:72px; background:rgba(255,255,255,0.15);
            border-radius:20px; display:flex;align-items:center;justify-content:center;
            font-size:36px;color:white; margin:0 auto 20px;
        }
        .auth-brand-title { font-size:24px;font-weight:700;color:#fff;margin-bottom:8px; }
        .auth-brand-subtitle { font-size:14px;color:rgba(255,255,255,0.65);line-height:1.6; }
        .auth-note {
            margin-top:32px;
            background:rgba(255,255,255,0.1);
            border-radius:12px; padding:16px 18px;
            font-size:13px;color:rgba(255,255,255,0.8);
            line-height:1.6; text-align:left;
        }
        .auth-note strong { color:#fff; }

        .auth-right {
            flex:1; display:flex;
            align-items:center; justify-content:center;
            padding:40px 24px;
        }
        .auth-form-wrap { width:100%;max-width:440px; }
        .auth-form-title { font-size:22px;font-weight:700;color:#111827;margin-bottom:6px; }
        .auth-form-subtitle { font-size:14px;color:#6B7280;margin-bottom:28px; }

        .form-label { font-size:13px;font-weight:600;color:#374151;margin-bottom:6px; }
        .form-control {
            border:1.5px solid #E5E7EB; border-radius:10px;
            padding:11px 14px; font-size:14px;
            font-family:'Inter',sans-serif; transition:all 0.2s; background:#fff;
        }
        .form-control:focus {
            border-color:#4F46E5;
            box-shadow:0 0 0 3px rgba(79,70,229,0.1); outline:none;
        }
        .form-control.is-invalid { border-color:#EF4444; }
        .invalid-feedback { font-size:12px;color:#EF4444;margin-top:4px; }

        .input-icon-wrap { position:relative; }
        .input-icon-wrap i.field-icon {
            position:absolute;left:12px;top:50%;
            transform:translateY(-50%);
            color:#9CA3AF;font-size:15px;pointer-events:none;
        }
        .input-icon-wrap .form-control { padding-left:38px; }
        /* Sembunyikan ikon mata bawaan browser (Microsoft Edge & Chrome) */
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }

        .input-icon-wrap .toggle-pw {
            position:absolute;right:6px;top:50%;
            transform:translateY(-50%);
            height:34px;width:34px;
            display:flex;align-items:center;justify-content:center;
            color:#4F46E5;font-size:18px;
            cursor:pointer;background:transparent;border:none;padding:0;
            border-radius:8px;
            z-index:10;
        }
        .input-icon-wrap .toggle-pw:hover { color:#3730A3;background:rgba(79,70,229,0.12); }
        .input-icon-wrap .form-control.has-toggle { padding-right:44px; }

        .password-strength {
            height:4px; border-radius:4px; margin-top:8px;
            background:#E5E7EB; overflow:hidden;
        }
        .password-strength-bar {
            height:100%; border-radius:4px;
            transition:all 0.3s; width:0%;
        }
        .strength-label { font-size:11px;margin-top:4px; }

        .btn-auth {
            width:100%; background:#4F46E5; color:#fff; border:none;
            border-radius:10px; padding:12px;
            font-size:14px;font-weight:600; font-family:'Inter',sans-serif;
            cursor:pointer; transition:all 0.2s;
            display:flex;align-items:center;justify-content:center;gap:8px;
        }
        .btn-auth:hover {
            background:#4338CA; transform:translateY(-1px);
            box-shadow:0 4px 14px rgba(79,70,229,0.35);
        }

        .divider {
            display:flex;align-items:center;gap:12px; margin:20px 0;
        }
        .divider::before,.divider::after {
            content:'';flex:1;height:1px;background:#E5E7EB;
        }
        .divider span { font-size:12px;color:#9CA3AF;white-space:nowrap; }

        .auth-link { display:block;text-align:center;font-size:13px;color:#6B7280; }
        .auth-link a { color:#4F46E5;font-weight:600;text-decoration:none; }
        .auth-link a:hover { text-decoration:underline; }

        @media (max-width:768px) { .auth-left { display:none; } }
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
                Daftar sebagai panitia untuk<br>mengakses sistem absensi
            </div>
            <div class="auth-note">
                <strong>⚠️ Perhatian:</strong><br>
                Akun yang didaftarkan akan langsung memiliki akses penuh ke admin panel.
                Pastikan hanya panitia resmi yang mendaftar.
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="auth-right">
        <div class="auth-form-wrap">

            <div class="auth-form-title">Daftar Panitia 🎓</div>
            <div class="auth-form-subtitle">Buat akun untuk mengakses admin panel</div>

            {{-- Error global --}}
            @if($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
                <div class="alert alert-danger" style="border-radius:10px;font-size:13px;margin-bottom:20px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" autocomplete="off">
                @csrf

                {{-- Nama --}}
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-person field-icon"></i>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Nama kamu" value="{{ old('name') }}" autocomplete="off" required autofocus>
                    </div>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="panitia@email.com" value="{{ old('email') }}" autocomplete="off" required>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PIN Panitia --}}
                <div class="mb-3">
                    <label class="form-label">PIN Panitia (4–6 Angka)</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-shield-lock field-icon"></i>
                        <input type="text" name="pin"
                               class="form-control @error('pin') is-invalid @enderror"
                               placeholder="Contoh: 123456" maxlength="6"
                               value="{{ old('pin') }}" autocomplete="off" required>
                    </div>
                    <div style="font-size:11px;color:#6B7280;margin-top:4px;">PIN ini digunakan untuk verifikasi log scan barcode.</div>
                    @error('pin')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" name="password" id="pw1"
                               class="form-control has-toggle @error('password') is-invalid @enderror"
                               placeholder="Min. 8 karakter" autocomplete="new-password" required
                               oninput="checkStrength(this.value)">
                        <button type="button" class="toggle-pw" title="Lihat/Sembunyikan Password" onclick="togglePw('pw1',this)">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-label" id="strengthLabel" style="color:#9CA3AF;"></div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-lock-fill field-icon"></i>
                        <input type="password" name="password_confirmation" id="pw2"
                               class="form-control has-toggle"
                               placeholder="Ulangi password" autocomplete="new-password" required>
                        <button type="button" class="toggle-pw" title="Lihat/Sembunyikan Password" onclick="togglePw('pw2',this)">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-auth">
                    <i class="bi bi-person-plus-fill"></i> Buat Akun Panitia
                </button>
            </form>

            <div class="divider"><span>sudah punya akun?</span></div>

            <p class="auth-link">
                <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk ke panel</a>
            </p>

        </div>
    </div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'bi bi-eye-slash-fill';
    } else {
        input.type     = 'password';
        icon.className = 'bi bi-eye-fill';
    }
}

function checkStrength(val) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 8)             score++;
    if (/[A-Z]/.test(val))           score++;
    if (/[0-9]/.test(val))           score++;
    if (/[^A-Za-z0-9]/.test(val))    score++;

    const levels = [
        { pct:'0%',   color:'#E5E7EB', text:'' },
        { pct:'25%',  color:'#EF4444', text:'Lemah' },
        { pct:'50%',  color:'#F59E0B', text:'Cukup' },
        { pct:'75%',  color:'#3B82F6', text:'Kuat' },
        { pct:'100%', color:'#10B981', text:'Sangat Kuat' },
    ];
    const l = levels[val.length === 0 ? 0 : score] || levels[0];
    bar.style.width      = l.pct;
    bar.style.background = l.color;
    label.textContent    = l.text;
    label.style.color    = l.color;
}
</script>
</body>
</html>
