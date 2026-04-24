<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BreakLogModel;
use App\Helpers\NotificationHelper;

class BreakController extends BaseController
{
    protected $breakModel;

    public function __construct()
    {
        $this->breakModel = new BreakLogModel();
    }

    public function mulai()
    {
        $userId = session()->get('user_id');

        // Cek apakah sedang break
        $aktif = $this->breakModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        if ($aktif) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Kamu sedang dalam break!'
            ]);
        }

        $jam = date('Y-m-d H:i:s');

        $this->breakModel->insert([
            'user_id' => $userId,
            'mulai'   => $jam,
        ]);

        NotificationHelper::breakMulai($userId, date('H:i', strtotime($jam)));

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Break dimulai. Jangan lupa balik ya! ☕'
        ]);
    }

    public function selesai()
    {
        $userId = session()->get('user_id');

        $break = $this->breakModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        if (!$break) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tidak ada break yang aktif.'
            ]);
        }

        $selesai = date('Y-m-d H:i:s');
        $durasi  = max(1, floor((time() - strtotime($break['mulai'])) / 60));

        $this->breakModel->update($break['id'], [
            'selesai' => $selesai,
            'durasi'  => $durasi,
        ]);

        NotificationHelper::breakSelesai($userId, date('H:i', strtotime($selesai)), $durasi);

        return $this->response->setJSON([
            'status'  => true,
            'message' => "Break selesai! Durasi: {$durasi} menit. Semangat! 💼"
        ]);
    }

    // Cek status break aktif (untuk update UI)
    public function status()
    {
        $userId = session()->get('user_id');

        $aktif = $this->breakModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first();

        return $this->response->setJSON([
            'sedang_break' => !empty($aktif),
            'mulai'        => $aktif ? date('H:i', strtotime($aktif['mulai'])) : null,
        ]);
    }
}