<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLatLngToAbsensi extends Migration
{
        public function up()
    {
        $this->forge->addColumn('absensi', [
            'latitude_masuk' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true
            ],
            'longitude_masuk' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true
            ],
            'latitude_keluar' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true
            ],
            'longitude_keluar' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('absensi', [
            'latitude_masuk',
            'longitude_masuk',
            'latitude_keluar',
            'longitude_keluar'
        ]);
    }
}