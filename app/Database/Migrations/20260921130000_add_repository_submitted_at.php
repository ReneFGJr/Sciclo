<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddRepositorySubmittedAt extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', ['submitted_at' => ['type' => 'DATETIME', 'null' => true]]);
    }
    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'submitted_at');
    }
}