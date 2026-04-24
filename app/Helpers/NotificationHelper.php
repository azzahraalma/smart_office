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
            ->where('role', 'manager')
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

    /**
     * Notif ke karyawan saat dapat task baru
     */
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

    /**
     * Notif ke semua manager saat karyawan update status task
     */
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

    /**
     * Notif ke karyawan + manager saat task melewati deadline
     */
    public static function taskTelat(int $assignedTo, string $judulTask, string $deadline): void
    {
        $deadlineLabel = date('d M Y, H:i', strtotime($deadline));
        $managerIds    = self::getManagerIds();
        $namaKaryawan  = self::getUserName($assignedTo);

        // Notif ke karyawan
        self::send(
            $assignedTo,
            '🚨 Task Melewati Deadline!',
            "Task \"{$judulTask}\" sudah melewati deadline ({$deadlineLabel}) dan belum selesai. Segera selesaikan!",
            'task'
        );

        // Notif ke semua manager
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

    /**
     * Notif ke karyawan + manager saat absen masuk
     */
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

    /**
     * Notif ke karyawan + manager saat absen pulang
     */
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
}