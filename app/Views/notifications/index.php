<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Helper: icon & warna berdasarkan tipe notif
function notifIcon(string $tipe): string {
    return match($tipe) {
        'task'   => '📋',
        'absen'  => '🕐',
        'idle'   => '😴',
        'break'  => '☕',
        default  => '🔔',
    };
}
function notifColor(string $tipe): string {
    return match($tipe) {
        'task'   => '#3b82f6',
        'absen'  => '#10b981',
        'idle'   => '#f59e0b',
        'break'  => '#8b5cf6',
        default  => '#6b7280',
    };
}
function notifBg(string $tipe): string {
    return match($tipe) {
        'task'   => '#eff6ff',
        'absen'  => '#f0fdf4',
        'idle'   => '#fffbeb',
        'break'  => '#f5f3ff',
        default  => '#f9fafb',
    };
}

// Kelompokkan notif: hari ini vs sebelumnya
$today  = [];
$before = [];
foreach ($notifications as $n) {
    if (date('Y-m-d', strtotime($n['created_at'])) === date('Y-m-d')) {
        $today[] = $n;
    } else {
        $before[] = $n;
    }
}
?>

<!-- HEADER -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h4 style="font-weight:800;margin-bottom:4px;">Notifikasi</h4>
        <div style="font-size:13px;color:var(--text-muted);">
            <?= count($notifications) ?> total notifikasi
        </div>
    </div>
    <?php if (!empty($notifications)): ?>
    <a href="/notifications/clear"
        onclick="return confirm('Hapus semua notifikasi?')"
        style="font-size:13px;color:#ef4444;text-decoration:none;font-weight:600;">
        🗑️ Hapus Semua
    </a>
    <?php endif; ?>
</div>

<!-- FLASH -->
<?php if (session()->getFlashdata('success')): ?>
<div class="so-alert success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (empty($notifications)): ?>
<!-- EMPTY STATE -->
<div class="n-empty">
    <div style="font-size:52px;margin-bottom:12px;">🔔</div>
    <div style="font-weight:700;font-size:16px;margin-bottom:6px;">Belum ada notifikasi</div>
    <div style="font-size:13px;color:var(--text-muted);">
        Notifikasi akan muncul di sini saat ada aktivitas baru
    </div>
</div>

<?php else: ?>

<!-- HARI INI -->
<?php if (!empty($today)): ?>
<div class="n-section-label">Hari Ini</div>
<?php foreach ($today as $n): ?>
<div class="n-card <?= $n['is_read'] ? 'read' : 'unread' ?>"
     style="--notif-color:<?= notifColor($n['tipe']) ?>;--notif-bg:<?= notifBg($n['tipe']) ?>;">
    <div class="n-icon-wrap">
        <?= notifIcon($n['tipe']) ?>
    </div>
    <div class="n-body">
        <div class="n-title"><?= esc($n['judul']) ?></div>
        <div class="n-pesan"><?= esc($n['pesan']) ?></div>
        <div class="n-time">
            <?= date('H:i', strtotime($n['created_at'])) ?> WIB
        </div>
    </div>
    <?php if (!$n['is_read']): ?>
    <div class="n-dot"></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- SEBELUMNYA -->
<?php if (!empty($before)): ?>
<div class="n-section-label" style="margin-top:24px;">Sebelumnya</div>
<?php foreach ($before as $n): ?>
<div class="n-card <?= $n['is_read'] ? 'read' : 'unread' ?>"
     style="--notif-color:<?= notifColor($n['tipe']) ?>;--notif-bg:<?= notifBg($n['tipe']) ?>;">
    <div class="n-icon-wrap">
        <?= notifIcon($n['tipe']) ?>
    </div>
    <div class="n-body">
        <div class="n-title"><?= esc($n['judul']) ?></div>
        <div class="n-pesan"><?= esc($n['pesan']) ?></div>
        <div class="n-time">
            <?= date('d M Y, H:i', strtotime($n['created_at'])) ?> WIB
        </div>
    </div>
    <?php if (!$n['is_read']): ?>
    <div class="n-dot"></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>

<style>
/* EMPTY */
.n-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-muted);
}

/* SECTION LABEL */
.n-section-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 10px;
}

/* CARD */
.n-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    background: white;
    margin-bottom: 10px;
    position: relative;
    transition: transform .15s, box-shadow .15s;
}
.n-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.n-card.unread {
    background: var(--notif-bg);
    border-color: var(--notif-color);
    border-left-width: 4px;
}

/* ICON */
.n-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: var(--notif-bg, #f3f4f6);
    border: 1px solid var(--notif-color, #e5e7eb);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

/* BODY */
.n-body { flex: 1; min-width: 0; }
.n-title {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 3px;
    color: var(--text);
}
.n-pesan {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 4px;
}
.n-time {
    font-size: 11px;
    color: var(--text-muted);
}

/* UNREAD DOT */
.n-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--notif-color, var(--primary));
    flex-shrink: 0;
    margin-top: 4px;
}
</style>

<?= $this->endSection() ?>