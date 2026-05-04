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
            // Aqui você pode processar o link do repositório, salvar ou redirecionar
            return view('application/application_harvesting', ['repo_link' => $repoLink]);
        }
        return view('application/application_form', ['repo_link' => $repoLink]);
    }
}
