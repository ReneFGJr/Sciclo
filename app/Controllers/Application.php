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
        if ($repoId > 0) {
            $answers = $answerModel->where('oai_pmh_id', $repoId)->findAll();
            foreach ($answers as $answer) {
                $savedAnswers[(int) $answer['questao_id']] = (string) $answer['resposta'];
                $savedComments[(int) $answer['questao_id']] = (string) ($answer['comentario'] ?? '');
            }
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

        return view('application/application_questionnaire', [
            'questions' => $questions,
            'axes' => $axes,
            'current_axis' => $currentAxis,
            'current_level2' => (string) $c2,
            'next_axis' => $nextAxis,
            'saved_answers' => $savedAnswers,
            'saved_comments' => $savedComments,
            'existing_evidences' => $existingEvidences,
            'evidences_by_question' => $evidencesByQuestion,
        ]);
    }

    public function selectQuestionnaire($id)
    {
        $OAI = new \App\Models\Oai_pmh\OaiPmhModel();
        $data['repo'] = $OAI->find($id);

        $method = strtolower($this->request->getMethod());

        if ($method === 'post') {
            $OaiPmhModel = new \App\Models\Oai_pmh\OaiPmhModel();
            $OaiPmhModel->update($id, ['repository_type' => $this->request->getPost('questionario')]);

            // Salvar RepoID na sessão
            session()->set('repo_id', $id);

            return redirect()->to(base_url('application/form/1'));
        }
        return view('application/application_select_questionnaire', $data);
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

        if (($question['tipo_resposta'] ?? '') === 'SN' && ! in_array($answerValue, ['1', '2'], true)) {
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

        return $this->response->setJSON([
            'saved' => true,
            'question_id' => $questionId,
            'resposta' => $answerValue,
            'comentario' => $comment,
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

        $axisQuestions = $questionModel->where('nivel1', $currentAxis)->findAll();
        if ($currentLevel2 !== '') {
            $axisQuestions = array_values(array_filter($axisQuestions, static function (array $question) use ($currentLevel2): bool {
                return trim((string) ($question['nivel2'] ?? '')) === $currentLevel2;
            }));
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

        if (! empty($missingAnswers)) {
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

            if (($question['tipo_resposta'] ?? '') === 'SN') {
                $comment = trim((string) ($postData['comentario_' . $questionId] ?? ''));
                $payload['comentario'] = $comment !== '' ? $comment : null;
            }

            if (! empty($existing['id'])) {
                $answersModel->update((int) $existing['id'], $payload);
            } else {
                $answersModel->insert($payload);
            }
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

        $level2Pages = array_keys($level2Map);
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

        $axes = array_keys($axesMap);
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

        session()->setFlashdata('questionnaire_success', 'Respostas salvas. Questionário finalizado.');
        $returnPath = 'application/form/' . $currentAxis . ($currentLevel2 !== '' ? '/' . $currentLevel2 : '');
        return redirect()->to(base_url($returnPath));
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


        for ($i = 0; $i < 10; $i++) {
            echo '<p>Processando o repositório... (Passo ' . ($i + 1) . ' de 10) -';
            switch ($i) {
                case 0:
                    echo 'Validando a URL (' . $RepoID . ') - <b>' . esc($repoLink) . '</b>...';
                    echo $this->message($OAI->validURL($repoLink));
                    break;
                case 1:
                    echo 'Identificando OAI-PMH do repositório...';
                    echo $this->message($OAI->getIdentifyOAI($RepoID));
                    break;
                case 2:
                    echo 'Validando dados...';
                    break;
                case 3:
                    echo 'Gerando relatórios...';
                    break;
                case 4:
                    echo 'Salvando resultados...';
                    break;
                case 5:
                    echo 'Enviando notificações...';
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
