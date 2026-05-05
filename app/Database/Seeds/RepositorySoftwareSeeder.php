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
            [
                'name' => 'Fedora',
                'description' => 'Sistema de gerenciamento de repositórios digitais, focado em preservação e acesso a longo prazo.',
            ],
            [
                'name' => 'EPrints',
                'description' => 'Software open source para criação de repositórios institucionais, com foco em facilidade de uso.',
            ],
            [
                'name' => 'Invenio',
                'description' => 'Plataforma open source para gerenciamento de repositórios digitais, desenvolvida pelo CERN.',
            ],
            [
                'name' => 'CKAN',
                'description' => 'Plataforma open source para gerenciamento de dados abertos, amplamente utilizada por governos e organizações.',
            ],
            [
                'name' => 'Outros',
                'description' => 'Software de repositório não listado, incluindo soluções personalizadas ou menos comuns.',
            ]
        ];
        $this->db->table('repository_software')->insertBatch($data);
    }
}
