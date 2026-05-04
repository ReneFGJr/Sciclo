<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Application extends Controller
{
    public function index()
    {
        $repoLink = $this->request->getPost('repo_link');
        if ($this->request->getMethod() === 'post' && $repoLink) {
            // Aqui você pode processar o link do repositório, salvar ou redirecionar
            return view('application/application_success', ['repo_link' => $repoLink]);
        }
        // Exibe uma página simples ou redireciona se acessar via GET
        return view('seals/seal_avaliation');
    }
}
