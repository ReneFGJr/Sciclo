<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSslToOaiPmh extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', ['SSL' => ['type' => 'TEXT', 'null' => true]]);
    }

    public function down()
    {
        $this->forge->dropColumn('oai_pmh', 'SSL');
    }
}
