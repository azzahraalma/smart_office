<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateKantorConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_kantor'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'latitude'     => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude'    => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'radius_meter' => ['type' => 'INT', 'default' => 100],
            'jam_masuk'    => ['type' => 'TIME'],
            'jam_keluar'   => ['type' => 'TIME'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('kantor_config');
    }
    public function down()
    {
        $this->forge->dropTable('kantor_config');
    }
}