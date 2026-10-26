<?php
namespace App\Controllers;

use App\Models\Oai_pmh\OaiPmhModel;

class Repositories extends BaseController
{

    public function move($id)
    {
        if (!session('logged_in') || !(new \App\Models\UserRuleModel())->isAdministrator((int) session('user_id'))) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado.');
        }
        $repository = (new OaiPmhModel())->find((int) $id);
        if (!$repository) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $url = site_url('repositories/' . (int) $id);
        $target = (string) $this->request->getPost('target');
        $details = trim((string) $this->request->getPost('details'));
        if (!in_array($target, ['accepted', 'revisions_required'], true)
            || ($target === 'revisions_required' && ($details === '' || mb_strlen($details) > 10000))) {
            return redirect()->to($url)->withInput()->with('workflow_error', 'Informe os detalhes da devolução (até 10.000 caracteres).');
        }
        $recipient = null;
        if ($target === 'revisions_required') {
            $owner = !empty($repository['submitted_by']) ? (new \App\Models\UserModel())->find($repository['submitted_by']) : null;
            $recipient = trim((string) ($owner['email'] ?? $repository['admin_email'] ?? ''));
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                return redirect()->to($url)->withInput()->with('workflow_error', 'A origem não possui um e-mail válido. Atualize o cadastro do responsável antes de devolver.');
            }
        }
        try {
            $movementId = \App\Libraries\RepositoryWorkflow::move($repository, $target, (int) session('user_id'), $details, $recipient);
        } catch (\DomainException $error) {
            return redirect()->to($url)->withInput()->with('workflow_error', $error->getMessage());
        } catch (\Throwable $error) {
            return redirect()->to($url)->withInput()->with('workflow_error', 'Não foi possível registrar a movimentação. Tente novamente.');
        }
        if ($recipient && !\App\Libraries\RepositoryWorkflow::notify($movementId)) {
            return redirect()->to($url)->with('workflow_error', 'Devolução registrada e edição liberada, mas o e-mail não foi enviado. Verifique a configuração de e-mail e use Reenviar no histórico.');
        }
        return redirect()->to($url)->with('workflow_success', $recipient ? 'Repositório devolvido à origem. Orientações enviadas por e-mail.' : 'Repositório aceito para avaliação.');
    }

    public function retryNotification($id, $movementId)
    {
        if (!session('logged_in') || !(new \App\Models\UserRuleModel())->isAdministrator((int) session('user_id'))) return $this->response->setStatusCode(403);
        $movement = (new \App\Models\RepositoryMovementModel())->find((int) $movementId);
        if (!$movement || (int) $movement['oai_pmh_id'] !== (int) $id) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $sent = \App\Libraries\RepositoryWorkflow::notify((int) $movementId);
        return redirect()->to(site_url('repositories/' . (int) $id))->with($sent ? 'workflow_success' : 'workflow_error', $sent ? 'E-mail enviado à origem.' : 'Não foi possível reenviar. Verifique as configurações de e-mail.');
    }

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
            'movements' => (new \App\Models\RepositoryMovementModel())->select('repository_movements.*, users.name AS actor_name')->join('users', 'users.id = repository_movements.actor_id', 'left')->where('oai_pmh_id', (int) $id)->orderBy('repository_movements.id', 'DESC')->findAll(),
            'evaluationCounts' => $evaluationCounts,
            'evaluations' => $evaluations,
            'availableEvaluators' => $availableEvaluators,
            'currentRound' => $currentRound,
            'repository' => $repository,
            'submitter' => !empty($repository['submitted_by'])
                ? (new \App\Models\UserModel())->select('id, name, email')->find((int) $repository['submitted_by'])
                : null,
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
            'revisions_required' => ['title' => 'Revisões necessárias', 'badge' => 'bg-warning text-dark', 'items' => []],
            'accepted' => ['title' => 'Aceitos para avaliação', 'badge' => 'bg-info text-dark', 'items' => []],
            'submitted' => ['title' => 'Enviados para avaliação', 'badge' => 'bg-primary', 'items' => []],
            'evaluated' => ['title' => 'Avaliados', 'badge' => 'bg-success', 'items' => []],
            'draft' => ['title' => 'Em processo de submissão', 'badge' => 'bg-secondary', 'items' => []],
        ];
        $repositories = (new OaiPmhModel())
            ->select('*')
            ->orderBy('repository_name', 'ASC')->orderBy('id', 'ASC')->findAll();
        foreach ($repositories as $repository) {
            $evaluated = !empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00';
            $key = $evaluated ? 'evaluated' : (!empty($repository['submitted_at']) ? 'submitted' : 'draft');
            if (!$evaluated && in_array($repository['workflow_status'] ?? '', ['revisions_required', 'accepted'], true)) $key = $repository['workflow_status'];
            $groups[$key]['items'][] = $repository;
        }
        return view('repositories/index', ['groups' => $groups]);
    }
}
