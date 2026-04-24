<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">Absensi</h4>
        <div style="font-size:13px;color:var(--text-muted);" id="absen-date-header">—</div>
    </div>
    <a href="/absensi/riwayat" class="btn-so-outline">
        <span class="material-icons-round" style="font-size:18px;">history</span>
        Riwayat Absensi
    </a>
</div>

<!-- FLASH -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="so-alert success"><span class="material-icons-round">check_circle_outline</span><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="so-alert error"><span class="material-icons-round">error_outline</span><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">

    <!-- PANEL ABSENSI -->
    <div class="col-lg-7">
        <div class="so-card">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">fingerprint</span>
                    Absensi Hari Ini
                </span>
                <!-- Live Clock -->
                <div style="font-size:22px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;" id="absen-clock">--:--:--</div>
            </div>
            <div class="so-card-body">

                <!-- Status GPS -->
                <div id="location-status" style="display:flex;align-items:center;gap:10px;background:#f8faff;border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:20px;">
                    <span class="material-icons-round" style="font-size:20px;color:var(--text-muted);animation:spin 1s linear infinite;" id="loc-icon">location_searching</span>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--text-muted);" id="loc-text">Mengambil lokasi GPS...</div>
                        <div style="font-size:11px;color:var(--text-muted);" id="loc-coords"></div>
                    </div>
                </div>

                <!-- Tombol Absen -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">

                    <button id="btn-masuk" class="btn-so-success"
                        onclick="doAbsen('masuk')"
                        style="justify-content:center;padding:16px;font-size:15px;flex-direction:column;gap:6px;"
                        <?= ($absenHariIni && !empty($absenHariIni['jam_masuk'])) ? 'disabled' : '' ?>>
                        <span class="material-icons-round" style="font-size:28px;">login</span>
                        <span>Absen Masuk</span>
                        <?php if ($absenHariIni && !empty($absenHariIni['jam_masuk'])): ?>
                            <small style="font-size:12px;opacity:.8;">Sudah: <?= substr($absenHariIni['jam_masuk'],0,5) ?></small>
                        <?php endif; ?>
                    </button>

                    <button id="btn-pulang" class="btn-so-danger"
                        onclick="doAbsen('pulang')"
                        style="justify-content:center;padding:16px;font-size:15px;flex-direction:column;gap:6px;"
                        <?= (!$absenHariIni || !empty($absenHariIni['jam_keluar'])) ? 'disabled' : '' ?>>
                        <span class="material-icons-round" style="font-size:28px;">logout</span>
                        <span>Absen Pulang</span>
                        <?php if ($absenHariIni && !empty($absenHariIni['jam_keluar'])): ?>
                            <small style="font-size:12px;opacity:.8;">Sudah: <?= substr($absenHariIni['jam_keluar'],0,5) ?></small>
                        <?php endif; ?>
                    </button>

                </div>

                <!-- Hasil Absen -->
                <div id="absen-result" class="so-alert d-none"></div>

                <!-- Info Jarak -->
                <div id="jarak-info" style="display:none;text-align:center;font-size:13px;color:var(--text-muted);margin-top:8px;">
                    Jarak dari kantor: <strong id="jarak-val">—</strong> meter
                </div>

            </div>
        </div>
    </div>

    <!-- STATUS ABSENSI HARI INI -->
    <div class="col-lg-5">
        <div class="so-card h-100">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">today</span>
                    Status Hari Ini
                </span>
            </div>
            <div class="so-card-body">

                <?php if ($absenHariIni): ?>
                    <!-- Status Badge -->
                    <?php
                        $statusMap = ['hadir'=>'hadir','telat'=>'telat','izin'=>'izin','alpha'=>'absen'];
                        $badge = $statusMap[$absenHariIni['status']] ?? 'todo';
                        $statusLabel = ['hadir'=>'Hadir Tepat Waktu','telat'=>'Terlambat','izin'=>'Izin','alpha'=>'Alpha'];
                    ?>
                    <div style="text-align:center;padding:20px 0 24px;">
                        <div style="width:72px;height:72px;border-radius:20px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;
                            background:<?= $absenHariIni['status']==='hadir'?'#d1fae5':($absenHariIni['status']==='telat'?'#fef3c7':'#fee2e2') ?>;">
                            <span class="material-icons-round" style="font-size:36px;color:<?= $absenHariIni['status']==='hadir'?'#059669':($absenHariIni['status']==='telat'?'#d97706':'#dc2626') ?>;">
                                <?= $absenHariIni['status']==='hadir'?'check_circle':($absenHariIni['status']==='telat'?'schedule':'cancel') ?>
                            </span>
                        </div>
                        <div style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:4px;">
                            <?= $statusLabel[$absenHariIni['status']] ?? ucfirst($absenHariIni['status']) ?>
                        </div>
                        <span class="so-badge <?= $badge ?>"><?= ucfirst($absenHariIni['status']) ?></span>
                    </div>

                    <hr class="so-divider">

                    <!-- Detail Waktu -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div style="background:#f8faff;border-radius:10px;padding:14px;text-align:center;">
                            <span class="material-icons-round" style="font-size:20px;color:var(--success);display:block;margin-bottom:6px;">login</span>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Masuk</div>
                            <div style="font-size:20px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;">
                                <?= $absenHariIni['jam_masuk'] ? substr($absenHariIni['jam_masuk'],0,5) : '—' ?>
                            </div>
                        </div>
                        <div style="background:#f8faff;border-radius:10px;padding:14px;text-align:center;">
                            <span class="material-icons-round" style="font-size:20px;color:var(--danger);display:block;margin-bottom:6px;">logout</span>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Pulang</div>
                            <div style="font-size:20px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;">
                                <?= !empty($absenHariIni['jam_keluar']) ? substr($absenHariIni['jam_keluar'],0,5) : '—' ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($absenHariIni['keterangan']): ?>
                        <div style="margin-top:16px;background:#f8faff;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Keterangan</div>
                            <div style="font-size:13px;color:var(--text);"><?= esc($absenHariIni['keterangan']) ?></div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align:center;padding:40px 0;">
                        <span class="material-icons-round" style="font-size:56px;color:var(--border);display:block;margin-bottom:12px;">fingerprint</span>
                        <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px;">Belum Absen</div>
                        <div style="font-size:13px;color:var(--text-muted);">Silahkan tekan tombol "Absen Masuk" untuk memulai kehadiran hari ini.</div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
button:disabled { opacity: .45 !important; cursor: not-allowed !important; }
.btn-so-success, .btn-so-danger { display:flex; }
</style>

<script>
// Live clock
function updateClock() {
    const n = new Date();
    const p = v => String(v).padStart(2,'0');
    document.getElementById('absen-clock').textContent = `${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    document.getElementById('absen-date-header').textContent =
        `${days[n.getDay()]}, ${n.getDate()} ${months[n.getMonth()]} ${n.getFullYear()}`;
}
updateClock(); setInterval(updateClock, 1000);

// GPS
let userLat = null, userLng = null;

navigator.geolocation.getCurrentPosition(
    pos => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        document.getElementById('loc-icon').textContent = 'location_on';
        document.getElementById('loc-icon').style.color = 'var(--success)';
        document.getElementById('loc-icon').style.animation = 'none';
        document.getElementById('loc-text').textContent = 'Lokasi berhasil dideteksi';
        document.getElementById('loc-text').style.color = 'var(--success)';
        document.getElementById('loc-coords').textContent = `${userLat.toFixed(6)}, ${userLng.toFixed(6)}`;
    },
    err => {
        document.getElementById('loc-icon').textContent = 'location_off';
        document.getElementById('loc-icon').style.color = 'var(--danger)';
        document.getElementById('loc-icon').style.animation = 'none';
        document.getElementById('loc-text').textContent = 'Gagal mendapatkan lokasi GPS';
        document.getElementById('loc-text').style.color = 'var(--danger)';
    }
);

function showResult(ok, msg) {
    const el = document.getElementById('absen-result');
    el.className = `so-alert ${ok ? 'success' : 'error'}`;
    el.innerHTML = `<span class="material-icons-round">${ok ? 'check_circle_outline' : 'error_outline'}</span>${msg}`;
    el.classList.remove('d-none');
    if (ok) setTimeout(() => location.reload(), 2000);
}

function doAbsen(tipe) {
    if (!userLat) { showResult(false, 'Lokasi GPS belum siap.'); return; }
    const url = tipe === 'masuk' ? '/absen-masuk' : '/absen-pulang';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `latitude=${userLat}&longitude=${userLng}`
    })
    .then(r => r.json())
    .then(d => {
        showResult(d.status, d.message);
        if (d.jarak_meter !== undefined) {
            document.getElementById('jarak-info').style.display = 'block';
            document.getElementById('jarak-val').textContent = d.jarak_meter;
        }
    })
    .catch(() => showResult(false, 'Gagal terhubung ke server.'));
}
</script>

<?= $this->endSection() ?>