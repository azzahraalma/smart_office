<?php

namespace App\Helpers;

use App\Models\NotificationModel;

class NotificationHelper
{
    /**
     * Kirim notifikasi ke satu atau banyak user
     */
    public static function send($userIds, string $judul, string $pesan, string $tipe = 'info'): void
    {
        $model = new NotificationModel();

        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        foreach ($userIds as $uid) {
            if (!$uid) continue;
            $model->insert([
                'user_id' => $uid,
                'judul'   => $judul,
                'pesan'   => $pesan,
                'tipe'    => $tipe,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Ambil semua user_id dengan role manager
     */
    public static function getManagerIds(): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('users')
            ->like('role', 'manager') // 🔥 bukan where lagi
            ->get()
            ->getResultArray();

        return array_column($rows, 'id');
    }
    /**
     * Ambil nama user berdasarkan ID
     */
    public static function getUserName(int $userId): string
    {
        $db   = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();
        return $user['nama'] ?? $user['name'] ?? ('User #' . $userId);
    }

    // ─────────────────────────────────────────────
    //  TASK
    // ─────────────────────────────────────────────

    public static function taskBaru(int $assignedTo, string $judulTask, ?string $deadline, string $prioritas): void
    {
        $deadlineLabel = $deadline ? date('d M Y, H:i', strtotime($deadline)) : 'Tidak ada deadline';

        self::send(
            $assignedTo,
            '📋 Task Baru Untukmu',
            "Kamu mendapat task baru: \"{$judulTask}\". Deadline: {$deadlineLabel}. Prioritas: {$prioritas}.",
            'task'
        );
    }

    public static function taskProgressUpdate(int $karyawanId, string $judulTask, string $statusBaru): void
    {
        $nama       = self::getUserName($karyawanId);
        $managerIds = self::getManagerIds();

        $labelStatus = match ($statusBaru) {
            'on_progress' => 'Sedang Dikerjakan',
            'done'        => 'Selesai ✅',
            default       => ucfirst($statusBaru),
        };

        self::send(
            $managerIds,
            '🔄 Progress Task Diperbarui',
            "{$nama} mengubah status task \"{$judulTask}\" menjadi {$labelStatus}.",
            'task'
        );
    }

    public static function taskTelat(int $assignedTo, string $judulTask, string $deadline): void
    {
        $deadlineLabel = date('d M Y, H:i', strtotime($deadline));
        $managerIds    = self::getManagerIds();
        $namaKaryawan  = self::getUserName($assignedTo);

        self::send(
            $assignedTo,
            '🚨 Task Melewati Deadline!',
            "Task \"{$judulTask}\" sudah melewati deadline ({$deadlineLabel}) dan belum selesai. Segera selesaikan!",
            'task'
        );

        self::send(
            $managerIds,
            '🚨 Task Telat Dikerjakan',
            "Task \"{$judulTask}\" yang diassign ke {$namaKaryawan} sudah melewati deadline ({$deadlineLabel}) dan belum selesai.",
            'task'
        );
    }

    // ─────────────────────────────────────────────
    //  ABSENSI
    // ─────────────────────────────────────────────

    public static function absenMasuk(int $userId, string $jamMasuk, string $status): void
    {
        $nama        = self::getUserName($userId);
        $labelStatus = $status === 'telat' ? '⚠️ Telat' : '✅ Tepat Waktu';
        $managerIds  = self::getManagerIds();

        self::send(
            $userId,
            '✅ Absen Masuk Tercatat',
            "Kamu berhasil absen masuk hari ini pukul {$jamMasuk}. Status: {$labelStatus}.",
            'absensi'
        );

        self::send(
            $managerIds,
            '📍 Absen Masuk Karyawan',
            "{$nama} absen masuk pukul {$jamMasuk}. Status: {$labelStatus}.",
            'absensi'
        );
    }

    public static function absenPulang(int $userId, string $jamKeluar): void
    {
        $nama       = self::getUserName($userId);
        $managerIds = self::getManagerIds();

        self::send(
            $userId,
            '🏠 Absen Pulang Tercatat',
            "Kamu berhasil absen pulang hari ini pukul {$jamKeluar}. Sampai jumpa besok!",
            'absensi'
        );

        self::send(
            $managerIds,
            '📍 Absen Pulang Karyawan',
            "{$nama} absen pulang pukul {$jamKeluar}.",
            'absensi'
        );
    }

    // ─────────────────────────────────────────────
    //  BREAK
    // ─────────────────────────────────────────────

    public static function breakMulai(int $userId, string $jamMulai): void
    {
        $nama       = self::getUserName($userId);
        $managerIds = self::getManagerIds();

        self::send(
            $userId,
            '☕ Break Dimulai',
            "Break kamu dimulai pukul {$jamMulai}. Jangan lupa balik ya!",
            'break'
        );

        self::send(
            $managerIds,
            '☕ Karyawan Mulai Break',
            "{$nama} mulai break pukul {$jamMulai}.",
            'break'
        );
    }

    public static function breakSelesai(int $userId, string $jamSelesai, int $durasiMenit): void
    {
        $nama       = self::getUserName($userId);
        $managerIds = self::getManagerIds();

        self::send(
            $userId,
            '💼 Break Selesai',
            "Break kamu selesai pukul {$jamSelesai}. Durasi: {$durasiMenit} menit. Semangat bekerja!",
            'break'
        );

        self::send(
            $managerIds,
            '💼 Karyawan Selesai Break',
            "{$nama} selesai break pukul {$jamSelesai}. Durasi: {$durasiMenit} menit.",
            'break'
        );
    }

    // ─────────────────────────────────────────────
    //  IDLE
    // ─────────────────────────────────────────────

    public static function idleTerdeteksi(int $userId, int $durasiMenit): void
    {
        $nama       = self::getUserName($userId);
        $managerIds = self::getManagerIds();

        self::send(
            $userId,
            '⚠️ Kamu Terdeteksi Idle',
            "Kamu tidak aktif selama {$durasiMenit} menit. Pastikan kamu tetap produktif ya!",
            'idle'
        );

        self::send(
            $managerIds,
            '🔴 Idle Terdeteksi',
            "{$nama} tidak aktif selama {$durasiMenit} menit.",
            'idle'
        );
    }

    // ─────────────────────────────────────────────
    //  IZIN  ← semua pakai self::send() sekarang
    // ─────────────────────────────────────────────

    public static function izinRequest(int $userId, string $jenis, string $ket): void
    {
        $nama       = self::getUserName($userId);
        $managerIds = self::getManagerIds();

        // Notif ke semua manager
        self::send(
            $managerIds,
            '📋 Pengajuan ' . ucfirst($jenis),
            "{$nama} mengajukan {$jenis}: {$ket}",
            'absensi'
        );

        // Notif ke karyawan sendiri
        self::send(
            $userId,
            '📨 Pengajuan Terkirim',
            "Pengajuan {$jenis} kamu sudah dikirim ke manager. Tunggu konfirmasi ya!",
            'absensi'
        );
    }

    public static function izinApproved(int $userId, string $jenis): void
    {
        self::send(
            $userId,
            '✅ Pengajuan Disetujui',
            "Pengajuan {$jenis} kamu telah disetujui oleh manager.",
            'absensi'
        );
    }

    public static function izinRejected(int $userId, string $jenis): void
    {
        self::send(
            $userId,
            '❌ Pengajuan Ditolak',
            "Maaf, pengajuan {$jenis} kamu ditolak oleh manager.",
            'absensi'
        );
    }
}