<?php
namespace App\Controllers\Admin;

use App\Models\Question\CertificacaoQuestoesModel;
use CodeIgniter\Controller;

class Questions extends Controller

{
    public function index()
    {
        $model = new CertificacaoQuestoesModel();
        $questions = $model->findAll();

        usort($questions, static function (array $a, array $b): int {
            foreach (['criterio', 'nivel1', 'nivel2', 'nivel3'] as $field) {
                $cmp = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        $data['questions'] = $questions;
        return view('admin/questions', $data);
    }

    public function edit($id)
    {
        $model = new CertificacaoQuestoesModel();
        $request = service('request');
        $question = $model->find($id);
        if (!$question) {
            return redirect()->to(base_url('admin/questions'));
        }
        $method = strtolower($request->getMethod());
        if ($method === 'post') {
            $data = [
                'criterio' => $request->getPost('criterio'),
                'nivel1' => $request->getPost('nivel1'),
                'nivel2' => $request->getPost('nivel2'),
                'nivel3' => $request->getPost('nivel3'),
                'questao' => $request->getPost('questao'),
                'tipo_resposta' => $request->getPost('tipo_resposta'),
                'descricao' => $request->getPost('descricao'),
                'icone' => $request->getPost('icone'),
                'imagem' => $request->getPost('imagem'),
                'condicional' => $request->getPost('condicional'),
            ];
            $model->update($id, $data);
            return redirect()->to(base_url('admin/questions'));
        }
        return view('admin/add_question', ['question' => $question]);
    }

    public function delete($id)
    {
        $model = new CertificacaoQuestoesModel();
        $model->delete($id);
        return redirect()->to(base_url('admin/questions'));
    }

    public function add()
    {
        $model = new CertificacaoQuestoesModel();
        $request = service('request');
        $method = strtolower($request->getMethod());
        if ($method === 'post') {
            $data = [
                'criterio' => $request->getPost('criterio'),
                'nivel1' => $request->getPost('nivel1'),
                'nivel2' => $request->getPost('nivel2'),
                'nivel3' => $request->getPost('nivel3'),
                'questao' => $request->getPost('questao'),
                'tipo_resposta' => $request->getPost('tipo_resposta'),
                'descricao' => $request->getPost('descricao'),
                'icone' => $request->getPost('icone'),
                'imagem' => $request->getPost('imagem'),
                'condicional' => $request->getPost('condicional'),
            ];
            $model->insert($data);
            return redirect()->to(base_url('admin/questions'));
        }
        return view('admin/add_question');
    }
}
