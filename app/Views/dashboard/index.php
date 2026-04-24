<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $namaUser   = session()->get('nama') ?? 'User';
    $roleUser   = session()->get('role') ?? 'karyawan';
    $jamSekarang = (int) date('H');
    if ($jamSekarang < 11)      $greet = 'Selamat Pagi';
    elseif ($jamSekarang < 15)  $greet = 'Selamat Siang';
    elseif ($jamSekarang < 18)  $greet = 'Selamat Sore';
    else                        $greet = 'Selamat Malam';
?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">
            <?= $greet ?>, <?= esc($namaUser) ?> 👋
        </h4>
        <div style="font-size:13px;color:var(--text-muted);">
            <span id="date-header">—</span>
        </div>
    </div>

    <!-- LIVE CLOCK -->
    <div style="background:white;border:1px solid var(--border);border-radius:14px;padding:12px 20px;display:flex;align-items:center;gap:12px;box-shadow:var(--shadow-card);">
        <div style="width:40px;height:40px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;">
            <span class="material-icons-round" style="color:var(--primary);font-size:20px;">schedule</span>
        </div>
        <div>
            <div id="clock-live" style="font-size:22px;font-weight:800;color:var(--text);line-height:1;font-variant-numeric:tabular-nums;">--:--:--</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Waktu Sekarang</div>
        </div>
    </div>
</div>

<!-- FLASH MESSAGES -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="so-alert success" style="margin-bottom:24px;">
        <span class="material-icons-round">check_circle_outline</span>
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">

    <!-- Absensi Hari Ini -->
    <div class="col-6 col-lg-3">
        <div class="stat-card <?= $absenHariIni ? 'success' : 'warning' ?>">
            <div class="stat-card-icon <?= $absenHariIni ? 'success' : 'warning' ?>">
                <span class="material-icons-round">fingerprint</span>
            </div>
            <div class="stat-card-label">Absensi Hari Ini</div>
            <div class="stat-card-value" style="font-size:18px;font-weight:700;">
                <?php if ($absenHariIni): ?>
                    <?php if (!empty($absenHariIni['jam_masuk'])): ?>
                        <?= substr($absenHariIni['jam_masuk'], 0, 5) ?>
                    <?php else: ?>
                        Hadir
                    <?php endif; ?>
                <?php else: ?>
                    Belum
                <?php endif; ?>
            </div>
            <div class="stat-card-sub">
                <?php if ($absenHariIni): ?>
                    <span style="color:var(--success);font-weight:600;">● Sudah absen masuk</span>
                    <?php if (!empty($absenHariIni['jam_keluar'])): ?>
                        · Pulang <?= substr($absenHariIni['jam_keluar'], 0, 5) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:var(--warning);font-weight:600;">● Belum absen hari ini</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Kehadiran -->
    <div class="col-6 col-lg-3">
        <div class="stat-card primary">
            <div class="stat-card-icon primary">
                <span class="material-icons-round">how_to_reg</span>
            </div>
            <div class="stat-card-label">Status Kehadiran</div>
            <div class="stat-card-value" style="font-size:18px;font-weight:700;">
                <?php
                    if ($absenHariIni) {
                        $statusMap = ['hadir'=>'Hadir','telat'=>'Telat','izin'=>'Izin','alpha'=>'Alpha'];
                        echo $statusMap[$absenHariIni['status']] ?? ucfirst($absenHariIni['status']);
                    } else {
                        echo '—';
                    }
                ?>
            </div>
            <div class="stat-card-sub">
                <?php if ($absenHariIni): ?>
                    <?php
                        $badgeClass = ['hadir'=>'hadir','telat'=>'telat','izin'=>'izin','alpha'=>'absen'][$absenHariIni['status']] ?? 'todo';
                    ?>
                    <span class="so-badge <?= $badgeClass ?>" style="font-size:11px;padding:3px 8px;">
                        <?= ucfirst($absenHariIni['status']) ?>
                    </span>
                <?php else: ?>
                    Belum ada data
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Task Aktif -->
    <div class="col-6 col-lg-3">
        <div class="stat-card accent">
            <div class="stat-card-icon accent">
                <span class="material-icons-round">task_alt</span>
            </div>
            <div class="stat-card-label">Task Aktif</div>
            <div class="stat-card-value"><?= $taskAktif ?></div>
            <div class="stat-card-sub">
                <a href="/task" style="color:var(--accent);font-weight:600;text-decoration:none;">Lihat semua →</a>
            </div>
        </div>
    </div>

    <!-- Notifikasi Belum Dibaca -->
    <div class="col-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-card-icon warning">
                <span class="material-icons-round">notifications</span>
            </div>
            <div class="stat-card-label">Notifikasi</div>
            <div class="stat-card-value"><?= count($notifikasi) ?></div>
            <div class="stat-card-sub">
                <a href="/notifications" style="color:var(--warning);font-weight:600;text-decoration:none;">Lihat semua →</a>
            </div>
        </div>
    </div>

</div>

<!-- MAIN CONTENT ROW -->
<div class="row g-3">

    <!-- ABSENSI CEPAT -->
    <div class="col-lg-5">
        <div class="so-card h-100">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">fingerprint</span>
                    Absensi Cepat
                </span>
                <a href="/absensi" class="btn-so-outline" style="padding:6px 12px;font-size:12px;">Detail</a>
            </div>
            <div class="so-card-body">

                <!-- Status Lokasi -->
                <div id="location-status" style="display:flex;align-items:center;gap:10px;background:#f8faff;border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:16px;">
                    <span class="material-icons-round" style="font-size:20px;color:var(--text-muted);">location_searching</span>
                    <span style="font-size:13px;color:var(--text-muted);">Mengambil lokasi GPS...</span>
                </div>

                <!-- Tombol Absen -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <button id="btn-masuk" class="btn-so-success" onclick="doAbsen('masuk')"
                        style="justify-content:center;padding:14px;"
                        <?= ($absenHariIni && !empty($absenHariIni['jam_masuk'])) ? 'disabled style="opacity:.5;cursor:not-allowed;justify-content:center;padding:14px;"' : '' ?>>
                        <span class="material-icons-round">login</span>
                        Masuk
                    </button>
                    <button id="btn-pulang" class="btn-so-danger" onclick="doAbsen('pulang')"
                        style="justify-content:center;padding:14px;"
                        <?= (!$absenHariIni || !empty($absenHariIni['jam_keluar'])) ? 'disabled style="opacity:.5;cursor:not-allowed;justify-content:center;padding:14px;"' : '' ?>>
                        <span class="material-icons-round">logout</span>
                        Pulang
                    </button>
                </div>

                <!-- Hasil -->
                <div id="absen-result" class="so-alert d-none"></div>

                <!-- Info Absen Hari Ini -->
                <?php if ($absenHariIni): ?>
                <div style="background:#f8faff;border-radius:10px;padding:14px;margin-top:4px;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:10px;">Info Absensi Hari Ini</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Jam Masuk</div>
                            <div style="font-size:16px;font-weight:700;color:var(--text);">
                                <?= $absenHariIni['jam_masuk'] ? substr($absenHariIni['jam_masuk'], 0, 5) : '—' ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Jam Pulang</div>
                            <div style="font-size:16px;font-weight:700;color:var(--text);">
                                <?= !empty($absenHariIni['jam_keluar']) ? substr($absenHariIni['jam_keluar'], 0, 5) : '—' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- NOTIFIKASI TERBARU -->
    <div class="col-lg-7">
        <div class="so-card h-100">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">notifications</span>
                    Notifikasi Terbaru
                </span>
                <a href="/notifications" class="btn-so-outline" style="padding:6px 12px;font-size:12px;">Semua</a>
            </div>
            <div class="so-card-body" style="padding:0;">
                <?php if (!empty($notifikasi)): ?>
                    <?php foreach ($notifikasi as $n): ?>
                        <div style="display:flex;gap:14px;align-items:flex-start;padding:16px 22px;border-bottom:1px solid var(--border);">
                            <div style="width:36px;height:36px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                                <span class="material-icons-round" style="font-size:18px;color:var(--primary);">
                                    <?php
                                        $tipeIcon = [
                                            'absensi'  => 'fingerprint',
                                            'task'     => 'task_alt',
                                            'system'   => 'settings',
                                            'warning'  => 'warning',
                                        ];
                                        echo $tipeIcon[$n['tipe'] ?? ''] ?? 'notifications';
                                    ?>
                                </span>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                                    <div style="font-size:14px;font-weight:600;color:var(--text);"><?= esc($n['judul']) ?></div>
                                    <?php if (!$n['is_read']): ?>
                                        <span style="width:8px;height:8px;background:var(--primary);border-radius:50%;flex-shrink:0;margin-top:4px;"></span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:13px;color:var(--text-muted);margin-top:3px;"><?= esc($n['pesan']) ?></div>
                                <?php if ($n['created_at']): ?>
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center;padding:48px 24px;">
                        <span class="material-icons-round" style="font-size:48px;color:var(--border);display:block;margin-bottom:12px;">notifications_none</span>
                        <div style="font-size:14px;color:var(--text-muted);">Belum ada notifikasi</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
// Live clock
function updateClock() {
    const now   = new Date();
    const hh    = String(now.getHours()).padStart(2,'0');
    const mm    = String(now.getMinutes()).padStart(2,'0');
    const ss    = String(now.getSeconds()).padStart(2,'0');
    const days  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months= ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    document.getElementById('clock-live').textContent    = `${hh}:${mm}:${ss}`;
    document.getElementById('date-header').textContent   =
        `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
}
updateClock();
setInterval(updateClock, 1000);

// Absen via GPS
let userLat = null, userLng = null;

function setLocationStatus(ok, msg) {
    const el = document.getElementById('location-status');
    el.innerHTML = `
        <span class="material-icons-round" style="font-size:20px;color:${ok?'var(--success)':'var(--danger)'};">
            ${ok ? 'location_on' : 'location_off'}
        </span>
        <span style="font-size:13px;color:${ok?'#065f46':'#991b1b'};font-weight:500;">${msg}</span>`;
}

navigator.geolocation.getCurrentPosition(
    pos => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        setLocationStatus(true, `Lokasi terdeteksi · ${userLat.toFixed(5)}, ${userLng.toFixed(5)}`);
    },
    () => setLocationStatus(false, 'Gagal mendapatkan lokasi GPS')
);

function showAbsenResult(ok, msg) {
    const el = document.getElementById('absen-result');
    el.className = `so-alert ${ok ? 'success' : 'error'}`;
    el.innerHTML = `<span class="material-icons-round">${ok?'check_circle_outline':'error_outline'}</span>${msg}`;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 5000);
}

function doAbsen(tipe) {
    if (!userLat) { showAbsenResult(false, 'Lokasi belum siap, tunggu sebentar.'); return; }
    const url = tipe === 'masuk' ? '/absen-masuk' : '/absen-pulang';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `latitude=${userLat}&longitude=${userLng}`
    })
    .then(r => r.json())
    .then(d => {
        showAbsenResult(d.status, d.message + (d.jarak_meter ? ` · Jarak: ${d.jarak_meter}m` : ''));
        if (d.status) setTimeout(() => location.reload(), 1500);
    })
    .catch(() => showAbsenResult(false, 'Gagal menghubungi server.'));
}
</script>

<?= $this->endSection() ?>