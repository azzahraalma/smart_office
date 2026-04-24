<?php

namespace App\Controllers;

use App\Models\IdleLogModel;
use App\Helpers\NotificationHelper;

class Idle extends BaseController
{
    protected $idleModel;

    // Threshold idle (menit) sebelum notif dikirim ke manager
    const IDLE_THRESHOLD_MENIT = 5;

    public function __construct()
    {
        $this->idleModel = new IdleLogModel();
    }

    public function start()
    {
        $userId = session()->get('user_id') ?? 1;

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
        $userId = session()->get('user_id') ?? 1;

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

            // 🔔 TRIGGER NOTIFIKASI IDLE — hanya jika melebihi threshold
            if ($durasiMenit >= self::IDLE_THRESHOLD_MENIT) {
                NotificationHelper::idleTerdeteksi($userId, $durasiMenit);
            }
        }

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Endpoint opsional: cek apakah sedang idle (untuk polling frontend)
     */
    public function status()
    {
        $userId = session()->get('user_id') ?? 1;

        $idle = $this->idleModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        if ($idle) {
            $durasiMenit = (int) floor((time() - strtotime($idle['mulai'])) / 60);
            return $this->response->setJSON([
                'status'        => true,
                'sedang_idle'   => true,
                'durasi_menit'  => $durasiMenit,
            ]);
        }

        return $this->response->setJSON([
            'status'      => true,
            'sedang_idle' => false,
        ]);
    }
}