<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-wrapper">

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Manajemen User</h1>
            <p class="page-sub">Kelola akun dan akses pengguna sistem</p>
        </div>
        <a href="/users/create" class="btn-add">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/>
            </svg>
            Tambah User
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Stats Bar -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-num"><?= count($users) ?></span>
            <span class="stat-label">Total User</span>
        </div>
        <div class="stat-card">
            <span class="stat-num"><?= count(array_filter($users, fn($u) => $u['status'] === 'aktif')) ?></span>
            <span class="stat-label">Aktif</span>
        </div>
        <div class="stat-card">
            <span class="stat-num"><?= count(array_filter($users, fn($u) => $u['status'] === 'nonaktif')) ?></span>
            <span class="stat-label">Nonaktif</span>
        </div>
        <div class="stat-card">
            <span class="stat-num"><?= count(array_filter($users, fn($u) => $u['role'] === 'manager')) ?></span>
            <span class="stat-label">Manager</span>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="toolbar">
        <div class="search-box">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Cari nama atau email..." oninput="filterTable()">
        </div>
        <div class="filter-group">
            <select id="filterRole" onchange="filterTable()">
                <option value="">Semua Role</option>
                <option value="manager">Manager</option>
                <option value="karyawan">Karyawan</option>
            </select>
            <select id="filterStatus" onchange="filterTable()">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <table class="user-table" id="userTable">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>Pengguna</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th style="width:160px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <p>Belum ada user terdaftar</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $user): ?>
                        <tr data-role="<?= esc($user['role']) ?>" data-status="<?= esc($user['status']) ?>">
                            <td class="td-num"><?= $i + 1 ?></td>

                            <!-- Avatar + Nama -->
                            <td>
                                <div class="user-info">
                                    <?php if (!empty($user['foto'])): ?>
                                        <img src="/uploads/foto/<?= esc($user['foto']) ?>" alt="foto" class="avatar">
                                    <?php else: ?>
                                        <div class="avatar avatar-placeholder">
                                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="user-name"><?= esc($user['nama']) ?></span>
                                </div>
                            </td>

                            <td class="td-email"><?= esc($user['email']) ?></td>

                            <!-- Role Badge -->
                            <td>
                                <span class="badge badge-role badge-<?= $user['role'] === 'manager' ? 'manager' : 'karyawan' ?>">
                                    <?= ucfirst(esc($user['role'])) ?>
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <span class="badge badge-status badge-<?= $user['status'] === 'aktif' ? 'aktif' : 'nonaktif' ?>">
                                    <span class="dot"></span>
                                    <?= ucfirst(esc($user['status'])) ?>
                                </span>
                            </td>

                            <td class="td-date">
                                <?= date('d M Y', strtotime($user['created_at'])) ?>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="action-group">
                                    <!-- Edit -->
                                    <a href="/users/edit/<?= $user['id'] ?>" class="btn-icon btn-edit" title="Edit">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>

                                    <!-- Toggle Status -->
                                    <?php if (session()->get('user_id') != $user['id']): ?>
                                        <form action="/users/toggle/<?= $user['id'] ?>" method="post" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" 
                                                class="btn-icon <?= $user['status'] === 'aktif' ? 'btn-warn' : 'btn-success' ?>"
                                                title="<?= $user['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                <?php if ($user['status'] === 'aktif'): ?>
                                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <rect x="1" y="5" width="22" height="14" rx="7"/><circle cx="16" cy="12" r="3" fill="currentColor"/>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <rect x="1" y="5" width="22" height="14" rx="7"/><circle cx="8" cy="12" r="3" fill="currentColor"/>
                                                    </svg>
                                                <?php endif; ?>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form action="/users/delete/<?= $user['id'] ?>" method="post" style="display:inline"
                                              onsubmit="return confirm('Hapus user <?= esc($user['nama'], 'js') ?>? Tindakan ini tidak bisa dibatalkan.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-icon btn-danger" title="Hapus">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                                    <path d="M10 11v6"/><path d="M14 11v6"/>
                                                    <path d="M9 6V4h6v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="self-badge" title="Akun Anda">Saya</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- No result state (hidden by default) -->
        <div id="noResult" class="empty-state" style="display:none; padding: 2.5rem 1rem;">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
            <p>Tidak ada hasil yang cocok</p>
        </div>
    </div>

</div><!-- .page-wrapper -->


<!-- ===================== STYLES ===================== -->
<style>
    /* --- Layout --- */
    .page-wrapper {
        padding: 1.75rem 2rem;
        max-width: 1200px;
        font-family: 'Segoe UI', system-ui, sans-serif;
        color: #1e293b;
    }

    /* --- Header --- */
    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 .2rem; }
    .page-sub   { color: #64748b; font-size: .875rem; margin: 0; }

    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: #2563eb;
        color: #fff;
        text-decoration: none;
        padding: .55rem 1.1rem;
        border-radius: 8px;
        font-size: .875rem;
        font-weight: 600;
        transition: background .18s, transform .15s;
        white-space: nowrap;
    }
    .btn-add:hover { background: #1d4ed8; transform: translateY(-1px); }

    /* --- Alerts --- */
    .alert {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .75rem 1rem;
        border-radius: 8px;
        font-size: .875rem;
        font-weight: 500;
        margin-bottom: 1.25rem;
    }
    .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-error   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

    /* --- Stats --- */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .9rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .85rem 1.1rem;
        display: flex;
        flex-direction: column;
        gap: .2rem;
    }
    .stat-num   { font-size: 1.6rem; font-weight: 700; color: #2563eb; line-height: 1; }
    .stat-label { font-size: .78rem; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }

    /* --- Toolbar --- */
    .toolbar {
        display: flex;
        gap: .75rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .search-box {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: .5rem .85rem;
        flex: 1;
        min-width: 200px;
    }
    .search-box svg { color: #94a3b8; flex-shrink: 0; }
    .search-box input {
        border: none;
        outline: none;
        font-size: .875rem;
        color: #1e293b;
        width: 100%;
        background: transparent;
    }
    .filter-group { display: flex; gap: .6rem; }
    .filter-group select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: .5rem .75rem;
        font-size: .875rem;
        color: #334155;
        background: #fff;
        cursor: pointer;
        outline: none;
    }
    .filter-group select:focus { border-color: #93c5fd; }

    /* --- Table Card --- */
    .table-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .user-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .875rem;
    }
    .user-table thead tr {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .user-table th {
        padding: .75rem 1rem;
        text-align: left;
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        white-space: nowrap;
    }
    .user-table td {
        padding: .85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .user-table tbody tr:last-child td { border-bottom: none; }
    .user-table tbody tr:hover { background: #f8fafc; }

    .td-num  { color: #94a3b8; font-size: .8rem; text-align: center; }
    .td-email { color: #475569; }
    .td-date  { color: #94a3b8; font-size: .8rem; white-space: nowrap; }

    /* --- User Info --- */
    .user-info {
        display: flex;
        align-items: center;
        gap: .65rem;
    }
    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        flex-shrink: 0;
    }
    .avatar-placeholder {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: #fff;
        font-weight: 700;
        font-size: .875rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .user-name { font-weight: 600; color: #1e293b; }

    /* --- Badges --- */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .65rem;
        border-radius: 20px;
        font-size: .775rem;
        font-weight: 600;
    }
    .badge-manager  { background: #ede9fe; color: #6d28d9; }
    .badge-karyawan { background: #e0f2fe; color: #0369a1; }
    .badge-aktif    { background: #f0fdf4; color: #15803d; }
    .badge-nonaktif { background: #fef2f2; color: #b91c1c; }
    .dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    /* --- Actions --- */
    .action-group {
        display: flex;
        gap: .4rem;
        align-items: center;
    }
    .btn-icon {
        width: 32px; height: 32px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background .15s, transform .12s;
        background: none;
        padding: 0;
        text-decoration: none;
    }
    .btn-icon:hover { transform: translateY(-1px); }
    .btn-edit    { color: #2563eb; background: #eff6ff; border-color: #bfdbfe; }
    .btn-edit:hover { background: #dbeafe; }
    .btn-warn    { color: #d97706; background: #fffbeb; border-color: #fde68a; }
    .btn-warn:hover { background: #fef3c7; }
    .btn-success { color: #15803d; background: #f0fdf4; border-color: #bbf7d0; }
    .btn-success:hover { background: #dcfce7; }
    .btn-danger  { color: #dc2626; background: #fff1f2; border-color: #fecaca; }
    .btn-danger:hover { background: #fee2e2; }

    .self-badge {
        font-size: .75rem;
        color: #94a3b8;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: .2rem .5rem;
        white-space: nowrap;
    }

    /* --- Empty state --- */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }
    .empty-state svg  { display: block; margin: 0 auto .75rem; opacity: .5; }
    .empty-state p    { margin: 0; font-size: .9rem; }

    /* --- Responsive --- */
    @media (max-width: 768px) {
        .page-wrapper { padding: 1rem; }
        .stats-row    { grid-template-columns: repeat(2, 1fr); }
        .td-email, .td-date { display: none; }
        .filter-group { flex-wrap: wrap; }
    }
</style>

<!-- ===================== SCRIPT ===================== -->
<script>
function filterTable() {
    const q      = document.getElementById('searchInput').value.toLowerCase();
    const role   = document.getElementById('filterRole').value.toLowerCase();
    const status = document.getElementById('filterStatus').value.toLowerCase();

    const rows   = document.querySelectorAll('#userTable tbody tr[data-role]');
    let   visible = 0;

    rows.forEach(row => {
        const text    = row.innerText.toLowerCase();
        const rRole   = row.dataset.role;
        const rStatus = row.dataset.status;

        const matchQ      = text.includes(q);
        const matchRole   = !role   || rRole   === role;
        const matchStatus = !status || rStatus === status;

        if (matchQ && matchRole && matchStatus) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('noResult').style.display = visible === 0 ? 'flex' : 'none';
    if (visible === 0) {
        document.getElementById('noResult').style.flexDirection = 'column';
        document.getElementById('noResult').style.alignItems    = 'center';
    }
}
</script>

<?= $this->endSection() ?>