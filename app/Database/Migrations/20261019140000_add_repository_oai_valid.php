<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRepositoryOaiValid extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', [
            'repository_oai_valid' => ['type' => 'BOOLEAN', 'default' => false, 'null' => false],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'repository_oai_valid');
    }
}
