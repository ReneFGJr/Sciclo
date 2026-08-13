<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeedbackToCertificacaoQuestoes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('certificacao_questoes', [
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'descricao',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('certificacao_questoes', 'feedback');
    }
}