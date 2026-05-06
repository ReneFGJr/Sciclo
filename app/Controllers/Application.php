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
        $data = [];
        $question = new \App\Models\Question\CertificacaoQuestoesModel();
        $data['questions'] = $question->where('nivel1', $c1)->findAll();

        return view('application/application_questionnaire',$data);
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
