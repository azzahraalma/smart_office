<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbsensiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true],
            'tanggal'      => ['type' => 'DATE'],
            'jam_masuk'    => ['type' => 'TIME', 'null' => true],
            'jam_keluar'   => ['type' => 'TIME', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['hadir', 'telat', 'izin', 'alpha'], 'default' => 'hadir'],
            'lokasi_masuk' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lokasi_keluar'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absensi');
    }

    public function down()
    {
        $this->forge->dropTable('absensi');
    }
}