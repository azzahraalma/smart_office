<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveLokasiColumns extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('absensi', [
            'lokasi_masuk',
            'lokasi_keluar'
        ]);
    }

    public function down()
    {
        $this->forge->addColumn('absensi', [
            'lokasi_masuk' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'lokasi_keluar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ]
        ]);
    }
}