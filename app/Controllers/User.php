<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class User extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ================= LIST USER =================
    public function index()
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $users = $this->userModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('auth/users', [
            'users' => $users ?? []
        ]);
    }

    // ================= EDIT =================
    public function edit($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        return view('auth/edit', [
            'user' => $user
        ]);
    }

    // ================= UPDATE =================
    public function update($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'role'   => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        // password optional
        if ($this->request->getPost('password')) {
            $data['password'] = password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            );
        }

        // upload foto
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $namaFoto = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/foto', $namaFoto);
            $data['foto'] = $namaFoto;
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/users')->with('success', 'User berhasil diupdate');
    }

    // ================= TOGGLE =================
    public function toggleStatus($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        if (session()->get('user_id') == $id) {
            return redirect()->to('/users')->with('error', 'Tidak bisa ubah akun sendiri.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $statusBaru = ($user['status'] === 'aktif') ? 'nonaktif' : 'aktif';

        $this->userModel->update($id, [
            'status' => $statusBaru
        ]);

        return redirect()->to('/users')->with('success', 'Status berhasil diubah');
    }

    // ================= DELETE =================
    public function delete($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        if (session()->get('user_id') == $id) {
            return redirect()->to('/users')->with('error', 'Tidak bisa hapus akun sendiri.');
        }

        $this->userModel->delete($id);

        return redirect()->to('/users')->with('success', 'User berhasil dihapus');
    }
}