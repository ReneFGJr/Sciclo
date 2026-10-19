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
require APPPATH . 'Database/Migrations/20261019120000_create_oai_pmh_metadata_table.php';
(new App\Database\Migrations\CreateOaiPmhTable($forge))->up();
$forge->addColumn('oai_pmh', ['base_url_oai' => ['type' => 'TEXT', 'null' => true]]);
(new App\Database\Migrations\CreateOaiPmhMetadataTable($forge))->up();
function expectMetadata(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}
class MetadataFixtureRepository extends App\Models\Oai_pmh\OaiPmhModel {
    public ?string $body = null;
    public string $requestedUrl = '';
    protected function fetchSoftwareMetadata(string $url): ?string {
        $this->requestedUrl = $url;
        return $this->body;
    }
}
$repo = new MetadataFixtureRepository();
$db->table('oai_pmh')->insert(['base_url' => 'https://example.test', 'base_url_oai' => 'https://example.test/server/oai/request?verb=Identify&scope=public']);
$id = (int) $db->insertID();
$format = static fn(string $prefix, string $schema = 'https://example.test/schema.xsd'): string => '<metadataFormat><metadataPrefix>' . $prefix . '</metadataPrefix><schema>' . $schema . '</schema><metadataNamespace>http://example.test/ns</metadataNamespace></metadataFormat>';
$wrap = static fn(string $content): string => '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><ListMetadataFormats>' . $content . '</ListMetadataFormats></OAI-PMH>';
$repo->body = $wrap($format('oai_dc') . $format('mods'));
$result = $repo->collectMetadataFormats($id);
expectMetadata($result['status'] === '200', 'Collection succeeded');
expectMetadata($repo->requestedUrl === 'https://example.test/server/oai/request?scope=public&verb=ListMetadataFormats', 'Correct endpoint and verb');
expectMetadata($db->table('oai_pmh_metadata')->where('oai_pmh_id', $id)->countAllResults() === 2, 'Formats linked to repository');
$repo->body = $wrap($format('oai_dc', 'https://example.test/new.xsd') . $format('mods') . $format('mods'));
expectMetadata($repo->collectMetadataFormats($id)['status'] === '200', 'Revalidation succeeded');
expectMetadata($db->table('oai_pmh_metadata')->countAllResults() === 2, 'No duplicates');
expectMetadata($db->table('oai_pmh_metadata')->where('metadata_prefix', 'oai_dc')->get()->getRowArray()['schema_url'] === 'https://example.test/new.xsd', 'Existing format refreshed');
foreach ([null, '<html>Error</html>', '<broken', $wrap(''), $wrap('<metadataFormat><metadataPrefix>bad</metadataPrefix></metadataFormat>'), '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><error code="badVerb">Invalid</error></OAI-PMH>', '<!DOCTYPE x><OAI-PMH/>'] as $invalid) {
    $repo->body = $invalid;
    expectMetadata($repo->collectMetadataFormats($id)['status'] === '500', 'Invalid responses rejected');
    expectMetadata($db->table('oai_pmh_metadata')->countAllResults() === 2, 'Stored formats preserved on failure');
}
$db->table('oai_pmh')->where('id', $id)->delete();
expectMetadata($db->table('oai_pmh_metadata')->countAllResults() === 0, 'Foreign key cascade');
echo "PASS: metadata collection, XML errors, URL construction, persistence, revalidation and repository relation (SQLite memory).\n";
