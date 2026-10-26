<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRepositoryMovements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oai_pmh', [
            'workflow_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'oai_pmh_id' => ['type' => 'INT', 'unsigned' => true],
            'actor_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'to_status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'details' => ['type' => 'TEXT', 'null' => true],
            'recipient' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true],
            'email_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'not_required'],
            'email_sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['oai_pmh_id', 'created_at']);
        $this->forge->createTable('repository_movements');
    }

    public function down()
    {
        $this->forge->dropTable('repository_movements');
        $this->forge->dropColumn('oai_pmh', 'workflow_status');
    }
}
