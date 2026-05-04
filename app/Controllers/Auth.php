<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        helper(['form']);
        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();
            if ($user && password_verify($password, $user['password'])) {
                session()->set([
                    'user_id' => $user['id'],
                    'user_name' => $user['name'],
                    'logged_in' => true
                ]);
                return redirect()->to('/');
            } else {
                return view('auth/login', ['error' => 'E-mail ou senha inválidos.']);
            }
        }
        return view('auth/login');
    }

    public function register()
    {
        helper(['form']);
        $method = strtolower($this->request->getMethod());
        if ($method === 'post') {
            $userModel = new UserModel();
            $data = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
            ];
            $userModel->insert($data);
            return redirect()->to('/login');
        }
        return view('auth/register');
    }

    public function forgot()
    {
        helper(['form']);
        // Apenas exibe o formulário, lógica de envio de e-mail pode ser implementada depois
        return view('auth/forgot');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
