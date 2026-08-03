<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvidenciesTable extends Migration
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
            'oai_pmh_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'questao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'url' => [
                'type'       => 'TEXT',
                'null'       => false,
            ],
            'url_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'descricao' => [
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
        $this->forge->addKey('oai_pmh_id');
        $this->forge->addKey('questao_id');
        $this->forge->addKey('url_hash');
        $this->forge->addUniqueKey(['oai_pmh_id', 'questao_id', 'url_hash']);

        $this->forge->addForeignKey('oai_pmh_id', 'oai_pmh', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('questao_id', 'certificacao_questoes', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('evidencies');
    }

    public function down()
    {
        $this->forge->dropTable('evidencies', true);
    }
}
