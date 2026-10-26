<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EmailTest extends BaseController
{
    public function index()
    {
        $recipient = '';
        $errors = [];
        $diagnostic = null;
        if ($this->request->is('post')) {
            $recipient = trim((string) $this->request->getPost('email'));
            $validation = service('validation');
            $validation->setRules(['email' => [
                'label' => 'Destinatário',
                'rules' => 'required|valid_email|max_length[254]',
                'errors' => ['required' => 'Informe o e-mail do destinatário.', 'valid_email' => 'Informe um e-mail válido.'],
            ]]);
            if ($validation->run(['email' => $recipient])) {
                $mailer = new \App\Libraries\SystemEmail();
                if ($mailer->send($recipient, 'E-mail de teste', 'emails/test', [],
                    "Sciclo — E-mail de teste\n\nOlá! Esta é uma mensagem de teste enviada pela administração do Sciclo para verificar o envio de e-mails da plataforma.\n\nSe você recebeu esta mensagem, o teste de entrega foi concluído. Nenhuma ação é necessária.")) {
                    return redirect()->to(site_url('admin/email_test'))
                        ->with('success', 'E-mail de teste enviado para ' . $recipient . '. Confira também a pasta de spam.');
                }
                $diagnostic = $mailer->getDiagnostic();
                $errors[] = 'Não foi possível enviar o e-mail de teste. Veja os detalhes da falha abaixo.';
            } else {
                $errors = $validation->getErrors();
            }
        }

        return view('admin/email_test', compact('recipient', 'errors', 'diagnostic'));
    }

}
