<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'task_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');

        // INDEX
        $this->forge->addKey('task_id');
        $this->forge->addKey('user_id');

        // FK
        $this->forge->addForeignKey('task_id', 'tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('task_assignments', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('task_assignments');
    }
}