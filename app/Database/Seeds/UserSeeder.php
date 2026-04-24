<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'nama'       => 'Almalia Azzahra Wally',
                'email'      => 'almaazzahra@smartoffice.com',
                'password'   => password_hash('manager123', PASSWORD_DEFAULT),
                'role'       => 'manager',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Muhammad Haris',
                'email'      => 'haris@smartoffice.com',
                'password'   => password_hash('haris123', PASSWORD_DEFAULT),
                'role'       => 'karyawan',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Haikal Azmi',
                'email'      => 'haikal@smartoffice.com',
                'password'   => password_hash('haikal123', PASSWORD_DEFAULT),
                'role'       => 'karyawan',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Felicia Oktovanny',
                'email'      => 'felicia@smartoffice.com',
                'password'   => password_hash('felicia123', PASSWORD_DEFAULT),
                'role'       => 'karyawan',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($users);
    }
}