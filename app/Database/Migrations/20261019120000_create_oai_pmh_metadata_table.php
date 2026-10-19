<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOaiPmhMetadataTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'oai_pmh_id' => ['type' => 'INT', 'unsigned' => true],
            'metadata_prefix' => ['type' => 'VARCHAR', 'constraint' => 255],
            'schema_url' => ['type' => 'TEXT'],
            'metadata_namespace' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['oai_pmh_id', 'metadata_prefix']);
        $this->forge->addForeignKey('oai_pmh_id', 'oai_pmh', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oai_pmh_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('oai_pmh_metadata', true);
    }
}
