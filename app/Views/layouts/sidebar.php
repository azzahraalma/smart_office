<?php

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
        <!-- Tombol logout — buka modal, bukan confirm() -->
        <button onclick="document.getElementById('modal-logout').style.display='flex'"
            title="Logout"
            style="color:#94a3b8;background:none;border:none;cursor:pointer;display:flex;padding:0;line-height:1;">
            <span class="material-icons-round" style="font-size:20px;">logout</span>
        </button>
    </div>
</div>

<!-- ===== MODAL LOGOUT ===== -->
<div id="modal-logout"
     style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(15,23,42,0.45);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;padding:32px 28px;width:100%;max-width:360px;box-shadow:0 24px 64px rgba(0,0,0,0.18);text-align:center;margin:16px;animation:soModalIn .18s ease;">
        <!-- Icon -->
        <div style="width:64px;height:64px;border-radius:16px;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <span class="material-icons-round" style="font-size:32px;color:#ef4444;">logout</span>
        </div>
        <!-- Teks -->
        <div style="font-size:18px;font-weight:800;color:#1e2b3c;margin-bottom:8px;">Keluar dari akun?</div>
        <div style="font-size:14px;color:#6b7280;margin-bottom:28px;line-height:1.6;">
            Kamu akan keluar dari sesi ini.<br>Pastikan semua pekerjaan sudah tersimpan.
        </div>
        <!-- Tombol -->
        <div style="display:flex;gap:12px;">
            <button onclick="document.getElementById('modal-logout').style.display='none'"
                style="flex:1;padding:12px;border-radius:12px;border:1.5px solid #e5e7eb;background:white;font-size:14px;font-weight:600;color:#374151;cursor:pointer;transition:background .15s;"
                onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                Batal
            </button>
            <a href="/logout"
                style="flex:1;padding:12px;border-radius:12px;background:#ef4444;font-size:14px;font-weight:600;color:white;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:background .15s;"
                onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                <span class="material-icons-round" style="font-size:16px;">logout</span>
                Ya, Keluar
            </a>
        </div>
    </div>
</div>

<style>
@keyframes soModalIn {
    from { opacity:0; transform:scale(.95) translateY(8px); }
    to   { opacity:1; transform:scale(1)  translateY(0); }
}
</style>

<script>
// Tutup modal klik backdrop
document.getElementById('modal-logout').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
// Tutup modal tekan ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('modal-logout').style.display = 'none';
});
</script>