<?php
// Isolated SQLite test; no application data or outgoing email is used.
ob_start();
require __DIR__ . '/repository_workflow_smoke.php';
$db->query('ALTER TABLE oai_pmh ADD COLUMN workflow_status TEXT');
$db->query('ALTER TABLE oai_pmh ADD COLUMN submitted_by INTEGER');
$db->query('CREATE TABLE certificacao_questoes (id INTEGER PRIMARY KEY, criterio TEXT, nivel1 TEXT, nivel2 TEXT, nivel3 TEXT, questao TEXT, tipo_resposta TEXT, condicional_1 INTEGER DEFAULT 0, condicional_2 INTEGER DEFAULT 0)');
$db->query('CREATE TABLE certificacao_questoes_answer (id INTEGER PRIMARY KEY AUTOINCREMENT, oai_pmh_id INTEGER, questao_id INTEGER, resposta TEXT, comentario TEXT, created_at TEXT, updated_at TEXT)');
$db->query('CREATE TABLE repository_software (id INTEGER PRIMARY KEY, name TEXT)');
$db->query('CREATE TABLE oai_pmh_metadata (id INTEGER PRIMARY KEY, oai_pmh_id INTEGER, metadata_prefix TEXT)');
$db->query('CREATE TABLE repository_movements (id INTEGER PRIMARY KEY, oai_pmh_id INTEGER, to_status TEXT, details TEXT)');
$returned = $repos->where('repository_name', 'Submitted marker')->first();
$locked = $repos->where('repository_name', 'Evaluated marker')->first();
$db->table('oai_pmh')->where('id', $returned['id'])->update(['submitted_by' => $admin, 'workflow_status' => 'revisions_required']);
session()->set(['logged_in' => true, 'user_id' => $admin, 'repo_id' => $locked['id']]);
$html = controller(App\Controllers\Application::class)->selectQuestionnaire($returned['id']);
check(is_string($html) && str_contains($html, 'Continuar preenchimento'), 'Owner can open returned submission');
check((int) session('repo_id') === (int) $returned['id'], 'Opening returned submission replaces stale locked repository in session');
$response = controller(App\Controllers\Application::class, 'POST', ['question_id' => 0])->saveQuestionnaireAnswer();
check($response->getStatusCode() === 422, 'Answer endpoint reaches validation instead of edit denial');
$db->table('certificacao_questoes')->insert(['id' => 1, 'criterio' => '1.1', 'nivel1' => '1', 'nivel2' => '1', 'nivel3' => '1', 'questao' => 'Revision question', 'tipo_resposta' => 'SN']);
$response = controller(App\Controllers\Application::class, 'POST', ['question_id' => 1, 'resposta' => '1'])->saveQuestionnaireAnswer();
check(json_decode($response->getBody(), true)['saved'] === true, 'Owner can save a returned submission answer');
check($db->table('certificacao_questoes_answer')->where('oai_pmh_id', $returned['id'])->countAllResults() === 1
    && $db->table('certificacao_questoes_answer')->where('oai_pmh_id', $locked['id'])->countAllResults() === 0, 'Answer saved only to selected returned repository');
session()->set('user_id', $admin + 1000);
$response = controller(App\Controllers\Application::class)->selectQuestionnaire($returned['id']);
check($response->getStatusCode() === 403, 'Another user cannot edit returned submission');
session()->set(['user_id' => $admin, 'repo_id' => $returned['id']]);
$response = controller(App\Controllers\Application::class)->selectQuestionnaire($locked['id']);
check($response->getStatusCode() === 403 && (int) session('repo_id') === (int) $returned['id'], 'Locked submission cannot replace active editable repository');
ob_end_clean();
echo "PASS: returned submission opens, replaces stale session and permits answer validation; ownership and evaluated lock preserved.\n";
