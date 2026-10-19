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

$db->query('CREATE TABLE certificacao_questoes (id INTEGER PRIMARY KEY, nivel1 TEXT, nivel2 TEXT)');
$db->query('CREATE TABLE evidencies (id INTEGER PRIMARY KEY AUTOINCREMENT, oai_pmh_id INTEGER, questao_id INTEGER, url TEXT, url_hash TEXT, titulo TEXT, descricao TEXT, created_at TEXT, updated_at TEXT, UNIQUE(oai_pmh_id, questao_id, url_hash))');
$db->table('evidencies')->insert(['oai_pmh_id' => 1, 'questao_id' => 1, 'url' => 'https://example.test/old', 'url_hash' => hash('sha256', 'https://example.test/old'), 'titulo' => 'Existing title']);
$forge = \Config\Database::forge($db);
require APPPATH . 'Database/Migrations/20261019160000_add_nome_to_evidencies.php';
(new App\Database\Migrations\AddNomeToEvidencies($forge))->up();
function expectEvidence(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function saveNamedEvidence(array $post) {
    $request = new CodeIgniter\HTTP\IncomingRequest(config('App'), new CodeIgniter\HTTP\SiteURI(config('App')), null, new CodeIgniter\HTTP\UserAgent());
    \Config\Services::injectMock('request', $request);
    $request->setMethod('POST'); $request->setGlobal('post', $post);
    $controller = new App\Controllers\Application();
    $controller->initController($request, service('response'), service('logger'));
    return $controller->saveEvidence();
}
$model = new App\Models\Question\EvidencyModel();
expectEvidence($model->first()['nome'] === 'Existing title', 'Existing evidence names backfilled');
$db->table('certificacao_questoes')->insertBatch([['id' => 1, 'nivel1' => '1', 'nivel2' => '5'], ['id' => 2, 'nivel1' => '1', 'nivel2' => '5']]);
session()->set('repo_id', 1);
$response = saveNamedEvidence(['questao_id' => 1, 'current_axis' => '1', 'nome' => 'Evidence policy', 'url' => 'https://example.test/policy', 'descricao' => 'Policy description']);
$row = $model->where('url', 'https://example.test/policy')->first();
expectEvidence($row && $row['nome'] === 'Evidence policy', 'Custom name saved');
expectEvidence(str_contains($response->getHeaderLine('Location'), '/application/form/1/5#evidence-' . $row['id']), 'Return to saved evidence');
saveNamedEvidence(['questao_id' => 2, 'evidence_id' => $row['id']]);
$copy = $model->where('questao_id', 2)->first();
expectEvidence($copy['nome'] === 'Evidence policy' && $copy['url'] === $row['url'], 'Reuse retains name and URL');
saveNamedEvidence(['questao_id' => 2, 'edit_id' => $copy['id'], 'nome' => 'Updated evidence', 'url' => $copy['url']]);
expectEvidence($model->find($copy['id'])['nome'] === 'Updated evidence', 'Editing name works');
$count = $model->countAllResults();
saveNamedEvidence(['questao_id' => 2, 'nome' => '', 'url' => 'https://example.test/no-name']);
expectEvidence($model->countAllResults() === $count, 'Name required');
echo "PASS: evidence name migration, saving, reuse, edits, validation and same-group redirect (SQLite memory).\n";
