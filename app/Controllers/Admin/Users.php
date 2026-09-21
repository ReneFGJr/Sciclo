<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RuleModel;
use App\Models\UserRuleModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Users extends BaseController
{
    public function index()
    {
        $model = new UserModel();
        return view('admin/users_list', ['users' => $model->orderBy('name')->paginate(25), 'pager' => $model->pager]);
    }

    public function create() { return $this->form(); }
    public function edit($id) { return $this->form((int) $id); }

    private function user(int $id): array
    {
        return (new UserModel())->find($id) ?? throw PageNotFoundException::forPageNotFound();
    }

    private function form(?int $id = null)
    {
        helper('form');
        $user = $id ? $this->user($id) : ['name' => '', 'email' => ''];
        $errors = [];
        if ($this->request->is('post')) {
            $data = [
                'name' => trim((string) $this->request->getPost('name')),
                'email' => trim((string) $this->request->getPost('email')),
                'password' => (string) $this->request->getPost('password'),
            ];
            $validation = service('validation');
            $validation->setRules([
                'name' => 'required|max_length[100]',
                'email' => 'required|valid_email|max_length[150]|is_unique[users.email,id,' . ($id ?? 0) . ']',
                'password' => ($id ? 'permit_empty' : 'required') . '|min_length[8]|max_length[72]',
            ]);
            if ($validation->run($data)) {
                if ($data['password'] !== '') {
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                } else {
                    unset($data['password']);
                }
                $model = new UserModel();
                if ($id) {
                    $model->update($id, $data);
                } else {
                    $id = (int) $model->insert($data);
                }
                if ($id === (int) session('user_id')) {
                    session()->set('user_name', $data['name']);
                }
                return redirect()->to(base_url('admin/users/edit/' . $id))->with('success', 'Usuário salvo.');
            }
            $errors = $validation->getErrors();
            $user = array_merge($user, ['name' => $data['name'], 'email' => $data['email']]);
        }
        $assignments = $id ? (new UserRuleModel())->select('user_rules.*, rules.name, rules.code')
            ->join('rules', 'rules.id = user_rules.rule_id')->where('user_id', $id)->orderBy('starts_at', 'DESC')->findAll() : [];
        return view('admin/users_form', ['user' => $user, 'errors' => $errors, 'assignments' => $assignments,
            'rules' => (new RuleModel())->orderBy('name')->findAll()]);
    }

    public function delete($id)
    {
        $this->user((int) $id);
        if ((int) $id === (int) session('user_id')) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Você não pode excluir sua própria conta.');
        }
        try {
            (new UserModel())->delete($id);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Não foi possível excluir: o usuário possui registros vinculados.');
        }
        return redirect()->to(base_url('admin/users'))->with('success', 'Usuário excluído.');
    }

    public function assignRule($id)
    {
        $this->user((int) $id);
        $data = [
            'user_id' => (int) $id,
            'rule_id' => $this->request->getPost('rule_id'),
            'starts_at' => $this->request->getPost('starts_at'),
            'ends_at' => $this->request->getPost('ends_at'),
        ];
        $target = base_url('admin/users/edit/' . $id);
        $validation = service('validation');
        $validation->setRules([
            'rule_id' => 'required|is_natural_no_zero|is_not_unique[rules.id]',
            'starts_at' => 'required|valid_date[Y-m-d]',
            'ends_at' => 'required|valid_date[Y-m-d]',
        ]);
        if (!$validation->run($data)) {
            return redirect()->to($target)->withInput()->with('error', implode(' ', $validation->getErrors()));
        }
        if ($data['ends_at'] < $data['starts_at']) {
            return redirect()->to($target)->withInput()->with('error', 'A data de fim deve ser igual ou posterior à data de início.');
        }
        $model = new UserRuleModel();
        $assignmentId = $this->request->getPost('assignment_id');
        if ($assignmentId && (!$assignment = $model->where('user_id', $id)->find($assignmentId))) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($assignmentId && (int) $id === (int) session('user_id')
            && (new RuleModel())->find($assignment['rule_id'])['code'] === 'site-manager') {
            return redirect()->to($target)->with('error', 'Peça a outro administrador para alterar seu perfil de Gerente do site.');
        }
        $overlap = $model->where('user_id', $id)->where('rule_id', $data['rule_id'])
            ->where('starts_at <=', $data['ends_at'])->where('ends_at >=', $data['starts_at']);
        if ($assignmentId) {
            $overlap->where('id !=', $assignmentId);
        }
        if ($overlap->countAllResults()) {
            return redirect()->to($target)->with('error', 'Já existe uma atribuição deste perfil nesse período.');
        }
        $assignmentId ? $model->update($assignmentId, $data) : $model->insert($data);
        return redirect()->to($target)->with('success', 'Perfil e período salvos.');
    }

    public function deleteRule($id, $assignmentId)
    {
        $assignment = (new UserRuleModel())->where('user_id', $id)->find($assignmentId);
        if (!$assignment) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ((int) $id === (int) session('user_id')
            && (new RuleModel())->find($assignment['rule_id'])['code'] === 'site-manager') {
            return redirect()->to(base_url('admin/users/edit/' . $id))->with('error', 'Peça a outro administrador para remover seu perfil de Gerente do site.');
        }
        (new UserRuleModel())->delete($assignmentId);
        return redirect()->to(base_url('admin/users/edit/' . $id))->with('success', 'Atribuição removida.');
    }
}