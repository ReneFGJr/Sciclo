<?php
ob_start();
require __DIR__ . '/repositories_smoke.php';
require APPPATH . 'Database/Migrations/20260923193000_create_repository_evaluations.php';
$migration = new App\Database\Migrations\CreateRepositoryEvaluations($forge);
$migration->up();
$model = new App\Models\RepositoryEvaluationModel();
$repoId = $db->table('oai_pmh')->get()->getRowArray()['id'];
$payload = ['oai_pmh_id' => $repoId, 'evaluator_id' => $admin, 'assigned_by' => $admin, 'assigned_at' => '2026-09-23 12:00:00'];
$id = $model->insert($payload);
check($id !== false, 'Create evaluation');
$row = $model->find($id);
check($row['status'] === 'assigned' && (int) $row['round'] === 1, 'Defaults');
check($row['viewed_at'] === null && $row['completed_at'] === null, 'Unoccurred dates remain null');
check(!empty($row['created_at']) && !empty($row['updated_at']), 'Audit timestamps');
check($model->update($id, ['status' => 'completed', 'recommendation' => 'approved', 'report' => 'Review complete', 'completed_at' => '2026-09-24 12:00:00']), 'Update evaluation');
check($model->update($id, ['status' => 'invalid']) === false, 'Invalid status rejected');
check($model->insert($payload + ['round' => 2]) !== false, 'New round permitted');
function evaluationRejects(callable $operation, string $message): void {
    $rejected = false;
    try { $rejected = $operation() === false; } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) { $rejected = true; }
    check($rejected, $message);
}
evaluationRejects(fn () => $db->table('repository_evaluations')->insert($payload), 'Duplicate assignment rejected');
evaluationRejects(fn () => $db->table('repository_evaluations')->insert(array_replace($payload, ['evaluator_id' => 999999])), 'Missing user rejected');
evaluationRejects(fn () => $db->table('repository_evaluations')->insert(array_replace($payload, ['oai_pmh_id' => 999999])), 'Missing repository rejected');
evaluationRejects(fn () => $db->table('oai_pmh')->where('id', $repoId)->delete(), 'Repository history preserved');
evaluationRejects(fn () => $db->table('users')->where('id', $admin)->delete(), 'Evaluator history preserved');
$migration->down();
check(!$db->tableExists('repository_evaluations'), 'Migration rollback');
ob_end_clean();
echo "PASS: evaluation CRUD, defaults, validation, unique rounds, foreign keys and rollback (SQLite memory).\n";
