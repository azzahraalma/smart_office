<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- HEADER -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h4 style="font-weight:800;">Detail Task</h4>
        <div style="font-size:13px;color:var(--text-muted);">Informasi lengkap task</div>
    </div>
    <a href="/task" class="btn-so-outline">Kembali</a>
</div>

<!-- FLASH -->
<?php if (session()->getFlashdata('success')): ?>
<div class="so-alert success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="so-alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php
$statusClass = match($task['status']) {
    'todo'        => 'todo',
    'on_progress' => 'in-progress',
    'done'        => 'done',
    default       => 'todo'
};
$statusLabel = match($task['status']) {
    'todo'        => 'To Do',
    'on_progress' => 'In Progress',
    'done'        => 'Selesai',
    default       => $task['status']
};
$prioClass = match($task['prioritas']) {
    'rendah' => 'low',
    'sedang' => 'medium',
    'tinggi' => 'high',
    default  => 'low'
};
$prioLabel = ucfirst($task['prioritas']);
$isOverdue = $task['deadline']
    && strtotime($task['deadline']) < time()
    && $task['status'] !== 'done';
$progress  = match($task['status']) {
    'todo'        => 0,
    'on_progress' => 60,
    'done'        => 100,
    default       => 0
};

function fileIcon(string $name): string {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return match($ext) {
        'pdf'              => '📄',
        'jpg','jpeg','png' => '🖼️',
        'doc','docx'       => '📝',
        'xls','xlsx'       => '📊',
        default            => '📎'
    };
}
?>

<!-- BANNER TELAT -->
<?php if ($isOverdue): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <span style="font-size:24px;">🚨</span>
    <div>
        <div style="font-weight:700;color:#dc2626;font-size:14px;">Task Ini Sudah Melewati Deadline!</div>
        <div style="font-size:13px;color:#991b1b;margin-top:2px;">
            Deadline: <?= date('d M Y, H:i', strtotime($task['deadline'])) ?> · Segera selesaikan task ini.
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">

<!-- ===================== MAIN ===================== -->
<div class="col-lg-8">
<div class="so-card">
<div class="so-card-body">

<h3 style="font-weight:800;margin-bottom:6px;"><?= esc($task['judul']) ?></h3>

<div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
    <span class="so-badge <?= $prioClass ?>"><?= $prioLabel ?></span>
    <span class="so-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
    <?php if ($isOverdue): ?>
        <span class="so-badge" style="background:#fee2e2;color:#dc2626;">⚠️ Telat</span>
    <?php endif; ?>
</div>

<p style="color:var(--text-muted);line-height:1.6;">
    <?= esc($task['deskripsi']) ?: 'Tidak ada deskripsi' ?>
</p>

<!-- PROGRESS -->
<div style="margin:18px 0;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <div style="font-size:12px;color:var(--text-muted);">Progress</div>
        <div style="font-size:12px;font-weight:700;"><?= $progress ?>%</div>
    </div>
    <div style="height:8px;background:#eef1f6;border-radius:999px;overflow:hidden;">
        <div style="width:<?= $progress ?>%;height:100%;background:<?= $isOverdue ? 'linear-gradient(90deg,#ef4444,#f87171)' : 'linear-gradient(90deg,#4f8cff,#6ea8ff)' ?>;transition:width .4s;"></div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-4 mb-3">
        <div style="font-size:12px;color:var(--text-muted);">Prioritas</div>
        <div style="font-weight:600;"><?= $prioLabel ?></div>
    </div>
    <div class="col-md-4 mb-3">
        <div style="font-size:12px;color:var(--text-muted);">Status</div>
        <div style="font-weight:600;"><?= $statusLabel ?></div>
    </div>
    <div class="col-md-4 mb-3">
        <div style="font-size:12px;color:var(--text-muted);">Deadline</div>
        <div style="<?= $isOverdue ? 'color:#dc2626;font-weight:700;' : 'font-weight:600;' ?>">
            <?= $task['deadline'] ? date('d M Y, H:i', strtotime($task['deadline'])) : '-' ?>
            <?php if ($isOverdue): ?>
                <span style="font-size:11px;background:#fee2e2;color:#dc2626;padding:2px 6px;border-radius:6px;margin-left:4px;">Telat</span>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
</div>

<!-- FILE REFERENSI DARI MANAGER -->
<?php if (!empty($task['manager_file'])): ?>
<div class="so-card mt-3">
<div class="so-card-body">
    <div style="font-size:13px;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
        📋 <span>File Referensi dari Manager</span>
    </div>
    <a href="/uploads/tasks/<?= esc($task['manager_file']) ?>" target="_blank" class="file-card-item manager-file">
        <div class="file-card-icon"><?= fileIcon($task['manager_file']) ?></div>
        <div class="file-card-info">
            <div class="file-card-name"><?= esc($task['manager_file']) ?></div>
            <div class="file-card-meta">Dibuat bersama task · Klik untuk buka</div>
        </div>
        <div class="file-card-arrow">↗</div>
    </a>
</div>
</div>
<?php endif; ?>

<!-- FILE HASIL KERJA KARYAWAN -->
<div class="so-card mt-3">
<div class="so-card-body">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div style="font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;">
            📁 <span>File Hasil Kerja</span>
            <?php if (!empty($files)): ?>
            <span class="file-count-badge"><?= count($files) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($files)): ?>
    <div class="file-list">
    <?php foreach ($files as $f): ?>
    <a href="/uploads/tasks/<?= esc($f['file_name']) ?>" target="_blank" class="file-card-item">
        <div class="file-card-icon"><?= fileIcon($f['original_name']) ?></div>
        <div class="file-card-info">
            <div class="file-card-name"><?= esc($f['original_name']) ?></div>
            <div class="file-card-meta">Diupload <?= date('d M Y, H:i', strtotime($f['created_at'])) ?></div>
        </div>
        <div class="file-card-arrow">↗</div>
    </a>
    <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="file-empty">
        <div style="font-size:32px;margin-bottom:8px;">📭</div>
        <div>Belum ada file yang diupload</div>
    </div>
    <?php endif; ?>
</div>
</div>

</div>

<!-- ===================== SIDE ===================== -->
<div class="col-lg-4">

<!-- UPDATE STATUS (KARYAWAN ONLY) -->
<?php if (session()->get('role') === 'karyawan'): ?>
<div class="so-card mb-3">
<div class="so-card-body">
    <h6 style="font-weight:700;margin-bottom:10px;">Update Status</h6>
    <form action="/task/update-status/<?= $task['id'] ?>" method="POST">
    <?= csrf_field() ?>
    <select name="status" class="so-select mb-2">
        <option value="todo"        <?= $task['status']==='todo'        ?'selected':'' ?>>To Do</option>
        <option value="on_progress" <?= $task['status']==='on_progress' ?'selected':'' ?>>In Progress</option>
        <option value="done"        <?= $task['status']==='done'        ?'selected':'' ?>>Done</option>
    </select>
    <button class="btn-so-primary w-100">Update Status</button>
    </form>
</div>
</div>
<?php endif; ?>

<!-- UPLOAD FILE (KARYAWAN ONLY) -->
<?php if (session()->get('role') === 'karyawan'): ?>
<div class="so-card mb-3">
<div class="so-card-body">
    <h6 style="font-weight:700;margin-bottom:10px;">Upload File</h6>
    <form action="/task/upload/<?= $task['id'] ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="file-upload-area" id="fileUploadArea">
        <input type="file" name="file" id="uploadFile"
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
            onchange="handleFileChange(this)" style="display:none;" required>
        <div class="file-upload-inner" onclick="document.getElementById('uploadFile').click()">
            <div style="font-size:22px;margin-bottom:4px;">⬆️</div>
            <div id="fileLabel" style="font-size:12px;font-weight:600;color:var(--text);">Pilih file</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">PDF, JPG, PNG, DOC, XLS</div>
        </div>
    </div>
    <button class="btn-so-primary w-100 mt-2">Upload</button>
    </form>
</div>
</div>
<?php endif; ?>

</div>
</div>

<style>
.file-card-item { display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;border:1px solid #eef1f6;background:#fafbff;text-decoration:none;color:var(--text);transition:all .2s;margin-bottom:8px; }
.file-card-item:last-child { margin-bottom:0; }
.file-card-item:hover { border-color:var(--primary);background:#f0f6ff;transform:translateX(4px); }
.file-card-item.manager-file { background:#fffbf0;border-color:#fde68a; }
.file-card-item.manager-file:hover { background:#fef9e7;border-color:#f59e0b; }
.file-card-icon { font-size:22px;flex-shrink:0; }
.file-card-info { flex:1;min-width:0; }
.file-card-name { font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.file-card-meta { font-size:11px;color:var(--text-muted);margin-top:2px; }
.file-card-arrow { font-size:14px;color:var(--text-muted);flex-shrink:0; }
.file-empty { text-align:center;padding:28px 0;color:var(--text-muted);font-size:13px; }
.file-count-badge { background:var(--primary);color:white;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px; }
.file-upload-area { border:2px dashed var(--border);border-radius:10px;transition:.2s;cursor:pointer; }
.file-upload-area:hover { border-color:var(--primary);background:#f0f6ff; }
.file-upload-inner { padding:14px;text-align:center; }
.file-upload-area.has-file { border-style:solid;border-color:#10b981;background:#f0fdf4; }
</style>

<script>
function handleFileChange(input) {
    const area  = document.getElementById('fileUploadArea');
    const label = document.getElementById('fileLabel');
    if (input.files && input.files[0]) {
        label.innerText = '✅ ' + input.files[0].name;
        area.classList.add('has-file');
    } else {
        label.innerText = 'Pilih file';
        area.classList.remove('has-file');
    }
}
</script>

<?= $this->endSection() ?>