<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h4 style="font-weight:800;color:var(--text);margin-bottom:4px;">Manajemen User</h4>
        <div style="font-size:13px;color:var(--text-muted);">Kelola akun karyawan</div>
    </div>
    <a href="/users/create" class="btn-so-primary">
        <span class="material-icons-round" style="font-size:18px;">person_add</span>
        Tambah User
    </a>
</div>

<!-- FLASH -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="so-alert success"><span class="material-icons-round">check_circle_outline</span><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="so-alert error"><span class="material-icons-round">error_outline</span><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- STAT RINGKASAN -->
<?php
$totalManager  = count(array_filter($users, fn($u) => $u['role'] === 'manager'));
$totalKaryawan = count(array_filter($users, fn($u) => $u['role'] === 'karyawan'));
$totalAktif    = count(array_filter($users, fn($u) => $u['status'] === 'aktif'));
$totalNonaktif = count(array_filter($users, fn($u) => $u['status'] === 'nonaktif'));
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:16px;box-shadow:var(--shadow-card);display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span class="material-icons-round" style="font-size:20px;color:var(--primary);">group</span>
            </div>
            <div>
                <div style="font-size:22px;font-weight:800;color:var(--text);line-height:1;"><?= count($users) ?></div>
                <div style="font-size:12px;color:var(--text-muted);">Total User</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:16px;box-shadow:var(--shadow-card);display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;background:#fee2e2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span class="material-icons-round" style="font-size:20px;color:var(--danger);">manager_panel_settings</span>
            </div>
            <div>
                <div style="font-size:22px;font-weight:800;color:var(--text);line-height:1;"><?= $totalManager ?></div>
                <div style="font-size:12px;color:var(--text-muted);">Manager</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:16px;box-shadow:var(--shadow-card);display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;background:#d1fae5;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span class="material-icons-round" style="font-size:20px;color:var(--success);">badge</span>
            </div>
            <div>
                <div style="font-size:22px;font-weight:800;color:var(--text);line-height:1;"><?= $totalKaryawan ?></div>
                <div style="font-size:12px;color:var(--text-muted);">Karyawan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:16px;box-shadow:var(--shadow-card);display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span class="material-icons-round" style="font-size:20px;color:var(--warning);">block</span>
            </div>
            <div>
                <div style="font-size:22px;font-weight:800;color:var(--text);line-height:1;"><?= $totalNonaktif ?></div>
                <div style="font-size:12px;color:var(--text-muted);">Nonaktif</div>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH + FILTER -->
<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
    <div style="position:relative;flex:1;min-width:200px;max-width:320px;">
        <span class="material-icons-round" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:18px;color:var(--text-muted);pointer-events:none;">search</span>
        <input type="text" id="searchUser" class="so-input" style="padding-left:40px;"
            placeholder="Cari nama atau email..." oninput="filterUsers()">
    </div>
    <select id="filterRole" class="so-select" style="width:auto;font-size:13px;padding:10px 12px;" onchange="filterUsers()">
        <option value="">Semua Role</option>
        <option value="manager">Manager</option>
        <option value="karyawan">Karyawan</option>
    </select>
    <select id="filterStatus" class="so-select" style="width:auto;font-size:13px;padding:10px 12px;" onchange="filterUsers()">
        <option value="">Semua Status</option>
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
    </select>
</div>

<!-- TABEL USER -->
<div class="so-card">
    <div class="so-card-header">
        <span class="so-card-title">
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px;color:var(--primary);">manage_accounts</span>
            Daftar Akun (<span id="user-count"><?= count($users) ?></span>)
        </span>
    </div>

    <?php if (empty($users)): ?>
        <div style="text-align:center;padding:64px 24px;">
            <span class="material-icons-round" style="font-size:56px;color:var(--border);display:block;margin-bottom:12px;">group_off</span>
            <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px;">Belum Ada User</div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">Tambahkan akun karyawan.</div>
            <a href="/users/create" class="btn-so-primary" style="display:inline-flex;">
                <span class="material-icons-round" style="font-size:18px;">person_add</span>Tambah User
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="so-table" id="userTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u):
                        $words = explode(' ', trim($u['nama']));
                        $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                    ?>
                        <tr
                            data-nama="<?= esc(strtolower($u['nama'] ?? '')) ?>"
                            data-email="<?= esc(strtolower($u['email'] ?? '')) ?>"
                            data-role="<?= esc($u['role'] ?? 'karyawan') ?>"
                            data-status="<?= esc($u['status'] ?? 'inactive') ?>">


                            <!-- User Info -->
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white;flex-shrink:0;">
                                        <?php if (!empty($u['foto'])): ?>
                                            <img src="/uploads/foto/<?= esc($u['foto']) ?>" style="width:100%;height:100%;border-radius:12px;object-fit:cover;">
                                        <?php else: ?>
                                            <?= $initials ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-size:14px;font-weight:700;color:var(--text);"><?= esc($u['nama']) ?></div>
                                        <?php if ($u['id'] == session()->get('user_id')): ?>
                                            <div style="font-size:11px;color:var(--primary);font-weight:600;background:var(--primary-light);padding:1px 6px;border-radius:4px;display:inline-block;">Kamu</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td style="color:var(--text-muted);font-size:13px;"><?= esc($u['email']) ?></td>

                            <!-- Role -->
                            <td>
                                <span class="so-badge <?= $u['role'] === 'manager' ? 'high' : 'todo' ?>">
                                    <span class="material-icons-round" style="font-size:12px;"><?= $u['role'] === 'manager' ? 'manager_panel_settings' : 'badge' ?></span>
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="so-badge <?= $u['status'] === 'aktif' ? 'hadir' : 'absen' ?>">
                                    <span class="material-icons-round" style="font-size:12px;"><?= $u['status'] === 'aktif' ? 'check_circle' : 'block' ?></span>
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>

                            <!-- Tanggal -->
                            <td style="font-size:13px;color:var(--text-muted);">
                                <?= $u['created_at'] ? date('d M Y', strtotime($u['created_at'])) : '—' ?>
                            </td>

                            <!-- Aksi -->
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <a href="/users/edit/<?= $u['id'] ?>" class="btn-so-outline" style="padding:6px 10px;font-size:12px;">
                                        <span class="material-icons-round" style="font-size:14px;">edit</span>
                                        Edit
                                    </a>
                                    <?php if ($u['id'] != session()->get('user_id')): ?>
                                        <!-- Toggle Status -->
                                        <form action="/users/toggle/<?= $u['id'] ?>" method="POST" style="display:inline;"
                                            onsubmit="return confirm('<?= $u['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?> user <?= esc($u['nama']) ?>?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-so-outline"
                                                style="padding:6px 10px;font-size:12px;
                                               color:<?= $u['status'] === 'aktif' ? 'var(--warning)' : 'var(--success)' ?>;
                                               border-color:<?= $u['status'] === 'aktif' ? 'var(--warning)' : 'var(--success)' ?>;">
                                                <span class="material-icons-round" style="font-size:14px;"><?= $u['status'] === 'aktif' ? 'block' : 'check_circle' ?></span>
                                                <?= $u['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                        <!-- Hapus -->
                                        <form action="/users/delete/<?= $u['id'] ?>" method="POST" style="display:inline;"
                                            onsubmit="return confirm('Hapus user <?= esc(addslashes($u['nama'])) ?>?\nSemua data absensi & task terkait juga akan terhapus.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-so-danger" style="padding:6px 10px;font-size:12px;">
                                                <span class="material-icons-round" style="font-size:14px;">delete</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- No Result -->
        <div id="no-result" style="display:none;text-align:center;padding:40px 24px;">
            <span class="material-icons-round" style="font-size:40px;color:var(--border);display:block;margin-bottom:8px;">search_off</span>
            <div style="font-size:14px;color:var(--text-muted);">Tidak ada user yang cocok dengan filter.</div>
        </div>

    <?php endif; ?>
</div>

<script>
    function filterUsers() {
        const search = document.getElementById('searchUser').value.toLowerCase();
        const role = document.getElementById('filterRole').value;
        const status = document.getElementById('filterStatus').value;
        const rows = document.querySelectorAll('#userTable tbody tr');
        let visible = 0;

        rows.forEach(row => {
            const matchSearch = !search ||
                row.dataset.nama.includes(search) ||
                row.dataset.email.includes(search);
            const matchRole = !role || row.dataset.role === role;
            const matchStatus = !status || row.dataset.status === status;
            const show = matchSearch && matchRole && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('user-count').textContent = visible;
        const noResult = document.getElementById('no-result');
        if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
    }
</script>

<?= $this->endSection() ?>