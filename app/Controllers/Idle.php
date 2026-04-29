<?php

namespace App\Controllers;

use App\Models\IdleLogModel;
use App\Models\AbsensiModel; // sesuaikan dengan nama model absensi kamu
use App\Helpers\NotificationHelper;

class Idle extends BaseController
{
    protected $idleModel;
    protected $absensiModel;

    const IDLE_THRESHOLD_MENIT = 5;

    public function __construct()
    {
        $this->idleModel    = new IdleLogModel();
        $this->absensiModel = new AbsensiModel(); // sesuaikan nama model absensi kamu
    }

    /**
     * Cek apakah karyawan sedang absen normal hari ini.
     * Return false kalau: belum absen, status izin/sakit/alpha,
     * approval masih pending, atau sudah absen pulang.
     */
    private function isAbsenNormal(int $userId): bool
    {
        $absen = $this->absensiModel
            ->where('user_id', $userId)
            ->where('DATE(tanggal)', date('Y-m-d')) // sesuaikan nama kolom tanggal kamu
            ->first();

        if (!$absen) return false;

        $statusTidakMasuk = in_array($absen['status'] ?? '', ['izin', 'sakit', 'alpha']);
        $approvalPending  = ($absen['approval_status'] ?? '') === 'pending';
        $sudahPulang      = !empty($absen['jam_keluar']);

        if ($statusTidakMasuk || $approvalPending || $sudahPulang) return false;

        return true;
    }

    public function start()
    {
        // Manager tidak perlu idle detection
        if (session()->get('role') === 'manager') {
            return $this->response->setJSON(['status' => true]);
        }

        $userId = session()->get('user_id') ?? 1;

        // FIX: skip kalau karyawan tidak sedang absen normal
        if (!$this->isAbsenNormal($userId)) {
            return $this->response->setJSON(['status' => true]);
        }

        $existing = $this->idleModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        if (!$existing) {
            $this->idleModel->insert([
                'user_id' => $userId,
                'mulai'   => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON(['status' => true]);
    }

    public function stop()
    {
        // Manager tidak perlu idle detection
        if (session()->get('role') === 'manager') {
            return $this->response->setJSON(['status' => true]);
        }

        $userId = session()->get('user_id') ?? 1;

        // FIX: skip kalau karyawan tidak sedang absen normal
        // Tapi tetap cek kalau ada idle yang belum ditutup (bisa terjadi kalau
        // status berubah setelah idle dimulai), biar datanya bersih
        $idle = $this->idleModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        if ($idle) {
            $mulai       = strtotime($idle['mulai']);
            $selesai     = time();
            $durasiMenit = (int) floor(($selesai - $mulai) / 60);

            $this->idleModel->update($idle['id'], [
                'selesai' => date('Y-m-d H:i:s'),
                'durasi'  => $durasiMenit,
            ]);

            // Notifikasi hanya dikirim kalau memang sedang absen normal
            if ($this->isAbsenNormal($userId) && $durasiMenit >= self::IDLE_THRESHOLD_MENIT) {
                NotificationHelper::idleTerdeteksi($userId, $durasiMenit);
            }
        }

        return $this->response->setJSON(['status' => true]);
    }

    public function status()
    {
        // Manager tidak perlu idle detection
        if (session()->get('role') === 'manager') {
            return $this->response->setJSON([
                'status'      => true,
                'sedang_idle' => false,
            ]);
        }

        $userId = session()->get('user_id') ?? 1;

        // FIX: kalau tidak absen normal, anggap tidak idle
        if (!$this->isAbsenNormal($userId)) {
            return $this->response->setJSON([
                'status'      => true,
                'sedang_idle' => false,
            ]);
        }

        $idle = $this->idleModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        if ($idle) {
            $durasiMenit = (int) floor((time() - strtotime($idle['mulai'])) / 60);
            return $this->response->setJSON([
                'status'       => true,
                'sedang_idle'  => true,
                'durasi_menit' => $durasiMenit,
            ]);
        }

        return $this->response->setJSON([
            'status'      => true,
            'sedang_idle' => false,
        ]);
    }
}