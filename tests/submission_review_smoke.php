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

$db->query('CREATE TABLE oai_pmh (id INTEGER PRIMARY KEY AUTOINCREMENT, base_url TEXT, submitted_by INTEGER, submitted_at TEXT, status INTEGER DEFAULT 0, repository_oai_valid INTEGER DEFAULT 0, created_at TEXT, repository_name TEXT)');
$db->query('CREATE UNIQUE INDEX base_url ON oai_pmh (base_url)');
$forge = \Config\Database::forge($db);
require APPPATH . 'Database/Migrations/20261019150000_scope_repository_url_to_submitter.php';
(new App\Database\Migrations\ScopeRepositoryUrlToSubmitter($forge))->up();
$db->query('CREATE TABLE certificacao_questoes (id INTEGER PRIMARY KEY, criterio TEXT, nivel1 TEXT, nivel2 TEXT, nivel3 TEXT, questao TEXT, tipo_resposta TEXT, condicional_1 INTEGER DEFAULT 0, condicional_2 INTEGER DEFAULT 0)');
$db->query('CREATE TABLE certificacao_questoes_answer (id INTEGER PRIMARY KEY AUTOINCREMENT, oai_pmh_id INTEGER, questao_id INTEGER, resposta TEXT, comentario TEXT, created_at TEXT, updated_at TEXT)');
function expectReview(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function reviewController(string $method, array $post = []): App\Controllers\Application {
    $request = new CodeIgniter\HTTP\IncomingRequest(config('App'), new CodeIgniter\HTTP\SiteURI(config('App')), null, new CodeIgniter\HTTP\UserAgent());
    \Config\Services::injectMock('request', $request);
    $request->setMethod($method);
    $request->setGlobal('post', $post);
    $controller = new App\Controllers\Application();
    $controller->initController($request, service('response'), service('logger'));
    return $controller;
}
$repo = new App\Models\Oai_pmh\OaiPmhModel();
session()->set(['logged_in' => true, 'user_id' => 1]);
$first = $repo->saveURL('https://example.test');
expectReview($first > 0 && $repo->saveURL('https://example.test') === $first, 'Same user reuses own URL');
session()->set('user_id', 2);
$second = $repo->saveURL('https://example.test');
expectReview($second > 0 && $second !== $first, 'Different user gets separate record');
expectReview((int) $repo->find($first)['submitted_by'] === 1 && (int) $repo->find($second)['submitted_by'] === 2, 'Ownership isolated');
expectReview($repo->saveURL('https://example.test') === $second, 'Second owner reuses own record');
$db->table('certificacao_questoes')->insert(['id' => 1, 'criterio' => '1.1', 'nivel1' => '1', 'nivel2' => '1', 'nivel3' => '1', 'questao' => 'Question', 'tipo_resposta' => 'SN']);
session()->set('repo_id', $second);
$answers = new App\Models\Question\CertificacaoQuestoesAnswerModel();
// Saving the last group must only redirect to review.
$response = reviewController('POST', ['current_axis' => '1', 'current_level2' => '1', 'questao_1' => '1'])->submitQuestionnaire();
expectReview(str_contains($response->getHeaderLine('Location'), 'application/review'), 'Last save goes to review');
expectReview(empty($repo->find($second)['submitted_at']) && (int) $repo->find($second)['status'] !== 2, 'Saving does not submit');
$db->query('CREATE TABLE user_rules (user_id INTEGER, rule_id INTEGER, starts_at TEXT, ends_at TEXT)');
$db->query('CREATE TABLE rules (id INTEGER, code TEXT)');
$html = reviewController('GET')->reviewSubmission();
expectReview(is_string($html) && str_contains($html, 'confirm_submission') && str_contains($html, 'Termo de confirma'), 'Review shows summary and consent');
reviewController('POST')->reviewSubmission();
expectReview(empty($repo->find($second)['submitted_at']), 'Consent required on server');
// A new pending question must prevent confirmation.
$db->table('certificacao_questoes')->insert(['id' => 2, 'criterio' => '1.1', 'nivel1' => '1', 'nivel2' => '1', 'nivel3' => '2', 'tipo_resposta' => 'SN']);
reviewController('POST', ['confirm_submission' => '1'])->reviewSubmission();
expectReview(empty($repo->find($second)['submitted_at']), 'Pending answers block sending');
$answers->insert(['oai_pmh_id' => $second, 'questao_id' => 2, 'resposta' => '2']);
reviewController('POST', ['confirm_submission' => '1'])->reviewSubmission();
$row = $repo->find($second);
expectReview((int) $row['status'] === 2 && !empty($row['submitted_at']), 'Confirmation sets status 2 and timestamp');
$timestamp = $row['submitted_at'];
reviewController('POST', ['confirm_submission' => '1'])->reviewSubmission();
expectReview($repo->find($second)['submitted_at'] === $timestamp, 'Repeated confirmation preserves timestamp');
session()->set('user_id', 1);
expectReview(reviewController('POST', ['confirm_submission' => '1'])->reviewSubmission()->getStatusCode() === 403, 'Different owner cannot submit');
echo "PASS: per-user URLs, final save, consent, pending answers, submission status and ownership (SQLite memory).\n";
