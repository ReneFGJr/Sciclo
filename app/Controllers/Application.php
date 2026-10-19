<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Application extends Controller
{
    public function index()
    {
        $repoLink = $this->request->getPost('repo_link');

        $method = strtolower($this->request->getMethod());
        if ($method === 'post' && $repoLink) {
            $this->streamHarvesting($repoLink);
            exit;
        }

        return view('application/application_form', ['repo_link' => $repoLink]);
    }

    private function message(array $rsp): void
    {
        echo view('components/message', ['status' => $rsp['status'], 'message' => $rsp['message']]);
        @ob_flush();
        if ($rsp['status'] != '200') {
            echo view('layout/footer');
            flush();
            exit;
        }
    }

    public function form($c1=0,$c2=0,$c3=0)
    {
        $questionModel = new \App\Models\Question\CertificacaoQuestoesModel();
        $answerModel = new \App\Models\Question\CertificacaoQuestoesAnswerModel();
        $evidenceModel = new \App\Models\Question\EvidencyModel();

        $axisRows = $questionModel
            ->select('nivel1, criterio, questao')
            ->where('nivel2', '')
            ->findAll();

        $axesMap = [];
        foreach ($axisRows as $row) {
            $axisKey = trim((string) ($row['nivel1'] ?? ''));
            if ($axisKey === '') {
                continue;
            }

            if (! isset($axesMap[$axisKey])) {
                $axesMap[$axisKey] = [
                    'eixo' => $axisKey,
                    'criterio' => (string) ($row['criterio'] ?? ''),
                    'titulo' => (string) ($row['questao'] ?? ''),
                ];
            }
        }

        $axes = array_values($axesMap);
        usort($axes, static fn (array $a, array $b): int => strnatcmp((string) $a['eixo'], (string) $b['eixo']));

        $level2Rows = $questionModel
            ->select('nivel1, nivel2, criterio, questao')
            ->where('nivel2 !=', '')
            ->where('nivel3 !=', '')
            ->findAll();

        $level2ByAxis = [];
        foreach ($level2Rows as $row) {
            $axisKey = trim((string) ($row['nivel1'] ?? ''));
            $level2Key = trim((string) ($row['nivel2'] ?? ''));
            if ($axisKey === '' || $level2Key === '' || isset($level2ByAxis[$axisKey][$level2Key])) {
                continue;
            }

            $level2ByAxis[$axisKey][$level2Key] = [
                'nivel2' => $level2Key,
                'criterio' => (string) ($row['criterio'] ?? ''),
                'titulo' => (string) ($row['questao'] ?? ''),
            ];
        }

        foreach ($axes as &$axis) {
            $sublevels = array_values($level2ByAxis[(string) $axis['eixo']] ?? []);
            usort($sublevels, static fn (array $a, array $b): int => strnatcmp($a['nivel2'], $b['nivel2']));
            $axis['sublevels'] = $sublevels;
        }
        unset($axis);

        if ((int) $c1 <= 0 && ! empty($axes)) {
            $c1 = (int) $axes[0]['eixo'];
        }

        $currentSublevels = array_values($level2ByAxis[(string) $c1] ?? []);
        usort($currentSublevels, static fn (array $a, array $b): int => strnatcmp($a['nivel2'], $b['nivel2']));
        if ((int) $c2 <= 0 && ! empty($currentSublevels)) {
            $c2 = (int) $currentSublevels[0]['nivel2'];
        }

        $questions = $questionModel->where('nivel1', (string) $c1)->findAll();
        usort($questions, static function (array $a, array $b): int {
            foreach (['criterio', 'nivel1', 'nivel2', 'nivel3'] as $field) {
                $cmp = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        if ((int) $c2 > 0) {
            $questions = array_values(array_filter($questions, static function (array $question) use ($c2): bool {
                $level2 = trim((string) ($question['nivel2'] ?? ''));
                return $level2 === '' || $level2 === (string) $c2;
            }));
        }

        $savedAnswers = [];
        $savedComments = [];
        $repoId = (int) (session()->get('repo_id') ?? 0);
        $softwareValidatedIds = $repoId > 0 ? $answerModel->saveSoftwareValidatedAnswers($repoId) : [];
        if ($repoId > 0) {
            $answers = $answerModel->where('oai_pmh_id', $repoId)->findAll();
            foreach ($answers as $answer) {
                $savedAnswers[(int) $answer['questao_id']] = (string) $answer['resposta'];
                $savedComments[(int) $answer['questao_id']] = (string) ($answer['comentario'] ?? '');
            }
        }

        $skippedIds = $repoId > 0 ? $this->saveSkippedAnswers($repoId) : [];
        foreach ($skippedIds as $skippedId) {
            $savedAnswers[$skippedId] = '2';
        }

        $existingEvidences = $repoId > 0
            ? $evidenceModel
                ->where('oai_pmh_id', $repoId)
                ->orderBy('updated_at', 'DESC')
                ->findAll(200)
            : [];

        $evidencesByQuestion = [];
        foreach ($existingEvidences as $evidence) {
            $questionId = (int) ($evidence['questao_id'] ?? 0);
            if ($questionId <= 0) {
                continue;
            }

            if (! isset($evidencesByQuestion[$questionId])) {
                $evidencesByQuestion[$questionId] = [];
            }

            $evidencesByQuestion[$questionId][] = $evidence;
        }

        $nextAxis = null;
        $currentAxis = (string) $c1;
        $totalAxes = count($axes);
        for ($i = 0; $i < $totalAxes; $i++) {
            if ((string) $axes[$i]['eixo'] === $currentAxis) {
                if (isset($axes[$i + 1])) {
                    $nextAxis = (string) $axes[$i + 1]['eixo'];
                }
                break;
            }
        }

        $lastSublevel = $currentSublevels !== [] ? $currentSublevels[count($currentSublevels) - 1] : null;
        $isLastQuestionGroup = $nextAxis === null
            && ($lastSublevel === null || (string) $lastSublevel['nivel2'] === (string) $c2);

        return view('application/application_questionnaire', [
            'questions' => $questions,
            'axes' => $axes,
            'current_axis' => $currentAxis,
            'current_level2' => (string) $c2,
            'next_axis' => $nextAxis,
            'is_last_question_group' => $isLastQuestionGroup,
            'highlight_pending' => $repoId > 0 && (int) session()->get('questionnaire_pending_repo') === $repoId,
            'questionnaire_progress' => $this->questionnaireProgress($repoId),
            'software_validated_ids' => $softwareValidatedIds,
            'saved_answers' => $savedAnswers,
            'skipped_question_ids' => $skippedIds,
            'saved_comments' => $savedComments,
            'existing_evidences' => $existingEvidences,
            'evidences_by_question' => $evidencesByQuestion,
        ]);
    }

    public function selectQuestionnaire($id)
    {
        $OAI = new \App\Models\Oai_pmh\OaiPmhModel();
        $data['repo'] = $OAI->find($id);
        if (!$data['repo']) {
            return redirect()->to(site_url('application'));
        }

        $method = strtolower($this->request->getMethod());

        if ($method === 'post') {
            $OaiPmhModel = new \App\Models\Oai_pmh\OaiPmhModel();
            $OaiPmhModel->update($id, ['repository_type' => $this->request->getPost('questionario')]);

            // Salvar RepoID na sessão
            session()->set('repo_id', $id);
            if (session('logged_in')) {
                $OaiPmhModel->registerSubmitter((int) $id, (int) session('user_id'));
            }

            return redirect()->to(base_url('application/form/1'));
        }
        $software = \Config\Database::connect()->table('repository_software')
            ->select('name')
            ->where('id', (int) ($data['repo']['repository_software'] ?? 0))
            ->get()->getRowArray();
        $data['repo']['repository_software_name'] = $software['name'] ?? '';
        $this->saveSkippedAnswers((int) $id);
        $data['progress'] = $this->questionnaireProgress((int) $id);
        $data['metadataFormats'] = (new \App\Models\Oai_pmh\OaiPmhMetadataModel())
            ->where('oai_pmh_id', (int) $id)
            ->orderBy('metadata_prefix', 'ASC')
            ->findAll();
        return view('application/application_select_questionnaire', $data);
    }

    private function questionnaireProgress(int $repoId): array
    {
        $questions = (new \App\Models\Question\CertificacaoQuestoesModel())->findAll();
        $answers = [];
        if ($repoId > 0) {
            foreach ((new \App\Models\Question\CertificacaoQuestoesAnswerModel())->where('oai_pmh_id', $repoId)->findAll() as $answer) {
                $answers[(int) $answer['questao_id']] = $answer;
            }
        }
        $groups = [];
        $axisTitles = [];
        foreach ($questions as $question) {
            $level1 = trim((string) ($question['nivel1'] ?? ''));
            if (trim((string) ($question['nivel2'] ?? '')) === '' && !empty($question['questao'])) {
                $axisTitles[$level1] = trim(strip_tags((string) $question['questao']));
            }
            if (($question['tipo_resposta'] ?? '') === 'INFO') continue;
            $groups[$level1][] = $question;
        }
        uksort($groups, static fn ($a, $b): int => strnatcmp((string) $a, (string) $b));
        $summary = \App\Libraries\RepositorySummary::build($groups, $answers, []);
        $progress = array_intersect_key($summary, array_flip(['total', 'answered', 'pending', 'completion', 'groups']));
        $progress['axis_titles'] = $axisTitles;
        return $progress;
    }

    private function saveSkippedAnswers(int $repoId): array
    {
        (new \App\Models\Question\CertificacaoQuestoesAnswerModel())->saveSoftwareValidatedAnswers($repoId);
        $questionModel = new \App\Models\Question\CertificacaoQuestoesModel();
        $answersModel = new \App\Models\Question\CertificacaoQuestoesAnswerModel();
        $answers = [];
        $existingByQuestion = [];
        foreach ($answersModel->where('oai_pmh_id', $repoId)->findAll() as $answer) {
            $id = (int) $answer['questao_id'];
            $answers[$id] = (string) $answer['resposta'];
            $existingByQuestion[$id] = $answer;
        }
        $skippedIds = \App\Libraries\QuestionnaireBranching::skippedIds($questionModel->findAll(), $answers);
        foreach ($skippedIds as $id) {
            if (($answers[$id] ?? '') === '2') {
                continue;
            }
            $payload = ['oai_pmh_id' => $repoId, 'questao_id' => $id, 'resposta' => '2'];
            $saved = isset($existingByQuestion[$id])
                ? $answersModel->update((int) $existingByQuestion[$id]['id'], $payload)
                : $answersModel->insert($payload) !== false;
            if (!$saved) {
                throw new \RuntimeException('Falha ao salvar as respostas das questoes puladas.');
            }
        }
        return $skippedIds;
    }

    public function saveQuestionnaireAnswer()
    {
        $repoId = (int) (session()->get('repo_id') ?? 0);
        if ($repoId <= 0) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['saved' => false, 'message' => 'Selecione um reposit?rio antes de responder.']);
        }

        $questionId = (int) ($this->request->getPost('question_id') ?? 0);
        $answerValue = trim((string) ($this->request->getPost('resposta') ?? ''));
        $comment = trim((string) ($this->request->getPost('comentario') ?? ''));

        $questionModel = new \App\Models\Question\CertificacaoQuestoesModel();
        $question = $questionModel->find($questionId);

        if (! $question || ($question['tipo_resposta'] ?? '') === 'INFO' || $answerValue === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['saved' => false, 'message' => 'Resposta inv?lida.']);
        }

        if (($question['tipo_resposta'] ?? '') === 'SOFTWARE') {
            $repository = (new \App\Models\Oai_pmh\OaiPmhModel())->find($repoId);
            if ((bool) ($repository['repository_oai_valid'] ?? false)) $answerValue = '1';
        }
        if (in_array($question['tipo_resposta'] ?? '', ['SN', 'SOFTWARE'], true) && ! in_array($answerValue, ['1', '2'], true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['saved' => false, 'message' => 'Op??o de resposta inv?lida.']);
        }

        $answersModel = new \App\Models\Question\CertificacaoQuestoesAnswerModel();
        $existing = $answersModel
            ->where('oai_pmh_id', $repoId)
            ->where('questao_id', $questionId)
            ->first();

        $payload = [
            'oai_pmh_id' => $repoId,
            'questao_id' => $questionId,
            'resposta' => $answerValue,
            'comentario' => $comment !== '' ? $comment : null,
        ];

        if (! empty($existing['id'])) {
            $saved = $answersModel->update((int) $existing['id'], $payload);
        } else {
            $saved = $answersModel->insert($payload) !== false;
        }

        if (! $saved) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['saved' => false, 'message' => 'N?o foi poss?vel salvar a resposta.']);
        }

        $skippedIds = $this->saveSkippedAnswers($repoId);

        return $this->response->setJSON([
            'saved' => true,
            'question_id' => $questionId,
            'resposta' => $answerValue,
            'comentario' => $comment,
            'skipped_ids' => $skippedIds,
            'progress' => $this->questionnaireProgress($repoId),
        ]);
    }

    public function submitQuestionnaire()
    {
        $repoId = (int) (session()->get('repo_id') ?? 0);
        if ($repoId <= 0) {
            return redirect()->to(base_url('application'));
        }

        $currentAxis = trim((string) ($this->request->getPost('current_axis') ?? ''));
        $currentLevel2 = trim((string) ($this->request->getPost('current_level2') ?? ''));
        if ($currentAxis === '') {
            return redirect()->to(base_url('application/form/1'));
        }

        $questionModel = new \App\Models\Question\CertificacaoQuestoesModel();
        $answersModel = new \App\Models\Question\CertificacaoQuestoesAnswerModel();
        $postData = $this->request->getPost();
        $saveForLater = ($postData['save_for_later'] ?? '') === '1';
        foreach ($answersModel->saveSoftwareValidatedAnswers($repoId) as $validatedId) {
            $postData['questao_' . $validatedId] = '1';
        }

        $axisQuestions = $questionModel->where('nivel1', $currentAxis)->findAll();
        if ($currentLevel2 !== '') {
            $axisQuestions = array_values(array_filter($axisQuestions, static function (array $question) use ($currentLevel2): bool {
                return trim((string) ($question['nivel2'] ?? '')) === $currentLevel2;
            }));
        }
        $branchAnswers = [];
        foreach ($answersModel->where('oai_pmh_id', $repoId)->findAll() as $answer) {
            $branchAnswers[(int) $answer['questao_id']] = (string) $answer['resposta'];
        }
        foreach ($postData as $field => $value) {
            if (strpos((string) $field, 'questao_') === 0 && is_scalar($value)) {
                $branchAnswers[(int) substr($field, 8)] = (string) $value;
            }
        }
        foreach (\App\Libraries\QuestionnaireBranching::skippedIds($questionModel->findAll(), $branchAnswers) as $skippedId) {
            $postData['questao_' . $skippedId] = '2';
        }
        $missingAnswers = [];

        foreach ($axisQuestions as $question) {
            if (($question['tipo_resposta'] ?? '') === 'INFO') {
                continue;
            }

            $field = 'questao_' . (int) ($question['id'] ?? 0);
            $value = $postData[$field] ?? null;
            $hasValue = is_array($value) ? ! empty($value) : trim((string) $value) !== '';
            if (! $hasValue) {
                $missingAnswers[] = (int) ($question['id'] ?? 0);
            }
        }

        if (!$saveForLater && ! empty($missingAnswers)) {
            session()->set('questionnaire_pending_repo', $repoId);
            session()->setFlashdata('questionnaire_error', 'Preencha todas as respostas obrigatórias antes de continuar.');
            $returnPath = 'application/form/' . $currentAxis . ($currentLevel2 !== '' ? '/' . $currentLevel2 : '');
            return redirect()->to(base_url($returnPath));
        }

        foreach ($postData as $field => $value) {
            if (strpos((string) $field, 'questao_') !== 0) {
                continue;
            }

            $questionId = (int) substr((string) $field, 8);
            if ($questionId <= 0) {
                continue;
            }

            if (is_array($value)) {
                $answerValue = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $answerValue = trim((string) $value);
            }

            if ($answerValue === '') {
                continue;
            }

            $existing = $answersModel
                ->where('oai_pmh_id', $repoId)
                ->where('questao_id', $questionId)
                ->first();

            $payload = [
                'oai_pmh_id' => $repoId,
                'questao_id' => $questionId,
                'resposta' => $answerValue,
            ];

            $question = null;
            foreach ($axisQuestions as $axisQuestion) {
                if ((int) ($axisQuestion['id'] ?? 0) === $questionId) {
                    $question = $axisQuestion;
                    break;
                }
            }

            if (in_array($question['tipo_resposta'] ?? '', ['SN', 'SOFTWARE'], true)
                && array_key_exists('comentario_' . $questionId, $postData)) {
                $comment = trim((string) ($postData['comentario_' . $questionId] ?? ''));
                $payload['comentario'] = $comment !== '' ? $comment : null;
            }

            if (! empty($existing['id'])) {
                $answersModel->update((int) $existing['id'], $payload);
            } else {
                $answersModel->insert($payload);
            }
        }

        $this->saveSkippedAnswers($repoId);

        if ($saveForLater) {
            return redirect()->to(base_url('/'));
        }

        $level2Rows = $questionModel
            ->select('nivel2')
            ->where('nivel1', $currentAxis)
            ->where('nivel2 !=', '')
            ->findAll();

        $level2Map = [];
        foreach ($level2Rows as $row) {
            $level2 = trim((string) ($row['nivel2'] ?? ''));
            if ($level2 !== '') {
                $level2Map[$level2] = true;
            }
        }

        $level2Pages = array_map('strval', array_keys($level2Map));
        usort($level2Pages, static fn (string $a, string $b): int => strnatcmp($a, $b));

        $nextLevel2 = null;
        foreach ($level2Pages as $index => $level2) {
            if ($level2 === $currentLevel2 && isset($level2Pages[$index + 1])) {
                $nextLevel2 = $level2Pages[$index + 1];
                break;
            }
        }

        if ($nextLevel2 !== null) {
            session()->setFlashdata('questionnaire_success', 'Respostas salvas. Você avançou para o próximo grupo.');
            return redirect()->to(base_url('application/form/' . $currentAxis . '/' . $nextLevel2));
        }

        $axisRows = $questionModel
            ->select('nivel1')
            ->where('nivel2', '')
            ->findAll();

        $axesMap = [];
        foreach ($axisRows as $row) {
            $axisKey = trim((string) ($row['nivel1'] ?? ''));
            if ($axisKey !== '') {
                $axesMap[$axisKey] = true;
            }
        }

        $axes = array_map('strval', array_keys($axesMap));
        usort($axes, static fn (string $a, string $b): int => strnatcmp($a, $b));

        $nextAxis = null;
        $totalAxes = count($axes);
        for ($i = 0; $i < $totalAxes; $i++) {
            if ($axes[$i] === $currentAxis) {
                if (isset($axes[$i + 1])) {
                    $nextAxis = $axes[$i + 1];
                }
                break;
            }
        }

        if ($nextAxis !== null) {
            session()->setFlashdata('questionnaire_success', 'Respostas salvas. Você avançou para a próxima etapa.');
            return redirect()->to(base_url('application/form/' . $nextAxis));
        }

        // Only mark the repository as submitted when every required answer is saved.
        $requiredQuestions = $questionModel->where('tipo_resposta !=', 'INFO')->findAll();
        $savedAnswers = $answersModel->where('oai_pmh_id', $repoId)->findAll();
        $answeredIds = [];
        foreach ($savedAnswers as $answer) {
            $value = trim((string) $answer['resposta']);
            $decoded = json_decode($value, true);
            if ($value !== '' && !(is_array($decoded) && $decoded === [])) {
                $answeredIds[(int) $answer['questao_id']] = true;
            }
        }
        $pendingQuestions = array_values(array_filter($requiredQuestions, static function (array $question) use ($answeredIds): bool {
            return !isset($answeredIds[(int) $question['id']]);
        }));
        usort($pendingQuestions, static function (array $a, array $b): int {
            foreach (['nivel1', 'nivel2', 'nivel3'] as $field) {
                $cmp = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) return $cmp;
            }
            return (int) $a['id'] <=> (int) $b['id'];
        });
        if ($pendingQuestions !== []) {
            session()->set('questionnaire_pending_repo', $repoId);
            $firstPending = $pendingQuestions[0];
            $pendingAxis = (string) $firstPending['nivel1'];
            $pendingLevel = trim((string) ($firstPending['nivel2'] ?? ''));
            $pendingPath = 'application/form/' . rawurlencode($pendingAxis)
                . ($pendingLevel !== '' ? '/' . rawurlencode($pendingLevel) : '')
                . '#question-' . (int) $firstPending['id'];
            $message = 'Existem respostas pendentes. Complete todas as etapas antes de enviar para avaliação. '
                . 'Você foi direcionado ao eixo ' . $pendingAxis
                . ($pendingLevel !== '' ? ', grupo ' . $pendingLevel : '')
                . ', com questões em aberto destacadas em vermelho. Faltam ' . count($pendingQuestions) . ' questões no questionário.';
            return redirect()->to(base_url($pendingPath))->with('questionnaire_error', $message);
        }
        session()->remove('questionnaire_pending_repo');
        return redirect()->to(site_url('application/review'));
    }

    public function reviewSubmission()
    {
        $repoId = (int) (session('repo_id') ?? 0);
        $repositories = new \App\Models\Oai_pmh\OaiPmhModel();
        $repo = $repoId > 0 ? $repositories->find($repoId) : null;
        if (!$repo) return redirect()->to(site_url('application'));
        if (!empty($repo['submitted_by']) && (!session('logged_in') || (int) $repo['submitted_by'] !== (int) session('user_id'))) {
            return $this->response->setStatusCode(403)->setBody('Este repositório pertence a outro usuário.');
        }
        $this->saveSkippedAnswers($repoId);
        $progress = $this->questionnaireProgress($repoId);
        if (strtolower($this->request->getMethod()) === 'post') {
            if ($progress['total'] === 0 || $progress['pending'] > 0) {
                return redirect()->to(site_url('application/review'))->with('review_error', 'Existem respostas pendentes. Revise o preenchimento antes de enviar.');
            }
            if ($this->request->getPost('confirm_submission') !== '1') {
                return redirect()->to(site_url('application/review'))->with('review_error', 'Confirme o termo para enviar a submissão.');
            }
            if (empty($repo['submitted_at'])) {
                if (!$repositories->update($repoId, ['status' => 2, 'submitted_at' => date('Y-m-d H:i:s')])) {
                    return redirect()->to(site_url('application/review'))->with('review_error', 'Não foi possível enviar a submissão. Tente novamente.');
                }
            }
            return redirect()->to(site_url('application/review'))->with('review_success', 'Submissão enviada para avaliação.');
        }
        return view('application/submission_review', ['repo' => $repo, 'progress' => $progress]);
    }

    public function saveEvidence()
    {
        $repoId = (int) (session()->get('repo_id') ?? 0);
        if ($repoId <= 0) {
            return redirect()->to(base_url('application'));
        }

        $questionId = (int) ($this->request->getPost('questao_id') ?? 0);
        $currentAxis = trim((string) ($this->request->getPost('current_axis') ?? '1'));
        $existingEvidenceId = (int) ($this->request->getPost('evidence_id') ?? 0);
        $editEvidenceId = (int) ($this->request->getPost('edit_id') ?? 0);

        $evidenceModel = new \App\Models\Question\EvidencyModel();

        $url = trim((string) ($this->request->getPost('url') ?? ''));
        $descricao = trim((string) ($this->request->getPost('descricao') ?? ''));

        if ($existingEvidenceId > 0) {
            $existing = $evidenceModel
                ->where('id', $existingEvidenceId)
                ->where('oai_pmh_id', $repoId)
                ->first();
            if ($existing) {
                if ($url === '') {
                    $url = (string) ($existing['url'] ?? '');
                }
                if ($descricao === '') {
                    $descricao = (string) ($existing['descricao'] ?? '');
                }
            }
        }

        if ($questionId <= 0 || $url === '') {
            session()->setFlashdata('evidence_error', 'Informe uma URL válida para a evidência.');
            session()->setFlashdata('evidence_modal_question', $questionId);
            return redirect()->to(base_url('application/form/' . $currentAxis));
        }

        $urlHash = hash('sha256', strtolower($url));

        $titulo = $this->resolveEvidenceTitle($url);
        if ($titulo === '') {
            $titulo = $url;
        }

        $payload = [
            'oai_pmh_id' => $repoId,
            'questao_id' => $questionId,
            'url' => $url,
            'url_hash' => $urlHash,
            'titulo' => $titulo,
            'descricao' => $descricao,
        ];

        $existing = null;
        if ($editEvidenceId > 0) {
            $existing = $evidenceModel
                ->where('id', $editEvidenceId)
                ->where('oai_pmh_id', $repoId)
                ->first();
        }

        if (! $existing) {
            $existing = $evidenceModel
                ->where('oai_pmh_id', $repoId)
                ->where('questao_id', $questionId)
                ->where('url_hash', $urlHash)
                ->first();
        }

        $saved = false;
        if (! empty($existing['id'])) {
            $saved = $evidenceModel->update((int) $existing['id'], $payload);
        } else {
            $saved = $evidenceModel->insert($payload) !== false;
        }

        if (! $saved) {
            session()->setFlashdata('evidence_error', 'Não foi possível salvar a evidência. Tente novamente.');
            session()->setFlashdata('evidence_modal_question', $questionId);
            return redirect()->to(base_url('application/form/' . $currentAxis));
        }

        session()->setFlashdata('evidence_success', 'Evidência salva com sucesso: ' . $titulo);
        session()->setFlashdata('evidence_modal_question', $questionId);
        return redirect()->to(base_url('application/form/' . $currentAxis));
    }

    public function deleteEvidence($id = null)
    {
        $repoId = (int) (session()->get('repo_id') ?? 0);
        if ($repoId <= 0) {
            return redirect()->to(base_url('application'));
        }

        $evidenceModel = new \App\Models\Question\EvidencyModel();
        $existing = $evidenceModel->find($id);

        if (! $existing || (int) ($existing['oai_pmh_id'] ?? 0) !== $repoId) {
            session()->setFlashdata('evidence_error', 'Evidência não encontrada.');
            return redirect()->back();
        }

        $currentAxis = trim((string) ($this->request->getPost('axis') ?? $this->request->getGet('axis') ?? '1'));
        $evidenceModel->delete($id);

        session()->setFlashdata('evidence_success', 'Evidência excluída com sucesso.');
        session()->setFlashdata('evidence_modal_question', (int) ($existing['questao_id'] ?? 0));
        return redirect()->to(base_url('application/form/' . $currentAxis));
    }

    public function editEvidence($id = null)
    {
        $repoId = (int) (session()->get('repo_id') ?? 0);
        if ($repoId <= 0) {
            return redirect()->to(base_url('application'));
        }

        $evidenceModel = new \App\Models\Question\EvidencyModel();
        $existing = $evidenceModel->find($id);
        if (! $existing || (int) ($existing['oai_pmh_id'] ?? 0) !== $repoId) {
            session()->setFlashdata('evidence_error', 'Evidência não encontrada para edição.');
            return redirect()->back();
        }

        return redirect()->to(base_url('application/form/' . (int) ($this->request->getGet('axis') ?? 1)));
    }

    private function resolveEvidenceTitle(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $html = curl_exec($ch);
        curl_close($ch);

        if (is_string($html) && $html !== '') {
            if (preg_match('~<title[^>]*>(.*?)</title>~is', $html, $matches)) {
                $title = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($title !== '') {
                    return $title;
                }
            }
        }

        $parts = parse_url($url);
        if (is_array($parts) && ! empty($parts['host'])) {
            $path = trim((string) ($parts['path'] ?? ''), '/');
            if ($path !== '') {
                return $parts['host'] . ' / ' . basename($path);
            }

            return (string) $parts['host'];
        }

        return $url;
    }

    private function streamHarvesting(string $repoLink): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session();
        }

        $OAI = new \App\Models\Oai_pmh\OaiPmhModel();
        $RepoID = $OAI->saveURL($repoLink);
        if (!$RepoID) {
            echo view('components/message', ['status' => '500', 'message' => 'Nao foi possivel cadastrar a URL do repositorio.']);
            return;
        }
        session()->set('repo_id', (int) $RepoID);
        if ($RepoID && session('logged_in')) {
            $OAI->registerSubmitter((int) $RepoID, (int) session('user_id'));
        }

        $this->response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');
        $this->response->setHeader('X-Accel-Buffering', 'no');

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        echo view('layout/header');
        echo view('layout/navbar');

        echo '<div class="container py-5">';
        echo '<h2 class="mb-3">Processando repositório</h2>';
        echo '<p><strong>URL:</strong> ' . esc($repoLink) . '</p>';
        echo '<hr>';
        echo str_repeat(' ', 4096);

        echo '<h1>'.$RepoID.'</h1>';
        flush();


        $oaiResult = null;
        for ($i = 0; $i < 10; $i++) {
            echo '<p>Processando o repositório... (Passo ' . ($i + 1) . ' de 10) -';
            switch ($i) {
                case 0:
                    echo 'Validando a URL (' . $RepoID . ') - <b>' . esc($repoLink) . '</b>...';
                    echo $this->message($OAI->validURL($repoLink));
                    break;
                case 1:
                    echo 'Identificando OAI-PMH do repositório...';
                    $oaiResult = $OAI->getIdentifyOAI($RepoID);
                    echo view('components/message', ['status' => $oaiResult['status'], 'message' => $oaiResult['message']]);
                    break;
                case 2:
                    echo 'Validando dados e identificando o software do reposit&#243;rio...';
                    echo $this->message($OAI->detectRepositorySoftware((int) $RepoID));
                    if (($oaiResult['status'] ?? '500') !== '200') {
                        $this->message($oaiResult);
                    }
                    break;
                case 3:
                    echo 'Gerando relatórios e consultando os formatos de metadados OAI-PMH...';
                    echo $this->message($OAI->collectMetadataFormats((int) $RepoID));
                    break;
                case 4:
                    echo 'Salvando resultados...';
                    break;
                case 5:
                    echo 'Validando o certificado SSL do reposit&#243;rio...';
                    echo view('components/message', $OAI->validateRepositorySsl((int) $RepoID));
                    break;
                case 6:
                    echo 'Finalizando processo...';
                    break;
                case 9:
                    echo '<a href="' . base_url('application/form/select/' . $RepoID) . '" class="btn btn-primary mt-3">Responder questionário de certificação</a>';
                    break;
                default:
                    echo 'Processo em andamento...';
                    break;
            }
            echo '</p>';
            @ob_flush();
            flush();
            sleep(1);
        }

        echo '<div class="alert alert-success mt-4" role="alert">Processamento concluído.</div>';
        echo '</div>';
        echo view('layout/footer');

        @ob_flush();
        flush();
    }
}
