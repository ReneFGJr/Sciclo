<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRepositoryTypeToOaiPmh extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', [
            'repository_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'repository_software_version',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'repository_type');
    }
}
