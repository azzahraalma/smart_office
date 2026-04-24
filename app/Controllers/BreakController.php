<?php

namespace App\Controllers;

use App\Models\BreakLogModel;

class BreakController extends BaseController
{
    protected $breakModel;

    public function __construct()
    {
        $this->breakModel = new BreakLogModel();
    }

    public function mulai()
    {
        $this->breakModel->insert([
            'user_id' => 1,
            'mulai' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Break dimulai');
    }

    public function selesai()
    {
        $break = $this->breakModel
            ->where('user_id', 1)
            ->where('selesai', null)
            ->first();

        if ($break) {
            $durasi = floor((time() - strtotime($break['mulai'])) / 60);

            $this->breakModel->update($break['id'], [
                'selesai' => date('Y-m-d H:i:s'),
                'durasi' => $durasi
            ]);
        }

        return redirect()->back()->with('success', 'Break selesai');
    }
}