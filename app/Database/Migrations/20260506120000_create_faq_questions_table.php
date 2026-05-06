<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFaqQuestionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'question'    => [
                'type'       => 'TEXT',
                'null'       => false,
            ],
            'answer'      => [
                'type'       => 'TEXT',
                'null'       => false,
            ],
            'axis'        => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'created_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('faq_questions');
    }

    public function down()
    {
        $this->forge->dropTable('faq_questions');
    }
}
