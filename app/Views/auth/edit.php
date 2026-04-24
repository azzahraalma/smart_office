<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">Edit User</h4>
        <div style="font-size:13px;color:var(--text-muted);">Perbarui data akun: <?= esc($user['nama']) ?></div>
    </div>
    <a href="/users" class="btn-so-outline">
        <span class="material-icons-round" style="font-size:18px;">arrow_back</span>
        Kembali
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="so-alert error">
        <span class="material-icons-round">error_outline</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="so-card" style="max-width:600px;">
    <div class="so-card-header">
        <span class="so-card-title">Informasi Akun</span>
    </div>
    <div class="so-card-body">
        <form action="/users/update/<?= $user['id'] ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="so-label">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                <input type="text" name="nama" class="so-input" value="<?= esc(old('nama', $user['nama'])) ?>" required>
            </div>

            <div class="form-group">
                <label class="so-label">Email <span style="color:var(--danger)">*</span></label>
                <input type="email" name="email" class="so-input" value="<?= esc(old('email', $user['email'])) ?>" required>
            </div>

            <div class="form-group">
                <label class="so-label">
                    Password Baru
                    <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(kosongkan jika tidak ingin diubah)</span>
                </label>
                <div style="position:relative;">
                    <input type="password" name="password" id="inputPassword" class="so-input" placeholder="Minimal 6 karakter">
                    <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);display:flex;">
                        <span class="material-icons-round" id="passIcon" style="font-size:18px;">visibility_off</span>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="so-label">Role <span style="color:var(--danger)">*</span></label>
                <?php if ($user['id'] == session()->get('user_id')): ?>
                    <input type="text" class="so-input" value="<?= ucfirst($user['role']) ?>" disabled style="background:#f8faff;color:var(--text-muted);">
                    <input type="hidden" name="role" value="<?= esc($user['role']) ?>">
                    <small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Role tidak bisa diubah untuk akun sendiri.</small>
                <?php else: ?>
                    <select name="role" class="so-select" required>
                        <option value="manager"    <?= old('role', $user['role']) === 'manager'    ? 'selected' : '' ?>>Manager</option>
                        <option value="karyawan" <?= old('role', $user['role']) === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="so-label">Status</label>
                <?php if ($user['id'] == session()->get('user_id')): ?>
                    <input type="text" class="so-input" value="Aktif" disabled style="background:#f8faff;color:var(--text-muted);">
                    <input type="hidden" name="status" value="aktif">
                <?php else: ?>
                    <select name="status" class="so-select">
                        <option value="aktif"    <?= old('status', $user['status']) === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= old('status', $user['status']) === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="so-label">
                    Foto Profil Baru
                    <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(opsional)</span>
                </label>
                <?php if (!empty($user['foto'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="/uploads/foto/<?= esc($user['foto']) ?>" alt="foto profil" style="width:60px;height:60px;border-radius:12px;object-fit:cover;border:2px solid var(--border);">
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*" class="so-input" style="padding:8px 14px;">
            </div>

            <hr class="so-divider">

            <div class="d-flex gap-3">
                <button type="submit" class="btn-so-primary">
                    <span class="material-icons-round" style="font-size:18px;">save</span>
                    Simpan Perubahan
                </button>
                <a href="/users" class="btn-so-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePass() {
        const input = document.getElementById('inputPassword');
        const icon  = document.getElementById('passIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility_off';
        }
    }
</script>

<?= $this->endSection() ?>