<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOaiPmhAddFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', [
            'base_url_oai' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'id',
            ],
            'repository_software' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'base_url_oai',
            ],
            'repository_software_version' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'after' => 'repository_software',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'base_url_oai');
        $this->forge->dropColumn('oai_pmh', 'repository_software');
        $this->forge->dropColumn('oai_pmh', 'repository_software_version');
    }
}
