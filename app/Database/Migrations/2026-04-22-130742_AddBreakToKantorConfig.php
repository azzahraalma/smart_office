<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBreakToKantorConfig extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kantor_config', [
            'maks_break_menit' => [
                'type' => 'INT',
                'default' => 120
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('kantor_config', 'maks_break_menit');
    }
}