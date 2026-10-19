<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ScopeRepositoryUrlToSubmitter extends Migration
{
    public function up()
    {
        foreach ($this->db->getIndexData('oai_pmh') as $name => $index) {
            if ($index->fields === ['base_url'] && $index->type === 'UNIQUE') {
                $this->forge->dropKey('oai_pmh', $name);
            }
        }
        $this->forge->addUniqueKey(['base_url', 'submitted_by'], 'repository_url_submitter');
        $this->forge->processIndexes('oai_pmh');
    }

    public function down()
    {
        $duplicate = $this->db->table('oai_pmh')->select('base_url')->groupBy('base_url')->having('COUNT(*) > 1', null, false)->get(1)->getRowArray();
        if ($duplicate) throw new \RuntimeException('URLs compartilhadas por usuarios distintos impedem restaurar a unicidade global.');
        $this->forge->dropKey('oai_pmh', 'repository_url_submitter');
        $this->forge->addUniqueKey('base_url');
        $this->forge->processIndexes('oai_pmh');
    }
}
