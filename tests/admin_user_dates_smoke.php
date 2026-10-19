<?php
ob_start();
require __DIR__ . '/admin_users_smoke.php';
$datesUser = $users->insert(['name' => 'Date test user', 'email' => 'dates@example.test', 'password' => password_hash('password123', PASSWORD_DEFAULT)]);
$datesRule = $rules->where('code', 'evaluator')->first();
controller(App\Controllers\Admin\Users::class, 'POST', ['rule_id' => $datesRule['id'], 'starts_at' => '19/10/2026', 'ends_at' => '31/12/2026'])->assignRule($datesUser);
$saved = $links->where('user_id', $datesUser)->first();
check($saved && $saved['starts_at'] === '2026-10-19' && $saved['ends_at'] === '2026-12-31', 'Brazilian dates saved in database format');
$html = controller(App\Controllers\Admin\Users::class)->edit($datesUser);
check(str_contains($html, 'value="' . esc('19/10/2026', 'attr') . '"') && str_contains($html, 'value="' . esc('31/12/2026', 'attr') . '"'), 'Date fields display dd/mm/yyyy');
controller(App\Controllers\Admin\Users::class, 'POST', ['rule_id' => $datesRule['id'], 'starts_at' => '31/02/2027', 'ends_at' => '31/12/2027'])->assignRule($datesUser);
check($links->where('user_id', $datesUser)->countAllResults() === 1, 'Invalid calendar date rejected');
controller(App\Controllers\Admin\Users::class, 'POST', ['rule_id' => $datesRule['id'], 'starts_at' => '20/12/2027', 'ends_at' => '19/12/2027'])->assignRule($datesUser);
check($links->where('user_id', $datesUser)->countAllResults() === 1, 'End before start rejected');
ob_end_clean();
echo "PASS: date display, Brazilian input, database conversion and invalid dates (SQLite memory).\n";
