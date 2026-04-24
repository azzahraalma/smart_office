<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">Konfigurasi Kantor</h4>
        <div style="font-size:13px;color:var(--text-muted);">Atur parameter absensi dan lokasi kantor</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;background:#f8faff;border:1px solid var(--border);border-radius:10px;padding:8px 14px;">
        <span class="material-icons-round" style="font-size:16px;color:var(--primary);">location_on</span>
        <span style="font-size:13px;color:var(--text-muted);"><?= esc($kantor['nama_kantor'] ?? 'Kantor') ?></span>
    </div>
</div>

<!-- FLASH -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="so-alert success"><span class="material-icons-round">check_circle_outline</span><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="so-alert error"><span class="material-icons-round">error_outline</span><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">

    <!-- FORM KONFIGURASI -->
    <div class="col-lg-7">
        <div class="so-card">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">settings</span>
                    Parameter Kantor
                </span>
            </div>
            <div class="so-card-body">
                <form action="/kantor/update" method="POST" id="kantorForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $kantor['id'] ?? '' ?>">

                    <!-- Nama Kantor -->
                    <div class="form-group">
                        <label class="so-label">
                            <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">business</span>
                            Nama Kantor <span style="color:var(--danger)">*</span>
                        </label>
                        <input type="text" name="nama_kantor" class="so-input"
                               placeholder="Contoh: Smart Office Headquarter"
                               value="<?= esc($kantor['nama_kantor'] ?? '') ?>" required>
                    </div>

                    <!-- Koordinat -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="so-label">
                                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">my_location</span>
                                    Latitude <span style="color:var(--danger)">*</span>
                                </label>
                                <input type="text" name="latitude" class="so-input" id="inputLat"
                                       placeholder="-6.39629463"
                                       value="<?= esc($kantor['latitude'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="so-label">
                                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">my_location</span>
                                    Longitude <span style="color:var(--danger)">*</span>
                                </label>
                                <input type="text" name="longitude" class="so-input" id="inputLng"
                                       placeholder="106.82169899"
                                       value="<?= esc($kantor['longitude'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol ambil lokasi GPS -->
                    <div style="margin-bottom:20px;margin-top:-8px;">
                        <button type="button" onclick="getMyLocation()" class="btn-so-outline" style="padding:8px 14px;font-size:13px;">
                            <span class="material-icons-round" style="font-size:16px;">gps_fixed</span>
                            Gunakan Lokasi GPS Saya
                        </button>
                        <span id="gps-status" style="font-size:12px;color:var(--text-muted);margin-left:8px;"></span>
                    </div>

                    <!-- Radius -->
                    <div class="form-group">
                        <label class="so-label">
                            <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">radio_button_checked</span>
                            Radius Absensi (meter) <span style="color:var(--danger)">*</span>
                        </label>
                        <input type="number" name="radius_meter" class="so-input"
                               placeholder="100"
                               value="<?= esc($kantor['radius_meter'] ?? '100') ?>"
                               min="10" max="5000" required
                               oninput="updateRadiusLabel(this.value)">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:5px;">
                            Karyawan harus berada dalam radius <strong id="radius-label"><?= esc($kantor['radius_meter'] ?? '100') ?></strong> meter dari kantor untuk bisa absen.
                        </div>
                    </div>

                    <!-- Allowed IP -->
                    <div class="form-group">
                        <label class="so-label">
                            <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">router</span>
                            Allowed IP
                            <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(opsional)</span>
                        </label>
                        <input type="text" name="allowed_ip" class="so-input"
                               placeholder="Contoh: 192.168.100.36"
                               value="<?= esc($kantor['allowed_ip'] ?? '') ?>">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:5px;">
                            Kosongkan jika tidak ingin membatasi IP. Jika diisi, hanya IP ini yang bisa absen.
                        </div>
                    </div>

                    <!-- Jam Kerja -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="so-label">
                                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">login</span>
                                    Jam Masuk <span style="color:var(--danger)">*</span>
                                </label>
                                <input type="time" name="jam_masuk" class="so-input"
                                       value="<?= esc($kantor['jam_masuk'] ?? '09:00') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="so-label">
                                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">logout</span>
                                    Jam Keluar <span style="color:var(--danger)">*</span>
                                </label>
                                <input type="time" name="jam_keluar" class="so-input"
                                       value="<?= esc($kantor['jam_keluar'] ?? '17:00') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Maks Break -->
                    <div class="form-group">
                        <label class="so-label">
                            <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">free_breakfast</span>
                            Maksimal Break (menit)
                        </label>
                        <input type="number" name="maks_break_menit" class="so-input"
                               placeholder="120"
                               value="<?= esc($kantor['maks_break_menit'] ?? '120') ?>"
                               min="0" max="480">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:5px;">
                            Total waktu istirahat/break yang diizinkan per hari.
                        </div>
                    </div>

                    <hr class="so-divider">

                    <button type="submit" class="btn-so-primary">
                        <span class="material-icons-round" style="font-size:18px;">save</span>
                        Simpan Konfigurasi
                    </button>

                </form>
            </div>
        </div>
    </div>

    <!-- RINGKASAN & MAP -->
    <div class="col-lg-5">

        <!-- Ringkasan Saat Ini -->
        <div class="so-card" style="margin-bottom:12px;">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">info</span>
                    Konfigurasi Aktif
                </span>
            </div>
            <div class="so-card-body" style="padding:16px;">
                <div style="display:flex;flex-direction:column;gap:12px;">

                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:36px;height:36px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:18px;color:var(--primary);">location_on</span>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Koordinat</div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);">
                                <?= esc($kantor['latitude'] ?? '—') ?>, <?= esc($kantor['longitude'] ?? '—') ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:36px;height:36px;background:#d1fae5;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:18px;color:var(--success);">radio_button_checked</span>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Radius</div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);"><?= esc($kantor['radius_meter'] ?? '—') ?> meter</div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:36px;height:36px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:18px;color:var(--warning);">schedule</span>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Jam Kerja</div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);">
                                <?= esc(substr($kantor['jam_masuk']??'09:00',0,5)) ?> – <?= esc(substr($kantor['jam_keluar']??'17:00',0,5)) ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:36px;height:36px;background:#e0f2fe;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:18px;color:var(--accent);">router</span>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Allowed IP</div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);">
                                <?= !empty($kantor['allowed_ip']) ? esc($kantor['allowed_ip']) : 'Tidak dibatasi' ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:36px;height:36px;background:#fce7f3;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:18px;color:#db2777;">free_breakfast</span>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);">Maks Break</div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);">
                                <?= esc($kantor['maks_break_menit'] ?? '120') ?> menit
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Link Google Maps -->
        <?php if (!empty($kantor['latitude']) && !empty($kantor['longitude'])): ?>
        <div class="so-card">
            <div class="so-card-body" style="padding:16px;text-align:center;">
                <span class="material-icons-round" style="font-size:36px;color:var(--primary);display:block;margin-bottom:8px;">map</span>
                <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">Lihat lokasi kantor di peta</div>
                <a href="https://www.google.com/maps?q=<?= $kantor['latitude'] ?>,<?= $kantor['longitude'] ?>"
                   target="_blank" class="btn-so-primary" style="display:inline-flex;">
                    <span class="material-icons-round" style="font-size:16px;">open_in_new</span>
                    Buka Google Maps
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function updateRadiusLabel(v) {
    document.getElementById('radius-label').textContent = v || '—';
}

function getMyLocation() {
    const statusEl = document.getElementById('gps-status');
    statusEl.textContent = 'Mengambil lokasi...';
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('inputLat').value = pos.coords.latitude.toFixed(8);
            document.getElementById('inputLng').value = pos.coords.longitude.toFixed(8);
            statusEl.style.color = 'var(--success)';
            statusEl.textContent = '✓ Lokasi berhasil diambil';
        },
        () => {
            statusEl.style.color = 'var(--danger)';
            statusEl.textContent = '✗ Gagal mengambil lokasi GPS';
        }
    );
}
</script>

<?= $this->endSection() ?>