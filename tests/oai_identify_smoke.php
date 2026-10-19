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
require APPPATH . 'Database/Migrations/20261019140000_add_repository_oai_valid.php';
(new App\Database\Migrations\CreateOaiPmhTable($forge))->up();
$forge->addColumn('oai_pmh', ['base_url_oai' => ['type' => 'TEXT', 'null' => true], 'status' => ['type' => 'INT', 'null' => true]]);
(new App\Database\Migrations\AddRepositoryOaiValid($forge))->up();
function expectIdentify(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}
class FixtureIdentifyRepository extends App\Models\Oai_pmh\OaiPmhModel {
    public array $result = [];
    public function validURLOAI($url) { return $this->result; }
}
$xml = '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><Identify><repositoryName>Test Repository</repositoryName><protocolVersion>2.0</protocolVersion><adminEmail>a@example.test</adminEmail><adminEmail>b@example.test</adminEmail><earliestDatestamp>2001-01-01T00:00:00Z</earliestDatestamp><deletedRecord>persistent</deletedRecord><granularity>YYYY-MM-DDThh:mm:ssZ</granularity><compression>gzip</compression></Identify></OAI-PMH>';
$db->table('oai_pmh')->insert(['base_url' => 'https://example.test', 'base_url_oai' => 'https://example.test/oai?verb=Identify']);
$id = (int) $db->insertID();
$model = new FixtureIdentifyRepository();
expectIdentify(!(bool) $model->find($id)['repository_oai_valid'], 'Default false');
$model->result = ['status' => '200', 'identify_xml' => $xml, 'software' => []];
expectIdentify($model->getIdentifyOAI($id)['status'] === '200', 'Identify succeeds');
$row = $model->find($id);
expectIdentify((bool) $row['repository_oai_valid'], 'Valid flag saved');
foreach (App\Models\Oai_pmh\OaiPmhModel::identifyFields($xml) as $key => $value) {
    expectIdentify($row[$key] === $value, 'Saved ' . $key);
}
expectIdentify($row['admin_email'] === "a@example.test\nb@example.test", 'All admin emails saved');
$model->result = ['status' => '500', 'message' => 'Connection failed'];
$model->getIdentifyOAI($id);
$row = $model->find($id);
expectIdentify(!(bool) $row['repository_oai_valid'], 'Failed revalidation clears flag');
expectIdentify($row['repository_name'] === 'Test Repository', 'Failed collection preserves repository data');
foreach (['<broken', '<OAI-PMH><Identify/></OAI-PMH>', '<OAI-PMH><error code="badVerb">Bad verb</error></OAI-PMH>'] as $invalid) {
    try {
        App\Models\Oai_pmh\OaiPmhModel::identifyFields($invalid);
        throw new LogicException('Invalid XML accepted');
    } catch (RuntimeException $expected) {}
}
echo "PASS: Identify field persistence, default flag, failed revalidation and malformed responses (SQLite memory).\n";
