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

$db->query('CREATE TABLE oai_pmh (id INTEGER PRIMARY KEY, repository_oai_valid INTEGER NOT NULL DEFAULT 0)');
$db->query('CREATE TABLE certificacao_questoes (id INTEGER PRIMARY KEY, tipo_resposta TEXT)');
$db->query('CREATE TABLE certificacao_questoes_answer (id INTEGER PRIMARY KEY AUTOINCREMENT, oai_pmh_id INTEGER, questao_id INTEGER, resposta TEXT, comentario TEXT, created_at TEXT, updated_at TEXT, UNIQUE(oai_pmh_id, questao_id))');
$db->table('oai_pmh')->insertBatch([['id' => 1, 'repository_oai_valid' => 0], ['id' => 2, 'repository_oai_valid' => 1]]);
$db->table('certificacao_questoes')->insertBatch([['id' => 77, 'tipo_resposta' => 'SOFTWARE'], ['id' => 78, 'tipo_resposta' => 'SN']]);
$answers = new App\Models\Question\CertificacaoQuestoesAnswerModel();
function expectSoftwareAnswer(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}
expectSoftwareAnswer($answers->saveSoftwareValidatedAnswers(1) === [], 'Invalid OAI leaves manual answering available');
expectSoftwareAnswer($answers->countAllResults() === 0, 'No automatic answer when invalid');
expectSoftwareAnswer($answers->saveSoftwareValidatedAnswers(2) === [77], 'Only SOFTWARE questions auto answered');
$row = $answers->where('oai_pmh_id', 2)->where('questao_id', 77)->first();
expectSoftwareAnswer($row['resposta'] === '1', 'Automatic SIM persisted');
$answers->update($row['id'], ['resposta' => '2', 'comentario' => 'Keep comment']);
$answers->saveSoftwareValidatedAnswers(2);
$row = $answers->find($row['id']);
expectSoftwareAnswer($row['resposta'] === '1' && $row['comentario'] === 'Keep comment', 'Valid OAI enforces SIM preserving comment');
expectSoftwareAnswer($answers->countAllResults() === 1, 'Repeated synchronization creates no duplicates');
$answers->insert(['oai_pmh_id' => 1, 'questao_id' => 77, 'resposta' => '2']);
$answers->saveSoftwareValidatedAnswers(1);
expectSoftwareAnswer($answers->where('oai_pmh_id', 1)->first()['resposta'] === '2', 'Manual NAO retained when invalid');
$question = ['id' => 77, 'tipo_resposta' => 'SOFTWARE'];
$summary = App\Libraries\RepositorySummary::build([[$question]], [77 => ['resposta' => '1']], []);
expectSoftwareAnswer($summary['yes'] === 1 && $summary['completion'] === 100.0, 'Software SIM counts in progress');
echo "PASS: software answers, OAI flag, manual fallback, comments and progress (SQLite memory).\n";
