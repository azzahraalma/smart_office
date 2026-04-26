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
$statusHadir  = $statusHadir     ?? ['hadir' => 0, 'telat' => 0, 'izin' => 0, 'alpha' => 0];

// Warna task aktif — dihitung di PHP, bukan di inline style
$taskSubColor = $taskAktif > 10 ? '#ef4444' : '#9ca3af';
$taskSubText  = $taskAktif > 10 ? $taskAktif . ' mendekati deadline' : 'Normal';
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
            <div class="stat-card-sub" style="color:#10b981">+2 bulan ini</div>
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
            <div class="stat-card-sub" style="color:<?= $taskSubColor ?>">
                <?= $taskSubText ?>
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
            <div class="stat-card-sub" style="color:#10b981">+5 minggu ini</div>
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
                <?php
                // Generate label 7 hari terakhir secara dinamis
                // index 0 = 6 hari lalu, index 6 = hari ini
                $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                $labels7  = [];
                for ($di = 6; $di >= 0; $di--) {
                    $labels7[] = $dayNames[(int) date('w', strtotime("-{$di} days"))];
                }

                // Pastikan $vals selalu 7 elemen
                $vals = array_values($chartHadir);
                while (count($vals) < 7) $vals[] = 0;
                $vals = array_slice($vals, 0, 7);
                $maxV = max($vals) ?: 1;
                ?>
                <div class="dash-barchart">
                    <?php foreach ($labels7 as $i => $d):
                        $pct      = round(($vals[$i] / $maxV) * 100);
                        $barH     = $vals[$i] > 0 ? max($pct, 10) : 0;
                        $isToday  = ($i === 6);
                        // Semua nilai disiapkan sebagai variabel PHP murni
                        $barBg    = $isToday ? 'linear-gradient(to top,#3b82f6,#6366f1)' : 'linear-gradient(to top,#3b82f6,#93c5fd)';
                        $lblColor = $isToday ? '#3b82f6' : '#6b7280';
                        $lblWeight = $isToday ? '800' : '600';
                    ?>
                        <div class="dash-bar-col">
                            <div class="dash-bar-num"><?= $vals[$i] ?></div>
                            <div class="dash-bar-track">
                                <?php if ($barH > 0): ?>
                                    <div class="dash-bar-fill" style="height:<?= $barH ?>%;background:<?= $barBg ?>"></div>
                                <?php endif; ?>
                            </div>
                            <div class="dash-bar-lbl" style="color:<?= $lblColor ?>;font-weight:<?= $lblWeight ?>"><?= $d ?></div>
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
                <div class="dash-donut-wrap">
                    <div class="dash-donut-svg">
                        <svg viewBox="0 0 100 100" width="120" height="120" style="transform:rotate(-90deg)">
                            <?php
                            $donutColors = ['#10b981', '#f59e0b', '#0ea5e9'];
                            $donutLabels = ['hadir', 'telat', 'izin'];
                            $donutTotal  = max(1, array_sum(array_intersect_key($statusHadir, array_flip($donutLabels))));
                            $circ        = 2 * M_PI * 38;
                            $off         = 0;
                            foreach ($donutLabels as $li => $lbl):
                                $v    = $statusHadir[$lbl] ?? 0;
                                $dash = round(($v / $donutTotal) * $circ, 2);
                                $gap  = round($circ - $dash, 2);
                                $dashOffset = round($off, 2);
                            ?>
                                <circle cx="50" cy="50" r="38"
                                    fill="none"
                                    stroke="<?= $donutColors[$li] ?>"
                                    stroke-width="12"
                                    stroke-dasharray="<?= $dash ?> <?= $gap ?>"
                                    stroke-dashoffset="-<?= $dashOffset ?>" />
                            <?php $off += $dash;
                            endforeach; ?>
                            <text x="50" y="54" text-anchor="middle"
                                style="transform:rotate(90deg);transform-origin:center;font-size:18px;font-weight:800;fill:#1e2b3c">
                                <?= $hadirHariIni ?>
                            </text>
                        </svg>
                    </div>
                    <div class="dash-donut-legend">
                        <?php foreach ($donutLabels as $li => $lbl): ?>
                            <div class="dash-legend-row">
                                <span class="dash-legend-dot" style="background:<?= $donutColors[$li] ?>"></span>
                                <span class="dash-legend-name"><?= ucfirst($lbl) ?></span>
                                <span class="dash-legend-val">— <?= $statusHadir[$lbl] ?? 0 ?></span>
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
                <a href="/absensi" class="so-card-link">Lihat semua →</a>
            </div>
            <div style="overflow-x:auto">
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
                            <tr>
                                <td colspan="3" class="so-table-empty">Belum ada data hari ini</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($absensiList, 0, 6) as $a):
                                $initials = strtoupper(substr($a['nama'] ?? 'K', 0, 2));
                                $st       = strtolower($a['status'] ?? 'hadir');
                                $stLabel  = match ($st) {
                                    'hadir' => 'Hadir',
                                    'telat' => 'Telat',
                                    'izin'  => 'Izin',
                                    'alpha' => 'Alpha',
                                    default => ucfirst($st)
                                };
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="table-avatar"><?= $initials ?></div>
                                            <div>
                                                <div style="font-size:13px;font-weight:600"><?= esc($a['nama'] ?? '-') ?></div>
                                                <div style="font-size:11px;color:#9ca3af"><?= esc($a['divisi'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size:13px;font-weight:600"><?= $a['jam_masuk'] ? date('H:i', strtotime($a['jam_masuk'])) : '-' ?></td>
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
                <a href="/task" class="so-card-link">Lihat semua →</a>
            </div>
            <div style="overflow-x:auto">
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
                            <tr>
                                <td colspan="3" class="so-table-empty">Belum ada task</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($taskList, 0, 6) as $t):
                                $tc = match ($t['status'] ?? 'todo') {
                                    'todo'        => 'todo',
                                    'on_progress' => 'in-progress',
                                    'done'        => 'done',
                                    default       => 'todo'
                                };
                                $tl = match ($t['status'] ?? 'todo') {
                                    'todo'        => 'Todo',
                                    'on_progress' => 'On Progress',
                                    'done'        => 'Done',
                                    default       => 'Todo'
                                };
                            ?>
                                <tr>
                                    <td>
                                        <a href="/task/detail/<?= $t['id'] ?>" style="font-size:13px;font-weight:600;color:inherit;text-decoration:none">
                                            <?= esc(mb_strlen($t['judul']) > 24 ? mb_substr($t['judul'], 0, 24) . '…' : $t['judul']) ?>
                                        </a>
                                    </td>
                                    <td style="font-size:13px;color:#9ca3af"><?= esc($t['assignee_nama'] ?? '-') ?></td>
                                    <td><span class="so-badge <?= $tc ?>" style="font-size:11px"><?= $tl ?></span></td>
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
    /* ── Typography ── */
    .page-heading {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 4px;
    }

    .page-sub {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
    }

    .so-card-link {
        font-size: 12px;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
    }

    .so-table-empty {
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }

    /* ── Bar Chart ── */
    .dash-barchart {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        height: 160px;
        padding-bottom: 28px;
    }

    .dash-bar-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        height: 100%;
        justify-content: flex-end;
    }

    .dash-bar-num {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        line-height: 1;
    }

    .dash-bar-track {
        width: 100%;
        height: 100px;
        background: #e5e7eb;
        border-radius: 6px 6px 0 0;
        position: relative;
        overflow: hidden;
    }

    .dash-bar-fill {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 4px 4px 0 0;
        transition: height .4s ease;
    }

    .dash-bar-lbl {
        font-size: 11px;
        line-height: 1;
    }

    /* ── Donut Chart ── */
    .dash-donut-wrap {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .dash-donut-svg {
        flex-shrink: 0;
    }

    .dash-donut-legend {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .dash-legend-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .dash-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dash-legend-name {
        color: var(--text);
    }

    .dash-legend-val {
        color: #6b7280;
        font-weight: 600;
    }

    /* ── Table Avatar ── */
    .table-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }
</style>

<?= $this->endSection() ?>