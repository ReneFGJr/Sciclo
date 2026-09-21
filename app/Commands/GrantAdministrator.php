<?php
namespace App\Commands;
use App\Models\UserModel;
use App\Models\RuleModel;
use App\Models\UserRuleModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class GrantAdministrator extends BaseCommand
{
    protected $group = 'Admin';
    protected $name = 'admin:grant';
    protected $description = 'Atribui Gerente do site a um usuário existente por um período.';
    protected $usage = 'admin:grant email AAAA-MM-DD AAAA-MM-DD';
    public function run(array $params)
    {
        [$email, $start, $end] = array_pad($params, 3, '');
        $validation = service('validation');
        $validation->setRules(['email' => 'required|valid_email', 'start' => 'required|valid_date[Y-m-d]', 'end' => 'required|valid_date[Y-m-d]']);
        if (!$validation->run(['email' => $email, 'start' => $start, 'end' => $end]) || $end < $start) {
            CLI::error('Informe e-mail e datas válidas; o fim não pode anteceder o início.');
            return EXIT_ERROR;
        }
        $user = (new UserModel())->where('email', $email)->first();
        $rule = (new RuleModel())->where('code', 'site-manager')->first();
        if (!$user || !$rule) {
            CLI::error('Usuário ou perfil não encontrado.');
            return EXIT_ERROR;
        }
        $model = new UserRuleModel();
        if ($model->where('user_id', $user['id'])->where('rule_id', $rule['id'])->where('starts_at <=', $end)->where('ends_at >=', $start)->countAllResults()) {
            CLI::error('Já existe atribuição de administrador nesse período.');
            return EXIT_ERROR;
        }
        $model->insert(['user_id' => $user['id'], 'rule_id' => $rule['id'], 'starts_at' => $start, 'ends_at' => $end]);
        CLI::write('Perfil Gerente do site atribuído.', 'green');
        return EXIT_SUCCESS;
    }
}