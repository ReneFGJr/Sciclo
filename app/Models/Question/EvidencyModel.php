<?php

namespace App\Models\Question;

use CodeIgniter\Model;

class EvidencyModel extends Model
{
    protected $table = 'evidencies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'oai_pmh_id',
        'questao_id',
        'url',
        'url_hash',
        'titulo',
        'descricao',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
}
