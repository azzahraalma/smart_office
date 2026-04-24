<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAllowedIpToKantorConfig extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kantor_config', [
        'allowed_ip' => [
            'type' => 'VARCHAR',
            'constraint' => 50,
            'null' => true
        ]
    ]);
    }

    public function down()
    {
        $this->forge->dropColumn('kantor_config', 'allowed_ip');
    }
}