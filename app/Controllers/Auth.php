<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

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
                'email' => trim((string) $this->request->getPost('email')),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
            ];
            $duplicateEmail = [
                'error' => 'Este e-mail já está cadastrado.',
                'name' => $data['name'],
                'email' => $data['email'],
            ];
            if ($userModel->where('email', $data['email'])->first()) {
                return view('auth/register', $duplicateEmail);
            }

            try {
                $userModel->insert($data);
            } catch (DatabaseException $exception) {
                // Handle simultaneous registrations for the same e-mail.
                if ((int) $exception->getCode() === 1062
                    && $userModel->where('email', $data['email'])->first()) {
                    return view('auth/register', $duplicateEmail);
                }

                throw $exception;
            }
            return redirect()->to(site_url('login'));
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

        $repositoryModel = new \App\Models\Oai_pmh\OaiPmhModel();
        $repositoryModel->registerSubmitter((int) session('repo_id'), (int) $user['id']);
        $repositories = $repositoryModel->where('submitted_by', $user['id'])
            ->orderBy('id', 'DESC')->findAll();
        if ($repositories) {
            $questions = (new \App\Models\Question\CertificacaoQuestoesModel())->findAll();
            $ids = array_column($repositories, 'id');
            $answersByRepository = [];
            foreach ((new \App\Models\Question\CertificacaoQuestoesAnswerModel())->whereIn('oai_pmh_id', $ids)->findAll() as $answer) {
                $answersByRepository[$answer['oai_pmh_id']][(int) $answer['questao_id']] = $answer;
            }
            $evaluationsByRepository = [];
            foreach ((new \App\Models\RepositoryEvaluationModel())->whereIn('oai_pmh_id', $ids)
                ->orderBy('round', 'DESC')->findAll() as $evaluation) {
                $evaluationsByRepository[$evaluation['oai_pmh_id']][] = $evaluation;
            }
            foreach ($repositories as &$repository) {
                $repository['summary'] = \App\Libraries\RepositorySummary::build(
                    ['all' => $questions], $answersByRepository[$repository['id']] ?? [], []
                );
                $repository['evaluationStatus'] = 'Ainda não enviada para avaliação';
                $repository['evaluationClass'] = 'bg-secondary';
                if (!empty($repository['submitted_at'])) {
                    $repository['evaluationStatus'] = 'Aguardando avaliação';
                    $repository['evaluationClass'] = 'bg-primary';
                }
                $evaluations = $evaluationsByRepository[$repository['id']] ?? [];
                if ($evaluations) {
                    $latestRound = $evaluations[0]['round'];
                    $statuses = array_column(array_filter($evaluations, static fn ($item) => $item['round'] === $latestRound), 'status');
                    if (in_array('in_progress', $statuses, true) || in_array('accepted', $statuses, true)) {
                        $repository['evaluationStatus'] = 'Avaliação em andamento';
                        $repository['evaluationClass'] = 'bg-warning text-dark';
                    } elseif (count(array_filter($statuses, static fn ($status) => $status === 'completed')) === count($statuses)) {
                        $repository['evaluationStatus'] = 'Avaliação concluída';
                        $repository['evaluationClass'] = 'bg-success';
                    } else {
                        $repository['evaluationStatus'] = 'Aguardando avaliação';
                        $repository['evaluationClass'] = 'bg-primary';
                    }
                }
                if (!empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00') {
                    $repository['evaluationStatus'] = 'Avaliação concluída';
                    $repository['evaluationClass'] = 'bg-success';
                }
            }
            unset($repository);
        }

        return view('auth/profile', ['user' => $user, 'assignments' => $assignments, 'repositories' => $repositories]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url());
    }
}
