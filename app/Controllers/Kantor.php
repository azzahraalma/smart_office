<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KantorConfigModel;

class Kantor extends BaseController
{
    protected $kantorModel;

    public function __construct()
    {
        $this->kantorModel = new KantorConfigModel();
    }

    public function index()
    {
        $data['kantor'] = $this->kantorModel->first();

        return view('kantor/index', $data);
    }

    public function update()
    {
        $id = $this->request->getPost('id');

        $this->kantorModel->update($id, [
            'nama_kantor'      => $this->request->getPost('nama_kantor'),
            'latitude'         => $this->request->getPost('latitude'),
            'longitude'        => $this->request->getPost('longitude'),
            'radius_meter'     => $this->request->getPost('radius_meter'),
            'allowed_ip'       => $this->request->getPost('allowed_ip'),
            'jam_masuk'        => $this->request->getPost('jam_masuk'),
            'jam_keluar'       => $this->request->getPost('jam_keluar'),
            'maks_break_menit' => $this->request->getPost('maks_break_menit') ?? 120,
        ]);

        return redirect()->to('/kantor')->with('success', 'Konfigurasi berhasil disimpan');
    }
}