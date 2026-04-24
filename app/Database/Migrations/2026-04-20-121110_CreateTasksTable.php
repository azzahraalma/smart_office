<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTasksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'judul' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'deadline' => [
                'type' => 'DATE',
                'null' => true
            ],
            'prioritas' => [
                'type' => 'ENUM',
                'constraint' => ['rendah', 'sedang', 'tinggi'],
                'default' => 'sedang'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['todo', 'on_progress', 'done'],
                'default' => 'todo'
            ],
            'dibuat_oleh' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            // KOLOM BARU: untuk file referensi dari manager
            'manager_file' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'assigned_to' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->addKey('dibuat_oleh');
        $this->forge->addKey('assigned_to');
        $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_to', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('tasks', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tasks');
    }
}