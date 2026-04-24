<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$nama     = session()->get('nama') ?? 'Karyawan';
$initials = strtoupper(substr($nama, 0, 2));
$jam      = (int) date('H');
$greeting = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$today    = date('l, d F Y');

// Data from controller
$absenHariIni   = $absenHariIni   ?? null;
$breakLogs      = $breakLogs      ?? [];
$idleLogs       = $idleLogs       ?? [];
$tasks = $taskList ?? [];
$totalBreakMnt  = $totalBreakMnt  ?? 0;
$sisaJamKerja   = $sisaJamKerja   ?? null;
$taskSelesai    = $taskSelesai    ?? 0;
$taskTotal      = $taskTotal      ?? 0;
$isBreak        = $isBreak        ?? false;
$isIdle         = $isIdle         ?? false;
$overtime       = $overtime       ?? false;

$jamMasuk   = $absenHariIni ? date('H:i', strtotime($absenHariIni['jam_masuk'])) : null;
$totalKerja = '-';
if ($jamMasuk) {
    $diff = (time() - strtotime($absenHariIni['jam_masuk']));
    $totalKerja = floor($diff/3600).'j '.floor(($diff%3600)/60).'m';
}
?>

<!-- Greeting Row -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="dash-avatar"><?= $initials ?></div>
        <div>
            <div class="dash-greeting"><?= $greeting ?>, <?= esc(explode(' ', $nama)[0]) ?> 👋</div>
            <div class="dash-sub">Semangat kerja hari ini!</div>
        </div>
    </div>
    <div class="dash-date-pill">
        <span class="material-icons-round" style="font-size:16px;">calendar_today</span>
        <?= $today ?>
    </div>
</div>

<!-- Work Status Banner -->
<div class="work-status-banner mb-4 <?= $absenHariIni ? ($isBreak ? 'break' : ($overtime ? 'overtime' : 'working')) : 'absent' ?>">
    <div class="d-flex align-items-center gap-3">
        <div class="work-status-dot"></div>
        <div>
            <div class="work-status-label">
                <?php if (!$absenHariIni): ?>
                    Belum absen masuk hari ini
                <?php elseif ($isBreak): ?>
                    Sedang break
                <?php elseif ($overtime): ?>
                    ⚠️ Overtime — sudah melebihi jam kerja
                <?php else: ?>
                    Sedang bekerja
                <?php endif; ?>
            </div>
            <?php if ($absenHariIni && $jamMasuk): ?>
            <div style="font-size:12px;opacity:.75;">Absen masuk pukul <?= $jamMasuk ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="live-clock" id="liveClock"><?= date('H:i:s') ?></div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card primary">
            <div class="stat-card-icon primary">
                <span class="material-icons-round">schedule</span>
            </div>
            <div class="stat-card-label">Total Kerja Hari Ini</div>
            <div class="stat-card-value"><?= $absenHariIni ? $totalKerja : '-' ?></div>
            <div class="stat-card-sub"><?= $absenHariIni ? 'Mulai '.$jamMasuk.' pagi' : 'Belum absen' ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="stat-card-icon success">
                <span class="material-icons-round">hourglass_bottom</span>
            </div>
            <div class="stat-card-label">Sisa Jam Kerja</div>
            <div class="stat-card-value" style="color:var(--success)">
                <?php if ($absenHariIni && !$absenHariIni['jam_keluar']): ?>
                    <?php
                        $batas = strtotime(date('Y-m-d').' 17:00:00');
                        $sisa  = max(0, $batas - time());
                        echo floor($sisa/3600).'j '.floor(($sisa%3600)/60).'m';
                    ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
            <div class="stat-card-sub">Selesai pukul 17:00</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-card-icon warning">
                <span class="material-icons-round">free_breakfast</span>
            </div>
            <div class="stat-card-label">Total Break Hari Ini</div>
            <div class="stat-card-value" style="color:var(--warning)"><?= $totalBreakMnt ?>m</div>
            <div class="stat-card-sub"><?= count($breakLogs) ?>x break diambil</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card accent">
            <div class="stat-card-icon accent">
                <span class="material-icons-round">task_alt</span>
            </div>
            <div class="stat-card-label">Task Selesai</div>
            <div class="stat-card-value" style="color:var(--accent)"><?= $taskSelesai ?>/<?= $taskTotal ?></div>
            <div class="stat-card-sub" style="color:<?= ($taskTotal - $taskSelesai) > 0 ? 'var(--danger)' : 'var(--success)' ?>">
                <?= ($taskTotal - $taskSelesai) > 0 ? ($taskTotal - $taskSelesai).' task belum selesai' : 'Semua task selesai 🎉' ?>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Timeline + Task List -->
<div class="row g-4">
    <!-- Aktivitas Hari Ini -->
    <div class="col-lg-6">
        <div class="so-card h-100">
            <div class="so-card-header">
                <div class="so-card-title">Aktivitas hari ini</div>
                <span style="font-size:12px;font-weight:600;color:var(--primary);cursor:pointer;">Timeline</span>
            </div>
            <div class="so-card-body">
                <div class="timeline-list">
                    <?php if ($absenHariIni): ?>
                    <div class="timeline-item success">
                        <div class="timeline-icon"><span class="material-icons-round">check</span></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Absen masuk</div>
                            <div class="timeline-sub"><?= $jamMasuk ?> — GPS & IP terverifikasi</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($breakLogs as $bl): ?>
                    <div class="timeline-item warning">
                        <div class="timeline-icon" style="background:#fef3c7;color:#d97706;"><span class="material-icons-round">free_breakfast</span></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Break <?= $bl['durasi'] ?? '?' ?> menit</div>
                            <div class="timeline-sub"><?= date('H:i', strtotime($bl['mulai'])) ?> — <?= $bl['selesai'] ? date('H:i', strtotime($bl['selesai'])) : 'sedang berlangsung' ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php foreach ($idleLogs as $il): ?>
                    <div class="timeline-item danger">
                        <div class="timeline-icon" style="background:#fee2e2;color:#dc2626;"><span class="material-icons-round">warning_amber</span></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Idle terdeteksi</div>
                            <div class="timeline-sub"><?= date('H:i', strtotime($il['mulai'])) ?> — selama <?= $il['durasi'] ?? '?' ?> menit</div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($absenHariIni && !$absenHariIni['jam_keluar']): ?>
                    <div class="timeline-item active">
                        <div class="timeline-icon" style="background:var(--primary-light);color:var(--primary);animation:pulse 2s infinite;"><span class="material-icons-round">work</span></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Sedang bekerja...</div>
                            <div class="timeline-sub">Sekarang</div>
                        </div>
                    </div>
                    <?php elseif ($absenHariIni && $absenHariIni['jam_keluar']): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon"><span class="material-icons-round">logout</span></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Absen pulang</div>
                            <div class="timeline-sub"><?= date('H:i', strtotime($absenHariIni['jam_keluar'])) ?></div>
                        </div>
                    </div>
                    <?php elseif (!$absenHariIni): ?>
                    <div style="text-align:center;padding:24px 0;color:var(--text-muted);font-size:13px;">
                        <span class="material-icons-round" style="display:block;font-size:32px;margin-bottom:8px;color:var(--border);">calendar_today</span>
                        Belum ada aktivitas hari ini
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Hari Ini -->
    <div class="col-lg-6">
        <div class="so-card h-100">
            <div class="so-card-header">
                <div class="so-card-title">Task hari ini</div>
                <span class="so-badge todo"><?= $taskTotal ?> task</span>
            </div>
            <div class="so-card-body" style="padding:12px 0 16px;">
                <?php if (empty($tasks)): ?>
                <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;">
                    <span class="material-icons-round" style="display:block;font-size:32px;margin-bottom:8px;color:var(--border);">inbox</span>
                    Tidak ada task untuk hari ini
                </div>
                <?php else: ?>
                <div class="task-list-scroll">
                <?php foreach (array_slice($tasks, 0, 6) as $task): ?>
                <?php
$tc = match($task['status']) { 'todo'=>'todo','on_progress'=>'in-progress','done'=>'done',default=>'todo'};
$tl = match($task['status']) { 'todo'=>'Todo','on_progress'=>'On progress','done'=>'Selesai',default=>''};
$tdOverdue = $task['deadline'] && $task['status'] !== 'done' && strtotime($task['deadline']) < time();
$tdLabel = $task['deadline'] ? (date('Y-m-d',strtotime($task['deadline']))==date('Y-m-d') ? 'Due: hari ini' : (date('Y-m-d',strtotime($task['deadline']))==date('Y-m-d',strtotime('-1 day')) ? 'Due: kemarin' : 'Due: '.date('d M',strtotime($task['deadline'])))) : '';
?>
                <div class="dash-task-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="dash-task-check <?= $task['status'] === 'done' ? 'checked' : '' ?>" onclick="quickDone(<?= $task['id'] ?>, this)">
                            <?php if ($task['status'] === 'done'): ?><span class="material-icons-round" style="font-size:14px;">check</span><?php endif; ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="dash-task-title <?= $task['status'] === 'done' ? 'done' : '' ?>"><?= esc($task['judul']) ?></div>
                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                <span class="so-badge <?= $tc ?>" style="font-size:10px;padding:2px 8px;"><?= $tl ?></span>
                                <?php if ($tdLabel): ?>
                                <span style="font-size:11px;color:<?= $tdOverdue ? 'var(--danger)' : 'var(--text-muted)' ?>;"><?= $tdLabel ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (count($tasks) > 6): ?>
                <div style="text-align:center;padding:10px;">
                    <a href="/task" style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;">Lihat semua +<?= count($tasks)-6 ?> task →</a>
                </div>
                <?php endif; ?>
                </div>
                <?php endif; ?>

                <div style="padding:0 22px;margin-top:12px;">
                    <a href="/task" class="btn-so-outline" style="width:100%;justify-content:center;font-size:13px;">
                        <span class="material-icons-round" style="font-size:16px;">list</span>
                        Lihat Semua Task
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overtime Alert Modal -->
<?php if ($overtime): ?>
<div id="overtimeModal" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
    <div style="background:white;border-radius:16px;padding:32px;max-width:400px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <span class="material-icons-round" style="color:var(--danger);font-size:28px;">warning</span>
        </div>
        <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:8px;">Peringatan Overtime!</div>
        <div style="font-size:14px;color:var(--text-muted);line-height:1.6;margin-bottom:24px;">
            Kamu sudah bekerja melebihi jam kerja normal (17:00). Pertimbangkan untuk segera absen pulang.
        </div>
        <button onclick="document.getElementById('overtimeModal').style.display='none'"
            class="btn-so-primary" style="width:100%;justify-content:center;">
            Oke, saya mengerti
        </button>
    </div>
</div>
<?php endif; ?>

<style>
.dash-avatar      { width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:white; }
.dash-greeting    { font-size:20px;font-weight:800;color:var(--text); }
.dash-sub         { font-size:13px;color:var(--text-muted); }
.dash-date-pill   { display:flex;align-items:center;gap:6px;padding:8px 16px;background:white;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-weight:600;color:var(--text-muted); }

.work-status-banner { display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-radius:var(--radius);border:1.5px solid; }
.work-status-banner.working { background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-color:#6ee7b7; }
.work-status-banner.break   { background:linear-gradient(135deg,#fef3c7,#fde68a);border-color:#fcd34d; }
.work-status-banner.overtime{ background:linear-gradient(135deg,#fee2e2,#fca5a5);border-color:#f87171; }
.work-status-banner.absent  { background:linear-gradient(135deg,var(--primary-light),#dbeafe);border-color:var(--primary); }
.work-status-dot  { width:10px;height:10px;border-radius:50%;background:currentColor;animation:pulse 2s infinite; }
.work-status-banner.working .work-status-dot  { color:#10b981; }
.work-status-banner.break .work-status-dot    { color:#f59e0b; }
.work-status-banner.overtime .work-status-dot { color:#ef4444; }
.work-status-banner.absent .work-status-dot   { color:var(--primary); }
.work-status-label { font-size:14px;font-weight:700;color:var(--text); }
.live-clock        { font-size:28px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums; }

/* Timeline */
.timeline-list    { display:flex;flex-direction:column;gap:0; }
.timeline-item    { display:flex;align-items:flex-start;gap:12px;padding:12px 0;position:relative; }
.timeline-item:not(:last-child)::after { content:'';position:absolute;left:17px;top:44px;bottom:0;width:2px;background:var(--border); }
.timeline-icon    { width:36px;height:36px;border-radius:50%;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.timeline-icon .material-icons-round { font-size:16px; }
.timeline-title   { font-size:13px;font-weight:700;color:var(--text); }
.timeline-sub     { font-size:11px;color:var(--text-muted);margin-top:2px; }

/* Task List */
.task-list-scroll { max-height:280px;overflow-y:auto;padding:0 22px; }
.dash-task-item   { padding:10px 0;border-bottom:1px solid var(--border); }
.dash-task-item:last-child { border-bottom:none; }
.dash-task-check  { width:20px;height:20px;border-radius:6px;border:2px solid var(--border);flex-shrink:0;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;margin-top:1px; }
.dash-task-check.checked   { background:var(--success);border-color:var(--success);color:white; }
.dash-task-title  { font-size:13px;font-weight:600;color:var(--text);line-height:1.4; }
.dash-task-title.done { text-decoration:line-through;color:var(--text-muted); }
</style>

<script>
// Live Clock
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('liveClock');
    if (el) el.textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);

// Quick done toggle
function quickDone(taskId, el) {
    const isDone = el.classList.contains('checked');
    const newStatus = isDone ? 'todo' : 'done';
    const fd = new FormData();
    fd.append('status', newStatus);
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    fetch(`/task/update-status/${taskId}`, { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            if (d.status) {
                el.classList.toggle('checked');
                const title = el.closest('.d-flex').querySelector('.dash-task-title');
                if (title) title.classList.toggle('done');
                if (!isDone) el.innerHTML = '<span class="material-icons-round" style="font-size:14px;">check</span>';
                else el.innerHTML = '';
            }
        });
}
</script>

<?= $this->endSection() ?>
