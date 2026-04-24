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

    public function index()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return redirect()->to('/login');
        }

        $absenHariIni = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        return view('absensi/index', [
            'absenHariIni' => $absenHariIni
        ]);
    }

    public function absenMasuk()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login ulang']);
        }

        $kantor  = $this->kantorModel->first();
        $userLat = $this->request->getPost('latitude');
        $userLng = $this->request->getPost('longitude');
        $userIp  = $this->request->getIPAddress();

        // 1. VALIDASI JARAK
        $jarak = $this->hitungJarak($userLat, $userLng, $kantor['latitude'], $kantor['longitude']);
        if ($jarak > $kantor['radius_meter']) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Di luar area kantor (Jarak: ' . round($jarak) . 'm)'
            ]);
        }

        // 2. VALIDASI IP
        if (!$this->isIpAllowed($userIp, $kantor['allowed_ip'])) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'IP tidak diizinkan. IP Anda: ' . $userIp
            ]);
        }

        // 3. CEK DOUBLE ABSEN
        $sudahAbsen = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if ($sudahAbsen) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Kamu sudah absen masuk hari ini'
            ]);
        }

        $sekarang = date('H:i:s');
        $status   = ($sekarang > $kantor['jam_masuk']) ? 'telat' : 'hadir';

        $this->absensiModel->insert([
            'user_id'         => $userId,
            'tanggal'         => date('Y-m-d'),
            'jam_masuk'       => $sekarang,
            'status'          => $status,
            'latitude_masuk'  => $userLat,
            'longitude_masuk' => $userLng
        ]);

        // 🔔 TRIGGER NOTIFIKASI ABSEN MASUK
        NotificationHelper::absenMasuk($userId, $sekarang, $status);

        return $this->createResponse(true, 'Absen masuk berhasil', round($jarak), $status);
    }

    public function absenPulang()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi tidak valid']);
        }

        $kantor  = $this->kantorModel->first();
        $userLat = $this->request->getPost('latitude');
        $userLng = $this->request->getPost('longitude');
        $userIp  = $this->request->getIPAddress();

        $absenHariIni = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if (!$absenHariIni) {
            return $this->response->setJSON(['status' => false, 'message' => 'Belum absen masuk']);
        }

        if (!empty($absenHariIni['jam_keluar'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sudah absen pulang']);
        }

        // VALIDASI JARAK & IP PULANG
        $jarak = $this->hitungJarak($userLat, $userLng, $kantor['latitude'], $kantor['longitude']);
        if ($jarak > $kantor['radius_meter'] || !$this->isIpAllowed($userIp, $kantor['allowed_ip'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal: Pastikan Anda di kantor dan jaringan benar']);
        }

        $jamKeluar = date('H:i:s');

        $this->absensiModel->update($absenHariIni['id'], [
            'jam_keluar'       => $jamKeluar,
            'latitude_keluar'  => $userLat,
            'longitude_keluar' => $userLng
        ]);

        // 🔔 TRIGGER NOTIFIKASI ABSEN PULANG
        NotificationHelper::absenPulang($userId, $jamKeluar);

        return $this->response->setJSON(['status' => true, 'message' => 'Absen pulang berhasil']);
    }

    // ─────────────────────────────────────────────
    // BREAK (tambahan method)
    // ─────────────────────────────────────────────

    public function breakMulai()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi tidak valid']);
        }

        $absenHariIni = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if (!$absenHariIni) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kamu belum absen masuk']);
        }

        if (!empty($absenHariIni['break_mulai']) && empty($absenHariIni['break_selesai'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kamu sedang dalam break']);
        }

        $jamBreak = date('H:i:s');

        $this->absensiModel->update($absenHariIni['id'], [
            'break_mulai'   => $jamBreak,
            'break_selesai' => null,
        ]);

        // 🔔 TRIGGER NOTIFIKASI BREAK MULAI
        NotificationHelper::breakMulai($userId, $jamBreak);

        return $this->response->setJSON(['status' => true, 'message' => 'Break dimulai pukul ' . $jamBreak]);
    }

    public function breakSelesai()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi tidak valid']);
        }

        $absenHariIni = $this->absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if (!$absenHariIni || empty($absenHariIni['break_mulai'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kamu belum mulai break']);
        }

        if (!empty($absenHariIni['break_selesai'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Break sudah selesai']);
        }

        $jamSelesai  = date('H:i:s');
        $durasiMenit = (int) floor((strtotime($jamSelesai) - strtotime($absenHariIni['break_mulai'])) / 60);

        $this->absensiModel->update($absenHariIni['id'], [
            'break_selesai' => $jamSelesai,
            'durasi_break'  => $durasiMenit,
        ]);

        // 🔔 TRIGGER NOTIFIKASI BREAK SELESAI
        NotificationHelper::breakSelesai($userId, $jamSelesai, $durasiMenit);

        return $this->response->setJSON(['status' => true, 'message' => "Break selesai. Durasi: {$durasiMenit} menit"]);
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    private function isIpAllowed($currentIp, $allowedIpsString)
    {
        if (empty($allowedIpsString)) return true;
        $allowedIps = array_map('trim', explode(',', $allowedIpsString));
        return in_array($currentIp, $allowedIps);
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function createResponse($status, $msg, $jarak = null, $kehadiran = null)
    {
        return $this->response->setJSON([
            'status'            => $status,
            'message'           => $msg,
            'jarak_meter'       => $jarak,
            'status_kehadiran'  => $kehadiran
        ]);
    }
}