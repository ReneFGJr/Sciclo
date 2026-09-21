<?php
// Run: php tests/admin_users_smoke.php. No connection to the application MySQL database.
define('FCPATH', dirname(__DIR__) . '/public/');
define('ENVIRONMENT', 'testing');
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootConsole($paths);
date_default_timezone_set('UTC');
// Explicitly replace the default connection before models, validation or migrations run.
$config = config('Database');
$config->defaultGroup = 'tests';
$config->tests = [
    'DSN' => '', 'hostname' => '', 'username' => '', 'password' => '',
    'database' => ':memory:', 'DBDriver' => 'SQLite3', 'DBPrefix' => '',
    'pConnect' => false, 'DBDebug' => true, 'charset' => 'utf8',
    'DBCollat' => '', 'swapPre' => '', 'failover' => [], 'foreignKeys' => true,
    'busyTimeout' => 1000, 'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
];
$db = \Config\Database::connect('tests');
if ($db->DBDriver !== 'SQLite3' || $db->database !== ':memory:') {
    throw new RuntimeException('Refusing to run outside in-memory SQLite.');
}
$forge = \Config\Database::forge($db);
require APPPATH . 'Database/Migrations/20260504180900_create_users_table.php';
require APPPATH . 'Database/Migrations/20260921120000_create_rules_tables.php';
(new App\Database\Migrations\CreateUsersTable($forge))->up();
(new App\Database\Migrations\CreateRulesTables($forge))->up();
function check($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
function controller($class, $method = 'GET', $post = []) {
    $request = new CodeIgniter\HTTP\IncomingRequest(config('App'), new CodeIgniter\HTTP\SiteURI(config('App')), null, new CodeIgniter\HTTP\UserAgent());
    \Config\Services::injectMock('request', $request);
    service('validation')->reset();
    $request->setMethod($method);
    $request->setGlobal('post', $post);
    $controller = new $class();
    $controller->initController($request, service('response'), service('logger'));
    return $controller;
}
$users = new App\Models\UserModel();
$rules = new App\Models\RuleModel();
$links = new App\Models\UserRuleModel();
check($rules->countAllResults() === 4, 'Default profiles');
$admin = $users->insert(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => password_hash('password123', PASSWORD_DEFAULT)]);
$role = $rules->where('code', 'site-manager')->first();
check(!$links->isAdministrator($admin), 'No implicit admin');
$link = $links->insert(['user_id' => $admin, 'rule_id' => $role['id'], 'starts_at' => date('Y-m-d'), 'ends_at' => date('Y-m-d')]);
check($links->isAdministrator($admin), 'Inclusive boundaries');
$links->update($link, ['starts_at' => '2000-01-01', 'ends_at' => '2000-01-02']);
check(!$links->isAdministrator($admin), 'Expired denied');
$links->update($link, ['starts_at' => '2099-01-01', 'ends_at' => '2099-12-31']);
check(!$links->isAdministrator($admin), 'Future denied');
$links->update($link, ['starts_at' => date('Y-m-d'), 'ends_at' => date('Y-m-d')]);
session()->set(['logged_in' => true, 'user_id' => $admin]);
$class = App\Controllers\Admin\Users::class;
controller($class, 'POST', ['name' => 'New', 'email' => 'new@example.test', 'password' => 'password123'])->create();
$user = $users->where('email', 'new@example.test')->first();
check($user && password_verify('password123', $user['password']), 'Create and hash');
controller($class, 'POST', ['name' => 'Edited', 'email' => 'new@example.test', 'password' => ''])->edit($user['id']);
check($users->find($user['id'])['password'] === $user['password'], 'Preserve password');
controller($class, 'POST', ['name' => 'Duplicate', 'email' => 'new@example.test', 'password' => 'password123'])->create();
check($users->where('email', 'new@example.test')->countAllResults() === 1, 'Duplicate rejected');
$filter = new App\Filters\Administrator();
check($filter->before(service('request')) === null, 'Admin allowed');
session()->set('user_id', $user['id']);
check($filter->before(service('request'))->getStatusCode() === 403, 'Regular user denied');
session()->set('user_id', $admin);
$post = ['rule_id' => $role['id'], 'starts_at' => '2026-09-21', 'ends_at' => '2026-09-20'];
controller($class, 'POST', $post)->assignRule($user['id']);
check($links->where('user_id', $user['id'])->countAllResults() === 0, 'Reversed dates rejected');
$post['ends_at'] = '2026-12-31';
controller($class, 'POST', $post)->assignRule($user['id']);
controller($class, 'POST', $post)->assignRule($user['id']);
check($links->where('user_id', $user['id'])->countAllResults() === 1, 'Overlap rejected');
$assignment = $links->where('user_id', $user['id'])->first();
$post['assignment_id'] = $assignment['id'];
$post['ends_at'] = '2027-01-31';
controller($class, 'POST', $post)->assignRule($user['id']);
check($links->find($assignment['id'])['ends_at'] === '2027-01-31', 'Period update');
controller($class, 'POST')->deleteRule($admin, $link);
check($links->find($link) !== null, 'Self revocation denied');
controller($class, 'POST')->delete($admin);
check($users->find($admin) !== null, 'Self deletion denied');
$rc = App\Controllers\Admin\Rules::class;
controller($rc, 'POST', ['name' => 'Renamed admin'])->edit($role['id']);
check($links->isAdministrator($admin), 'Rename retains admin capability');
controller($rc, 'POST')->delete($role['id']);
check($rules->find($role['id']) !== null, 'Admin role protected');
controller($rc, 'POST', ['name' => 'Custom'])->create();
$custom = $rules->where('name', 'Custom')->first();
controller($rc, 'POST')->delete($custom['id']);
check($rules->find($custom['id']) === null, 'Role CRUD');
check(is_string(controller($class)->index()), 'User list renders');
check(is_string(controller($class)->edit($user['id'])), 'User form renders');
foreach (['config', 'index', 'create'] as $method) {
    check(is_string(controller($rc)->$method()), 'Role views render');
}
controller($class, 'POST')->deleteRule($user['id'], $assignment['id']);
check($links->find($assignment['id']) === null, 'Assignment delete');
controller($class, 'POST')->delete($user['id']);
check($users->find($user['id']) === null, 'User delete');
echo "PASS: CRUD, authorization, date boundaries, validation and views (SQLite memory).\n";