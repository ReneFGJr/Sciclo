<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        helper(['form']);
        $method = strtolower($this->request->getMethod());
        if ($method === 'post') {
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

    public function profile()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('login'));
        }

        $user = (new UserModel())->select('id, name, email')->find((int) session('user_id'));
        if (!$user) {
            session()->destroy();
            return redirect()->to(site_url('login'));
        }

        $assignments = (new \App\Models\UserRuleModel())
            ->select('rules.name, user_rules.starts_at, user_rules.ends_at')
            ->join('rules', 'rules.id = user_rules.rule_id')
            ->where('user_rules.user_id', $user['id'])
            ->orderBy('user_rules.starts_at', 'DESC')
            ->findAll();
        $today = date('Y-m-d');
        foreach ($assignments as &$assignment) {
            if ($assignment['ends_at'] < $today) {
                $assignment['status'] = 'Expirado';
                $assignment['statusClass'] = 'bg-danger';
            } elseif ($assignment['starts_at'] > $today) {
                $assignment['status'] = 'Ainda não ativo';
                $assignment['statusClass'] = 'bg-secondary';
            } else {
                $assignment['status'] = 'Ativo';
                $assignment['statusClass'] = 'bg-success';
            }
        }
        unset($assignment);

        return view('auth/profile', ['user' => $user, 'assignments' => $assignments]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url());
    }
}
