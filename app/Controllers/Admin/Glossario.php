<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GlossarioModel;

class Glossario extends BaseController
{
    public function index()
    {
        $model = new GlossarioModel();
        $data['items'] = $model->orderBy('termo', 'ASC')->findAll();
        return view('admin/glossario_list', $data);
    }

    public function create()
    {
        $model = new GlossarioModel();

        if (strtolower($this->request->getMethod()) === 'post') {
            $model->insert([
                'termo' => trim((string) $this->request->getPost('termo')),
                'definicao' => trim((string) $this->request->getPost('definicao')),
            ]);

            return redirect()->to(base_url('/admin/glossario'))->with('success', 'Termo adicionado com sucesso!');
        }

        return view('admin/glossario_form', [
            'item' => null,
            'title' => 'Novo termo',
            'action' => base_url('/admin/glossario/create'),
        ]);
    }

    public function edit($id = null)
    {
        $model = new GlossarioModel();
        $item = $model->find($id);

        if (! $item) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Termo não encontrado');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $model->update($id, [
                'termo' => trim((string) $this->request->getPost('termo')),
                'definicao' => trim((string) $this->request->getPost('definicao')),
            ]);

            return redirect()->to(base_url('/admin/glossario'))->with('success', 'Termo atualizado com sucesso!');
        }

        return view('admin/glossario_form', [
            'item' => $item,
            'title' => 'Editar termo',
            'action' => base_url('/admin/glossario/edit/' . $item['id']),
        ]);
    }

    public function delete($id = null)
    {
        $model = new GlossarioModel();
        $model->delete($id);
        return redirect()->to(base_url('/admin/glossario'))->with('success', 'Termo excluído com sucesso!');
    }
}
