<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateRulesTables extends Migration
{
    public function up()
    {
        $id = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true];
        $this->forge->addField(['id' => $id, 'name' => ['type' => 'VARCHAR', 'constraint' => 100], 'code' => ['type' => 'VARCHAR', 'constraint' => 100]]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('rules');
        $this->db->table('rules')->insertBatch([
            ['name' => 'Gerente do site', 'code' => 'site-manager'],
            ['name' => 'Gerente da avaliação', 'code' => 'evaluation-manager'],
            ['name' => 'Avaliador', 'code' => 'evaluator'],
            ['name' => 'Consultor', 'code' => 'consultant'],
        ]);
        $this->forge->addField([
            'id' => $id,
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rule_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'starts_at' => ['type' => 'DATE'],
            'ends_at' => ['type' => 'DATE'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'rule_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rule_id', 'rules', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('user_rules');
    }
    public function down()
    {
        $this->forge->dropTable('user_rules');
        $this->forge->dropTable('rules');
    }
}