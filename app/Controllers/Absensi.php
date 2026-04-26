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

    const JAM_MASUK_NORMAL  = '08:00:00';
    const JAM_PULANG_NORMAL = '17:00:00';
    const MAX_JAM_KERJA     = 8;

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

        $userModel = new \App\Models\UserModel();
        $users     = $userModel->findAll();
        $userMap   = array_column($users, 'nama', 'id');

        $data = $this->absensiModel
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->findAll();

        $pending = $this->absensiModel
            ->where('approval_status', 'pending')
            ->findAll();

        $summary = ['hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0, 'overtime' => 0];
        foreach ($data as $d) {
            if ($d['status'] === 'hadir')     $summary['hadir']++;
            elseif ($d['status'] === 'telat') $summary['telat']++;
            elseif ($d['status'] === 'izin') {
                if (($d['jenis'] ?? '') === 'sakit') $summary['sakit']++;
                else $summary['izin']++;
            }
            if (!empty($d['is_overtime'])) $summary['overtime']++;
        }

        $rekapUser = [];
        foreach ($data as $d) {
            $uid = $d['user_id'];
            if (!isset($rekapUser[$uid])) {
                $rekapUser[$uid] = [
                    'hadir'            => 0,
                    'telat'            => 0,
                    'izin'             => 0,
                    'sakit'            => 0,
                    'overtime_hari'    => 0,
                    'overtime_minutes' => 0,
                ];
            }
            if ($d['status'] === 'hadir')     $rekapUser[$uid]['hadir']++;
            elseif ($d['status'] === 'telat') $rekapUser[$uid]['telat']++;
            elseif ($d['status'] === 'izin') {
                if (($d['jenis'] ?? '') === 'sakit') $rekapUser[$uid]['sakit']++;
                else $rekapUser[$uid]['izin']++;
            }
            if (!empty($d['is_overtime'])) {
                $rekapUser[$uid]['overtime_hari']++;
                $rekapUser[$uid]['overtime_minutes'] += (int)($d['overtime_minutes'] ?? 0);
            }
        }

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
            'userMap'     => $userMap,
        ]);
    }

    // ================= ABSEN MASUK =================
    public function absenMasuk()
    {
        $userId = session()->get('user_id');

        // ── Cek sudah absen hari ini ──────────────────────────────────────
        $cek = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if ($cek) return $this->res(false, 'Sudah absen masuk hari ini.');

        // ── Ambil konfigurasi kantor ──────────────────────────────────────
        $kantor = $this->kantorModel->first();

        // ── Validasi GPS ──────────────────────────────────────────────────
        if ($kantor && !empty($kantor['latitude']) && !empty($kantor['longitude'])) {
            $lat = (float) $this->request->getPost('latitude');
            $lng = (float) $this->request->getPost('longitude');

            // Koordinat wajib dikirim dari frontend
            if (!$lat || !$lng) {
                return $this->res(false, 'Lokasi GPS tidak terdeteksi. Aktifkan GPS dan coba lagi.');
            }

            $jarakMeter = $this->hitungJarak(
                $kantor['latitude'],
                $kantor['longitude'],
                $lat,
                $lng
            );

            $radiusIzin = (int)($kantor['radius_meter'] ?? 100);

            if ($jarakMeter > $radiusIzin) {
                return $this->res(false, sprintf(
                    'Kamu berada %.0f meter dari kantor. Maksimal radius absen: %d meter.',
                    $jarakMeter,
                    $radiusIzin
                ), ['jarak_meter' => round($jarakMeter)]);
            }
        }

        // ── Validasi IP ───────────────────────────────────────────────────
        if ($kantor && !empty($kantor['allowed_ip'])) {
            $allowedIps  = array_map('trim', explode(',', $kantor['allowed_ip']));
            $clientIp    = $this->request->getIPAddress();

            if (!in_array($clientIp, $allowedIps)) {
                return $this->res(false, sprintf(
                    'Absen hanya diizinkan dari jaringan kantor. IP kamu: %s',
                    $clientIp
                ));
            }
        }

        // ── Simpan absen ──────────────────────────────────────────────────
        $jam    = date('H:i:s');
        $status = ($jam > self::JAM_MASUK_NORMAL) ? 'telat' : 'hadir';

        $lat = (float) $this->request->getPost('latitude');
        $lng = (float) $this->request->getPost('longitude');

        $this->absensiModel->insert([
            'user_id'          => $userId,
            'tanggal'          => date('Y-m-d'),
            'jam_masuk'        => $jam,
            'status'           => $status,
            'latitude_masuk'   => $lat ?: null,
            'longitude_masuk'  => $lng ?: null,
            'ip_masuk'         => $this->request->getIPAddress(),
            'approval_status'  => 'approved',
            'is_overtime'      => 0,
            'overtime_minutes' => 0,
        ]);

        NotificationHelper::absenMasuk($userId, substr($jam, 0, 5), $status);

        $jarakInfo = isset($jarakMeter) ? round($jarakMeter) : null;
        return $this->res(true, 'Absen masuk berhasil pukul ' . substr($jam, 0, 5) . '.', [
            'jarak_meter' => $jarakInfo,
        ]);
    }

    // ================= ABSEN PULANG =================
    public function absenPulang()
    {
        $userId = session()->get('user_id');

        $data = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if (!$data)                      return $this->res(false, 'Belum absen masuk.');
        if (!empty($data['jam_keluar'])) return $this->res(false, 'Sudah absen pulang hari ini.');
        if (empty($data['jam_masuk']))   return $this->res(false, 'Data jam masuk tidak valid.');

        // ── Validasi GPS (pulang) ─────────────────────────────────────────
        $kantor = $this->kantorModel->first();

        if ($kantor && !empty($kantor['latitude']) && !empty($kantor['longitude'])) {
            $lat = (float) $this->request->getPost('latitude');
            $lng = (float) $this->request->getPost('longitude');

            if (!$lat || !$lng) {
                return $this->res(false, 'Lokasi GPS tidak terdeteksi. Aktifkan GPS dan coba lagi.');
            }

            $jarakMeter = $this->hitungJarak(
                $kantor['latitude'],
                $kantor['longitude'],
                $lat,
                $lng
            );

            $radiusIzin = (int)($kantor['radius_meter'] ?? 100);

            if ($jarakMeter > $radiusIzin) {
                return $this->res(false, sprintf(
                    'Kamu berada %.0f meter dari kantor. Maksimal radius absen: %d meter.',
                    $jarakMeter,
                    $radiusIzin
                ), ['jarak_meter' => round($jarakMeter)]);
            }
        }

        // ── Validasi IP (pulang) ──────────────────────────────────────────
        if ($kantor && !empty($kantor['allowed_ip'])) {
            $allowedIps = array_map('trim', explode(',', $kantor['allowed_ip']));
            $clientIp   = $this->request->getIPAddress();

            if (!in_array($clientIp, $allowedIps)) {
                return $this->res(false, sprintf(
                    'Absen hanya diizinkan dari jaringan kantor. IP kamu: %s',
                    $clientIp
                ));
            }
        }

        // ── Hitung overtime ───────────────────────────────────────────────
        $jamKeluar     = date('H:i:s');
        $tsJamMasuk    = strtotime($data['jam_masuk']);
        $tsJamKeluar   = strtotime($jamKeluar);
        $tsBatasNormal = $tsJamMasuk + (self::MAX_JAM_KERJA * 3600);

        $isOvertime      = false;
        $overtimeMinutes = 0;

        if ($tsJamKeluar > $tsBatasNormal) {
            $isOvertime      = true;
            $overtimeMinutes = (int) round(($tsJamKeluar - $tsBatasNormal) / 60);
        }

        $lat = (float) $this->request->getPost('latitude');
        $lng = (float) $this->request->getPost('longitude');

        $this->absensiModel->update($data['id'], [
            'jam_keluar'        => $jamKeluar,
            'latitude_keluar'   => $lat ?: null,
            'longitude_keluar'  => $lng ?: null,
            'ip_keluar'         => $this->request->getIPAddress(),
            'is_overtime'       => $isOvertime ? 1 : 0,
            'overtime_minutes'  => $overtimeMinutes,
        ]);

        NotificationHelper::absenPulang($userId, substr($jamKeluar, 0, 5));

        if ($isOvertime) {
            NotificationHelper::overtime($userId, substr($jamKeluar, 0, 5), $overtimeMinutes);
        }

        $msg = 'Absen pulang berhasil pukul ' . substr($jamKeluar, 0, 5) . '.';
        if ($isOvertime) {
            $jam  = floor($overtimeMinutes / 60);
            $mnt  = $overtimeMinutes % 60;
            $msg .= " Overtime: {$jam}j {$mnt}m tercatat.";
        }

        $jarakInfo = isset($jarakMeter) ? round($jarakMeter) : null;
        return $this->res(true, $msg, ['jarak_meter' => $jarakInfo]);
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
            'approval_status' => 'pending',
        ]);

        NotificationHelper::izinRequest($userId, $jenis, $ket);

        return $this->res(true, 'Pengajuan dikirim. Tunggu persetujuan manager.');
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
            'approved_at'     => date('Y-m-d H:i:s'),
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
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);

        NotificationHelper::izinRejected($data['user_id'], $data['jenis'] ?? 'izin');

        return redirect()->to('/absensi/manager')->with('success', 'Pengajuan ditolak ❌');
    }

    // ================= HELPERS =================
    private function res(bool $s, string $m, array $extra = [])
    {
        return $this->response->setJSON(array_merge([
            'status'  => $s,
            'message' => $m,
        ], $extra));
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon / 2) * sin($dLon / 2);

        return $R * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}