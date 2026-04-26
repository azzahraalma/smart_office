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

<?php
// Tentukan kondisi tombol di PHP agar logikanya jelas
$sudahMasuk  = $absenHariIni && !empty($absenHariIni['jam_masuk']);
$sudahPulang = $absenHariIni && !empty($absenHariIni['jam_keluar']);
$sudahIzin   = $absenHariIni && in_array($absenHariIni['status'], ['izin', 'sakit']);

// Tombol masuk: disabled kalau sudah masuk ATAU sudah izin
$disableMasuk  = $sudahMasuk || $sudahIzin;
// Tombol pulang: disabled kalau belum masuk, sudah pulang, atau status izin
$disablePulang = !$sudahMasuk || $sudahPulang || $sudahIzin;
// Tombol izin: disabled kalau sudah ada data absensi apapun hari ini
$disableIzin   = $absenHariIni !== null;
?>

<div class="row g-3">

    <!-- PANEL ABSENSI -->
    <div class="col-lg-7">
        <div class="so-card">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">fingerprint</span>
                    Absensi Hari Ini
                </span>
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

                <!-- Tombol Absen Masuk & Pulang -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <button id="btn-masuk" class="btn-so-success"
                        onclick="doAbsen('masuk')"
                        style="justify-content:center;padding:16px;font-size:15px;flex-direction:column;gap:6px;"
                        <?= $disableMasuk ? 'disabled' : '' ?>>
                        <span class="material-icons-round" style="font-size:28px;">login</span>
                        <span>Absen Masuk</span>
                        <?php if ($sudahMasuk): ?>
                            <small style="font-size:12px;opacity:.8;">Sudah: <?= substr($absenHariIni['jam_masuk'], 0, 5) ?></small>
                        <?php elseif ($sudahIzin): ?>
                            <small style="font-size:12px;opacity:.8;">Status: <?= ucfirst($absenHariIni['status']) ?></small>
                        <?php endif; ?>
                    </button>

                    <button id="btn-pulang" class="btn-so-danger"
                        onclick="doAbsen('pulang')"
                        style="justify-content:center;padding:16px;font-size:15px;flex-direction:column;gap:6px;"
                        <?= $disablePulang ? 'disabled' : '' ?>>
                        <span class="material-icons-round" style="font-size:28px;">logout</span>
                        <span>Absen Pulang</span>
                        <?php if ($sudahPulang): ?>
                            <small style="font-size:12px;opacity:.8;">Sudah: <?= substr($absenHariIni['jam_keluar'], 0, 5) ?></small>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Tombol Izin -->
                <div style="margin-bottom:14px;">
                    <?php if ($disableIzin): ?>
                        <!-- Sudah ada data absensi — tampilkan info, bukan tombol aktif -->
                        <div style="width:100%;padding:12px;font-size:14px;border:1.5px solid var(--border);border-radius:12px;
                                    background:#f8faff;color:var(--text-muted);display:flex;align-items:center;
                                    justify-content:center;gap:8px;">
                            <span class="material-icons-round" style="font-size:20px;">event_busy</span>
                            Izin sudah diajukan atau absensi hari ini sudah tercatat
                        </div>
                    <?php else: ?>
                        <button onclick="openIzinModal()"
                            class="btn-so-outline"
                            style="width:100%;justify-content:center;padding:12px;font-size:14px;gap:8px;">
                            <span class="material-icons-round" style="font-size:20px;">event_busy</span>
                            Ajukan Izin / Sakit
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Tombol Break -->
                <?php if ($sudahMasuk && !$sudahPulang): ?>
                <div style="margin-bottom:14px;">
                    <button id="btn-break"
                        onclick="toggleBreak()"
                        class="btn-so-outline"
                        style="width:100%;justify-content:center;padding:12px;font-size:14px;gap:8px;border-color:#f59e0b;color:#d97706;">
                        <span class="material-icons-round" style="font-size:20px;" id="break-icon">free_breakfast</span>
                        <span id="break-label">Mulai Break</span>
                    </button>
                    <div style="text-align:center;font-size:11px;color:var(--text-muted);margin-top:6px;" id="break-info"></div>
                </div>
                <?php endif; ?>

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
                    <?php
                        $statusMap   = ['hadir'=>'hadir','telat'=>'telat','izin'=>'izin','sakit'=>'izin','alpha'=>'absen'];
                        $badge       = $statusMap[$absenHariIni['status']] ?? 'todo';
                        $statusLabel = ['hadir'=>'Hadir Hari ini','telat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha'];
                        $iconMap     = ['hadir'=>'check_circle','telat'=>'schedule','izin'=>'event_busy','sakit'=>'medical_services','alpha'=>'cancel'];
                        $colorBg     = ['hadir'=>'#d1fae5','telat'=>'#fef3c7','izin'=>'#dbeafe','sakit'=>'#dbeafe','alpha'=>'#fee2e2'];
                        $colorIcon   = ['hadir'=>'#059669','telat'=>'#d97706','izin'=>'#2563eb','sakit'=>'#2563eb','alpha'=>'#dc2626'];

                        $durasiKerja = null;
                        if (!empty($absenHariIni['jam_masuk']) && !empty($absenHariIni['jam_keluar'])) {
                            $diff = strtotime($absenHariIni['jam_keluar']) - strtotime($absenHariIni['jam_masuk']);
                            if ($diff > 0) {
                                $durasiKerja = floor($diff / 3600) . 'j ' . floor(($diff % 3600) / 60) . 'm';
                            }
                        }

                        $isOt   = !empty($absenHariIni['is_overtime']);
                        $otMnt  = (int)($absenHariIni['overtime_minutes'] ?? 0);
                        $otJam  = floor($otMnt / 60);
                        $otSisa = $otMnt % 60;
                        $otLabel = $otJam > 0 ? "{$otJam}j {$otSisa}m" : "{$otSisa}m";
                        $st = $absenHariIni['status'];
                    ?>

                    <div style="text-align:center;padding:16px 0 20px;">
                        <div style="width:64px;height:64px;border-radius:18px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;background:<?= $colorBg[$st] ?? '#fee2e2' ?>;">
                            <span class="material-icons-round" style="font-size:32px;color:<?= $colorIcon[$st] ?? '#dc2626' ?>;">
                                <?= $iconMap[$st] ?? 'cancel' ?>
                            </span>
                        </div>
                        <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:6px;">
                            <?= $statusLabel[$st] ?? ucfirst($st) ?>
                        </div>
                        <span class="so-badge <?= $badge ?>"><?= ucfirst($st) ?></span>
                    </div>

                    <hr class="so-divider">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div style="background:#f8faff;border-radius:10px;padding:12px;text-align:center;">
                            <span class="material-icons-round" style="font-size:18px;color:var(--success);display:block;margin-bottom:4px;">login</span>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Masuk</div>
                            <div style="font-size:18px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;">
                                <?= !empty($absenHariIni['jam_masuk']) ? substr($absenHariIni['jam_masuk'], 0, 5) : '—' ?>
                            </div>
                        </div>
                        <div style="background:#f8faff;border-radius:10px;padding:12px;text-align:center;">
                            <span class="material-icons-round" style="font-size:18px;color:<?= $isOt ? '#ea580c' : 'var(--danger)' ?>;display:block;margin-bottom:4px;">logout</span>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Pulang</div>
                            <div style="font-size:18px;font-weight:800;color:<?= $isOt ? '#ea580c' : 'var(--text)' ?>;font-variant-numeric:tabular-nums;">
                                <?= !empty($absenHariIni['jam_keluar']) ? substr($absenHariIni['jam_keluar'], 0, 5) : '—' ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center;">
                            <span class="material-icons-round" style="font-size:18px;color:#16a34a;display:block;margin-bottom:4px;">timelapse</span>
                            <div style="font-size:11px;color:#16a34a;font-weight:600;margin-bottom:2px;">Durasi Kerja</div>
                            <div style="font-size:16px;font-weight:800;color:#15803d;"><?= $durasiKerja ?? '—' ?></div>
                            <?php if (!$durasiKerja && !empty($absenHariIni['jam_masuk']) && empty($absenHariIni['jam_keluar'])): ?>
                                <div style="font-size:10px;color:#16a34a;margin-top:2px;">Sedang berjalan</div>
                            <?php endif; ?>
                        </div>

                        <?php if ($isOt): ?>
                        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px;text-align:center;">
                            <span class="material-icons-round" style="font-size:18px;color:#ea580c;display:block;margin-bottom:4px;">timer</span>
                            <div style="font-size:11px;color:#ea580c;font-weight:600;margin-bottom:2px;">Overtime</div>
                            <div style="font-size:16px;font-weight:800;color:#c2410c;"><?= $otLabel ?></div>
                            <div style="font-size:10px;color:#9a3412;margin-top:2px;">Sudah tercatat</div>
                        </div>
                        <?php else: ?>
                        <div style="background:#f8faff;border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center;opacity:.6;">
                            <span class="material-icons-round" style="font-size:18px;color:var(--text-muted);display:block;margin-bottom:4px;">timer_off</span>
                            <div style="font-size:11px;color:var(--text-muted);font-weight:600;margin-bottom:2px;">Overtime</div>
                            <div style="font-size:16px;font-weight:800;color:var(--text-muted);">—</div>
                            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Tidak ada</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isOt): ?>
                    <div style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                        <span class="material-icons-round" style="font-size:20px;color:#ea580c;flex-shrink:0;">warning_amber</span>
                        <div>
                            <div style="font-size:12px;font-weight:700;color:#c2410c;">Overtime Tercatat</div>
                            <div style="font-size:11px;color:#9a3412;margin-top:1px;">Kamu lembur <?= $otLabel ?> hari ini. Manager sudah mendapat notifikasi.</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($absenHariIni['keterangan'])): ?>
                    <div style="background:#f8faff;border-radius:10px;padding:12px 14px;">
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

<!-- MODAL IZIN / SAKIT -->
<div id="izinModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;
           justify-content:center;align-items:center;padding:16px;">
    <div style="background:var(--surface,#fff);border-radius:18px;width:100%;max-width:420px;
                box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;animation:modalIn .25s ease;">

        <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="material-icons-round" style="font-size:22px;color:var(--primary);">event_busy</span>
                <span style="font-size:16px;font-weight:700;color:var(--text);">Ajukan Izin / Sakit</span>
            </div>
            <button onclick="closeIzinModal()"
                style="background:none;border:none;cursor:pointer;padding:4px;border-radius:8px;display:flex;align-items:center;color:var(--text-muted);">
                <span class="material-icons-round" style="font-size:22px;">close</span>
            </button>
        </div>

        <div style="padding:24px;">
            <div style="margin-bottom:18px;">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">
                    Jenis Ketidakhadiran
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <label id="label-izin" onclick="selectJenis('izin')"
                        style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:2px solid var(--primary);
                               border-radius:12px;cursor:pointer;background:#eff6ff;transition:.15s;">
                        <span class="material-icons-round" style="font-size:20px;color:var(--primary);">event_available</span>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);">Izin</div>
                            <div style="font-size:11px;color:var(--text-muted);">Ada keperluan</div>
                        </div>
                        <input type="radio" name="jenis" value="izin" id="radio-izin" checked style="display:none;">
                    </label>
                    <label id="label-sakit" onclick="selectJenis('sakit')"
                        style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:2px solid var(--border);
                               border-radius:12px;cursor:pointer;background:#f8faff;transition:.15s;">
                        <span class="material-icons-round" style="font-size:20px;color:var(--text-muted);">medical_services</span>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);">Sakit</div>
                            <div style="font-size:11px;color:var(--text-muted);">Tidak enak badan</div>
                        </div>
                        <input type="radio" name="jenis" value="sakit" id="radio-sakit" style="display:none;">
                    </label>
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                    Keterangan <span style="color:var(--danger);">*</span>
                </div>
                <textarea id="ket"
                    placeholder="Tulis keterangan izin atau sakitmu di sini..."
                    style="width:100%;min-height:100px;padding:12px 14px;border:1.5px solid var(--border);
                           border-radius:12px;font-size:13px;color:var(--text);resize:vertical;
                           font-family:inherit;outline:none;box-sizing:border-box;
                           transition:border-color .15s;background:var(--surface,#fff);"
                    onfocus="this.style.borderColor='var(--primary)'"
                    onblur="this.style.borderColor='var(--border)'"></textarea>
            </div>

            <div style="display:flex;gap:10px;">
                <button onclick="closeIzinModal()" class="btn-so-outline" style="flex:1;justify-content:center;">Batal</button>
                <button onclick="submitIzin()" id="btn-submit-izin" class="btn-so-primary" style="flex:2;justify-content:center;">
                    <span class="material-icons-round" style="font-size:18px;">send</span>
                    Kirim Pengajuan
                </button>
            </div>

            <div id="izin-result" class="so-alert d-none" style="margin-top:14px;"></div>
        </div>
    </div>
</div>

<style>
@keyframes spin    { to { transform:rotate(360deg); } }
@keyframes modalIn { from { opacity:0;transform:scale(.95) translateY(12px); } to { opacity:1;transform:scale(1) translateY(0); } }
button:disabled { opacity:.45 !important; cursor:not-allowed !important; pointer-events:none !important; }
.btn-so-success, .btn-so-danger, .btn-so-primary, .btn-so-outline { display:flex; }
</style>

<script>
// ── Live clock ──────────────────────────────────
function updateClock() {
    const n = new Date(), p = v => String(v).padStart(2,'0');
    document.getElementById('absen-clock').textContent =
        `${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli',
                    'Agustus','September','Oktober','November','Desember'];
    document.getElementById('absen-date-header').textContent =
        `${days[n.getDay()]}, ${n.getDate()} ${months[n.getMonth()]} ${n.getFullYear()}`;
}
updateClock(); setInterval(updateClock, 1000);

// ── GPS ─────────────────────────────────────────
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
        document.getElementById('loc-coords').textContent =
            `${userLat.toFixed(6)}, ${userLng.toFixed(6)}`;
    },
    () => {
        document.getElementById('loc-icon').textContent = 'location_off';
        document.getElementById('loc-icon').style.color = 'var(--danger)';
        document.getElementById('loc-icon').style.animation = 'none';
        document.getElementById('loc-text').textContent = 'Gagal mendapatkan lokasi GPS';
        document.getElementById('loc-text').style.color = 'var(--danger)';
    }
);

// ── Show result banner ───────────────────────────
function showResult(ok, msg, targetId = 'absen-result') {
    const el = document.getElementById(targetId);
    el.className = `so-alert ${ok ? 'success' : 'error'}`;
    el.innerHTML = `<span class="material-icons-round">${ok ? 'check_circle_outline' : 'error_outline'}</span>${msg}`;
    el.classList.remove('d-none');
    if (ok && targetId === 'absen-result') setTimeout(() => location.reload(), 1800);
}

// ── Absen masuk / pulang ─────────────────────────
function doAbsen(tipe) {
    if (!userLat) { showResult(false, 'Lokasi GPS belum siap. Tunggu sebentar.'); return; }
    const url = tipe === 'masuk' ? '/absen-masuk' : '/absen-pulang';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `latitude=${userLat}&longitude=${userLng}`
    })
    .then(r => r.json())
    .then(d => {
        showResult(d.status, d.message);
        if (d.jarak_meter != null) {
            document.getElementById('jarak-info').style.display = 'block';
            document.getElementById('jarak-val').textContent = d.jarak_meter;
        }
    })
    .catch(() => showResult(false, 'Gagal terhubung ke server.'));
}

// ── Modal Izin ───────────────────────────────────
function openIzinModal() {
    document.getElementById('izinModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeIzinModal() {
    document.getElementById('izinModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('ket').value = '';
    document.getElementById('izin-result').classList.add('d-none');
    selectJenis('izin');
}
document.getElementById('izinModal').addEventListener('click', function(e) {
    if (e.target === this) closeIzinModal();
});

function selectJenis(val) {
    const isIzin = val === 'izin';
    document.getElementById('label-izin').style.borderColor  = isIzin ? 'var(--primary)' : 'var(--border)';
    document.getElementById('label-izin').style.background   = isIzin ? '#eff6ff' : '#f8faff';
    document.getElementById('label-izin').querySelector('.material-icons-round').style.color = isIzin ? 'var(--primary)' : 'var(--text-muted)';
    document.getElementById('label-sakit').style.borderColor = !isIzin ? 'var(--primary)' : 'var(--border)';
    document.getElementById('label-sakit').style.background  = !isIzin ? '#eff6ff' : '#f8faff';
    document.getElementById('label-sakit').querySelector('.material-icons-round').style.color = !isIzin ? 'var(--primary)' : 'var(--text-muted)';
    document.getElementById(isIzin ? 'radio-izin' : 'radio-sakit').checked = true;
}

function submitIzin() {
    const jenis = document.querySelector('input[name="jenis"]:checked').value;
    const ket   = document.getElementById('ket').value.trim();
    if (!ket) { showResult(false, 'Keterangan tidak boleh kosong.', 'izin-result'); return; }

    const btn = document.getElementById('btn-submit-izin');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round" style="font-size:18px;animation:spin 1s linear infinite;">sync</span> Mengirim...';

    fetch('/absensi/izin', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `jenis=${encodeURIComponent(jenis)}&keterangan=${encodeURIComponent(ket)}`
    })
    .then(r => r.json())
    .then(d => {
        showResult(d.status, d.message, 'izin-result');
        if (d.status) {
            setTimeout(() => { closeIzinModal(); location.reload(); }, 1800);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round" style="font-size:18px;">send</span> Kirim Pengajuan';
        }
    })
    .catch(() => {
        showResult(false, 'Gagal terhubung ke server.', 'izin-result');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round" style="font-size:18px;">send</span> Kirim Pengajuan';
    });
}

// ── Break ────────────────────────────────────────
let sedangBreak = false;
<?php if ($sudahMasuk && !$sudahPulang): ?>
fetch('/break/status')
    .then(r => r.json())
    .then(d => {
        sedangBreak = d.sedang_break;
        updateBreakUI();
        if (d.sedang_break && d.mulai) {
            document.getElementById('break-info').textContent = `Break dimulai pukul ${d.mulai}`;
        }
    });
<?php endif; ?>

function updateBreakUI() {
    const btn   = document.getElementById('btn-break');
    const icon  = document.getElementById('break-icon');
    const label = document.getElementById('break-label');
    if (!btn) return;
    if (sedangBreak) {
        btn.style.borderColor = '#ef4444';
        btn.style.color       = '#ef4444';
        btn.style.background  = '#fef2f2';
        icon.textContent      = 'stop_circle';
        label.textContent     = 'Selesai Break';
    } else {
        btn.style.borderColor = '#f59e0b';
        btn.style.color       = '#d97706';
        btn.style.background  = '';
        icon.textContent      = 'free_breakfast';
        label.textContent     = 'Mulai Break';
    }
}

function toggleBreak() {
    const url = sedangBreak ? '/break/selesai' : '/break/mulai';
    const btn = document.getElementById('btn-break');
    btn.disabled = true;
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: ''
    })
    .then(r => r.json())
    .then(d => {
        showResult(d.status, d.message);
        if (d.status) {
            sedangBreak = !sedangBreak;
            updateBreakUI();
            const info = document.getElementById('break-info');
            if (sedangBreak) {
                const now = new Date();
                info.textContent = `Break dimulai pukul ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
            } else {
                info.textContent = '';
                setTimeout(() => location.reload(), 1800);
            }
        }
        btn.disabled = false;
    })
    .catch(() => {
        showResult(false, 'Gagal terhubung ke server.');
        btn.disabled = false;
    });
}
</script>

<?= $this->endSection() ?>