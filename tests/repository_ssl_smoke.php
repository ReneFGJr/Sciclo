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
require APPPATH . 'Database/Migrations/20260504193000_create_oai_pmh_table.php';
require APPPATH . 'Database/Migrations/20261019130000_add_ssl_to_oai_pmh.php';
(new App\Database\Migrations\CreateOaiPmhTable($forge))->up();
(new App\Database\Migrations\AddSslToOaiPmh($forge))->up();
function expectSsl(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}
class FixtureSslValidator extends App\Libraries\RepositorySslValidator {
    public array $response = ['errno' => 0, 'error' => '', 'verify_result' => 0, 'http_status' => 200, 'certificate' => ['issuer' => ['CN' => 'Test CA']]];
    public string $requested = '';
    protected function probe(string $url): array {
        $this->requested = $url;
        return $this->response;
    }
}
$validator = new FixtureSslValidator();
$result = $validator->check('http://example.test:80/repository');
expectSsl($result['valid'] === true && $result['status'] === 'valid', 'Trusted certificate');
expectSsl($validator->requested === 'https://example.test/repository', 'HTTP is tested over HTTPS');
expectSsl($result['certificate']['issuer']['CN'] === 'Test CA', 'Certificate details');
$validator->response['http_status'] = 403;
expectSsl($validator->check('https://example.test')['valid'] === true, 'HTTP errors do not invalidate SSL');
foreach (['certificate has expired', 'self-signed certificate', 'hostname mismatch'] as $error) {
    $validator->response = ['errno' => CURLE_SSL_CACERT, 'error' => $error, 'verify_result' => 10, 'http_status' => 0];
    $result = $validator->check('https://example.test');
    expectSsl($result['valid'] === false && str_contains($result['message'], $error), 'Certificate failure: ' . $error);
}
$validator->response = ['errno' => CURLE_OPERATION_TIMEDOUT, 'error' => 'Timeout', 'verify_result' => -1, 'http_status' => 0];
expectSsl($validator->check('https://example.test')['valid'] === null, 'Timeout is inconclusive');
$validator->response = ['errno' => CURLE_SSL_CACERT_BADFILE, 'error' => 'CA file unavailable', 'verify_result' => 1, 'http_status' => 0];
expectSsl($validator->check('https://example.test')['valid'] === null, 'Local CA error is inconclusive');
expectSsl($validator->check('ftp://example.test')['valid'] === null, 'Invalid scheme');
$db->table('oai_pmh')->insert(['base_url' => 'https://example.test']);
$id = (int) $db->insertID();
$model = new App\Models\Oai_pmh\OaiPmhModel();
$validator->response = ['errno' => 0, 'error' => '', 'verify_result' => 0, 'http_status' => 200];
expectSsl($model->validateRepositorySsl($id, $validator)['status'] === '200', 'Persist SSL result');
$stored = json_decode($model->find($id)['SSL'], true, 512, JSON_THROW_ON_ERROR);
expectSsl($stored['valid'] === true && isset($stored['checked_at']), 'Stored JSON and timestamp');
$validator->response = ['errno' => CURLE_SSL_CACERT, 'error' => 'Expired', 'verify_result' => 10, 'http_status' => 0];
$model->validateRepositorySsl($id, $validator);
expectSsl(json_decode($model->find($id)['SSL'], true)['valid'] === false, 'Revalidation updates stored result');
echo "PASS: SSL results, certificate errors, inconclusive checks and persistence (SQLite memory).\n";
