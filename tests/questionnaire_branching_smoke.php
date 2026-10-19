<?php
require __DIR__ . '/../app/Libraries/QuestionnaireBranching.php';

use App\Libraries\QuestionnaireBranching;

$questions = [];
foreach (range(1, 6) as $id) {
    $questions[] = ['id' => $id, 'criterio' => 'A', 'nivel1' => '1', 'nivel2' => '1', 'nivel3' => (string) $id, 'tipo_resposta' => 'SN', 'condicional_1' => 0, 'condicional_2' => 0];
}
$questions[0]['condicional_1'] = 4;
$questions[0]['condicional_2'] = 3;
$questions[1]['condicional_2'] = 6;
$check = static function (array $expected, array $actual, string $case): void {
    if ($expected !== $actual) throw new RuntimeException($case . ': ' . json_encode($actual));
};
$check([2, 3], QuestionnaireBranching::skippedIds($questions, [1 => '1', 2 => '2']), 'SIM skips intermediate questions without following skipped branches');
$check([2], QuestionnaireBranching::skippedIds($questions, [1 => '2']), 'NAO destination remains enabled');
$check([], QuestionnaireBranching::skippedIds($questions, []), 'Unanswered questions do not skip');
$questions[0]['condicional_1'] = 0;
$check([], QuestionnaireBranching::skippedIds($questions, [1 => '1']), 'Default sequence');
$questions[0]['condicional_1'] = 4;
$questions[3]['criterio'] = 'B';
$check([], QuestionnaireBranching::skippedIds($questions, [1 => '1']), 'Different criterion ignored');
$questions[3]['criterio'] = 'A';
$questions[2]['tipo_resposta'] = 'INFO';
$check([2], QuestionnaireBranching::skippedIds($questions, [1 => '1']), 'Information is not answered');
$questions[0]['condicional_1'] = 999;
$check([], QuestionnaireBranching::skippedIds($questions, [1 => '1']), 'Missing destination ignored');
$questions[0]['condicional_1'] = 1;
$check([], QuestionnaireBranching::skippedIds($questions, [1 => '1']), 'Backward or self destinations ignored');
echo "Questionnaire branching checks passed.\n";
