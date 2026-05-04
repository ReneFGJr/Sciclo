<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SealSeeder extends Seeder
{
    public function run()
    {
        $seals = [
            [
                'name' => 'Sciclo Dourado',
                'image' => '/assets/stamp/stamps_sciclo_s1.png',
                'description' => 'Disponibiliza dados em acesso aberto',
            ],
            [
                'name' => 'Sciclo Prata',
                'image' => '/assets/stamp/stamps_sciclo_s2.png',
                'description' => 'Interoperabilidade, uso de vocabulários controlados e padrões de metadados definidos.',
            ],
            [
                'name' => 'Sciclo Ferro',
                'image' => '/assets/stamp/stamps_sciclo_s3.png',
                'description' => 'Disponibiliza dados em acesso aberto, com padronização de metadados e uso de identificadores persistentes. ',
            ],
            [
                'name' => 'Sciclo Azul',
                'image' => '/assets/stamp/stamps_sciclo_s4.png',
                'description' => 'Repositório institucionalizado, com governança e politicas.',
            ],
            [
                'name' => 'Sciclo Verde',
                'image' => '/assets/stamp/stamps_sciclo_s5.png',
                'description' => 'Dados em acesso aberto e Políticas de interoperabilidade, reuso, e impacto comprovado.',
            ],
        ];
        $this->db->table('seals')->insertBatch($seals);
    }
}
