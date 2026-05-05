<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCertificacaoQuestoesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'criterio' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => false,
            ],
            'nivel1' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'nivel2' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'nivel3' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'questao' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'tipo_resposta' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'icone' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'imagem' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'condicional_1' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
            ],
            'condicional_2' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('certificacao_questoes');
    }

    public function down()
    {
        $this->forge->dropTable('certificacao_questoes');
    }
}
