<?php
// Query unread notif langsung di sidebar, tanpa perlu BaseController
$unreadNotif = 0;
if (session()->get('user_id')) {
    $notifModel = new \App\Models\NotificationModel();
    $unreadNotif = $notifModel
        ->where('user_id', session()->get('user_id'))
        ->where('is_read', 0)
        ->countAllResults();
}
?>

<a href="/" class="sidebar-brand">
    <div class="sidebar-brand-icon">
        <span class="material-icons-round">domain</span>
    </div>
    <div>
        <div class="sidebar-brand-text">Smart Office</div>
        <div class="sidebar-brand-sub">Management</div>
    </div>
</a>

<div class="sidebar-section">Menu Utama</div>
<nav class="sidebar-nav">
    <a href="/" class="sidebar-link <?= url_is('/') ? 'active' : '' ?>">
        <span class="material-icons-round">dashboard</span>
        Dashboard
    </a>
    <a href="/absensi" class="sidebar-link <?= url_is('absensi*') ? 'active' : '' ?>">
        <span class="material-icons-round">fingerprint</span>
        Absensi
    </a>
    <a href="/task" class="sidebar-link <?= url_is('task*') ? 'active' : '' ?>">
        <span class="material-icons-round">task_alt</span>
        Task
    </a>
    <a href="/notifications" class="sidebar-link <?= url_is('notifications*') ? 'active' : '' ?>">
        <span class="material-icons-round">notifications</span>
        Notifikasi
        <?php if ($unreadNotif > 0): ?>
            <span class="badge-dot"></span>
        <?php endif; ?>
    </a>
</nav>

<?php if (session()->get('role') === 'manager'): ?>
    <div class="sidebar-section">Manager</div>
    <nav class="sidebar-nav">
        <a href="/users" class="sidebar-link <?= url_is('users*') ? 'active' : '' ?>">
            <span class="material-icons-round">manage_accounts</span>
            Manajemen User
        </a>
        <a href="/kantor" class="sidebar-link <?= url_is('kantor*') ? 'active' : '' ?>">
            <span class="material-icons-round">settings</span>
            Konfigurasi Kantor
        </a>
    </nav>
<?php endif; ?>

<div class="sidebar-footer">
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?php
                $nama  = session()->get('nama') ?? 'U';
                $words = explode(' ', trim($nama));
                echo strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
            ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div class="sidebar-user-name"><?= esc($nama) ?></div>
            <div class="sidebar-user-role"><?= ucfirst(session()->get('role') ?? 'karyawan') ?></div>
        </div>
        <a href="/logout" title="Logout" style="color:#94a3b8;display:flex;text-decoration:none;" onclick="return confirm('Yakin ingin logout?')">
            <span class="material-icons-round" style="font-size:20px;">logout</span>
        </a>
    </div>
</div>