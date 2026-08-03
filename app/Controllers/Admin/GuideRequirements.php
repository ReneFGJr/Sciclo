<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Question\CertificacaoQuestoesModel;

class GuideRequirements extends BaseController
{
    public function index()
    {
        $model = new CertificacaoQuestoesModel();
        $questions = $model->findAll();

        usort($questions, static function (array $a, array $b): int {
            foreach (['nivel1', 'nivel2', 'nivel3'] as $field) {
                $cmp = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        $sections = [];
        foreach ($questions as $question) {
            $axisKey = trim((string) ($question['nivel1'] ?? ''));
            if ($axisKey === '') {
                continue;
            }

            if (! isset($sections[$axisKey])) {
                $sections[$axisKey] = [
                    'eixo' => $axisKey,
                    'titulo' => '',
                    'descricao' => '',
                    'items' => [],
                ];
            }

            if (trim((string) ($question['nivel2'] ?? '')) === '' && $sections[$axisKey]['titulo'] === '') {
                $sections[$axisKey]['titulo'] = (string) ($question['questao'] ?? '');
                $sections[$axisKey]['descricao'] = (string) ($question['descricao'] ?? '');
            }

            $sections[$axisKey]['items'][] = $question;
        }

        return view('admin/guide_requirements', [
            'questions' => $questions,
            'sections' => array_values($sections),
        ]);
    }
}
