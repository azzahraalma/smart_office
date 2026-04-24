<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        :root {
            --primary: #1a56db;
            --primary-dark: #1240a4;
            --accent: #0ea5e9;
            --bg: #f0f4ff;
            --text: #1e2b3c;
            --text-muted: #6b7a96;
            --border: #e2e8f6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-wrapper {
            display: flex;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 48px rgba(26, 86, 219, 0.12);
            overflow: hidden;
        }

        .auth-brand {
            flex: 1;
            background: linear-gradient(145deg, #0f1729 0%, #1a56db 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 240px; height: 240px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1;
        }

        .brand-logo-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }

        .brand-logo-icon .material-icons-round { color: white; font-size: 24px; }

        .brand-logo-text {
            font-size: 20px;
            font-weight: 800;
            color: white;
        }

        .brand-content { z-index: 1; }

        .brand-headline {
            font-size: 28px;
            font-weight: 800;
            color: white;
            line-height: 1.3;
            margin-bottom: 16px;
        }

        .brand-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
        }

        .brand-features { z-index: 1; }

        .brand-feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .brand-feature-item .material-icons-round { font-size: 18px; color: #0ea5e9; }
        .brand-feature-item span { font-size: 13px; color: rgba(255,255,255,0.75); font-weight: 500; }

        .auth-form-panel {
            flex: 1;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-form-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }

        .auth-form-sub {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        .so-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
        }

        .input-icon-wrap { position: relative; }

        .input-icon-wrap .material-icons-round {
            position: absolute;
            left: 13px; top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .so-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            background: white;
            transition: border 0.15s;
            outline: none;
        }

        .so-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.1);
        }

        .toggle-pass {
            position: absolute;
            right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 0; display: flex;
        }

        .toggle-pass .material-icons-round { font-size: 18px; }

        .form-group { margin-bottom: 20px; }

        .btn-login {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.18s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(26, 86, 219, 0.3);
        }

        .so-alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .so-alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .so-alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .so-alert .material-icons-round { font-size: 18px; flex-shrink: 0; }

        @media (max-width: 680px) {
            .auth-brand { display: none; }
            .auth-form-panel { padding: 36px 28px; }
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">

        <div class="auth-brand">
            <div class="brand-logo">
                <div class="brand-logo-icon">
                    <span class="material-icons-round">domain</span>
                </div>
                <span class="brand-logo-text">Smart Office</span>
            </div>

            <div class="brand-content">
                <div class="brand-headline">Kelola kantor<br>lebih cerdas & efisien</div>
                <div class="brand-sub">Platform manajemen absensi, tugas, dan produktivitas karyawan dalam satu sistem terintegrasi.</div>
            </div>

            <div class="brand-features">
                <div class="brand-feature-item">
                    <span class="material-icons-round">location_on</span>
                    <span>Absensi berbasis GPS & IP</span>
                </div>
                <div class="brand-feature-item">
                    <span class="material-icons-round">task_alt</span>
                    <span>Manajemen tugas real-time</span>
                </div>
                <div class="brand-feature-item">
                    <span class="material-icons-round">timer</span>
                    <span>Pantau produktivitas & idle time</span>
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-title">Selamat datang 👋</div>
            <div class="auth-form-sub">Masuk ke akun Smart Office kamu</div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="so-alert error">
                    <span class="material-icons-round">error_outline</span>
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="so-alert success">
                    <span class="material-icons-round">check_circle_outline</span>
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="so-label">Email</label>
                    <div class="input-icon-wrap">
                        <span class="material-icons-round">mail_outline</span>
                        <input
                            type="email"
                            name="email"
                            class="so-input"
                            placeholder="nama@perusahaan.com"
                            value="<?= old('email') ?>"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="so-label">Password</label>
                    <div class="input-icon-wrap">
                        <span class="material-icons-round">lock_outline</span>
                        <input
                            type="password"
                            name="password"
                            id="inputPassword"
                            class="so-input"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-pass" onclick="togglePassword()">
                            <span class="material-icons-round" id="passIcon">visibility_off</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <span class="material-icons-round">login</span>
                    Masuk
                </button>
            </form>
        </div>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('inputPassword');
            const icon  = document.getElementById('passIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }
    </script>
</body>
</html>