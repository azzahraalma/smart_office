<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ─── LOGIN ───────────────────────────────────────────────

    public function login()
    {
        // Kalau sudah login, langsung redirect ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/login', ['title' => 'Login - Smart Office']);
    }

    public function loginProcess()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Email dan password wajib diisi dengan benar.');
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Email tidak ditemukan.');
        }

        if ($user['status'] === 'nonaktif') {
            return redirect()->back()->withInput()->with('error', 'Akun kamu telah dinonaktifkan. Hubungi admin.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password yang kamu masukkan salah.');
        }

        // Simpan data user ke session
        session()->set([
            'user_id'   => $user['id'],
            'nama'      => $user['nama'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'foto'      => $user['foto'],
            'logged_in' => true,
        ]);

        return redirect()->to('/')->with('success', 'Selamat datang, ' . $user['nama'] . '!');
    }

    // ─── REGISTER ────────────────────────────────────────────

    public function register()
    {
        // Hanya manager yang bisa membuka halaman register
        if (session()->get('logged_in') && session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        return view('auth/register', ['title' => 'Tambah Akun - Smart Office']);
    }

    public function registerProcess()
    {
        // Hanya manager yang bisa menambah user
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'nama'     => 'required|min_length[3]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[manager,karyawan]',
        ];

        $messages = [
            'email' => [
                'is_unique' => 'Email sudah digunakan oleh akun lain.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $foto = null;
        $fileFoto = $this->request->getFile('foto');

        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $namaFoto = $fileFoto->getRandomName();
            $fileFoto->move(ROOTPATH . 'public/uploads/foto', $namaFoto);
            $foto = $namaFoto;
        }

        $this->userModel->insert([
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
            'foto'     => $foto,
            'status'   => 'aktif',
        ]);

        return redirect()->to('/users')->with('success', 'Akun berhasil ditambahkan.');
    }

    // ─── LOGOUT ──────────────────────────────────────────────

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Kamu berhasil logout.');
    }
}