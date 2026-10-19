<?php
require __DIR__ . '/../app/Libraries/RepositoryResponseTimeline.php';
use App\Libraries\RepositoryResponseTimeline as Timeline;
function expectTimeline(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
$questions = [];
foreach (range(1, 6) as $id) $questions[] = ['id' => $id, 'tipo_resposta' => 'SN', 'nivel1' => '1', 'nivel2' => $id < 3 ? '1' : '2'];
$answers = [
    ['questao_id' => 3, 'resposta' => '1', 'created_at' => '2026-10-19 10:03:00'],
    ['questao_id' => 2, 'resposta' => '2', 'created_at' => '2026-10-19 10:01:00'],
    ['questao_id' => 1, 'resposta' => '1', 'created_at' => '2026-10-19 10:00:00'],
    ['questao_id' => 4, 'resposta' => '2', 'created_at' => '2026-10-19 10:03:00'],
    ['questao_id' => 5, 'resposta' => '1', 'created_at' => null],
    ['questao_id' => 6, 'resposta' => '1', 'created_at' => '2026-10-19 12:00:00'],
];
$result = Timeline::build($questions, $answers, '2026-10-19 10:05:00');
expectTimeline(array_column(array_column($result['entries'], 'question'), 'id') === [1,2,3,4], 'Chronological order and deterministic ties');
expectTimeline(array_column($result['entries'], 'seconds') === [null,60,120,0], 'Correct intervals including simultaneous saves');
expectTimeline($result['groups']['1.1']['average_seconds'] === 60, 'First answer excluded from group mean');
expectTimeline($result['groups']['1.2']['average_seconds'] === 60, 'Group mean based on intervals');
expectTimeline($result['submission_seconds'] === 120, 'Time from final answer to submission');
expectTimeline($result['missing_dates'] === 1 && $result['after_submission'] === 1, 'Missing dates and later answers excluded');
expectTimeline(Timeline::duration(90061) === '1 d 1 h 1 min 1 s' && Timeline::duration(0) === '0 s', 'Human readable intervals');
expectTimeline(Timeline::build([], [], null)['groups'] === [], 'Empty timeline');
$answers[4]['created_at'] = '0000-00-00 00:00:00';
expectTimeline(Timeline::build($questions, $answers, null)['missing_dates'] === 1, 'Zero dates rejected');
echo "PASS: chronological responses, group averages, simultaneous saves, missing dates and submission boundary.\n";
