<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KantorConfigModel;
use App\Models\AbsensiModel;
use App\Helpers\NotificationHelper;

class Absensi extends BaseController
{
    protected $kantorModel;
    protected $absensiModel;

    public function __construct()
    {
        $this->kantorModel  = new KantorConfigModel();
        $this->absensiModel = new AbsensiModel();
    }

    // ================= INDEX =================
    public function index()
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        if (!$userId) return redirect()->to('/login');

        if ($role === 'manager') {
            return redirect()->to('/absensi/manager');
        }

        $absenHariIni = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        return view('absensi/index', [
            'absenHariIni' => $absenHariIni
        ]);
    }

    // ================= RIWAYAT =================
    public function riwayat()
    {
        $userId = session()->get('user_id');

        $data = $this->absensiModel
            ->where('user_id', $userId)
            ->orderBy('tanggal', 'DESC')
            ->findAll();

        return view('absensi/riwayat', [
            'riwayat' => $data
        ]);
    }

    // ================= MANAGER =================
    public function manager()
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/absensi');
        }

        $bulan = $this->request->getGet('bulan') ?? date('Y-m');
        $start = $bulan . '-01';
        $end   = date('Y-m-t', strtotime($start));

        // Ambil semua user buat mapping id → nama
        $userModel = new \App\Models\UserModel();
        $users     = $userModel->findAll();
        $userMap   = array_column($users, 'nama', 'id'); // [id => nama]

        $data = $this->absensiModel
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->findAll();

        $pending = $this->absensiModel
            ->where('approval_status', 'pending')
            ->findAll();

        // SUMMARY
        $summary = ['hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0];
        foreach ($data as $d) {
            if ($d['status'] === 'hadir') $summary['hadir']++;
            elseif ($d['status'] === 'telat') $summary['telat']++;
            elseif ($d['status'] === 'izin') {
                if (($d['jenis'] ?? '') === 'sakit') $summary['sakit']++;
                else $summary['izin']++;
            }
        }

        // REKAP USER
        $rekapUser = [];
        foreach ($data as $d) {
            $uid = $d['user_id'];
            if (!isset($rekapUser[$uid])) {
                $rekapUser[$uid] = ['hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0];
            }
            if ($d['status'] === 'hadir') $rekapUser[$uid]['hadir']++;
            elseif ($d['status'] === 'telat') $rekapUser[$uid]['telat']++;
            elseif ($d['status'] === 'izin') {
                if (($d['jenis'] ?? '') === 'sakit') $rekapUser[$uid]['sakit']++;
                else $rekapUser[$uid]['izin']++;
            }
        }

        // CHART
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl   = date('Y-m-d', strtotime("-$i days"));
            $count = 0;
            foreach ($data as $d) {
                if ($d['tanggal'] == $tgl && in_array($d['status'], ['hadir', 'telat'])) {
                    $count++;
                }
            }
            $chart[] = $count;
        }

        return view('absensi/manager', [
            'dataAbsensi' => $data,
            'pending'     => $pending,
            'summary'     => $summary,
            'rekapUser'   => $rekapUser,
            'chart'       => $chart,
            'bulan'       => $bulan,
            'userMap'     => $userMap, // ← tambah ini
        ]);
    }

    // ================= ABSEN MASUK =================
    public function absenMasuk()
    {
        $userId = session()->get('user_id');

        $cek = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if ($cek) return $this->res(false, 'Sudah absen');

        $jam = date('H:i:s');

        $this->absensiModel->insert([
            'user_id'         => $userId,
            'tanggal'         => date('Y-m-d'),
            'jam_masuk'       => $jam,
            'status'          => 'hadir',
            'approval_status' => 'approved'
        ]);

        NotificationHelper::absenMasuk($userId, $jam, 'hadir');

        return $this->res(true, 'Absen masuk berhasil');
    }

    // ================= ABSEN PULANG =================
    public function absenPulang()
    {
        $userId = session()->get('user_id');

        $data = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if (!$data) return $this->res(false, 'Belum absen masuk');

        $this->absensiModel->update($data['id'], [
            'jam_keluar' => date('H:i:s')
        ]);

        NotificationHelper::absenPulang($userId, date('H:i:s'));

        return $this->res(true, 'Absen pulang berhasil');
    }

    // ================= IZIN =================
    public function izin()
    {
        $userId = session()->get('user_id');
        $jenis  = $this->request->getPost('jenis');
        $ket    = $this->request->getPost('keterangan');

        $this->absensiModel->insert([
            'user_id'         => $userId,
            'tanggal'         => date('Y-m-d'),
            'status'          => 'izin',
            'jenis'           => $jenis,
            'keterangan'      => $ket,
            'approval_status' => 'pending'
        ]);

        NotificationHelper::izinRequest($userId, $jenis, $ket);

        return $this->res(true, 'Pengajuan dikirim');
    }

    // ================= APPROVE =================
    public function approve($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/absensi');
        }

        $data = $this->absensiModel->find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $this->absensiModel->update($id, [
            'approval_status' => 'approved',
            'approved_by'     => session()->get('user_id'),
            'approved_at'     => date('Y-m-d H:i:s')
        ]);

        NotificationHelper::izinApproved($data['user_id'], $data['jenis'] ?? 'izin');

        return redirect()->to('/absensi/manager')->with('success', 'Pengajuan disetujui ✅');
    }

    // ================= REJECT =================
    public function reject($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/absensi');
        }

        $data = $this->absensiModel->find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $this->absensiModel->update($id, [
            'approval_status' => 'rejected',
            'approved_by'     => session()->get('user_id'),
            'approved_at'     => date('Y-m-d H:i:s')
        ]);

        NotificationHelper::izinRejected($data['user_id'], $data['jenis'] ?? 'izin');

        return redirect()->to('/absensi/manager')->with('success', 'Pengajuan ditolak ❌');
    }

    // ================= HELPER =================
    private function res($s, $m)
    {
        return $this->response->setJSON([
            'status'  => $s,
            'message' => $m
        ]);
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        return $R * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}