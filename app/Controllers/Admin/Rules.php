<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\RuleModel;
use App\Models\UserRuleModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Rules extends BaseController
{
    public function config() { return view('admin/config'); }
    public function index()
    {
        return view('admin/rules_list', ['rules' => (new RuleModel())->orderBy('name')->findAll()]);
    }
    public function create() { return $this->form(); }
    public function edit($id) { return $this->form((int) $id); }
    private function form(?int $id = null)
    {
        $model = new RuleModel();
        $rule = $id ? ($model->find($id) ?? throw PageNotFoundException::forPageNotFound()) : ['name' => ''];
        $errors = [];
        if ($this->request->is('post')) {
            $name = trim((string) $this->request->getPost('name'));
            $validation = service('validation');
            $validation->setRules(['name' => 'required|max_length[100]|is_unique[rules.name,id,' . ($id ?? 0) . ']']);
            if ($validation->run(['name' => $name])) {
                if ($id) {
                    $model->update($id, ['name' => $name]);
                } else {
                    $model->insert(['name' => $name, 'code' => 'custom-' . bin2hex(random_bytes(16))]);
                }
                return redirect()->to(base_url('admin/config/rules'))->with('success', 'Perfil salvo.');
            }
            $errors = $validation->getErrors();
            $rule['name'] = $name;
        }
        return view('admin/rules_form', ['rule' => $rule, 'errors' => $errors]);
    }
    public function delete($id)
    {
        $model = new RuleModel();
        $rule = $model->find($id) ?? throw PageNotFoundException::forPageNotFound();
        if ($rule['code'] === 'site-manager' || (new UserRuleModel())->where('rule_id', $id)->countAllResults()) {
            return redirect()->to(base_url('admin/config/rules'))->with('error', 'Não é possível excluir o perfil de administrador ou um perfil atribuído a usuários.');
        }
        try {
            $model->delete($id);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->to(base_url('admin/config/rules'))->with('error', 'O perfil está vinculado a usuários e não pode ser excluído.');
        }
        return redirect()->to(base_url('admin/config/rules'))->with('success', 'Perfil excluído.');
    }
}