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

    private function streamHarvesting(string $repoLink): void
    {
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

        ob_implicit_flush(true);

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
                    echo 'Identificando o repositório (' . $RepoID . ') - <b>' . esc($repoLink) . '</b>...';
                    echo $OAI->getIdentify($repoLink);
                    break;
                case 1:
                    echo 'Coletando metadados...';
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
                default:
                    echo 'Aguardando próxima etapa...';
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
