<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RepositorySoftwareSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'DSpace',
                'description' => 'Plataforma open source para repositórios digitais, amplamente utilizada em instituições acadêmicas.',
            ],
            [
                'name' => 'Dataverse',
                'description' => 'Aplicação open source para gerenciamento e compartilhamento de dados de pesquisa.',
            ],
        ];
        $this->db->table('repository_software')->insertBatch($data);
    }
}
