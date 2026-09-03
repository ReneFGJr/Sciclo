<?php

namespace App\Models\Question;

use CodeIgniter\Model;

class CertificacaoQuestoesAnswerModel extends Model
{
    protected $table = 'certificacao_questoes_answer';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'oai_pmh_id',
        'questao_id',
        'resposta',
        'comentario',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
}
