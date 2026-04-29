<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Models\BreakLogModel;

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
        $breakModel   = new BreakLogModel();

        // absensi
        $absenHariIni = $absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        // break
        $breakLogs = $breakModel
            ->where('user_id', $userId)
            ->where('DATE(mulai)', date('Y-m-d'))
            ->orderBy('mulai', 'ASC')
            ->findAll();

        $totalBreakMnt = array_sum(array_column($breakLogs, 'durasi'));

        $isBreak = $breakModel
            ->where('user_id', $userId)
            ->where('selesai', null)
            ->first() ? true : false;

        // task
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

        // total kaywan
        $totalKaryawan = $userModel
            ->where('role', 'karyawan')
            ->countAllResults();

        // absensi harian
        $hadirHariIni = $absensiModel
            ->where('tanggal', date('Y-m-d'))
            ->whereIn('status', ['hadir', 'telat'])
            ->countAllResults();

        // absensi mingguan
        $chartHadir = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = date('Y-m-d', strtotime("-$i days"));
            $jumlah  = $absensiModel
                ->where('tanggal', $tanggal)
                ->whereIn('status', ['hadir', 'telat'])
                ->countAllResults();
            $chartHadir[] = $jumlah;
        }

        // status karyawan
        $statusHadir = [
            'hadir' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status', 'hadir')->countAllResults(),
            'telat' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status', 'telat')->countAllResults(),
            'izin'  => $absensiModel->where('tanggal', date('Y-m-d'))->where('status', 'izin')->countAllResults(),
            'sakit' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status', 'sakit')->countAllResults(),
            'alpha' => $absensiModel->where('tanggal', date('Y-m-d'))->where('status', 'alpha')->countAllResults(),
        ];

        // absen
        $absensiList = $absensiModel
            ->select('absensi.*, users.nama, users.role')
            ->join('users', 'users.id = absensi.user_id')
            ->where('absensi.tanggal', date('Y-m-d'))
            ->orderBy('absensi.jam_masuk', 'DESC')
            ->findAll(6);

        // notifikasi
        $notifikasi = $notifModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        // approval izin 
        $pendingIzin = [];
        if ($role === 'manager') {
            $pendingIzin = $absensiModel
                ->select('absensi.*, users.nama')
                ->join('users', 'users.id = absensi.user_id')
                ->where('absensi.approval_status', 'pending')
                ->orderBy('absensi.created_at', 'DESC')
                ->findAll();
        }

        // overtime
        $overtime = false;
        if ($absenHariIni && !$absenHariIni['jam_keluar']) {
            // Jangan hitung overtime kalau status izin/pending
            $statusAman = in_array($absenHariIni['status'], ['izin', 'alpha']);
            $approvalPending = ($absenHariIni['approval_status'] ?? '') === 'pending';

            if (!$statusAman && !$approvalPending) {
                $batas    = strtotime($absenHariIni['jam_masuk']) + (8 * 3600);
                $overtime = time() > $batas;
            }
        }

        // dashboard data
        $data = [
            'absenHariIni'  => $absenHariIni,

            'breakLogs'     => $breakLogs,
            'totalBreakMnt' => $totalBreakMnt,
            'isBreak'       => $isBreak,

            'taskAktif'     => $taskAktif,
            'taskSelesai'   => $taskSelesai,
            'taskList'      => $taskList,
            'taskTotal'     => $taskAktif + $taskSelesai,

            'totalKaryawan' => $totalKaryawan,
            'hadirHariIni'  => $hadirHariIni,
            'chartHadir'    => $chartHadir,
            'statusHadir'   => $statusHadir,
            'absensiList'   => $absensiList,

            'notifikasi'    => $notifikasi,
            'pendingIzin'   => $pendingIzin,

            'overtime'      => $overtime,
            'isIdle'        => false,
            'idleLogs'      => [],
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
