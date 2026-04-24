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

    // Daftar semua user — hanya admin
    public function index()
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Hanya manager yang bisa melihat halaman ini.');
        }

        $users = $this->userModel->orderBy('created_at', 'DESC')->findAll();

        return view('auth/users', [
            'title' => 'Manajemen User',
            'users' => $users,
        ]);
    }

    // Form edit user
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
            'title' => 'Edit User',
            'user'  => $user,
        ]);
    }

    // Proses update user
    public function update($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'nama'  => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'  => 'required|in_list[manager,karyawan]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data = [
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'role'   => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        // Update password hanya kalau diisi
        $passwordBaru = $this->request->getPost('password');
        if (!empty($passwordBaru)) {
            if (strlen($passwordBaru) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter.');
            }
            $data['password'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        // Update foto kalau ada file baru
        $fileFoto = $this->request->getFile('foto');
        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $namaFoto = $fileFoto->getRandomName();
            $fileFoto->move(ROOTPATH . 'public/uploads/foto', $namaFoto);
            $data['foto'] = $namaFoto;
        }

        $this->userModel->update($id, $data);

        // Kalau yang diedit adalah diri sendiri, update session juga
        if (session()->get('user_id') == $id) {
            session()->set([
                'nama'  => $data['nama'],
                'email' => $data['email'],
                'role'  => $data['role'],
            ]);
        }

        return redirect()->to('/users')->with('success', 'Data user berhasil diperbarui.');
    }

    // Toggle status aktif/nonaktif
    public function toggleStatus($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        // Manager tidak bisa nonaktifkan dirinya sendiri
        if (session()->get('user_id') == $id) {
            return redirect()->to('/users')->with('error', 'Kamu tidak bisa menonaktifkan akun sendiri.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $statusBaru = ($user['status'] === 'aktif') ? 'nonaktif' : 'aktif';
        $this->userModel->update($id, ['status' => $statusBaru]);

        return redirect()->to('/users')->with('success', 'Status user berhasil diubah menjadi ' . $statusBaru . '.');
    }

    // Hapus user
    public function delete($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        if (session()->get('user_id') == $id) {
            return redirect()->to('/users')->with('error', 'Kamu tidak bisa menghapus akun sendiri.');
        }

        $this->userModel->delete($id);

        return redirect()->to('/users')->with('success', 'User berhasil dihapus.');
    }
}