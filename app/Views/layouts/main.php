<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Smart Office' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        :root {
            --primary: #1a56db;
            --primary-light: #ebf0ff;
            --primary-dark: #1240a4;
            --accent: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-w: 260px;
            --topbar-h: 64px;
            --bg: #f0f4ff;
            --card-bg: #ffffff;
            --text: #1e2b3c;
            --text-muted: #6b7a96;
            --border: #e2e8f6;
            --sidebar-bg: #0f1729;
            --sidebar-text: #94a3b8;
            --sidebar-active: #ffffff;
            --sidebar-active-bg: #1a56db;
            --radius: 14px;
            --shadow: 0 2px 16px rgba(26, 86, 219, 0.08);
            --shadow-card: 0 1px 4px rgba(0, 0, 0, 0.06), 0 4px 24px rgba(26, 86, 219, 0.07);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 22px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-brand-icon .material-icons-round {
            font-size: 20px;
            color: white;
        }

        .sidebar-brand-text {
            font-size: 17px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }

        .sidebar-brand-sub {
            font-size: 10px;
            font-weight: 500;
            color: var(--sidebar-text);
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .sidebar-section {
            padding: 20px 16px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(148, 163, 184, 0.5);
        }

        .sidebar-nav {
            padding: 0 12px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all 0.18s ease;
            position: relative;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: white;
        }

        .sidebar-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active);
        }

        .sidebar-link .material-icons-round {
            font-size: 20px;
            flex-shrink: 0;
        }

        .sidebar-link .badge-dot {
            width: 7px;
            height: 7px;
            background: var(--accent);
            border-radius: 50%;
            position: absolute;
            right: 14px;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-user-name {
            font-size: 13px;
            font-weight: 600;
            color: white;
            line-height: 1.2;
        }

        .sidebar-user-role {
            font-size: 11px;
            color: var(--sidebar-text);
        }

        /* MAIN CONTENT */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* TOPBAR */
        .topbar {
            height: var(--topbar-h);
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            color: var(--text-muted);
            position: relative;
        }

        .topbar-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .topbar-btn .material-icons-round {
            font-size: 20px;
        }

        .topbar-notif-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid white;
        }

        .topbar-date {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* PAGE CONTENT */
        .page-content {
            padding: 28px;
            flex: 1;
        }

        /* CARDS */
        .so-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border);
        }

        .so-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .so-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
        }

        .so-card-body {
            padding: 22px;
        }

        /* STAT CARDS */
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            border-radius: 0 var(--radius) 0 80px;
            opacity: 0.08;
        }

        .stat-card.primary::before { background: var(--primary); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.accent::before  { background: var(--accent);  }

        .stat-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-card-icon.primary { background: var(--primary-light); color: var(--primary); }
        .stat-card-icon.success { background: #d1fae5; color: var(--success); }
        .stat-card-icon.warning { background: #fef3c7; color: var(--warning); }
        .stat-card-icon.accent  { background: #e0f2fe; color: var(--accent);  }

        .stat-card-icon .material-icons-round { font-size: 22px; }

        .stat-card-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .stat-card-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .stat-card-sub {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* BADGE & STATUS */
        .so-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .so-badge.hadir    { background: #d1fae5; color: #059669; }
        .so-badge.telat    { background: #fef3c7; color: #d97706; }
        .so-badge.absen    { background: #fee2e2; color: #dc2626; }
        .so-badge.izin     { background: #e0f2fe; color: #0284c7; }
        .so-badge.low      { background: #f0fdf4; color: #16a34a; }
        .so-badge.medium   { background: #fefce8; color: #ca8a04; }
        .so-badge.high     { background: #fff1f2; color: #e11d48; }
        .so-badge.todo        { background: var(--primary-light); color: var(--primary); }
        .so-badge.in-progress { background: #fef3c7; color: #d97706; }
        .so-badge.done        { background: #d1fae5; color: #059669; }

        /* BUTTONS */
        .btn-so-primary {
            background: var(--primary); color: white; border: none;
            border-radius: 10px; padding: 10px 20px; font-size: 14px;
            font-weight: 600; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; text-decoration: none; transition: all 0.18s;
        }
        .btn-so-primary:hover {
            background: var(--primary-dark); color: white;
            transform: translateY(-1px); box-shadow: 0 4px 14px rgba(26,86,219,0.3);
        }

        .btn-so-success {
            background: var(--success); color: white; border: none;
            border-radius: 10px; padding: 10px 20px; font-size: 14px;
            font-weight: 600; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; text-decoration: none; transition: all 0.18s;
        }
        .btn-so-success:hover {
            background: #059669; color: white;
            transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,185,129,0.3);
        }

        .btn-so-danger {
            background: var(--danger); color: white; border: none;
            border-radius: 10px; padding: 10px 20px; font-size: 14px;
            font-weight: 600; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; text-decoration: none; transition: all 0.18s;
        }
        .btn-so-danger:hover { background: #dc2626; color: white; transform: translateY(-1px); }

        .btn-so-outline {
            background: white; color: var(--primary);
            border: 1.5px solid var(--primary); border-radius: 10px;
            padding: 9px 18px; font-size: 14px; font-weight: 600;
            cursor: pointer; display: inline-flex; align-items: center;
            gap: 8px; text-decoration: none; transition: all 0.18s;
        }
        .btn-so-outline:hover { background: var(--primary-light); color: var(--primary); }

        /* FORMS */
        .so-label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--text); margin-bottom: 7px;
        }

        .so-input {
            width: 100%; padding: 10px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text); background: white; transition: border 0.15s; outline: none;
        }
        .so-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
        }

        .so-select {
            width: 100%; padding: 10px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text); background: white; appearance: none;
            cursor: pointer; outline: none;
        }
        .so-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
        }

        .form-group { margin-bottom: 20px; }

        /* TABLE */
        .so-table { width: 100%; border-collapse: collapse; }
        .so-table thead th {
            padding: 12px 16px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--text-muted); background: #f8faff;
            border-bottom: 1px solid var(--border);
        }
        .so-table tbody td {
            padding: 14px 16px; font-size: 14px;
            color: var(--text); border-bottom: 1px solid var(--border);
        }
        .so-table tbody tr:last-child td { border-bottom: none; }
        .so-table tbody tr:hover td { background: #f8faff; }

        /* ALERT */
        .so-alert {
            padding: 14px 18px; border-radius: 10px; font-size: 14px;
            font-weight: 500; display: flex; align-items: center;
            gap: 10px; margin-bottom: 20px;
        }
        .so-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .so-alert.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* DIVIDER */
        .so-divider { border: none; border-top: 1px solid var(--border); margin: 16px 0; }

        /* ANIMATIONS */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .page-content > * { animation: fadeInUp 0.3s ease forwards; }

        /* IDLE INDICATOR */
        .idle-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--success); display: inline-block;
            margin-right: 6px; transition: background 0.3s;
        }
        .idle-dot.idle { background: var(--warning); animation: pulse 1.5s infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c9d3e8; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a0aec0; }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <?php include APPPATH . 'Views/layouts/sidebar.php'; ?>
    </aside>

    <!-- MAIN -->
    <div class="main-wrapper">
        <?php include APPPATH . 'Views/layouts/header.php'; ?>
        <main class="page-content">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include APPPATH . 'Views/layouts/footer.php'; ?>

    <script>
        let idleTimer;
        let idleStarted = false;

        function startIdle() {
            if (!idleStarted) {
                idleStarted = true;
                fetch('/idle/start', { method: 'POST' });
            }
        }

        function stopIdle() {
            if (idleStarted) {
                idleStarted = false;
                fetch('/idle/stop', { method: 'POST' });
            }
        }

        function resetIdleTimer() {
            stopIdle();
            clearTimeout(idleTimer);
            idleTimer = setTimeout(() => { startIdle(); }, 300000);
        }

        ['mousemove', 'keydown', 'scroll', 'click'].forEach(event => {
            document.addEventListener(event, resetIdleTimer);
        });

        resetIdleTimer();
    </script>
</body>

</html>