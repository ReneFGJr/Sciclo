<?php
namespace App\Filters;
use App\Models\UserRuleModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
class Administrator implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session('logged_in')) {
            return redirect()->to(base_url('login'));
        }
        if (!(new UserRuleModel())->isAdministrator((int) session('user_id'))) {
            return service('response')->setStatusCode(403)->setBody('Acesso restrito ao administrador.');
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}