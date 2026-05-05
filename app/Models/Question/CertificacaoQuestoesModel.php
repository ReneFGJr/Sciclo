<?php
namespace App\Models\Question;

use CodeIgniter\Model;

class CertificacaoQuestoesModel extends Model
{
    protected $table = 'certificacao_questoes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'criterio', 'nivel1', 'nivel2', 'nivel3', 'questao', 'tipo_resposta', 'descricao', 'icone', 'imagem', 'condicional_1', 'condicional_2'
    ];
    public $timestamps = false;
}
