<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$today        = date('l, d F Y');
$totalKary    = $totalKaryawan   ?? 0;
$hadirHariIni = $hadirHariIni    ?? 0;
$taskAktif    = $taskAktif       ?? 0;
$taskSelesai  = $taskSelesai     ?? 0;
$absensiList  = $absensiList     ?? [];
$taskList     = $taskList        ?? [];
$chartHadir   = $chartHadir      ?? [];
$statusHadir  = $statusHadir     ?? ['hadir'=>0,'telat'=>0,'izin'=>0,'alpha'=>0];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading">Dashboard</h1>
        <p class="page-sub"><?= $today ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="/absensi" class="btn-so-outline" style="font-size:13px;padding:8px 14px;">
            <span class="material-icons-round" style="font-size:16px;">group</span>
            Kelola Absensi
        </a>
        <a href="/task/create" class="btn-so-primary" style="font-size:13px;padding:8px 14px;">
            <span class="material-icons-round" style="font-size:16px;">add</span>
            Buat Task
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card primary">
            <div class="stat-card-icon primary">
                <span class="material-icons-round">people</span>
            </div>
            <div class="stat-card-label">Total Karyawan</div>
            <div class="stat-card-value"><?= $totalKary ?></div>
            <div class="stat-card-sub" style="color:var(--success)">+2 bulan ini</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="stat-card-icon success">
                <span class="material-icons-round">how_to_reg</span>
            </div>
            <div class="stat-card-label">Hadir Hari Ini</div>
            <div class="stat-card-value"><?= $hadirHariIni ?></div>
            <div class="stat-card-sub">dari <?= $totalKary ?> karyawan</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-card-icon warning">
                <span class="material-icons-round">assignment</span>
            </div>
            <div class="stat-card-label">Task Aktif</div>
            <div class="stat-card-value"><?= $taskAktif ?></div>
            <div class="stat-card-sub" style="color:<?= $taskAktif > 10 ? 'var(--danger)' : 'var(--text-muted)' ?>">
                <?= $taskAktif > 10 ? $taskAktif.' mendekati deadline' : 'Normal' ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card accent">
            <div class="stat-card-icon accent">
                <span class="material-icons-round">task_alt</span>
            </div>
            <div class="stat-card-label">Task Selesai</div>
            <div class="stat-card-value"><?= $taskSelesai ?></div>
            <div class="stat-card-sub" style="color:var(--success)">+5 minggu ini</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Bar Chart Kehadiran 7 Hari -->
    <div class="col-lg-7">
        <div class="so-card h-100">
            <div class="so-card-header">
                <div class="so-card-title">Kehadiran 7 hari terakhir</div>
                <select class="so-select" style="width:auto;padding:5px 12px;font-size:12px;" id="chartRange">
                    <option>7 Hari</option>
                    <option>30 Hari</option>
                </select>
            </div>
            <div class="so-card-body">
                <div class="chart-bar-wrap" id="barChart">
                    <?php
                    $days  = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
                    $vals  = [38,35,42,39,44,12,8];
                    $maxV  = max($vals) ?: 1;
                    foreach ($days as $i => $d):
                        $pct = round(($vals[$i] / $maxV) * 100);
                        $isToday = $i === 0;
                    ?>
                    <div class="bar-item">
                        <div class="bar-val"><?= $vals[$i] ?></div>
                        <div class="bar-outer">
                            <div class="bar-fill <?= $isToday ? 'today' : '' ?>" style="height:<?= $pct ?>%"></div>
                        </div>
                        <div class="bar-label"><?= $d ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Donut Chart Status Kehadiran -->
    <div class="col-lg-5">
        <div class="so-card h-100">
            <div class="so-card-header">
                <div class="so-card-title">Status kehadiran hari ini</div>
            </div>
            <div class="so-card-body">
                <div class="donut-wrap">
                    <div class="donut-container">
                        <svg viewBox="0 0 100 100" style="width:120px;height:120px;transform:rotate(-90deg)">
                            <?php
                            $total = max(1, array_sum($statusHadir));
                            $colors = ['#10b981','#f59e0b','#0ea5e9','#ef4444'];
                            $labels = ['hadir','telat','izin','alpha'];
                            $circumference = 2 * M_PI * 38;
                            $offset = 0;
                            foreach ($labels as $li => $lbl):
                                $val = $statusHadir[$lbl] ?? 0;
                                $dash = ($val / $total) * $circumference;
                                $gap  = $circumference - $dash;
                            ?>
                            <circle cx="50" cy="50" r="38" fill="none" stroke="<?= $colors[$li] ?>"
                                stroke-width="12"
                                stroke-dasharray="<?= round($dash,2) ?> <?= round($gap,2) ?>"
                                stroke-dashoffset="-<?= round($offset,2) ?>"/>
                            <?php $offset += $dash; endforeach; ?>
                            <text x="50" y="54" text-anchor="middle" style="transform:rotate(90deg);transform-origin:center;font-size:18px;font-weight:800;fill:#1e2b3c;"><?= $total ?></text>
                        </svg>
                    </div>
                    <div class="donut-legend">
                        <?php foreach ($labels as $li => $lbl): ?>
                        <div class="legend-item">
                            <div class="legend-dot" style="background:<?= $colors[$li] ?>"></div>
                            <span><?= ucfirst($lbl) ?></span>
                            <span class="legend-val">— <?= $statusHadir[$lbl] ?? 0 ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Absensi Terbaru + Task Terbaru -->
<div class="row g-4">
    <!-- Absensi Terbaru -->
    <div class="col-lg-6">
        <div class="so-card">
            <div class="so-card-header">
                <div class="so-card-title">Absensi terbaru</div>
                <a href="/absensi" style="font-size:12px;font-weight:600;color:var(--primary);text-decoration:none;">Lihat semua →</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="so-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jam masuk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($absensiList)): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--text-muted);font-size:13px;">Belum ada data hari ini</td></tr>
                    <?php else: ?>
                    <?php foreach (array_slice($absensiList, 0, 6) as $a): ?>
                        <?php
                        $initials = strtoupper(substr($a['nama'] ?? 'K', 0, 2));
                        $st = strtolower($a['status'] ?? 'hadir');
                        $stLabel = match($st) { 'hadir'=>'Hadir','telat'=>'Telat','izin'=>'Izin','alpha'=>'Alpha',default=>ucfirst($st) };
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="table-avatar"><?= $initials ?></div>
                                    <div>
                                        <div style="font-size:13px;font-weight:600;"><?= esc($a['nama'] ?? '-') ?></div>
                                        <div style="font-size:11px;color:var(--text-muted);"><?= esc($a['divisi'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:13px;font-weight:600;"><?= $a['jam_masuk'] ? date('H:i', strtotime($a['jam_masuk'])) : '-' ?></td>
                            <td><span class="so-badge <?= $st ?>"><?= $stLabel ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Task Terbaru -->
    <div class="col-lg-6">
        <div class="so-card">
            <div class="so-card-header">
                <div class="so-card-title">Task terbaru</div>
                <a href="/task" style="font-size:12px;font-weight:600;color:var(--primary);text-decoration:none;">Lihat semua →</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="so-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Assignee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($taskList)): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--text-muted);font-size:13px;">Belum ada task</td></tr>
                    <?php else: ?>
                    <?php foreach (array_slice($taskList, 0, 6) as $t): ?>
                        <?php
                        $tc = match($t['status'] ?? 'todo') { 'todo'=>'todo','on_progress'=>'in-progress','done'=>'done',default=>'todo'};
                        $tl = match($t['status'] ?? 'todo') { 'todo'=>'Todo','on_progress'=>'On Progress','done'=>'Done',default=>'Todo'};
                        ?>
                        <tr>
                            <td>
                                <a href="/task/detail/<?= $t['id'] ?>" style="font-size:13px;font-weight:600;color:var(--text);text-decoration:none;">
                                    <?= esc(strlen($t['judul']) > 24 ? substr($t['judul'],0,24).'…' : $t['judul']) ?>
                                </a>
                            </td>
                            <td style="font-size:13px;color:var(--text-muted);"><?= esc($t['assignee_nama'] ?? '-') ?></td>
                            <td><span class="so-badge <?= $tc ?>" style="font-size:11px;"><?= $tl ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.page-heading { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.page-sub     { font-size:13px;color:var(--text-muted);margin:0; }

/* Bar Chart */
.chart-bar-wrap { display:flex;align-items:flex-end;gap:8px;height:160px;padding-top:24px; }
.bar-item       { display:flex;flex-direction:column;align-items:center;flex:1;gap:4px; }
.bar-val        { font-size:11px;font-weight:700;color:var(--text-muted); }
.bar-outer      { flex:1;width:100%;background:var(--border);border-radius:6px 6px 0 0;overflow:hidden;display:flex;align-items:flex-end; }
.bar-fill       { width:100%;background:linear-gradient(0deg,#3b82f6,#93c5fd);border-radius:4px 4px 0 0;transition:height .4s; }
.bar-fill.today { background:linear-gradient(0deg,var(--primary),var(--accent)); }
.bar-label      { font-size:11px;color:var(--text-muted);font-weight:600; }

/* Donut */
.donut-wrap      { display:flex;align-items:center;gap:24px; }
.donut-container { flex-shrink:0; }
.donut-legend    { display:flex;flex-direction:column;gap:10px; }
.legend-item     { display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text); }
.legend-dot      { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
.legend-val      { color:var(--text-muted);font-weight:600; }

/* Table avatar */
.table-avatar { width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0; }
</style>

<?= $this->endSection() ?>
