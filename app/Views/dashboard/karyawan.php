<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$nama     = session()->get('nama') ?? 'Karyawan';
$initials = strtoupper(substr($nama, 0, 2));
$jam      = (int) date('H');
$greeting = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$today    = date('l, d F Y');

$absenHariIni  = $absenHariIni  ?? null;
$breakLogs     = $breakLogs     ?? [];
$idleLogs      = $idleLogs      ?? [];
$tasks         = $taskList      ?? [];
$totalBreakMnt = $totalBreakMnt ?? 0;
$taskSelesai   = $taskSelesai   ?? 0;
$taskTotal     = $taskTotal     ?? 0;
$isBreak       = $isBreak       ?? false;
$isIdle        = $isIdle        ?? false;
$overtime      = $overtime      ?? false;

$jamMasuk   = $absenHariIni ? date('H:i', strtotime($absenHariIni['jam_masuk'])) : null;
$totalKerja = '-';
$sekarang   = time();
$batas      = null;

// Cek apakah status absensi adalah izin/sakit/pending
$statusIzin      = in_array($absenHariIni['status'] ?? '', ['izin', 'alpha']);
$approvalPending = ($absenHariIni['approval_status'] ?? '') === 'pending';
$isAbsenNormal   = $absenHariIni && !$statusIzin && !$approvalPending;

if ($isAbsenNormal && !empty($absenHariIni['jam_masuk'])) {
    $tsMasuk         = strtotime($absenHariIni['jam_masuk']);
    $totalDetik      = $sekarang - $tsMasuk;
    $totalBreakDetik = ($totalBreakMnt ?? 0) * 60;
    $kerjaBersih     = max(0, $totalDetik - $totalBreakDetik);
    $jam             = floor($kerjaBersih / 3600);
    $mnt             = floor(($kerjaBersih % 3600) / 60);
    $totalKerja      = $jam . 'j ' . $mnt . 'm';
    $batas           = $tsMasuk + (8 * 3600);
}
?>

<!-- Toast Container -->
<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>

<!-- Greeting -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="dash-avatar"><?= $initials ?></div>
        <div>
            <div class="dash-greeting"><?= $greeting ?>, <?= esc(explode(' ', $nama)[0]) ?> 👋</div>
            <div class="dash-sub">Semangat kerja hari ini!</div>
        </div>
    </div>
    <div class="dash-date-pill">
        <span class="material-icons-round" style="font-size:16px;">calendar_today</span>
        <?= $today ?>
    </div>
</div>

<!-- Work Status Banner -->
<div class="work-status-banner mb-4 <?= $absenHariIni ? ($isBreak ? 'break' : ($overtime ? 'overtime' : ($statusIzin || $approvalPending ? 'absent' : 'working'))) : 'absent' ?>">
    <div class="d-flex align-items-center gap-3">
        <div class="work-status-dot"></div>
        <div>
            <div class="work-status-label">
                <?php if (!$absenHariIni): ?>
                    Belum absen masuk hari ini
                <?php elseif ($statusIzin || $approvalPending): ?>
                    📌 <?= $approvalPending ? 'Menunggu persetujuan izin' : ucfirst($absenHariIni['status']) ?>
                <?php elseif ($overtime): ?>
                    ⚠️ Overtime — kamu sudah melebihi jam kerja
                <?php else: ?>
                    Terkini
                <?php endif; ?>
            </div>
            <?php if ($absenHariIni && $jamMasuk): ?>
                <div style="font-size:12px;opacity:.75;">Absen masuk pukul <?= $jamMasuk ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="live-clock" id="liveClock"><?= date('H:i:s') ?></div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card primary">
            <div class="stat-card-icon primary">
                <span class="material-icons-round">schedule</span>
            </div>
            <div class="stat-card-label">Total Kerja Hari Ini</div>
            <div class="stat-card-value"><?= $absenHariIni ? $totalKerja : '-' ?></div>
            <div class="stat-card-sub"><?= $absenHariIni ? 'Mulai ' . $jamMasuk : 'Belum absen' ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="stat-card-icon success">
                <span class="material-icons-round">hourglass_bottom</span>
            </div>
            <div class="stat-card-label">Sisa Jam Kerja</div>
            <div class="stat-card-value" style="color:var(--success)">
                <?php if ($isAbsenNormal && !$absenHariIni['jam_keluar'] && $batas): ?>
                    <?php $sisa = max(0, $batas - $sekarang);
                    echo floor($sisa / 3600) . 'j ' . floor(($sisa % 3600) / 60) . 'm'; ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
            <div class="stat-card-sub">
                <?= ($isAbsenNormal && $batas) ? 'Selesai pukul ' . date('H:i', $batas) : '-' ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-card-icon warning">
                <span class="material-icons-round">free_breakfast</span>
            </div>
            <div class="stat-card-label">Total Break Hari Ini</div>
            <div class="stat-card-value" style="color:var(--warning)"><?= $totalBreakMnt ?>m</div>
            <div class="stat-card-sub"><?= count($breakLogs) ?>x break diambil</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card accent">
            <div class="stat-card-icon accent">
                <span class="material-icons-round">task_alt</span>
            </div>
            <div class="stat-card-label">Task Selesai</div>
            <div class="stat-card-value" style="color:var(--accent)"><?= $taskSelesai ?>/<?= $taskTotal ?></div>
            <div class="stat-card-sub" style="color:<?= ($taskTotal - $taskSelesai) > 0 ? 'var(--danger)' : 'var(--success)' ?>">
                <?= ($taskTotal - $taskSelesai) > 0 ? ($taskTotal - $taskSelesai) . ' task belum selesai' : 'Semua task selesai 🎉' ?>
            </div>
        </div>
    </div>
</div>

<!-- Overtime Card -->
<?php if ($isAbsenNormal && $batas && !$absenHariIni['jam_keluar']): ?>
    <?php
    $masuk  = strtotime($absenHariIni['jam_masuk']);
    $durasi = $batas - $masuk;
    $jalan  = min($sekarang - $masuk, $durasi);
    $pct    = $durasi > 0 ? min(100, round(($jalan / $durasi) * 100)) : 0;

    if ($overtime) {
        $lewat   = $sekarang - $batas;
        $otJam   = floor($lewat / 3600);
        $otMenit = floor(($lewat % 3600) / 60);
        $otLabel = ($otJam > 0 ? $otJam . 'j ' : '') . $otMenit . 'm';
    } else {
        $sisaOt  = $batas ? max(0, $batas - $sekarang) : 0;
        $sisaJam = floor($sisaOt / 3600);
        $sisaMnt = floor(($sisaOt % 3600) / 60);
    }
    ?>
    <div class="overtime-card mb-4 <?= $overtime ? 'ot-active' : 'ot-normal' ?>">
        <div class="d-flex align-items-center gap-3 flex-wrap">

            <div class="ot-icon">
                <span class="material-icons-round"><?= $overtime ? 'warning' : 'timer' ?></span>
            </div>

            <div style="flex:1;min-width:160px;">
                <div class="ot-label">
                    <?= $overtime ? '⚠️ Kamu sedang overtime!' : 'Jam kerja berjalan normal' ?>
                </div>
                <div class="ot-sub">
                    <?php if ($overtime): ?>
                        Sudah melewati batas kerja selama <strong><?= $otLabel ?></strong> — segera absen pulang.
                    <?php else: ?>
                        Sisa <strong><?= $sisaJam ?>j <?= $sisaMnt ?>m</strong> untuk bekerja.
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($overtime): ?>
                <div style="flex-shrink:0;">
                    <button onclick="doAbsenPulang()" class="btn-so-primary" style="font-size:13px;padding:8px 18px;white-space:nowrap;gap:6px;cursor:pointer;border:none;">
                        <span class="material-icons-round" style="font-size:15px;">logout</span>
                        Absen Pulang
                    </button>
                </div>
            <?php else: ?>
                <div class="ot-progress-wrap">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:11px;color:var(--text-muted);">Progress hari ini</span>
                        <span style="font-size:11px;font-weight:700;color:var(--primary);"><?= $pct ?>%</span>
                    </div>
                    <div class="ot-bar">
                        <div class="ot-fill" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:4px;text-align:right;">
                        Mulai <?= $jamMasuk ?> — 8 jam kerja
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

<!-- Bottom Row: Timeline + Task List -->
<div class="row g-4">

    <!-- Aktivitas Hari Ini -->
    <div class="col-lg-6">
        <div class="so-card h-100">
            <div class="so-card-header">
                <div class="so-card-title">Aktivitas hari ini</div>
                <span style="font-size:12px;font-weight:600;color:var(--primary);">Timeline</span>
            </div>
            <div class="so-card-body">
                <div class="timeline-list">

                    <?php if ($absenHariIni): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon success-icon"><span class="material-icons-round">check</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Absen masuk</div>
                                <div class="timeline-sub"><?= $jamMasuk ?> — GPS & IP terverifikasi</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($breakLogs as $bl): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon warning-icon"><span class="material-icons-round">free_breakfast</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Break <?= $bl['durasi'] ?? '?' ?> menit</div>
                                <div class="timeline-sub">
                                    <?= date('H:i', strtotime($bl['mulai'])) ?> —
                                    <?= $bl['selesai'] ? date('H:i', strtotime($bl['selesai'])) : 'sedang berlangsung' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($idleLogs as $il): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon danger-icon"><span class="material-icons-round">warning_amber</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Idle terdeteksi</div>
                                <div class="timeline-sub"><?= date('H:i', strtotime($il['mulai'])) ?> — selama <?= $il['durasi'] ?? '?' ?> menit</div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($absenHariIni && $overtime && !$absenHariIni['jam_keluar']): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon danger-icon" style="animation:pulse 2s infinite;"><span class="material-icons-round">warning</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title" style="color:var(--danger);">Overtime berlangsung</div>
                                <div class="timeline-sub">Sudah melewati jam kerja normal</div>
                            </div>
                        </div>
                    <?php elseif ($absenHariIni && !$absenHariIni['jam_keluar']): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon active-icon" style="animation:pulse 2s infinite;"><span class="material-icons-round">work</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Sedang bekerja...</div>
                                <div class="timeline-sub">Sekarang</div>
                            </div>
                        </div>
                    <?php elseif ($absenHariIni && $absenHariIni['jam_keluar']): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon"><span class="material-icons-round">logout</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Absen pulang</div>
                                <div class="timeline-sub"><?= date('H:i', strtotime($absenHariIni['jam_keluar'])) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center;padding:24px 0;color:var(--text-muted);font-size:13px;">
                            <span class="material-icons-round" style="display:block;font-size:32px;margin-bottom:8px;color:var(--border);">calendar_today</span>
                            Belum ada aktivitas hari ini
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Task Hari Ini -->
    <div class="col-lg-6">
        <div class="so-card h-100">
            <div class="so-card-header">
                <div class="so-card-title">Task hari ini</div>
                <span class="so-badge todo"><?= $taskTotal ?> task</span>
            </div>
            <div class="so-card-body" style="padding:12px 0 16px;">

                <?php if (empty($tasks)): ?>
                    <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;">
                        <span class="material-icons-round" style="display:block;font-size:32px;margin-bottom:8px;color:var(--border);">inbox</span>
                        Tidak ada task untuk hari ini
                    </div>
                <?php else: ?>
                    <div class="task-list-scroll">
                        <?php foreach (array_slice($tasks, 0, 6) as $task):
                            $tc = match ($task['status']) {
                                'todo' => 'todo',
                                'on_progress' => 'in-progress',
                                'done' => 'done',
                                default => 'todo'
                            };
                            $tl = match ($task['status']) {
                                'todo' => 'Todo',
                                'on_progress' => 'On progress',
                                'done' => 'Selesai',
                                default => ''
                            };
                            $tdOverdue = $task['deadline'] && $task['status'] !== 'done' && strtotime($task['deadline']) < time();
                            $tdLabel   = '';
                            if ($task['deadline']) {
                                $dl = date('Y-m-d', strtotime($task['deadline']));
                                if ($dl === date('Y-m-d')) $tdLabel = 'Due: hari ini';
                                elseif ($dl === date('Y-m-d', strtotime('-1 day'))) $tdLabel = 'Due: kemarin';
                                else $tdLabel = 'Due: ' . date('d M', strtotime($task['deadline']));
                            }
                        ?>
                            <div class="dash-task-item">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="dash-task-check <?= $task['status'] === 'done' ? 'checked' : '' ?>" onclick="quickDone(<?= $task['id'] ?>, this)">
                                        <?php if ($task['status'] === 'done'): ?>
                                            <span class="material-icons-round" style="font-size:14px;">check</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div class="dash-task-title <?= $task['status'] === 'done' ? 'done' : '' ?>"><?= esc($task['judul']) ?></div>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span class="so-badge <?= $tc ?>" style="font-size:10px;padding:2px 8px;"><?= $tl ?></span>
                                            <?php if ($tdLabel): ?>
                                                <span style="font-size:11px;color:<?= $tdOverdue ? 'var(--danger)' : 'var(--text-muted)' ?>;"><?= $tdLabel ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($tasks) > 6): ?>
                            <div style="text-align:center;padding:10px;">
                                <a href="/task" style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;">
                                    Lihat semua +<?= count($tasks) - 6 ?> task →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div style="padding:0 22px;margin-top:12px;">
                    <a href="/task" class="btn-so-outline" style="width:100%;justify-content:center;font-size:13px;">
                        <span class="material-icons-round" style="font-size:16px;">list</span>
                        Lihat Semua Task
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Overtime Alert Modal -->
<?php if ($overtime): ?>
    <div id="overtimeModal" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
        <div style="background:white;border-radius:16px;padding:32px;max-width:400px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
            <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <span class="material-icons-round" style="color:var(--danger);font-size:28px;">warning</span>
            </div>
            <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:8px;">Peringatan Overtime!</div>
            <div style="font-size:14px;color:var(--text-muted);line-height:1.6;margin-bottom:24px;">
                Kamu sudah bekerja melebihi jam kerja normal. Pertimbangkan untuk segera absen pulang.
            </div>
            <button onclick="document.getElementById('overtimeModal').style.display='none'" class="btn-so-primary" style="width:100%;justify-content:center;">
                Oke, saya mengerti
            </button>
        </div>
    </div>
<?php endif; ?>

<style>
    .dash-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 800;
        color: white;
    }

    .dash-greeting {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
    }

    .dash-sub {
        font-size: 13px;
        color: var(--text-muted);
    }

    .dash-date-pill {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: white;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .work-status-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-radius: var(--radius);
        border: 1.5px solid;
    }

    .work-status-banner.working {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        border-color: #6ee7b7;
    }

    .work-status-banner.break {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-color: #fcd34d;
    }

    .work-status-banner.overtime {
        background: linear-gradient(135deg, #fee2e2, #fca5a5);
        border-color: #f87171;
    }

    .work-status-banner.absent {
        background: linear-gradient(135deg, var(--primary-light), #dbeafe);
        border-color: var(--primary);
    }

    .work-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: currentColor;
        animation: pulse 2s infinite;
    }

    .work-status-banner.working .work-status-dot {
        color: #10b981;
    }

    .work-status-banner.break .work-status-dot {
        color: #f59e0b;
    }

    .work-status-banner.overtime .work-status-dot {
        color: #ef4444;
    }

    .work-status-banner.absent .work-status-dot {
        color: var(--primary);
    }

    .work-status-label {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
    }

    .live-clock {
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        font-variant-numeric: tabular-nums;
    }

    .overtime-card {
        padding: 18px 24px;
        border-radius: var(--radius);
        border: 1.5px solid;
    }

    .ot-normal {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-color: #93c5fd;
    }

    .ot-active {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        border-color: #f87171;
        animation: pulse-border 2s infinite;
    }

    .ot-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .ot-normal .ot-icon {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .ot-active .ot-icon {
        background: #fecaca;
        color: #dc2626;
    }

    .ot-label {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 2px;
    }

    .ot-sub {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .ot-progress-wrap {
        min-width: 180px;
        flex-shrink: 0;
    }

    .ot-bar {
        height: 6px;
        background: #bfdbfe;
        border-radius: 999px;
        overflow: hidden;
    }

    .ot-fill {
        height: 100%;
        background: #1d4ed8;
        border-radius: 999px;
        transition: width .4s;
    }

    @keyframes pulse-border {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, .3);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
    }

    .timeline-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 17px;
        top: 44px;
        bottom: 0;
        width: 2px;
        background: var(--border);
    }

    .timeline-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--border);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .timeline-icon .material-icons-round {
        font-size: 16px;
    }

    .success-icon {
        background: #d1fae5;
        color: #059669;
    }

    .warning-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .danger-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .active-icon {
        background: var(--primary-light);
        color: var(--primary);
    }

    .timeline-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .timeline-sub {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .task-list-scroll {
        max-height: 280px;
        overflow-y: auto;
        padding: 0 22px;
    }

    .dash-task-item {
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }

    .dash-task-item:last-child {
        border-bottom: none;
    }

    .dash-task-check {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 2px solid var(--border);
        flex-shrink: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        margin-top: 1px;
    }

    .dash-task-check.checked {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .dash-task-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        line-height: 1.4;
    }

    .dash-task-title.done {
        text-decoration: line-through;
        color: var(--text-muted);
    }

    /* Toast */
    .so-toast {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        font-size: 13px;
        font-weight: 600;
        color: white;
        min-width: 280px;
        max-width: 360px;
        pointer-events: all;
        animation: toastIn .3s ease;
        cursor: pointer;
    }

    .so-toast.success {
        background: linear-gradient(135deg, #059669, #10b981);
    }

    .so-toast.error {
        background: linear-gradient(135deg, #dc2626, #ef4444);
    }

    .so-toast.warning {
        background: linear-gradient(135deg, #d97706, #f59e0b);
    }

    .so-toast.info {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
    }

    .so-toast.fade-out {
        animation: toastOut .3s ease forwards;
    }

    @keyframes toastIn {
        from {
            opacity: 0;
            transform: translateX(40px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes toastOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }

        to {
            opacity: 0;
            transform: translateX(40px);
        }
    }
</style>

<script>
    // ── Toast ────────────────────────────────────────
    function showToast(message, type = 'info', duration = 3500) {
        const container = document.getElementById('toast-container');
        const icons = {
            success: 'check_circle',
            error: 'error',
            warning: 'warning_amber',
            info: 'info'
        };

        const toast = document.createElement('div');
        toast.className = `so-toast ${type}`;
        toast.innerHTML = `
        <span class="material-icons-round" style="font-size:20px;flex-shrink:0;">${icons[type] ?? 'info'}</span>
        <span style="flex:1;line-height:1.4;">${message}</span>
        <span class="material-icons-round" style="font-size:16px;opacity:.7;flex-shrink:0;">close</span>
    `;
        toast.onclick = () => dismissToast(toast);
        container.appendChild(toast);

        setTimeout(() => dismissToast(toast), duration);
    }

    function dismissToast(toast) {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }

    // ── Live Clock ───────────────────────────────────
    function updateClock() {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const el = document.getElementById('liveClock');
        if (el) el.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    }
    setInterval(updateClock, 1000);

    // ── Quick done toggle ────────────────────────────
    function quickDone(taskId, el) {
        const isDone = el.classList.contains('checked');
        const newStatus = isDone ? 'todo' : 'done';
        const fd = new FormData();
        fd.append('status', newStatus);
        fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch(`/task/update-status/${taskId}`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(d => {
                if (d.status) {
                    el.classList.toggle('checked');
                    const title = el.closest('.d-flex').querySelector('.dash-task-title');
                    if (title) title.classList.toggle('done');
                    el.innerHTML = !isDone ? '<span class="material-icons-round" style="font-size:14px;">check</span>' : '';
                    showToast(isDone ? 'Task dikembalikan ke todo.' : 'Task ditandai selesai!', isDone ? 'info' : 'success');
                } else {
                    showToast('Gagal update task.', 'error');
                }
            })
            .catch(() => showToast('Gagal terhubung ke server.', 'error'));
    }

    // ── Absen Pulang ─────────────────────────────────
    function doAbsenPulang() {
        fetch('/absen-pulang', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: ''
            })
            .then(r => r.json())
            .then(d => {
                showToast(d.message, d.status ? 'success' : 'error');
                if (d.status) setTimeout(() => location.reload(), 2000);
            })
            .catch(() => showToast('Gagal terhubung ke server.', 'error'));
    }
</script>

<?= $this->endSection() ?>