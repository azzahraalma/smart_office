<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <h4 style="font-weight:800;margin-bottom:4px;">Buat Task Baru</h4>
    <div style="font-size:13px;color:var(--text-muted);">Isi detail tugas untuk tim kamu</div>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="so-alert error mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-4">

<div class="col-lg-7">
<div class="so-card">
<div class="so-card-body">

<form action="/task/store" method="POST" enctype="multipart/form-data">
<?= csrf_field() ?>

<!-- JUDUL -->
<div class="form-group mb-3">
    <label class="so-label">Judul Task *</label>
    <input type="text" name="judul" class="so-input"
        placeholder="Contoh: Buat laporan bulanan"
        value="<?= old('judul') ?>"
        oninput="updatePreview()" required>
</div>

<!-- DESKRIPSI -->
<div class="form-group mb-3">
    <label class="so-label">Deskripsi</label>
    <textarea name="deskripsi" class="so-input" rows="4"
        placeholder="Jelaskan detail task..."
        oninput="updatePreview()"><?= old('deskripsi') ?></textarea>
</div>

<!-- PRIORITAS + DEADLINE -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="so-label">Prioritas *</label>
        <select name="prioritas" class="so-select" onchange="updatePreview()" required>
            <option value="">Pilih prioritas</option>
            <option value="rendah">🟢 Rendah</option>
            <option value="sedang">🟡 Sedang</option>
            <option value="tinggi">🔴 Tinggi</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="so-label">Deadline</label>
        <!-- Tanggal + Jam dalam 1 baris -->
        <div style="display:flex;gap:8px;">
            <input type="date" name="deadline_date" id="deadlineDate"
                class="so-input" style="flex:1.4;"
                onchange="updatePreview()">
            <input type="time" name="deadline_time" id="deadlineTime"
                class="so-input" style="flex:1;"
                value="17:00"
                onchange="updatePreview()">
        </div>
    </div>
</div>

<!-- ASSIGN KE KARYAWAN -->
<?php if (session()->get('role') === 'manager'): ?>
<div class="form-group mb-3">
    <label class="so-label">Assign ke Karyawan</label>
    <select name="assigned_to" class="so-select">
        <option value="">-- Pilih karyawan --</option>
        <?php foreach ($karyawan as $k): ?>
        <option value="<?= $k['id'] ?>"><?= esc($k['nama']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<!-- FILE REFERENSI -->
<div class="form-group mb-3">
    <label class="so-label">
        File Referensi
        <span style="color:var(--text-muted);font-weight:400;">(opsional)</span>
    </label>
    <div class="c-file-area" id="fileArea" onclick="document.getElementById('managerFile').click()">
        <input type="file" name="manager_file" id="managerFile"
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
            onchange="handleFile(this)" style="display:none;">
        <div style="font-size:28px;margin-bottom:6px;">📎</div>
        <div id="fileLabel" style="font-size:13px;font-weight:600;color:var(--text);">
            Klik untuk upload file referensi
        </div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">PDF, JPG, PNG, DOC, XLS</div>
    </div>
</div>

<hr style="margin:20px 0;">

<div style="display:flex;gap:10px;">
    <button type="submit" class="btn-so-primary">upload task</button>
    <a href="/task" class="btn-so-outline">Batal</a>
</div>

</form>
</div>
</div>
</div>

<!-- PREVIEW -->
<div class="col-lg-5">
<div class="so-card" style="position:sticky;top:80px;">
<div class="so-card-body">
    <div style="font-weight:700;margin-bottom:14px;">Preview</div>
    <div class="c-preview-card">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
            <span id="p-prio" class="so-badge low">Prioritas</span>
            <span class="so-badge todo">To Do</span>
        </div>
        <div id="p-judul" style="font-size:15px;font-weight:700;margin-bottom:6px;">Judul task</div>
        <div id="p-desc" style="font-size:13px;color:var(--text-muted);margin-bottom:10px;">Deskripsi task</div>
        <div id="p-deadline" style="font-size:12px;color:var(--text-muted);">No deadline</div>
    </div>
</div>
</div>
</div>

</div>

<style>
.c-file-area {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.c-file-area:hover { border-color: var(--primary); background: #f0f6ff; }
.c-file-area.has-file { border-style:solid; border-color:#10b981; background:#f0fdf4; }
.c-preview-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px;
    background: #fafbff;
}
</style>

<script>
function handleFile(input) {
    const area  = document.getElementById('fileArea');
    const label = document.getElementById('fileLabel');
    if (input.files && input.files[0]) {
        label.innerText = '✅ ' + input.files[0].name;
        area.classList.add('has-file');
    } else {
        label.innerText = 'Klik untuk upload file referensi';
        area.classList.remove('has-file');
    }
}

function updatePreview() {
    const judul = document.querySelector('[name=judul]').value;
    const desc  = document.querySelector('[name=deskripsi]').value;
    const prio  = document.querySelector('[name=prioritas]').value;
    const date  = document.getElementById('deadlineDate').value;
    const time  = document.getElementById('deadlineTime').value;

    document.getElementById('p-judul').innerText = judul || 'Judul task';
    document.getElementById('p-desc').innerText  = desc  || 'Deskripsi task';

    const prioEl = document.getElementById('p-prio');
    const map = {
        rendah: ['low',    'Rendah'],
        sedang: ['medium', 'Sedang'],
        tinggi: ['high',   'Tinggi'],
    };
    if (map[prio]) {
        prioEl.className = 'so-badge ' + map[prio][0];
        prioEl.innerText = map[prio][1];
    } else {
        prioEl.className = 'so-badge todo';
        prioEl.innerText = 'Prioritas';
    }

    if (date) {
        const d    = new Date(date + 'T' + (time || '00:00'));
        const days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        const mons = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const label = `📅 ${days[d.getDay()]}, ${d.getDate()} ${mons[d.getMonth()]} ${d.getFullYear()}` +
                      (time ? ` · ⏰ ${time}` : '');
        document.getElementById('p-deadline').innerText = label;
    } else {
        document.getElementById('p-deadline').innerText = 'No deadline';
    }
}
</script>

<?= $this->endSection() ?>