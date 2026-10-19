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
        $evaluations = (new \App\Models\RepositoryEvaluationModel())
            ->select('repository_evaluations.*, users.name AS evaluator_name, users.email AS evaluator_email, assigner.name AS assigner_name')
            ->join('users', 'users.id = repository_evaluations.evaluator_id')
            ->join('users assigner', 'assigner.id = repository_evaluations.assigned_by', 'left')
            ->where('oai_pmh_id', (int) $id)->orderBy('round', 'DESC')->orderBy('assigned_at', 'DESC')->findAll();
        $currentRound = max([1, ...array_map('intval', array_column($evaluations, 'round'))]);
        $currentEvaluations = array_filter($evaluations, static fn ($evaluation) => (int) $evaluation['round'] === $currentRound);
        $evaluationCounts = [
            'assigned' => count($currentEvaluations),
            'viewed' => count(array_filter($currentEvaluations, static fn ($evaluation) => !empty($evaluation['viewed_at']) && $evaluation['viewed_at'] !== '0000-00-00 00:00:00')),
            'completed' => count(array_filter($currentEvaluations, static fn ($evaluation) => $evaluation['status'] === 'completed')),
        ];
        $assignedIds = array_column(array_filter($evaluations, static fn ($evaluation) => (int) $evaluation['round'] === $currentRound), 'evaluator_id');
        $availableEvaluators = array_values(array_filter((new \App\Models\UserRuleModel())->activeEvaluators(),
            static fn ($user) => (int) $user['id'] !== (int) ($repository['submitted_by'] ?? 0)
                && !in_array((int) $user['id'], array_map('intval', $assignedIds), true)));
        return view('repositories/show', [
            'evaluationCounts' => $evaluationCounts,
            'evaluations' => $evaluations,
            'availableEvaluators' => $availableEvaluators,
            'currentRound' => $currentRound,
            'repository' => $repository,
            'criteriaGroups' => $criteriaGroups,
            'answers' => $answers,
            'evidencesByQuestion' => $evidencesByQuestion,
            'fillingTime' => \App\Libraries\RepositorySummary::fillingTime($answers, $repository['submitted_at'] ?? null),
            'summary' => \App\Libraries\RepositorySummary::build($criteriaGroups, $answers, $evidencesByQuestion),
        ]);
    }
    public function assignEvaluator($id)
    {
        if (!session('logged_in') || !(new \App\Models\UserRuleModel())->isAdministrator((int) session('user_id'))) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado.');
        }
        $repository = (new OaiPmhModel())->find((int) $id);
        if (!$repository) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Repositorio nao encontrado.');
        $returnUrl = site_url('repositories/' . (int) $id) . '?tab=evaluators';
        $evaluatorId = filter_var($this->request->getPost('evaluator_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($evaluatorId && $evaluatorId === (int) ($repository['submitted_by'] ?? 0)) {
            return redirect()->to($returnUrl)->with('evaluator_error', 'O responsável pela submissão não pode avaliar o próprio repositório.');
        }
        $eligibleIds = array_map('intval', array_column((new \App\Models\UserRuleModel())->activeEvaluators(), 'id'));
        if (!$evaluatorId || !in_array($evaluatorId, $eligibleIds, true)) {
            return redirect()->to($returnUrl)->with('evaluator_error', 'Selecione um usuario com perfil de avaliador vigente.');
        }
        $model = new \App\Models\RepositoryEvaluationModel();
        $latest = $model->selectMax('round')->where('oai_pmh_id', (int) $id)->first();
        $round = max(1, (int) ($latest['round'] ?? 0));
        if ($model->where('oai_pmh_id', (int) $id)->where('evaluator_id', $evaluatorId)->where('round', $round)->first()) {
            return redirect()->to($returnUrl)->with('evaluator_error', 'Este avaliador ja foi indicado nesta rodada.');
        }
        $saved = $model->insert(['oai_pmh_id' => (int) $id, 'evaluator_id' => $evaluatorId,
            'assigned_by' => (int) session('user_id'), 'round' => $round,
            'assigned_at' => date('Y-m-d H:i:s'), 'status' => 'assigned']);
        return redirect()->to($returnUrl)->with($saved !== false ? 'evaluator_success' : 'evaluator_error',
            $saved !== false ? 'Avaliador indicado com sucesso.' : 'Nao foi possivel indicar o avaliador.');
    }

    public function responseTimeline($id)
    {
        $repository = (new OaiPmhModel())->find((int) $id);
        if (!$repository) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Repositorio nao encontrado.');
        $questions = (new \App\Models\Question\CertificacaoQuestoesModel())->findAll();
        $answers = (new \App\Models\Question\CertificacaoQuestoesAnswerModel())->where('oai_pmh_id', (int) $id)->findAll();
        return view('repositories/response_timeline', [
            'repository' => $repository,
            'timeline' => \App\Libraries\RepositoryResponseTimeline::build($questions, $answers, $repository['submitted_at'] ?? null),
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