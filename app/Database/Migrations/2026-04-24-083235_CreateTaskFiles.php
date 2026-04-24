<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskFiles extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],

            'task_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],

            'file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'uploaded_by' => [
                'type' => 'INT',
                'unsigned' => true,
            ],

            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['manager', 'karyawan'],
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // PRIMARY KEY
        $this->forge->addKey('id', true);

        // INDEX (biar query cepat)
        $this->forge->addKey('task_id');
        $this->forge->addKey('uploaded_by');

        // 🔥 FOREIGN KEY
        $this->forge->addForeignKey(
            'task_id',
            'tasks',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'uploaded_by',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('task_files');
    }

    public function down()
    {
        $this->forge->dropTable('task_files');
    }
}