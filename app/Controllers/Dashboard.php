<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Models\NotificationModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role   = session()->get('role');
        $userId = session()->get('user_id');

        $absensiModel = new AbsensiModel();
        $taskModel    = new TaskModel();
        $userModel    = new UserModel();
        $notifModel   = new NotificationModel();

        // ================= ABSEN HARI INI =================
        $absenHariIni = $absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        // ================= TASK =================
        if ($role === 'manager') {

            $taskAktif = $taskModel
                ->where('status !=', 'done')
                ->countAllResults();

            $taskSelesai = $taskModel
                ->where('status', 'done')
                ->countAllResults();

            $taskList = $taskModel
                ->select('tasks.*, users.nama as assignee_nama')
                ->join('users', 'users.id = tasks.assigned_to', 'left')
                ->orderBy('tasks.created_at', 'DESC')
                ->findAll(6);

        } else {

            $taskAktif = $taskModel
                ->where('assigned_to', $userId)
                ->where('status !=', 'done')
                ->countAllResults();

            $taskSelesai = $taskModel
                ->where('assigned_to', $userId)
                ->where('status', 'done')
                ->countAllResults();

            $taskList = $taskModel
                ->where('assigned_to', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll(6);
        }

        // ================= TOTAL KARYAWAN =================
        $totalKaryawan = $userModel
            ->where('role', 'karyawan')
            ->countAllResults();

        // ================= HADIR HARI INI =================
        $hadirHariIni = $absensiModel
            ->where('tanggal', date('Y-m-d'))
            ->whereIn('status', ['hadir','telat'])
            ->countAllResults();

        // ================= CHART 7 HARI =================
        $chartHadir = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = date('Y-m-d', strtotime("-$i days"));

            $jumlah = $absensiModel
                ->where('tanggal', $tanggal)
                ->whereIn('status', ['hadir','telat'])
                ->countAllResults();

            $chartHadir[] = $jumlah;
        }

        // ================= STATUS HADIR =================
        $statusHadir = [
            'hadir' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status','hadir')->countAllResults(),
            'telat' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status','telat')->countAllResults(),
            'izin'  => $absensiModel->where('tanggal', date('Y-m-d'))->where('status','izin')->countAllResults(),
            'sakit' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status','sakit')->countAllResults(),
            'alpha' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status','alpha')->countAllResults(),
        ];

        // ================= ABSENSI TERBARU =================
        $absensiList = $absensiModel
            ->select('absensi.*, users.nama, users.role')
            ->join('users', 'users.id = absensi.user_id')
            ->where('absensi.tanggal', date('Y-m-d'))
            ->orderBy('absensi.jam_masuk', 'DESC')
            ->findAll(6);

        // ================= NOTIFIKASI =================
        $notifikasi = $notifModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        // ================= PENDING IZIN (MANAGER) =================
        $pendingIzin = [];

        if ($role === 'manager') {
            $pendingIzin = $absensiModel
                ->select('absensi.*, users.nama')
                ->join('users', 'users.id = absensi.user_id')
                ->where('absensi.status', 'pending')
                ->where('absensi.tanggal', date('Y-m-d')) // ⬅️ penting
                ->orderBy('absensi.created_at', 'DESC')
                ->findAll();
        }

        // ================= DATA FINAL =================
        $data = [
            'absenHariIni'  => $absenHariIni,
            'taskAktif'     => $taskAktif,
            'taskSelesai'   => $taskSelesai,
            'taskList'      => $taskList,
            'taskTotal'     => count($taskList),

            'totalKaryawan' => $totalKaryawan,
            'hadirHariIni'  => $hadirHariIni,
            'chartHadir'    => $chartHadir,
            'statusHadir'   => $statusHadir,
            'absensiList'   => $absensiList,

            'notifikasi'    => $notifikasi,
            'pendingIzin'   => $pendingIzin
        ];

        // ================= VIEW =================
        if ($role === 'manager') {
            return view('dashboard/manager', $data);
        } elseif ($role === 'karyawan') {
            return view('dashboard/karyawan', $data);
        }

        return view('dashboard/index', $data);
    }
}