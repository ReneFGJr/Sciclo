<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRepositorySoftwareTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('repository_software');

        // Seed para softwares de repositório
        $seeder = \Config\Database::seeder();
        $seeder->call('UserSeeder');
        $seeder->call('RepositorySoftwareSeeder');
        $seeder->call('SealSeeder');
    }

    public function down()
    {
        $this->forge->dropTable('repository_software');
    }
}
