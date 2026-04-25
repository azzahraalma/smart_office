<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">Riwayat Absensi</h4>
        <div style="font-size:13px;color:var(--text-muted);">Rekap kehadiran <?= esc(session()->get('nama')) ?></div>
    </div>
    <a href="/absensi" class="btn-so-outline">
        <span class="material-icons-round" style="font-size:18px;">arrow_back</span>
        Kembali
    </a>
</div>

<!-- RINGKASAN STATISTIK -->
<?php
    $totalHadir    = 0; $totalTelat = 0; $totalIzin = 0; $totalSakit = 0;
    $totalOvertime = 0; $totalOvertimeMenit = 0;
    foreach ($riwayat as $r) {
        if ($r['status'] === 'hadir')      $totalHadir++;
        elseif ($r['status'] === 'telat')  $totalTelat++;
        elseif ($r['status'] === 'izin')   $totalIzin++;
        elseif ($r['status'] === 'sakit')  $totalSakit++;
        if (!empty($r['is_overtime'])) {
            $totalOvertime++;
            $totalOvertimeMenit += (int)($r['overtime_minutes'] ?? 0);
        }
    }
    $total = count($riwayat);
    $totalOtJam = floor($totalOvertimeMenit / 60);
    $totalOtMnt = $totalOvertimeMenit % 60;
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="stat-card-icon success"><span class="material-icons-round">check_circle</span></div>
            <div class="stat-card-label">Total Hadir</div>
            <div class="stat-card-value"><?= $totalHadir ?></div>
            <div class="stat-card-sub">hari</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-card-icon warning"><span class="material-icons-round">schedule</span></div>
            <div class="stat-card-label">Terlambat</div>
            <div class="stat-card-value"><?= $totalTelat ?></div>
            <div class="stat-card-sub">hari</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card accent">
            <div class="stat-card-icon accent"><span class="material-icons-round">event_busy</span></div>
            <div class="stat-card-label">Izin</div>
            <div class="stat-card-value"><?= $totalIzin ?></div>
            <div class="stat-card-sub">hari</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card primary">
            <div class="stat-card-icon primary"><span class="material-icons-round">cancel</span></div>
            <div class="stat-card-label">Sakit</div>
            <div class="stat-card-value"><?= $totalSakit ?></div>
            <div class="stat-card-sub">hari</div>
        </div>
    </div>
</div>

<!-- STAT OVERTIME -->
<?php if ($totalOvertime > 0): ?>
<div style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:14px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <span class="material-icons-round" style="font-size:32px;color:#ea580c;">timer</span>
    <div>
        <div style="font-size:13px;font-weight:700;color:#ea580c;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Total Overtime Bulan Ini</div>
        <div style="font-size:22px;font-weight:800;color:#c2410c;"><?= $totalOtJam ?>j <?= $totalOtMnt ?>m</div>
        <div style="font-size:12px;color:#9a3412;margin-top:2px;">dari <?= $totalOvertime ?> hari kerja lembur</div>
    </div>
</div>
<?php endif; ?>

<!-- TABEL RIWAYAT -->
<div class="so-card">
    <div class="so-card-header">
        <span class="so-card-title">
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">history</span>
            Daftar Riwayat (<?= $total ?> data)
        </span>

        <!-- Filter Bulan (frontend) -->
        <div style="display:flex;gap:8px;align-items:center;">
            <select id="filterBulan" class="so-select" style="width:auto;font-size:13px;padding:7px 12px;" onchange="filterTable()">
                <option value="">Semua Bulan</option>
                <?php
                    $bulanList = [];
                    foreach ($riwayat as $r) {
                        $bln = date('Y-m', strtotime($r['tanggal']));
                        $bulanList[$bln] = date('M Y', strtotime($r['tanggal']));
                    }
                    foreach ($bulanList as $val => $lbl): ?>
                        <option value="<?= $val ?>"><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <?php if (empty($riwayat)): ?>
            <div style="text-align:center;padding:64px 24px;">
                <span class="material-icons-round" style="font-size:56px;color:var(--border);display:block;margin-bottom:12px;">history_toggle_off</span>
                <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px;">Belum Ada Riwayat</div>
                <div style="font-size:13px;color:var(--text-muted);">Riwayat absensi akan muncul di sini setelah kamu melakukan absensi.</div>
            </div>
        <?php else: ?>
        <table class="so-table" id="riwayatTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Durasi Kerja</th>
                    <th>Overtime</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $r):
                    $tgl      = $r['tanggal'];
                    $hariNama = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][date('w', strtotime($tgl))];
                    $tglFmt   = date('d M Y', strtotime($tgl));

                    // Hitung durasi kerja total (termasuk overtime)
                    $durasi = '—';
                    if (!empty($r['jam_masuk']) && !empty($r['jam_keluar'])) {
                        $diff = strtotime($r['jam_keluar']) - strtotime($r['jam_masuk']);
                        if ($diff > 0) {
                            $h = floor($diff / 3600);
                            $m = floor(($diff % 3600) / 60);
                            $durasi = "{$h}j {$m}m";
                        }
                    }

                    // Overtime
                    $isOt   = !empty($r['is_overtime']);
                    $otMnt  = (int)($r['overtime_minutes'] ?? 0);
                    $otJam  = floor($otMnt / 60);
                    $otSisa = $otMnt % 60;
                    $otLabel = $isOt ? ($otJam > 0 ? "{$otJam}j {$otSisa}m" : "{$otSisa}m") : '—';

                    $badgeMap = ['hadir'=>'hadir','telat'=>'telat','izin'=>'izin','sakit'=>'absen'];
                    $badge    = $badgeMap[$r['status']] ?? 'todo';
                    $bln      = date('Y-m', strtotime($tgl));
                ?>
                <tr data-bulan="<?= $bln ?>">
                    <td>
                        <div style="font-weight:600;"><?= $tglFmt ?></div>
                    </td>
                    <td style="color:var(--text-muted);font-size:13px;"><?= $hariNama ?></td>
                    <td>
                        <?php if (!empty($r['jam_masuk'])): ?>
                            <span style="font-weight:600;color:var(--text);font-variant-numeric:tabular-nums;"><?= substr($r['jam_masuk'],0,5) ?></span>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($r['jam_keluar'])): ?>
                            <span style="font-weight:600;color:<?= $isOt ? '#ea580c' : 'var(--text)' ?>;font-variant-numeric:tabular-nums;">
                                <?= substr($r['jam_keluar'],0,5) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:13px;color:<?= $durasi!=='—' ? 'var(--text)' : 'var(--text-muted)' ?>;font-weight:<?= $durasi!=='—'?'600':'400' ?>;">
                            <?= $durasi ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isOt): ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                                <span class="material-icons-round" style="font-size:13px;">timer</span>
                                <?= $otLabel ?>
                            </span>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:13px;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="so-badge <?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                    </td>
                    <td style="font-size:13px;color:var(--text-muted);">
                        <?= !empty($r['keterangan']) ? esc($r['keterangan']) : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function filterTable() {
    const val = document.getElementById('filterBulan').value;
    document.querySelectorAll('#riwayatTable tbody tr').forEach(row => {
        row.style.display = (!val || row.dataset.bulan === val) ? '' : 'none';
    });
}
</script>

<?= $this->endSection() ?>