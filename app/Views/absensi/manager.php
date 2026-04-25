<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- HEADER -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="font-weight:800;margin:0;">Dashboard Absensi</h4>
        <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">
            <?= date('l, d F Y') ?>
        </div>
    </div>
    <form method="get">
        <input type="month" name="bulan" value="<?= esc($bulan) ?>"
            style="padding:8px 14px;border-radius:10px;border:1px solid #e2e8f0;font-size:13px;color:#334155;">
    </form>
</div>

<!-- FLASH -->
<?php if (session()->getFlashdata('success')): ?>
<div style="background:#dcfce7;color:#16a34a;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:8px;">
    ✅ <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div style="background:#fee2e2;color:#dc2626;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:8px;">
    ❌ <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<!-- SUMMARY CARDS — 5 kartu termasuk overtime -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:16px 20px;">
        <div style="font-size:11px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Hadir</div>
        <div style="font-size:28px;font-weight:800;color:#15803d;"><?= $summary['hadir'] ?></div>
    </div>
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:16px 20px;">
        <div style="font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Telat</div>
        <div style="font-size:28px;font-weight:800;color:#b45309;"><?= $summary['telat'] ?></div>
    </div>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:16px 20px;">
        <div style="font-size:11px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Izin</div>
        <div style="font-size:28px;font-weight:800;color:#1d4ed8;"><?= $summary['izin'] ?></div>
    </div>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:14px;padding:16px 20px;">
        <div style="font-size:11px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Sakit</div>
        <div style="font-size:28px;font-weight:800;color:#b91c1c;"><?= $summary['sakit'] ?></div>
    </div>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:16px 20px;">
        <div style="font-size:11px;font-weight:700;color:#ea580c;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Overtime</div>
        <div style="font-size:28px;font-weight:800;color:#c2410c;"><?= $summary['overtime'] ?></div>
        <div style="font-size:11px;color:#9a3412;margin-top:2px;">kejadian</div>
    </div>
</div>

<!-- CHART -->
<div class="card" style="margin-bottom:20px;">
    <div style="font-weight:700;font-size:14px;margin-bottom:16px;">📈 Kehadiran 7 Hari Terakhir</div>
    <canvas id="chart7hari" height="80"></canvas>
</div>

<!-- REKAP KARYAWAN — dengan kolom overtime -->
<div class="card" style="margin-bottom:20px;">
    <div style="font-weight:700;font-size:14px;margin-bottom:16px;">👥 Performa Karyawan — <?= date('F Y', strtotime($bulan . '-01')) ?></div>

    <?php if (empty($rekapUser)): ?>
        <div style="text-align:center;padding:30px;color:#94a3b8;">Belum ada data bulan ini</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:10px 14px;text-align:left;border-radius:8px 0 0 8px;color:#64748b;font-weight:600;">Karyawan</th>
                    <th style="padding:10px 14px;text-align:center;color:#16a34a;font-weight:600;">✔ Hadir</th>
                    <th style="padding:10px 14px;text-align:center;color:#d97706;font-weight:600;">⏰ Telat</th>
                    <th style="padding:10px 14px;text-align:center;color:#2563eb;font-weight:600;">📌 Izin</th>
                    <th style="padding:10px 14px;text-align:center;color:#dc2626;font-weight:600;">🤒 Sakit</th>
                    <th style="padding:10px 14px;text-align:center;border-radius:0 8px 8px 0;color:#ea580c;font-weight:600;">⏱ Overtime</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekapUser as $uid => $r):
                    $otJam  = floor($r['overtime_minutes'] / 60);
                    $otSisa = $r['overtime_minutes'] % 60;
                    $otLabel = $r['overtime_hari'] > 0
                        ? "{$r['overtime_hari']}x · " . ($otJam > 0 ? "{$otJam}j {$otSisa}m" : "{$otSisa}m")
                        : '—';
                ?>
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:12px 14px;font-weight:600;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
                                <?= strtoupper(substr($userMap[$uid] ?? 'U', 0, 1)) ?>
                            </div>
                            <?= esc($userMap[$uid] ?? 'User #' . $uid) ?>
                        </div>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="background:#f0fdf4;color:#16a34a;padding:3px 10px;border-radius:20px;font-weight:700;"><?= $r['hadir'] ?></span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="background:#fffbeb;color:#d97706;padding:3px 10px;border-radius:20px;font-weight:700;"><?= $r['telat'] ?></span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:20px;font-weight:700;"><?= $r['izin'] ?></span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:20px;font-weight:700;"><?= $r['sakit'] ?></span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <?php if ($r['overtime_hari'] > 0): ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;padding:3px 10px;border-radius:20px;font-weight:700;font-size:12px;">
                                <span class="material-icons-round" style="font-size:13px;">timer</span>
                                <?= $otLabel ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:13px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- APPROVAL -->
<div class="card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <div style="font-weight:700;font-size:14px;">📋 Approval Izin / Sakit</div>
        <?php if (!empty($pending)): ?>
        <span style="background:#ef4444;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">
            <?= count($pending) ?> pending
        </span>
        <?php endif; ?>
    </div>

    <?php if (empty($pending)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            <div style="font-size:36px;margin-bottom:8px;">🎉</div>
            <div style="font-weight:600;">Semua pengajuan sudah diproses!</div>
        </div>
    <?php else: ?>
    <div style="display:grid;gap:12px;">
        <?php foreach ($pending as $p): ?>
        <div style="border:1px solid #e2e8f0;border-radius:14px;padding:16px;background:#fff;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

            <!-- AVATAR -->
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#ef4444);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px;flex-shrink:0;">
                <?= strtoupper(substr($userMap[$p['user_id']] ?? 'U', 0, 1)) ?>
            </div>

            <!-- INFO -->
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:14px;margin-bottom:2px;">
                    <?= esc($userMap[$p['user_id']] ?? 'User #' . $p['user_id']) ?>
                </div>
                <div style="font-size:12px;color:#64748b;margin-bottom:4px;">
                    <?= date('d F Y', strtotime($p['tanggal'])) ?>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="background:<?= ($p['jenis'] ?? '') === 'sakit' ? '#fef2f2' : '#eff6ff' ?>;
                                 color:<?= ($p['jenis'] ?? '') === 'sakit' ? '#dc2626' : '#2563eb' ?>;
                                 font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;text-transform:uppercase;">
                        <?= $p['jenis'] ?? 'izin' ?>
                    </span>
                    <span style="font-size:12px;color:#64748b;"><?= esc($p['keterangan'] ?? '-') ?></span>
                </div>
            </div>

            <!-- ACTIONS -->
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="post" action="/absensi/approve/<?= $p['id'] ?>">
                    <?= csrf_field() ?>
                    <button type="submit"
                        style="background:#16a34a;color:#fff;padding:8px 18px;border-radius:9px;border:none;cursor:pointer;font-weight:600;font-size:13px;">
                        ✅ Approve
                    </button>
                </form>
                <form method="post" action="/absensi/reject/<?= $p['id'] ?>">
                    <?= csrf_field() ?>
                    <button type="submit"
                        style="background:#ef4444;color:#fff;padding:8px 18px;border-radius:9px;border:none;cursor:pointer;font-weight:600;font-size:13px;">
                        ❌ Reject
                    </button>
                </form>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chart7hari'), {
    type: 'line',
    data: {
        labels: ['-6','-5','-4','-3','-2','-1','Hari ini'],
        datasets: [{
            label: 'Hadir',
            data: <?= json_encode($chart) ?>,
            tension: .4,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,.08)',
            fill: true,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 5,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

<style>
.card { background:#fff;border-radius:16px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.05); }
@media(max-width:600px) {
    div[style*="grid-template-columns:repeat(5"] { grid-template-columns:repeat(2,1fr) !important; }
}
</style>

<?= $this->endSection() ?>