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

    public function __construct()
    {
        $this->kantorModel  = new KantorConfigModel();
        $this->absensiModel = new AbsensiModel();

        $this->lazyAutoAlpha();
    }

    // alpha indicator
    private function lazyAutoAlpha()
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');
        if (!$userId || $role === 'manager') return;

        for ($i = 1; $i <= 7; $i++) {
            $tgl = date('Y-m-d', strtotime("-{$i} days"));

            $dayOfWeek = (int) date('N', strtotime($tgl));
            if ($dayOfWeek >= 6) continue;

            $cek = $this->absensiModel
                ->where('user_id', $userId)
                ->where('tanggal', $tgl)
                ->first();

            if (!$cek) {
                $this->absensiModel->insert([
                    'user_id'          => $userId,
                    'tanggal'          => $tgl,
                    'status'           => 'alpha',
                    'approval_status'  => 'approved',
                    'is_overtime'      => 0,
                    'overtime_minutes' => 0,
                ]);
            }
        }
    }

    // index (karyawan) 
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

    // riwayat absen 
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

    // manager dashboard 
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
            ->whereIn('status', ['izin', 'sakit'])
            ->where('approval_status', 'pending')
            ->findAll();

        $summary = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'overtime' => 0];
        foreach ($data as $d) {
            if ($d['status'] === 'hadir')       $summary['hadir']++;
            elseif ($d['status'] === 'izin')    $summary['izin']++;
            elseif ($d['status'] === 'sakit')   $summary['sakit']++;
            elseif ($d['status'] === 'alpha')   $summary['alpha']++;
            if (!empty($d['is_overtime']))       $summary['overtime']++;
        }

        $rekapUser = [];
        foreach ($data as $d) {
            $uid = $d['user_id'];
            if (!isset($rekapUser[$uid])) {
                $rekapUser[$uid] = [
                    'hadir'            => 0,
                    'izin'             => 0,
                    'sakit'            => 0,
                    'alpha'            => 0,
                    'overtime_hari'    => 0,
                    'overtime_minutes' => 0,
                ];
            }
            if ($d['status'] === 'hadir')       $rekapUser[$uid]['hadir']++;
            elseif ($d['status'] === 'izin')    $rekapUser[$uid]['izin']++;
            elseif ($d['status'] === 'sakit')   $rekapUser[$uid]['sakit']++;
            elseif ($d['status'] === 'alpha')   $rekapUser[$uid]['alpha']++;

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
                if ($d['tanggal'] == $tgl && $d['status'] === 'hadir') {
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

    // absen masuk 
    public function absenMasuk()
    {
        $userId = session()->get('user_id');

        $cek = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if ($cek) return $this->res(false, 'Sudah absen masuk hari ini.');

        $kantor = $this->kantorModel->first();

        // Validasi lokasi
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

        // Validasi IP
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

        $jam = date('H:i:s');
        $lat = (float) $this->request->getPost('latitude');
        $lng = (float) $this->request->getPost('longitude');

        $this->absensiModel->insert([
            'user_id'          => $userId,
            'tanggal'          => date('Y-m-d'),
            'jam_masuk'        => $jam,
            'status'           => 'hadir',
            'latitude_masuk'   => $lat ?: null,
            'longitude_masuk'  => $lng ?: null,
            'ip_masuk'         => $this->request->getIPAddress(),
            'approval_status'  => 'approved',
            'is_overtime'      => 0,
            'overtime_minutes' => 0,
        ]);

        NotificationHelper::absenMasuk($userId, substr($jam, 0, 5), 'hadir');

        $jarakInfo = isset($jarakMeter) ? round($jarakMeter) : null;
        return $this->res(true, 'Absen masuk berhasil pukul ' . substr($jam, 0, 5) . '.', [
            'jarak_meter' => $jarakInfo,
        ]);
    }

    // absen pulang 
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

        $kantor = $this->kantorModel->first();

        // Validasi lokasi
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

        $waktuKerjaJam   = (float)($kantor['waktu_kerja_jam'] ?? 8);
        $batasKerjaDtk   = (int)($waktuKerjaJam * 3600);

        $jamKeluar       = date('H:i:s');
        $tsJamMasuk      = strtotime($data['jam_masuk']);
        $tsJamKeluar     = strtotime($jamKeluar);
        $tsBatasNormal   = $tsJamMasuk + $batasKerjaDtk;

        $isOvertime      = false;
        $overtimeMinutes = 0;

        if ($tsJamKeluar > $tsBatasNormal) {
            $isOvertime      = true;
            $overtimeMinutes = (int) round(($tsJamKeluar - $tsBatasNormal) / 60);
        }

        $lat = (float) $this->request->getPost('latitude');
        $lng = (float) $this->request->getPost('longitude');

        $this->absensiModel->update($data['id'], [
            'jam_keluar'       => $jamKeluar,
            'latitude_keluar'  => $lat ?: null,
            'longitude_keluar' => $lng ?: null,
            'ip_keluar'        => $this->request->getIPAddress(),
            'is_overtime'      => $isOvertime ? 1 : 0,
            'overtime_minutes' => $overtimeMinutes,
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

    // ajukan izin sakit
    public function izin()
    {
        $userId = session()->get('user_id');

        $cek = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if ($cek) {
            return $this->res(false, 'Sudah ada catatan absensi hari ini.');
        }

        $status = $this->request->getPost('status');
        $ket    = $this->request->getPost('keterangan');

        if (!in_array($status, ['izin', 'sakit'])) {
            return $this->res(false, 'Jenis pengajuan tidak valid.');
        }

        if (empty(trim($ket ?? ''))) {
            return $this->res(false, 'Keterangan tidak boleh kosong.');
        }

        $this->absensiModel->insert([
            'user_id'         => $userId,
            'tanggal'         => date('Y-m-d'),
            'status'          => $status,
            'keterangan'      => $ket,
            'approval_status' => 'pending',
        ]);

        NotificationHelper::izinRequest($userId, $status, $ket);

        return $this->res(true, 'Pengajuan dikirim. Tunggu persetujuan manager.');
    }

    // approve izin/sakit absen
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

        NotificationHelper::izinApproved($data['user_id'], $data['status']);

        return redirect()->to('/absensi/manager')->with('success', 'Pengajuan disetujui ✅');
    }

    // reject absen
    public function reject($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/absensi');
        }

        $data = $this->absensiModel->find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $this->absensiModel->delete($id);

        NotificationHelper::izinRejected($data['user_id'], $data['status']);

        return redirect()->to('/absensi/manager')->with('success', 'Pengajuan ditolak. Karyawan bisa absen atau ajukan ulang ❌');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

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