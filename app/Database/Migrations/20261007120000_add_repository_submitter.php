<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRepositorySubmitter extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', [
            'submitted_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->db->query('CREATE INDEX repository_submitter ON ' . $this->db->prefixTable('oai_pmh') . ' (submitted_by)');
    }

    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'submitted_by');
    }
}
