<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">Tambah Akun Baru</h4>
        <div style="font-size:13px;color:var(--text-muted);">Buat akun untuk karyawan baru</div>
    </div>
    <a href="/users" class="btn-so-outline">
        <span class="material-icons-round" style="font-size:18px;">arrow_back</span>
        Kembali ke Daftar User
    </a>
</div>

<!-- FLASH ERROR -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="so-alert error">
        <span class="material-icons-round">error_outline</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="so-card">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">person_add</span>
                    Informasi Akun
                </span>
            </div>
            <div class="so-card-body">
                <form action="/users/store" method="POST" enctype="multipart/form-data" id="registerForm">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="so-label">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                        <div style="position:relative;">
                            <span class="material-icons-round" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:18px;color:var(--text-muted);pointer-events:none;">person</span>
                            <input type="text" name="nama" class="so-input" style="padding-left:42px;"
                                   placeholder="Contoh: Budi Santoso"
                                   value="<?= old('nama') ?>" required minlength="3">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="so-label">Email <span style="color:var(--danger)">*</span></label>
                        <div style="position:relative;">
                            <span class="material-icons-round" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:18px;color:var(--text-muted);pointer-events:none;">mail_outline</span>
                            <input type="email" name="email" class="so-input" style="padding-left:42px;"
                                   placeholder="budi@perusahaan.com"
                                   value="<?= old('email') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="so-label">Password <span style="color:var(--danger)">*</span></label>
                        <div style="position:relative;">
                            <span class="material-icons-round" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:18px;color:var(--text-muted);pointer-events:none;">lock_outline</span>
                            <input type="password" name="password" id="inputPassword" class="so-input"
                                   style="padding-left:42px;padding-right:42px;"
                                   placeholder="Minimal 6 karakter" required minlength="6"
                                   oninput="checkPasswordStrength(this.value)">
                            <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);display:flex;padding:0;">
                                <span class="material-icons-round" id="passIcon" style="font-size:18px;">visibility_off</span>
                            </button>
                        </div>
                        <!-- Password Strength -->
                        <div style="margin-top:8px;" id="strength-wrap">
                            <div style="display:flex;gap:4px;margin-bottom:4px;">
                                <div class="strength-bar" id="s1" style="flex:1;height:4px;border-radius:2px;background:var(--border);transition:background .2s;"></div>
                                <div class="strength-bar" id="s2" style="flex:1;height:4px;border-radius:2px;background:var(--border);transition:background .2s;"></div>
                                <div class="strength-bar" id="s3" style="flex:1;height:4px;border-radius:2px;background:var(--border);transition:background .2s;"></div>
                                <div class="strength-bar" id="s4" style="flex:1;height:4px;border-radius:2px;background:var(--border);transition:background .2s;"></div>
                            </div>
                            <div id="strength-label" style="font-size:11px;color:var(--text-muted);"></div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="so-label">Role <span style="color:var(--danger)">*</span></label>
                                <select name="role" class="so-select" required>
                                    <option value="" disabled selected>Pilih role</option>
                                    <option value="manager"    <?= old('role')==='manager'    ?'selected':'' ?>>manager</option>
                                    <option value="karyawan" <?= old('role')==='karyawan' ?'selected':'' ?>>Karyawan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="so-label">Status</label>
                                <select name="status" class="so-select">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="so-label">
                            Foto Profil
                            <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(opsional, maks 2MB)</span>
                        </label>
                        <div style="border:2px dashed var(--border);border-radius:12px;padding:20px;text-align:center;cursor:pointer;position:relative;"
                             onclick="document.getElementById('inputFoto').click()"
                             id="dropzone">
                            <div id="foto-preview-wrap" style="display:none;margin-bottom:10px;">
                                <img id="foto-preview" style="width:72px;height:72px;border-radius:14px;object-fit:cover;border:2px solid var(--border);">
                            </div>
                            <span class="material-icons-round" style="font-size:32px;color:var(--text-muted);display:block;" id="foto-icon">add_photo_alternate</span>
                            <div style="font-size:13px;color:var(--text-muted);margin-top:6px;" id="foto-label">Klik untuk pilih foto</div>
                            <input type="file" name="foto" id="inputFoto" accept="image/*" style="display:none;" onchange="previewFoto(this)">
                        </div>
                    </div>

                    <hr class="so-divider">

                    <div style="display:flex;gap:12px;">
                        <button type="submit" class="btn-so-primary">
                            <span class="material-icons-round" style="font-size:18px;">person_add</span>
                            Buat Akun
                        </button>
                        <a href="/users" class="btn-so-outline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panduan -->
    <div class="col-lg-5">
        <div class="so-card">
            <div class="so-card-header">
                <span class="so-card-title">
                    <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--accent);">help_outline</span>
                    Panduan
                </span>
            </div>
            <div class="so-card-body" style="padding:20px;">
                <div style="display:flex;flex-direction:column;gap:16px;">

                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;background:var(--primary-light);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:16px;color:var(--primary);">manager_panel_settings</span>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);">Role Manager</div>
                            <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">Dapat mengakses semua menu, termasuk manajemen user, konfigurasi kantor, dan laporan.</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;background:#d1fae5;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:16px;color:var(--success);">badge</span>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);">Role Karyawan</div>
                            <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">Dapat absensi, melihat task sendiri, dan menerima notifikasi.</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;background:#fef3c7;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:16px;color:var(--warning);">lock</span>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);">Password Kuat</div>
                            <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol untuk keamanan maksimal.</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('inputPassword');
    const icon  = document.getElementById('passIcon');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.textContent = input.type === 'password' ? 'visibility_off' : 'visibility';
}

function checkPasswordStrength(val) {
    const bars   = ['s1','s2','s3','s4'];
    const colors = ['var(--danger)','var(--warning)','var(--accent)','var(--success)'];
    const labels = ['Sangat Lemah','Lemah','Cukup Kuat','Kuat','Sangat Kuat'];
    let score = 0;
    if (val.length >= 6)                        score++;
    if (val.length >= 10)                       score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val))              score++;
    bars.forEach((id, i) => {
        document.getElementById(id).style.background = i < score ? colors[score-1] : 'var(--border)';
    });
    const lbl = document.getElementById('strength-label');
    lbl.textContent = val ? labels[score] : '';
    lbl.style.color = score > 0 ? colors[score-1] : 'var(--text-muted)';
}

function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('foto-preview').src = e.target.result;
            document.getElementById('foto-preview-wrap').style.display = 'block';
            document.getElementById('foto-icon').style.display = 'none';
            document.getElementById('foto-label').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?= $this->endSection() ?>