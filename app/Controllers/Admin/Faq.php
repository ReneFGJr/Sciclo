<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FaqQuestionModel;

class Faq extends BaseController
{
    public function index()
    {
        $model = new FaqQuestionModel();
        $data['faqs'] = $model->findAll();
        return view('admin/faq_list', $data);
    }

    public function create()
    {
        $model = new FaqQuestionModel();
        $method = strtolower($this->request->getMethod());
        if ($method === 'post') {
            $data = [
                'question' => $this->request->getPost('question'),
                'answer'   => $this->request->getPost('answer'),
                'axis'     => $this->request->getPost('axis'),
            ];
            $model->insert($data);
            return redirect()->to(base_url('/admin/faq'))->with('success', 'Questão adicionada com sucesso!');
        }
        return view('admin/faq_create');
    }
    public function edit($id = null)
    {
        $model = new FaqQuestionModel();
        $faq = $model->find($id);
        if (!$faq) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pergunta não encontrada");
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'question' => $this->request->getPost('question'),
                'answer'   => $this->request->getPost('answer'),
                'axis'     => $this->request->getPost('axis'),
            ];
            $model->update($id, $data);
            return redirect()->to(base_url('/admin/faq'))->with('success', 'Questão atualizada com sucesso!');
        }

        return view('admin/faq_edit', ['faq' => $faq]);
    }
}
