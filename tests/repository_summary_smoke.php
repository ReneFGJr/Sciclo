<?php
require __DIR__ . '/../app/Libraries/RepositorySummary.php';

function expectSummary($condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
$groups = [
    '1' => [
        ['id' => 1, 'tipo_resposta' => 'SN'],
        ['id' => 2, 'tipo_resposta' => 'SN'],
        ['id' => 3, 'tipo_resposta' => 'INFO'],
        ['id' => 4, 'tipo_resposta' => 'TEXT'],
        ['id' => 5, 'tipo_resposta' => 'MULTI'],
    ],
    '2' => [
        ['id' => 6, 'tipo_resposta' => 'TEXT'],
        ['id' => 7, 'tipo_resposta' => 'SN'],
        ['id' => 8, 'tipo_resposta' => 'SN'],
        ['id' => 9, 'tipo_resposta' => 'SN'],
        ['id' => 10, 'tipo_resposta' => 'TEXT'],
    ],
];
$answers = [
    1 => ['resposta' => '1', 'comentario' => 'Evidence checked'],
    2 => ['resposta' => '2'],
    3 => ['resposta' => 'info', 'comentario' => 'Excluded'],
    4 => ['resposta' => '0'],
    5 => ['resposta' => ' [] '],
    6 => ['resposta' => '["a","b"]'],
    7 => ['resposta' => ' SIM '],
    8 => ['resposta' => 'Não'],
    10 => ['resposta' => '  ', 'comentario' => '   '],
    999 => ['resposta' => '1'],
];
$summary = App\Libraries\RepositorySummary::build($groups, $answers, [1 => [[], []], 2 => [[]], 3 => [[]], 999 => [[]]]);
foreach (['total' => 9, 'answered' => 6, 'pending' => 3, 'yes' => 2, 'no' => 2, 'other' => 2, 'comments' => 1, 'evidences' => 3, 'withEvidence' => 2] as $key => $expected) {
    expectSummary($summary[$key] === $expected, $key . ' count');
}
expectSummary($summary['completion'] === 66.7, 'Completion denominator excludes INFO');
expectSummary($summary['groups'][1]['completion'] === 75.0, 'Level completion');
expectSummary($summary['yes'] + $summary['no'] + $summary['other'] + $summary['pending'] === $summary['total'], 'Distribution accounts for all questions');
$empty = App\Libraries\RepositorySummary::build([], [], []);
expectSummary($empty['total'] === 0 && $empty['completion'] === 0, 'Empty questionnaire');
$unanswered = App\Libraries\RepositorySummary::build($groups, [], []);
expectSummary($unanswered['pending'] === 9 && $unanswered['answered'] === 0, 'No answers yet');
echo "PASS: summary totals, levels, empty answers, zero values, INFO exclusions and evidence counts.\n";
