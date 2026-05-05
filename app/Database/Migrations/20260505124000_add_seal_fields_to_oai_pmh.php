<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSealFieldsToOaiPmh extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', [
            'seal' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => -1,
                'null' => false,
                'after' => 'compression',
            ],
            'seal_data_avaliation' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'seal',
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'seal_data_avaliation',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'seal');
        $this->forge->dropColumn('oai_pmh', 'seal_data_avaliation');
        $this->forge->dropColumn('oai_pmh', 'status');
    }
}
