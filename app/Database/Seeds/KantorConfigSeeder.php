<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KantorConfigSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('kantor_config')->insert([
            'nama_kantor' => 'Smart Office Headquarter',
            'latitude' => -6.39629463, 
            'longitude' => 106.82169899,
            'radius_meter' => 100,
            'allowed_ip' => '192.168.100.36, ::1, 127.0.0.1',
            'jam_masuk' => '09:00:00',
            'jam_keluar' => '17:00:00',
            'maks_break_menit' => 120,
        ]);
    }
}