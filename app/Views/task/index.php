<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <h4 style="font-weight:800;margin-bottom:4px;">Manajemen Task</h4>
    <div style="font-size:13px;color:var(--text-muted);">Kelola dan pantau progress tugas</div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="so-alert success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="so-alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php
$totalTodo     = 0;
$totalProgress = 0;
$totalDone     = 0;
foreach ($tasks as $t) {
    if ($t['status'] === 'todo')            $totalTodo++;
    elseif ($t['status'] === 'on_progress') $totalProgress++;
    elseif ($t['status'] === 'done')        $totalDone++;
}
$total = count($tasks);
?>

<!-- statistik -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="i-stat-card" style="background:linear-gradient(135deg,#3b82f6,#60a5fa);">
            <div class="i-stat-value"><?= $totalTodo ?></div>
            <div class="i-stat-label">To Do</div>
        </div>
    </div>
    <div class="col-4">
        <div class="i-stat-card" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
            <div class="i-stat-value"><?= $totalProgress ?></div>
            <div class="i-stat-label">In Progress</div>
        </div>
    </div>
    <div class="col-4">
        <div class="i-stat-card" style="background:linear-gradient(135deg,#10b981,#34d399);">
            <div class="i-stat-value"><?= $totalDone ?></div>
            <div class="i-stat-label">Selesai</div>
        </div>
    </div>
</div>

<!-- filter -->
<div class="i-filter-wrap">
    <button class="i-filter-btn active" data-filter="all">Semua (<?= $total ?>)</button>
    <button class="i-filter-btn" data-filter="todo">To Do</button>
    <button class="i-filter-btn" data-filter="on_progress">Progress</button>
    <button class="i-filter-btn" data-filter="done">Done</button>
</div>

<!-- task -->
<div class="row g-3" id="taskGrid">

<?php if (empty($tasks)): ?>
<div class="col-12">
    <div style="text-align:center;padding:60px;color:var(--text-muted);">
        <div style="font-size:40px;margin-bottom:10px;">📋</div>
        <div>Belum ada task</div>
    </div>
</div>
<?php endif; ?>

<?php foreach ($tasks as $task): ?>
<?php
$statusClass = match($task['status']) {
    'todo'        => 'todo',
    'on_progress' => 'in-progress',
    'done'        => 'done',
    default       => 'todo'
};
$prioClass = match($task['prioritas']) {
    'tinggi' => 'high',
    'sedang' => 'medium',
    'rendah' => 'low',
    default  => 'low'
};
$progress = match($task['status']) {
    'todo'        => 0,
    'on_progress' => 60,
    'done'        => 100,
    default       => 0
};
$isOverdue = $task['deadline']
    && strtotime($task['deadline']) < time()
    && $task['status'] !== 'done';
?>

<div class="col-md-6 col-xl-4 i-task-item" data-status="<?= $task['status'] ?>">
<div class="i-task-card" onclick="location.href='/task/detail/<?= $task['id'] ?>'">

    <!-- TOP BADGE -->
    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
        <div style="display:flex;gap:6px;align-items:center;">
            <span class="so-badge <?= $prioClass ?>"><?= ucfirst($task['prioritas']) ?></span>
            <?php if ($isOverdue): ?>
                <span class="so-badge" style="background:#fee2e2;color:#dc2626;font-size:10px;padding:2px 7px;">
                    ⚠️ Telat
                </span>
            <?php endif; ?>
        </div>
        <span class="so-badge <?= $statusClass ?>"><?= ucfirst(str_replace('_',' ',$task['status'])) ?></span>
    </div>

    <!-- JUDUL -->
    <div style="font-weight:700;font-size:15px;margin-bottom:6px;color:var(--text);">
        <?= esc($task['judul']) ?>
    </div>

    <!-- DESKRIPSI -->
    <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;line-height:1.5;">
        <?= esc(mb_substr($task['deskripsi'] ?? '', 0, 80)) ?>
        <?= mb_strlen($task['deskripsi'] ?? '') > 80 ? '...' : '' ?>
    </div>

    <!-- PROGRESS BAR -->
    <div style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:4px;">
            <span>Progress</span>
            <span style="font-weight:700;"><?= $progress ?>%</span>
        </div>
        <div style="height:6px;background:#eef2f7;border-radius:999px;overflow:hidden;">
            <div class="i-progress-fill i-status-<?= $task['status'] ?>"
                style="width:<?= $progress ?>%;height:100%;border-radius:999px;"></div>
        </div>
    </div>

    <!-- FOOTER -->
    <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;">
        <span style="<?= $isOverdue ? 'color:red;font-weight:600;' : 'color:var(--text-muted);' ?>">
            <?= $task['deadline'] ? '📅 ' . date('d M Y, H:i', strtotime($task['deadline'])) : '-' ?>
        </span>
        <span style="color:var(--primary);font-weight:600;">Detail →</span>
    </div>

</div>
</div>

<?php endforeach; ?>
</div>

<?php if (session()->get('role') === 'manager'): ?>
<div style="margin-top:30px;text-align:center;">
    <a href="/task/create" class="btn-so-primary" style="padding:12px 28px;border-radius:12px;text-decoration:none;">
        + Tambah Task Baru
    </a>
</div>
<?php endif; ?>

<style>
.i-stat-card { padding:20px;border-radius:16px;color:white;text-align:center;box-shadow:0 8px 20px rgba(0,0,0,0.08); }
.i-stat-value { font-size:26px;font-weight:800; }
.i-stat-label { font-size:12px;margin-top:2px; }

.i-filter-wrap { display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap; }
.i-filter-btn { padding:7px 16px;border-radius:10px;border:1px solid var(--border);background:white;font-size:13px;cursor:pointer;transition:.15s; }
.i-filter-btn.active, .i-filter-btn:hover { background:var(--primary);color:white;border-color:var(--primary); }

.i-task-card { background:white;padding:18px;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 4px 20px rgba(0,0,0,0.05);cursor:pointer;transition:transform .2s,box-shadow .2s;height:100%; }
.i-task-card:hover { transform:translateY(-5px);box-shadow:0 12px 30px rgba(0,0,0,0.1); }

.i-progress-fill { transition:width .4s ease; }
.i-status-todo        { background:linear-gradient(90deg,#3b82f6,#60a5fa); }
.i-status-on_progress { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.i-status-done        { background:linear-gradient(90deg,#10b981,#34d399); }
</style>

<script>
document.querySelectorAll('.i-filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.i-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const f = btn.dataset.filter;
        document.querySelectorAll('.i-task-item').forEach(item => {
            item.style.display = (f === 'all' || item.dataset.status === f) ? '' : 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>