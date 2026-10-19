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
    public function saveSoftwareValidatedAnswers(int $repoId): array
    {
        $repository = (new \App\Models\Oai_pmh\OaiPmhModel())->find($repoId);
        if (!(bool) ($repository['repository_oai_valid'] ?? false)) return [];
        $questions = (new CertificacaoQuestoesModel())->where('tipo_resposta', 'SOFTWARE')->findAll();
        $ids = [];
        foreach ($questions as $question) {
            $id = (int) $question['id'];
            $ids[] = $id;
            $existing = $this->where('oai_pmh_id', $repoId)->where('questao_id', $id)->first();
            if (($existing['resposta'] ?? '') === '1') continue;
            $payload = ['oai_pmh_id' => $repoId, 'questao_id' => $id, 'resposta' => '1'];
            $saved = $existing ? $this->update((int) $existing['id'], $payload) : $this->insert($payload) !== false;
            if (!$saved) throw new \RuntimeException('Falha ao salvar a resposta validada pelo software.');
        }
        return $ids;
    }
}
