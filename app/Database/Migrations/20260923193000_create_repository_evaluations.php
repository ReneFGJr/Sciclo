<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRepositoryEvaluations extends Migration
{
    public function up()
    {
        $reference = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true];
        $date = ['type' => 'DATETIME', 'null' => true];
        $this->forge->addField([
            'id' => $reference + ['auto_increment' => true],
            'oai_pmh_id' => $reference,
            'evaluator_id' => $reference,
            'assigned_by' => $reference + ['null' => true],
            'round' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'assigned_at' => ['type' => 'DATETIME'],
            'viewed_at' => $date,
            'accepted_at' => $date,
            'due_at' => $date,
            'completed_at' => $date,
            'cancelled_at' => $date,
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'assigned'],
            'recommendation' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'report' => ['type' => 'TEXT', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'cancellation_reason' => ['type' => 'TEXT', 'null' => true],
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['oai_pmh_id', 'evaluator_id', 'round'], 'evaluation_repository_evaluator_round');
        $this->forge->addKey(['evaluator_id', 'status']);
        $this->forge->addKey(['status', 'due_at']);
        $this->forge->addForeignKey('oai_pmh_id', 'oai_pmh', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('evaluator_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('assigned_by', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('repository_evaluations');
    }

    public function down()
    {
        $this->forge->dropTable('repository_evaluations');
    }
}
