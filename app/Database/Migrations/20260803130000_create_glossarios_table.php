<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGlossariosTable extends Migration
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
            'termo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'definicao' => [
                'type' => 'LONGTEXT',
                'null' => false,
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
        $this->forge->addUniqueKey('termo');
        $this->forge->createTable('glossarios');
    }

    public function down()
    {
        $this->forge->dropTable('glossarios');
    }
}
