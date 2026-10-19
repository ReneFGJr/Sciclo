<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddNomeToEvidencies extends Migration
{
    public function up()
    {
        $this->forge->addColumn('evidencies', ['nome' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]]);
        $this->db->table('evidencies')->set('nome', 'titulo', false)->update();
    }
    public function down()
    {
        $this->forge->dropColumn('evidencies', 'nome');
    }
}
