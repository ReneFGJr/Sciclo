<?php
namespace App\Controllers;

use App\Models\Oai_pmh\OaiPmhModel;

class Repositories extends BaseController
{
    public function show($id)
    {
        $repository = (new OaiPmhModel())->find((int) $id);
        if (!$repository) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Repositório não encontrado.');
        }
        $questions = (new \App\Models\Question\CertificacaoQuestoesModel())->findAll();
        usort($questions, static function (array $a, array $b): int {
            foreach (['nivel1', 'criterio', 'nivel2', 'nivel3'] as $field) {
                $comparison = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($comparison !== 0) {
                    return $comparison;
                }
            }
            return (int) $a['id'] <=> (int) $b['id'];
        });
        $criteriaGroups = [];
        foreach ($questions as $question) {
            $level = trim((string) ($question['nivel1'] ?? ''));
            $key = $level !== '' ? $level : 'Outros';
            $criteriaGroups[$key][] = $question;
        }
        $answers = [];
        foreach ((new \App\Models\Question\CertificacaoQuestoesAnswerModel())->where('oai_pmh_id', (int) $id)->findAll() as $answer) {
            $answers[(int) $answer['questao_id']] = $answer;
        }
        $evidencesByQuestion = [];
        foreach ((new \App\Models\Question\EvidencyModel())->where('oai_pmh_id', (int) $id)->orderBy('updated_at', 'DESC')->findAll() as $evidence) {
            $evidencesByQuestion[(int) $evidence['questao_id']][] = $evidence;
        }
        return view('repositories/show', [
            'repository' => $repository,
            'criteriaGroups' => $criteriaGroups,
            'answers' => $answers,
            'evidencesByQuestion' => $evidencesByQuestion,
        ]);
    }
    public function index()
    {
        $groups = [
            'submitted' => ['title' => 'Enviados para avaliação', 'badge' => 'bg-primary', 'items' => []],
            'evaluated' => ['title' => 'Avaliados', 'badge' => 'bg-success', 'items' => []],
            'draft' => ['title' => 'Em processo de submissão', 'badge' => 'bg-secondary', 'items' => []],
        ];
        $repositories = (new OaiPmhModel())
            ->select('id, repository_name, base_url, repository_type, created_at, updated_at, submitted_at, seal_data_avaliation')
            ->orderBy('repository_name', 'ASC')->orderBy('id', 'ASC')->findAll();
        foreach ($repositories as $repository) {
            $evaluated = !empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00';
            $key = $evaluated ? 'evaluated' : (!empty($repository['submitted_at']) ? 'submitted' : 'draft');
            $groups[$key]['items'][] = $repository;
        }
        return view('repositories/index', ['groups' => $groups]);
    }
}