<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComentarioToCertificacaoQuestoesAnswer extends Migration
{
    public function up()
    {
        $this->forge->addColumn('certificacao_questoes_answer', [
            'comentario' => [
                'type'  => 'LONGTEXT',
                'null'  => true,
                'after' => 'resposta',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('certificacao_questoes_answer', 'comentario');
    }
}
