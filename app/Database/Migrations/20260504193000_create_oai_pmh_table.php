<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOaiPmhTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'base_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'repository_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'protocol_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'admin_email' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'earliest_datestamp' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'deleted_record' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'granularity' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'compression' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'raw_identify_xml' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('base_url');
        $this->forge->createTable('oai_pmh');
    }

    public function down()
    {
        $this->forge->dropTable('oai_pmh');
    }
}
