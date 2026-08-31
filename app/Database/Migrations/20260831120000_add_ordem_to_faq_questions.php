<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrdemToFaqQuestions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('faq_questions', [
            'ordem' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'axis',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('faq_questions', 'ordem');
    }
}
