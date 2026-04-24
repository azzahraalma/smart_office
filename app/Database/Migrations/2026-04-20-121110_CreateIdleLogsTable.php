<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateIdleLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true], // fix
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'mulai'      => ['type' => 'DATETIME'],
            'selesai'    => ['type' => 'DATETIME', 'null' => true],
            'durasi'     => ['type' => 'INT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('idle_logs');
    }
    public function down()
    {
        $this->forge->dropTable('idle_logs');
    }
}