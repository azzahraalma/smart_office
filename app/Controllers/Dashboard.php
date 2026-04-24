<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\TaskModel;
use App\Models\NotificationModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $absensiModel = new AbsensiModel();
        $taskModel = new TaskModel();
        $notifModel = new NotificationModel();

        return view('dashboard/index', [
            'absenHariIni' => $absensiModel
                ->where('user_id', 1)
                ->where('tanggal', date('Y-m-d'))
                ->first(),

            'taskAktif' => $taskModel
                ->where('status !=', 'done')
                ->countAllResults(),

            'notifikasi' => $notifModel
                ->orderBy('created_at', 'DESC')
                ->findAll(5)
        ]);
    }
}