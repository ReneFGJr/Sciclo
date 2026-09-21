<?php
// The shared smoke bootstrap explicitly uses SQLite :memory: and refuses other databases.
ob_start();
require __DIR__ . '/admin_users_smoke.php';
check($db->DBDriver === 'SQLite3' && $db->database === ':memory:', 'Isolated database required');
require APPPATH . 'Database/Migrations/20260504193000_create_oai_pmh_table.php';
require APPPATH . 'Database/Migrations/20260505124000_add_seal_fields_to_oai_pmh.php';
require APPPATH . 'Database/Migrations/20260921130000_add_repository_submitted_at.php';
require APPPATH . 'Database/Migrations/20260505131000_add_repository_type_to_oai_pmh.php';
(new App\Database\Migrations\CreateOaiPmhTable($forge))->up();
(new App\Database\Migrations\AddRepositoryTypeToOaiPmh($forge))->up();
(new App\Database\Migrations\AddSealFieldsToOaiPmh($forge))->up();
(new App\Database\Migrations\AddRepositorySubmittedAt($forge))->up();
$rows = [
    ['repository_name' => 'Draft marker', 'base_url' => 'https://draft.example.test', 'status' => 1, 'submitted_at' => null, 'seal_data_avaliation' => null],
    ['repository_name' => 'Submitted marker', 'base_url' => 'https://submitted.example.test', 'status' => 1, 'submitted_at' => '2026-09-21 10:00:00', 'seal_data_avaliation' => null],
    ['repository_name' => 'Evaluated marker', 'base_url' => 'https://evaluated.example.test', 'status' => 1, 'submitted_at' => '2026-09-20 10:00:00', 'seal_data_avaliation' => '2026-09-21 10:00:00'],
];
$db->table('oai_pmh')->insertBatch($rows);
$html = controller(App\Controllers\Repositories::class)->index();
foreach (['draft' => 'Draft marker', 'submitted' => 'Submitted marker', 'evaluated' => 'Evaluated marker'] as $key => $name) {
    preg_match('/<section[^>]*aria-labelledby="repositories-' . $key . '".*?<\/section>/s', $html, $section);
    check(isset($section[0]) && str_contains($section[0], $name), 'Correct repository group: ' . $key);
    check(substr_count($html, $name) === 1, 'No duplicated repositories');
}
ob_end_clean();
echo "PASS: repository classification and view rendering (SQLite memory).
";
