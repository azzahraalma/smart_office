<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOvertimeToAbsensi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('absensi', [
            'overtime_minutes' => [
                'type'    => 'INT',
                'default' => 0,
                'null'    => true,
                'after'   => 'jam_keluar',
            ],
            'is_overtime' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'overtime_minutes',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('absensi', ['overtime_minutes', 'is_overtime']);
    }
}